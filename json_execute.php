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

// Handle the form submission for downloading images
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['json']) && isset($_POST['weburl'])) {
  $json = $_POST['json'];
  $weburl = $_POST['weburl'];
  $rename = isset($_POST['rename']) ? true : false; // Check if rename is selected
  $folder = isset($_POST['folder']) ? $_POST['folder'] : ''; // Get folder path

  // Decode JSON input
  $data = json_decode($json, true);

  if (json_last_error() === JSON_ERROR_NONE && isset($data['image']) && isset($data['image_child'])) {
    // Extract file paths from JSON
    $image = $data['image'];
    $image_child = $data['image_child'];

    // Initialize output array
    $output = [];

    // Create folder if specified
    if ($folder && !file_exists($folder)) {
      if (!mkdir($folder, 0755, true)) {
        echo json_encode(['error' => "Unable to create directory $folder."]);
        exit;
      }
    }

    // Download and process the main image
    $image_url = $weburl . $image;
    $filename = basename($image_url);
    $save_to = $folder ? $folder . DIRECTORY_SEPARATOR . $filename : $filename;

    list($success, $size_kb) = download_image($image_url, $save_to);
    if ($success) {
      $output[] = "Successfully downloaded: <a href='$save_to' target='_blank'>$save_to</a> (Size: " . number_format($size_kb, 2) . " KB)";
      if ($rename) {
        $new_file_path = rename_file($save_to, 1);
        $output[] = "Renamed to: <a href='$new_file_path' target='_blank'>$new_file_path</a>";
      }
    } else {
      $output[] = "Failed to download: $image_url";
    }

    // Download and process child images
    $counter = 2;
    foreach ($image_child as $child_image) {
      $child_url = $weburl . $child_image;
      $child_filename = basename($child_url);
      $save_to = $folder ? $folder . DIRECTORY_SEPARATOR . $child_filename : $child_filename;

      list($success, $size_kb) = download_image($child_url, $save_to);
      if ($success) {
        $output[] = "Successfully downloaded: <a href='$save_to' target='_blank'>$save_to</a> (Size: " . number_format($size_kb, 2) . " KB)";
        if ($rename) {
          $new_file_path = rename_file($save_to, $counter);
          $output[] = "Renamed to: <a href='$new_file_path' target='_blank'>$new_file_path</a>";
        }
        $counter++;
      } else {
        $output[] = "Failed to download: $child_url";
      }
    }

    // Output the result
    echo json_encode(['output' => implode("<br>", $output)]);
    exit;
  } else {
    echo json_encode(['error' => 'Invalid JSON or missing keys.']);
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCODEUtility</title>
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
      <h1 class="fw-bold text-center">ArtCODEUtility</h1>
      <form id="downloadForm">
        <textarea name="json" rows="10" cols="50" class="mb-2 mt-3 form-control w-100 rounded border-0 bg-body-tertiary" placeholder='Paste your JSON here' required></textarea>
        <input type="text" class="mb-2 form-control w-100 rounded border-0 bg-body-tertiary" name="weburl" placeholder="Enter base URL (e.g., http://example.com)" required>
        <label><input type="checkbox" name="rename" class="mb-3 form-check-input"> Rename files sequentially (1.jpg, 2.jpg, etc.)</label>
        <input type="text" class="mb-2 form-control w-100 rounded border-0 bg-body-tertiary" name="folder" placeholder="Enter folder path (optional)">
        <button type="submit" class="btn btn-success small fw-medium w-100 mt-3">Download Images</button>
      </form>

      <div id="output" class="mt-4"></div>
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
            outputDiv.innerHTML = data.output;
          }
        })
        .catch(error => {
          outputDiv.innerHTML = `<span style="color: red;">Error: ${error.message}</span>`;
        });
      });
    </script>
  </body>
</html>
