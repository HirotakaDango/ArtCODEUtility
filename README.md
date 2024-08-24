# ArtCODEUtility

**ArtCODEUtility** is a PHP-based utility designed for downloading images from a specified API and managing them efficiently. This tool allows you to download images, optionally rename them based on their download order, handle complex JSON-based image structures, and provides detailed feedback on the download process.

## Features

- **Download Images**: Fetch and save images from a specified API endpoint or URLs provided via JSON.
- **Rename Files**: Optionally rename files based on their order of download.
- **Create ZIP Archives**: Archive downloaded images into a ZIP file.
- **Detailed Feedback**: Displays download progress, summary information, and handles complex image structures (e.g., parent-child relationships).
- **Shell Command Generation**: Convert JSON data to shell commands for image processing.

### 1. **shell_execute.php**
   - **`download_image($image_url, $save_to)`**: Fetches and saves the image from a URL, returning the size.
   - **`rename_file($file_path, $order)`**: Renames files based on download order.
   - **`process_images($api_url, $base_url, $save_dir, $rename_files)`**: Processes images, handles downloading, and renaming.
   - **`print_loading_bar($current, $total)`**: Displays a progress bar during downloads.
   - **`get_base_url()`**: Fetches base URL from configuration.

### 2. **index.php**
   - Similar core functions (`download_image`, `rename_file`, `process_images`) as found in `shell_execute.php`, focusing on downloading and managing images.
   - **Extended Processing**: Handles parent-child image relationships within the processing logic.

### 3. **convert_json_to_command.php**
   - Converts JSON data to shell commands for image processing.
   - Handles input via POST request, parsing JSON to generate appropriate shell commands for downloading and renaming images.
   - Supports folder creation and command execution for image management.

### 4. **json_execute.php**
   - **`create_zip($folder, $zip_name)`**: Creates a ZIP archive of the downloaded images.
   - Similar functions for downloading, renaming images, and creating a ZIP file.
   - **POST Handling**: Accepts JSON input and processes image downloading and archiving based on JSON data.

## Prerequisites

- PHP (version 7.0 or higher recommended)
- Command-line access to run PHP scripts
- Access to an API endpoint providing image URLs
- cURL support enabled in PHP

## Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/HirotakaDango/ArtCODEUtility.git
   cd ArtCODEUtility
   ```

2. **Prepare Configuration**:
   - Create a `config.txt` file in the root directory of the repository.
   - Add the base URL of your API endpoint to `config.txt`.

3. **Run the Script**:
   You can run the different scripts depending on your use case:
   - **shell.php**: For simple image downloads.
   - **index.php**: For handling parent-child image relationships.
   - **convert_json_to_command.php**: For converting JSON to shell commands.
   - **json_execute.php**: For JSON-based image processing and ZIP creation.

## Usage

### Running the Script

1. **Provide Directory**:
   The script will prompt you to enter the directory where images should be saved. If the directory does not exist, it will be created.

2. **Rename Files Option**:
   You will be asked whether you want to rename files based on their download order. Enter `y` for yes or `n` for no.

3. **Output**:
   The script will display:
   - The URL of the image being downloaded.
   - The path where the image is saved.
   - The size of each downloaded image.
   - A summary of total images downloaded and total size.
   - In case of `json_execute.php`, it will also create a ZIP archive of the downloaded images.

### Example

```bash
Enter the directory to save images (will be created if it does not exist): /path/to/save/images
Would you like to rename files based on the order? [y/n]: y

1 downloaded from http://example.com/images/filename1.ext
   successfully downloaded /path/to/save/images/image_1.ext (Size: 150.75 KB)

2 downloaded from http://example.com/images/filename2.ext
   successfully downloaded /path/to/save/images/image_2.ext (Size: 200.20 KB)

...

Total images downloaded: 10
Total size: 1234.56 KB
```

## Installation Instructions for HTTPie

To execute the generated commands, you need to have HTTPie installed. Follow the instructions below to install HTTPie on your operating system:

### Windows

Open Command Prompt or PowerShell and run:

```bash
pip install httpie
```

### Linux

Open Terminal and run:

```bash
sudo apt install httpie
```

*Or*

```bash
pip install httpie
```

### macOS

Open Terminal and run:

```bash
brew install httpie
```

*Or*

```bash
pip install httpie
```

## Code Explanation

### Functions

- **`download_image($image_url, $save_to)`**:  
  Fetches image data from the specified URL and saves it to the specified path. Returns an array with a success status and the image size in kilobytes.
  
- **`rename_file($file_path, $order)`**:  
  Renames the file based on its download order, using the format `image_<order>.<extension>`. Returns the new file path.

- **`create_zip($folder, $zip_name)`**:  
  Creates a ZIP archive from the specified folder and saves it with the given ZIP file name. Returns a boolean indicating success or failure.

### Main Execution

1. **Check Form Submission**:  
   The script checks if the form has been submitted with the required parameters (`json`, `weburl`). If valid, it processes the images accordingly.

2. **Create Folder**:  
   If a folder is specified and doesn't exist, the script attempts to create it.

3. **Download Images**:  
   Images specified in the JSON input are downloaded to the designated folder or the current directory. The main image and child images are processed sequentially.

4. **Rename Files**:  
   If the option to rename files is selected, each downloaded image is renamed in a sequential order (`1.jpg`, `2.jpg`, etc.).

5. **Create ZIP File**:  
   If the option to create a ZIP file is selected, the folder containing the downloaded images is compressed into a ZIP archive. A download link for the ZIP file is provided in the output.

6. **Output Results**:  
   The script outputs the result of the image downloads, including any errors, and the total number of images downloaded with their combined size. If a ZIP file was created, a download link is also provided.

### Example Usage

1. **Input JSON**: Paste your JSON input containing image paths in the provided textarea.

2. **Base URL**: Provide the base URL where the images are hosted.

3. **Folder Path**: Optionally, specify a folder path where the images will be saved. If left blank, images will be saved in the current directory.

4. **Rename Files**: Select the option to rename the downloaded images sequentially.

5. **Create ZIP File**: Select the option to create a ZIP archive of the downloaded images.

6. **Submit**: Click the "Download Images" button to start the download process.

### HTML Form Elements

- **Textarea (`name="json"`)**: For JSON input.
- **Text Input (`name="weburl"`)**: For the base URL.
- **Checkbox (`name="rename"`)**: To opt for renaming files sequentially.
- **Text Input (`name="folder"`)**: For specifying the folder path.
- **Checkbox (`name="zip"`)**: To opt for creating a ZIP file.
- **Submit Button**: To initiate the process.

### Prerequisites

Ensure you have PHP installed on your server and have the necessary permissions to create directories and files.

### Running the Script

- Host the script on a PHP-compatible server.
- Access the page via a web browser.
- Fill in the required fields and submit the form.
- View the output, which will include links to the downloaded images and optionally a ZIP file.

### Notes

- The script assumes that the `JSON` input contains keys `image` and `image_child`.
- Proper error handling is included to manage scenarios where images fail to download or the folder creation fails.
- The script is flexible and can be easily modified to add more features or adapt to different use cases.
