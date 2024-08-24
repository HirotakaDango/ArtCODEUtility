<?php
// Function to download an image from a URL and return its size
function download_image($image_url, $save_to) {
  // Fetch the image data
  $image_data = file_get_contents($image_url);
  if ($image_data === FALSE) {
    echo "Error: Unable to fetch the image from $image_url.\n";
    return [false, 0];
  }

  // Save the image data to a file
  $result = file_put_contents($save_to, $image_data);
  if ($result === FALSE) {
    echo "Error: Unable to save the image to $save_to.\n";
    return [false, 0];
  }

  // Get the size of the downloaded image
  $image_size = filesize($save_to);
  $image_size_kb = round($image_size / 1024, 2); // Convert bytes to kilobytes

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
    echo "Error: Unable to rename file $file_path.\n";
    return $file_path;
  }
}

// Function to fetch and process images and their children
function process_images($api_url, $base_url, $save_dir, $rename_files) {
  $total_size = 0; // Initialize total size
  $image_count = 0; // Initialize image count

  // Fetch the list of images from the API
  $response = file_get_contents($api_url);
  if ($response === FALSE) {
    echo "Error: Unable to fetch data from API.\n";
    return;
  }

  // Decode the JSON response
  $data = json_decode($response, true);
  if ($data === NULL) {
    echo "Error: Invalid JSON response from API.\n";
    return;
  }

  // Process the main image
  if (isset($data['image'])) {
    $image_urls = [$base_url . $data['image']];
    if (isset($data['image_child'])) {
      foreach ($data['image_child'] as $child_path) {
        $image_urls[] = $base_url . $child_path;
      }
    }

    // Loop through image URLs and download
    foreach ($image_urls as $index => $image_url) {
      $filename = basename($image_url);
      $save_to = $save_dir . DIRECTORY_SEPARATOR . $filename;

      // Download the image and get size
      list($success, $size_kb) = download_image($image_url, $save_to);
      if ($success) {
        $total_size += $size_kb * 1024; // Convert KB back to bytes for total size
        $image_count++;
        echo ($index + 1) . " downloaded from $image_url\n";
        echo "   successfully downloaded $save_to (Size: $size_kb KB)\n";

        // Rename file if option is selected
        if ($rename_files === 'y') {
          $new_file_path = rename_file($save_to, $index + 1);
          echo "   renamed to $new_file_path\n";
        }

        echo "\n";
      }
    }
  } else {
    echo "Error: No image data found in API response.\n";
  }

  // Output total number of images and total size
  echo "\nTotal images downloaded: $image_count\n";
  echo "Total size: " . round($total_size / 1024, 2) . " KB\n";
}

// Read base URL from config.txt
function get_base_url() {
  $config_file = __DIR__ . DIRECTORY_SEPARATOR . 'config.txt';
  
  // Check if config.txt exists
  if (!file_exists($config_file)) {
    // Create config.txt and prompt for URL
    $handle = fopen($config_file, 'w');
    if ($handle === false) {
      echo "Error: Unable to create config.txt.\n";
      exit(1);
    }

    // Prompt for a valid URL
    do {
      echo "config.txt not found. Please enter the base URL (must use http:// or https://): ";
      $base_url = trim(fgets(STDIN));

      // Validate URL
      if (preg_match('/^https?:\/\/[^\s\/$.?#].[^\s]*$/i', $base_url)) {
        fwrite($handle, $base_url);
        fclose($handle);
        break;
      } else {
        echo "Invalid URL. Please ensure it starts with http:// or https://.\n";
      }
    } while (true);
    
    return $base_url;
  } else {
    $base_url = trim(file_get_contents($config_file));
    if (empty($base_url)) {
      echo "Error: Base URL is empty in config.txt.\n";
      exit(1);
    }
    return $base_url;
  }
}

// Main execution
if ($argc < 2) {
  echo "Usage: php install.php <artworkid>\n";
  exit(1);
}

// Prompt for folder to save images
echo "Enter the directory to save images (will be created if it does not exist): ";
$save_dir = trim(fgets(STDIN));

// Create directory if it does not exist
if (!file_exists($save_dir)) {
  if (!mkdir($save_dir, 0755, true)) {
    echo "Error: Unable to create directory $save_dir.\n";
    exit(1);
  }
}

// Prompt for renaming files
echo "Would you like to rename files based on the order? [y/n]: ";
$rename_files = trim(fgets(STDIN));

// Validate renaming option
if (!in_array($rename_files, ['y', 'n'])) {
  echo "Error: Invalid option for renaming files.\n";
  exit(1);
}

$artworkId = intval($argv[1]);
$base_url = get_base_url();
$api_url = $base_url . "/artwork_id_api.php?artworkid=$artworkId";
process_images($api_url, $base_url, $save_dir, $rename_files);
?>
