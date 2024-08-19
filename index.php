<?php
// Function to download an image from a URL and return its size
function download_image($image_url, $save_to) {
  $image_data = file_get_contents($image_url);
  if ($image_data === FALSE) {
    return [false, 0];
  }

  $result = file_put_contents($save_to, $image_data);
  if ($result === FALSE) {
    return [false, 0];
  }

  $image_size = filesize($save_to);
  $image_size_kb = round($image_size / 1024, 2);

  return [true, $image_size_kb];
}

// Function to rename a file based on the order
function rename_file($file_path, $order) {
  $directory = dirname($file_path);
  $extension = pathinfo($file_path, PATHINFO_EXTENSION);
  $new_name = $directory . DIRECTORY_SEPARATOR . "image_$order.$extension";

  if (rename($file_path, $new_name)) {
    return $new_name;
  } else {
    return $file_path;
  }
}

// Function to fetch and process images and their children
function process_images($api_url, $base_url, $save_dir, $rename_files) {
  $total_size = 0;
  $image_count = 0;
  $output = [];

  $response = file_get_contents($api_url);
  if ($response === FALSE) {
    return;
  }

  $data = json_decode($response, true);
  if ($data === NULL) {
    return;
  }

  if (isset($data['image'])) {
    $image_urls = [$base_url . $data['image']];
    if (isset($data['image_child'])) {
      foreach ($data['image_child'] as $child_path) {
        $image_urls[] = $base_url . $child_path;
      }
    }

    // Define column widths
    $index_width = 4;  // width for index
    $url_width = 60;   // width for URL
    $size_width = 10;  // width for size

    foreach ($image_urls as $index => $image_url) {
      $filename = basename($image_url);
      $save_to = $save_dir . DIRECTORY_SEPARATOR . $filename;

      list($success, $size_kb) = download_image($image_url, $save_to);
      if ($success) {
        $total_size += $size_kb * 1024;
        $image_count++;
        $output[] = sprintf(
          "%-" . $index_width . "d  %-".$url_width."s\n      successfully downloaded <a href='%s' target='_blank'>%s</a> (Size: %".$size_width."s KB)",
          $index + 1,
          $image_url,
          $save_to,
          $save_to,
          number_format($size_kb, 2)
        );

        if ($rename_files === 'y') {
          $new_file_path = rename_file($save_to, $index + 1);
          $output[] = sprintf(
            "      renamed to <a href='%s' target='_blank'>%s</a>",
            $new_file_path,
            $new_file_path
          );
        }

        $output[] = "";
      }
    }
  } else {
    $output[] = "Error: No image data found in API response.";
  }

  $output[] = sprintf(
    "\nTotal images downloaded: %d\nTotal size: %.2f KB",
    $image_count,
    round($total_size / 1024, 2)
  );

  return implode("\n", $output);
}

// Read base URL from config.txt
function get_base_url() {
  $config_file = __DIR__ . DIRECTORY_SEPARATOR . 'config.txt';

  if (!file_exists($config_file)) {
    return null; // Indicate that config.txt does not exist
  }

  $base_url = trim(file_get_contents($config_file));
  return empty($base_url) ? null : $base_url;
}

// Handle the form submission for setting base URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['baseUrl'])) {
  $base_url = trim($_POST['baseUrl']);
  if (preg_match('/^https?:\/\/[^\s\/$.?#].[^\s]*$/i', $base_url)) {
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.txt', $base_url);
    header('Location: ' . $_SERVER['PHP_SELF']); // Refresh to clear the form
    exit;
  } else {
    echo '<p style="color: red;">Invalid URL. Please use http or https.</p>';
  }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artworkId'])) {
  $artworkId = intval($_POST['artworkId']);
  $save_dir = $_POST['saveDir'];
  $rename_files = $_POST['renameFiles'];

  if (!file_exists($save_dir)) {
    if (!mkdir($save_dir, 0755, true)) {
      echo json_encode(['error' => "Unable to create directory $save_dir."]);
      exit;
    }
  }

  $base_url = get_base_url();
  if ($base_url === null) {
    // Redirect to the form if config.txt is not properly set
    echo '<p>Please set the base URL first.</p>';
    exit;
  }

  $api_url = $base_url . "/artwork_id_api.php?artworkid=$artworkId";

  $output = process_images($api_url, $base_url, $save_dir, $rename_files);

  echo json_encode(['output' => $output]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODEUtility</title>
    <link rel="icon" type="image/png" href="<?php include('config.txt'); ?>/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      #output {
        white-space: pre-wrap;
        font-family: monospace;
        padding: 15px;
        margin-top: 20px;
        word-break: break-word;
      }
    </style>
  </head>
  <body>
    <div class="container my-5">
      <?php if (get_base_url() === null): ?>
        <h1 class="fw-bold text-center">Set Base URL</h1>
        <form method="post">
          <label class="form-label mt-3" for="baseUrl">Base URL (must use http or https):</label>
          <input class="form-control w-100" type="text" id="baseUrl" name="baseUrl" required>
          <button type="submit" class="btn btn-primary mt-3">Set URL</button>
        </form>
      <?php else: ?>
        <h1 class="fw-bold text-center">ArtCODEUtility</h1>
        <form id="downloadForm">
          <label class="form-label mt-3" for="artworkId">Artwork ID:</label>
          <input class="form-control w-100" type="number" id="artworkId" name="artworkId" required>

          <label class="form-label mt-3" for="saveDir">Save Directory:</label>
          <input class="form-control w-100" type="text" id="saveDir" name="saveDir" required>

          <label class="form-label mt-3" for="renameFiles">Rename files?</label>
          <select class="form-select w-100" id="renameFiles" name="renameFiles">
            <option value="n">No</option>
            <option value="y">Yes</option>
          </select>

          <button type="submit" class="btn btn-success small fw-medium w-100 mt-3">Download</button>
        </form>

        <div id="output"></div>
      <?php endif; ?>
    </div>

    <script>
      document.getElementById('downloadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const outputDiv = document.getElementById('output');
        outputDiv.innerHTML = 'Starting download...';

        fetch('', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            outputDiv.innerHTML = `<span style="color: red;">${data.error}</span>`;
          } else {
            outputDiv.innerHTML = data.output.replace(/\n/g, '<br>');
          }
        })
        .catch(error => {
          outputDiv.innerHTML = `<span style="color: red;">Error: ${error.message}</span>`;
        });
      });
    </script>
  </body>
</html>