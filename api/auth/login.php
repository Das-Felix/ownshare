<?php 
include_once("../functions.php");

if($_SERVER['REQUEST_METHOD'] != "POST") {
    echo '{"error": "wrong request method! expeted POST"}';
    exit();
}

$json = file_get_contents('php://input');
$body = json_decode($json);

if(!isset($body->username) || !isset($body->password)) {
    echo '{"error": "all fields must be set!"}';
    exit();
}

$username = $body->username;
$password = $body->password;

if($username == "" || $password == "") {
    echo '{"error": "all fields must be filled!"}';
    exit();
}

$session_token = $auth->loginUser($username, $password);

if($session_token == "") {
    echo '{"error": "wrong username or password"}';
    exit();
}

setcookie("session_token", $session_token, [
    "expires" => time() + (86400 * 30),
    "path" => "/",
    "httponly" => "true",
    "secure" => "true",
    "samesite" => "None"
]);

echo '{"message": "logged in successfully!"}';

trackAction("auth/login");

exit();