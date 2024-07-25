<?php 
include_once("../../functions.php");

if($_SERVER['REQUEST_METHOD'] != "GET") {
    echo '{"error": "wrong request method! expeted GET"}';
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

if($user["role"] != "admin") {
    echo '{"error": "Unautorized User!"}';
    exit();
}

$dir = ROOT_DIR . "/" . APP_THEME_FOLDER;
$folders = scandir($dir);
$themes = [];

foreach($folders as $folder) {
    if($folder == "." || $folder == "..") {
        continue;
    }

    $folderPath = $dir . DIRECTORY_SEPARATOR . $folder;
    if(is_dir($folderPath)) {
        array_push($themes, $folder);
    }
}

$json_themes = json_encode($themes);
echo $json_themes;
trackAction("admin/getAllThemes");

exit();