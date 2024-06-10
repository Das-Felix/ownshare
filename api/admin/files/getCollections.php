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

if($user["role"] != "admin" && $user["role"] != "manager") {
    echo '{"error": "Unautorized User!"}';
    exit();
}

if(!isset($_GET["sortField"]) || !isset($_GET["sortDir"])) {
    echo '{"error": "sortField or sortDir not set!"}';
    exit();
}

$sortField = $_GET["sortField"];
$sortDir = $_GET["sortDir"];

if($sortDir != "ASC" && $sortDir != "DESC") {
    $sortDir = "DESC";
}

if($sortField != "title" && $sortField != "totalFiles" && $sortField != "totalSize" && $sortField != "downloads" && $sortField != "uploaded_by" && $sortField != "uploaded_at" && $sortField != "save_until") {
    $sortField = "title";
}

$stmt = $db->prepare("SELECT * from file_collections ORDER BY " . $sortField . " " . $sortDir);
$stmt->execute();
$collections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

for($i = 0; $i < count($collections); $i++) {
    $collectionId = $collections[$i]["collection_id"];

    $stmt = $db->prepare("SELECT * FROM file_registry WHERE collection_id = ?");
    $stmt->bind_param("s", $collectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $files = $result->fetch_all(MYSQLI_ASSOC);

    $user = $auth->getUserById($collections[$i]["uploaded_by"]);

    $collections[$i]["files"] = $files;
    $collections[$i]["uploaded_by"] = $user;
}

$json_collections = json_encode($collections);

echo $json_collections;

exit();