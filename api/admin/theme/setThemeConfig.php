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

if($user["role"] != "admin") {
    echo '{"error": "Unautorized User!"}';
    exit();
}

if(!isset($_POST["theme"])) {
    echo '{"error": "theme has to be provided!"}';
    exit();
}

$theme = $_POST["theme"];

if(!isset($_POST["config"])) {
    echo '{"error": "config has to be provided!"}';
    exit();
}

$config = $_POST["config"];


$dir = ROOT_DIR . "/" . APP_THEME_FOLDER;

if(!file_exists("$dir/$theme")) {
    echo '{"error": "Theme not found!"}';
    exit();
}

if(!file_exists("$dir/$theme/theme.json")) {
    echo '{"error": "theme.json not found!"}';
    exit();
}


$decodedConfig = json_decode($config, true);
if (json_last_error() != JSON_ERROR_NONE) {
    echo '{"error": "Invalid JSON in config"}';
    exit();
}

$formattedConfig = json_encode($decodedConfig, JSON_PRETTY_PRINT);

file_put_contents("$dir/$theme/theme.json", $formattedConfig);
echo '{"message": "theme.json updated!"}';
trackAction("admin/setThemeConfig");

exit();