<?php 
include_once("../functions.php");

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

$json_user = json_encode($user);

echo $json_user;

exit();