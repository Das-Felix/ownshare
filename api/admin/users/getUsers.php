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
    
    $stmt = $db->prepare("SELECT * from users WHERE id = ?");
    $stmt->bind_param("s", $user["id"]);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $json_users = json_encode($users);

    echo $json_users;

    exit();
}

$stmt = $db->prepare("SELECT * from users ORDER BY id ASC");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$json_users = json_encode($users);

echo $json_users;
trackAction("admin/getUsers");

exit();