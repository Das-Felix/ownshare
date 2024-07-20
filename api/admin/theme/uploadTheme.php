<?php
include_once("../../functions.php");

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    echo '{"error": "Wrong request method! Expected POST"}';
    exit();
}

if (!isset($_COOKIE["session_token"])) {
    echo '{"error": "Invalid session"}';
    exit();
}

$token = $_COOKIE["session_token"];
$user = $auth->getUserFromSessionToken($token);

if ($user == null) {
    echo '{"error": "Invalid session"}';
    exit();
}

if ($user["role"] != "admin") {
    echo '{"error": "Unauthorized user!"}';
    exit();
}

$dir = ROOT_DIR . "/" . APP_THEME_FOLDER;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

if (!isset($_FILES['fileList'])) {
    echo '{"error": "No files were uploaded."}';
    exit();
}

$file = $_FILES['fileList'];

if (!isset($file['tmp_name'][0])) {
    echo '{"error": "No file was uploaded."}';
    exit();
}

$fileType = mime_content_type($file['tmp_name'][0]);
if ($fileType !== 'application/zip') {
    echo '{"error": "The theme has to be a zip folder."}';
    exit();
}

$zipName = pathinfo($file['name'][0], PATHINFO_FILENAME); // Get the name without extension
$newDir = $dir . '/' . $zipName; // Create a new directory path

if (!mkdir($newDir, 0755) && !is_dir($newDir)) {
    echo '{"error": "Failed to create directory for the theme."}';
    exit();
}

$zipPath = $newDir . '/' . basename($file['name'][0]);

if (!move_uploaded_file($file['tmp_name'][0], $zipPath)) {
    echo '{"error": "Failed to save the theme file."}';
    cleanUp($newDir);
    exit();
}

// Unzip the file
$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {
    $zip->extractTo($newDir);
    $zip->close();
    unlink($zipPath); // Remove the zip file after unpacking
} else {
    echo '{"error": "Failed to unpack the theme file!"}';
    cleanUp($newDir);
    exit();
}

// Check for required files
$requiredFiles = ['theme.json', 'template.html', 'theme.css'];
foreach ($requiredFiles as $requiredFile) {
    if (!file_exists($newDir . '/' . $requiredFile)) {
        echo '{"error": "Missing required theme file: ' . $requiredFile . '"}';
        cleanUp($newDir);
        exit();
    }
}

echo '{"message": "Theme uploaded successfully!"}';
exit();

function cleanUp($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object))
                    cleanUp($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}
?>
