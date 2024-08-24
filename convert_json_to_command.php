<?php
// Handle the AJAX request for generating commands
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

    // Initialize commands array
    $commands = [];

    // Create folder command if folder path is specified
    if ($folder) {
      $commands[] = "mkdir -p $folder";
    }

    // Base command for main image
    $base_command = $rename ? "$weburl$image --download --output $folder/1.jpg" : "$weburl$image --download";
    $commands[] = "http get $base_command";

    // Add child images with optional renaming
    $counter = 2;
    foreach ($image_child as $child_image) {
      $output_file = $rename ? "$folder/$counter.jpg" : "";
      $commands[] = "http get $weburl$child_image --download" . ($rename ? " --output $output_file" : "");
      $counter++;
    }

    // Join commands into a single string and add an extra newline
    $commands_output = implode("\n", $commands) . "\n";
  } else {
    $commands_output = 'Invalid JSON or missing keys.';
  }

  // Output the generated commands as JSON
  echo json_encode(['commands' => $commands_output]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <title>PHP JSON Formatter</title>
    <title>ArtCODEUtility</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function() {
        $('form').on('submit', function(event) {
          event.preventDefault(); // Prevent default form submission

          $.ajax({
            type: 'POST',
            url: '', // Submit to the same file
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
              // Update the textarea with the generated commands
              $('textarea[readonly]').val(response.commands);
            },
            error: function() {
              alert('An error occurred while processing your request.');
            }
          });
        });

        // Copy output to clipboard
        $('#copyButton').on('click', function() {
          const textarea = $('textarea[readonly]')[0];
          textarea.select();
          document.execCommand('copy');
          alert('Commands copied to clipboard!');
        });
      });
    </script>
  </head>
  <body>
    <div class="container my-5">
      <h1>Generate HTTPie Commands</h1>
      <form method="post">
        <textarea name="json" rows="10" cols="50" class="mb-2 form-control w-100 rounded border-0 bg-body-tertiary" placeholder='Paste your JSON here'></textarea>
        <input type="text" class="mb-2 form-control w-100 rounded border-0 bg-body-tertiary" name="weburl" placeholder="Enter base URL (e.g., http://example.com)">
        <label><input type="checkbox" name="rename" class="mb-3 form-check-input"> Rename files sequentially (1.jpg, 2.jpg, etc.)</label>
        <input type="text" class="mb-2 form-control w-100 rounded border-0 bg-body-tertiary" name="folder" placeholder="Enter folder path (optional)">
        <input type="submit" value="Generate Commands" class="btn btn-secondary fw-medium">
      </form>
    
      <h2 class="mt-4">Generated HTTPie Commands:</h2>
      <textarea rows="10" cols="50" class="form-control w-100 rounded border-0 bg-body-tertiary" readonly></textarea>
      <button id="copyButton" class="btn btn-primary mt-2">Copy Output</button>

      <h3 class="mt-5">HTTPie Installation Instructions:</h3>
      <p>To execute the generated commands, you need to have HTTPie installed. Here are the instructions to install HTTPie on various operating systems:</p>
      <ul>
        <li><strong>Windows:</strong> Open Command Prompt or PowerShell and run:
          <pre>pip install httpie</pre>
        </li>
        <li><strong>Linux:</strong> Open Terminal and run:
          <pre>sudo apt install httpie</pre>
          <em>or</em>
          <pre>pip install httpie</pre>
        </li>
        <li><strong>macOS:</strong> Open Terminal and run:
          <pre>brew install httpie</pre>
          <em>or</em>
          <pre>pip install httpie</pre>
        </li>
      </ul>
    </div>
  </body>
</html>
