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
$options->setOption("themes_current_theme", $theme);

echo '{"message": "Theme updated!"}';

exit();