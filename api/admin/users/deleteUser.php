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

$userId = $_POST["user_id"];

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();


echo '{"message": "user deleted successfully!"}';
trackAction("admin/deleteUser");
exit();