<?php 
include_once("../../functions.php");

if($_SERVER['REQUEST_METHOD'] != "POST") {
    echo '{"error": "wrong request method! expeted POST"}';
    exit();
}

if(!isset($_COOKIE["session_token"])) {
    echo '{"error": "invalid session"}';
    exit();
}

$token = $_COOKIE["session_token"];
$user = $auth->getUserFromSessionToken($token);

if($user == null) {
    echo '{"error": "invalid session"}';
    exit();
}


if($user["role"] != "admin" && $user["role"] != "manager") {
    echo '{"error": "unauthorized"}';
    exit();
}

if(!isset($_POST["collection_id"])) {
    echo '{"error": "collection_id is missing"}';
    exit();
}

$collectionId = $_POST["collection_id"];

echo $collectionId;

$collection = $upload->getCollectionEntry($collectionId);

if($collection == null) {
    echo '{"error": "collection not found!"}';
    exit();
}

$zipFilePath = ROOT_DIR . $collection['path'] . '/' . $collectionId . '.zip';

$zip = new ZipArchive();
$totalSize = 0;
$totalFiles = 0;

if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    echo '{"error": "cannot create zip file"}';
    exit();
}

foreach ($collection['files'] as $file) {
    $filePath = ROOT_DIR . $file['location'];
    if (file_exists($filePath)) {
        $zip->addFile($filePath, basename($filePath));
    } else {
        echo '{"error": "file ' . $filePath . ' not found"}';
        $zip->close();
        exit();
    }
    $totalSize += $file["size"];
    $totalFiles += 1;
}

$stmt = $db->prepare("UPDATE file_collections SET totalSize = ?, totalFiles = ? WHERE id = ?");
$stmt->bind_param("sss", $totalSize, $totalFiles, $collection["id"]);
$stmt->execute();

$zip->close();

echo '{"message": "upload finished successfully!"}';

trackAction("admin/finishUpload");

exit();