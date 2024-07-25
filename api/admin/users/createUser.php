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
    echo '{"error": "unauthorized"}';
    exit();
}

if(!isset($_POST["username"]) || !isset($_POST["email"]) || !isset($_POST["password"]) || !isset($_POST["role"])) {
    echo '{"error": "All fields must be filled!"}';
    exit();
}

$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST["password"];
$role = $_POST["role"];

if($username == "" || $email == "" || $password == "" || $role == "") {
    echo '{"error": "All fields must be filled!"}';
    exit();
}

$auth->createUser($username, $email, $password, $role);

echo '{"message": "created user"}';
trackAction("admin/createUser");

exit();