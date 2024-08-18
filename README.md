# ArtCODEUtility

**ArtCODEUtility** is a PHP-based utility designed for downloading images from a specified API and managing them efficiently. This tool allows you to download images, optionally rename them based on their download order, and provides detailed feedback on the download process.

## Features

- **Download Images**: Fetch and save images from a specified API endpoint.
- **Rename Files**: Optionally rename files based on their order of download.
- **Detailed Feedback**: Displays download progress and summary information, including file size and total number of images downloaded.

## Prerequisites

- PHP (version 7.0 or higher recommended)
- Command-line access to run PHP scripts
- Access to an API endpoint providing image URLs

## shellation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/HirotakaDango/ArtCODEUtility.git
   cd ArtCODEUtility
   ```

2. **Prepare Configuration**:
   - Create a `config.txt` file in the root directory of the repository.
   - Add the base URL of your API endpoint to `config.txt`.

3. **Run the Script**:
   ```bash
   php shell.php <artworkid>
   ```

   Replace `<artworkid>` with the specific ID for the artwork you want to download.

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

## Code Explanation

### Functions

- **`download_image($image_url, $save_to)`**
  - Fetches image data from the specified URL.
  - Saves the image to the specified path.
  - Returns success status and image size in kilobytes.

- **`rename_file($file_path, $order)`**
  - Renames the file based on its download order.
  - Uses the format `image_<order>.<extension>`.

- **`process_images($api_url, $base_url, $save_dir, $rename_files)`**
  - Fetches image data from the API.
  - Downloads and optionally renames images.
  - Provides progress feedback and summary of total images and size.

- **`print_loading_bar($current, $total)`**
  - Prints a progress bar indicating download completion percentage.

- **`get_base_url()`**
  - Reads and returns the base URL from `config.txt`.

### Main Execution

1. **Check Arguments**:
   Ensures that the script is run with the correct number of arguments.

2. **Prompt for Directory and Renaming Option**:
   Requests user input for saving directory and renaming option.

3. **Process Images**:
   Calls `process_images` with the appropriate parameters to handle downloading and processing.
