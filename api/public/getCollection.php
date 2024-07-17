<?php
include_once("../functions.php");


if($_SERVER['REQUEST_METHOD'] != "GET") {
    echo '{"error": "wrong request method! expeted GET"}';
    exit();
}

if(!isset($_GET["collectionId"])) {
    echo '{"error": "collectionId is missing"}';
    exit();
}

$collectionId = $_GET["collectionId"];

$collection = $upload->getCollectionEntry($collectionId);

if($collection == null || $collection == []) {
    echo '{"error": "Collection not found!"}';
    exit();
}

if($collection["password"] != "") {

    if(!isset($_GET["password"])) {
        echo '{"error": "prompt_password"}';
        exit();
    }

    $password = $_GET["password"];

    if($password == "") {
        echo '{"error": "prompt_password"}';
        exit();
    }

    if($collection["password"] != $password) {
        echo '{"error": "wrong_password"}';
        exit();
    }
}

if($collection["downloads"] >= $collection["max_downloads"]) {
    echo '{"error": "Max Downloads reached!"}';
    exit();
}

$expiryDate = new DateTime($collection['save_until']);
$now = new DateTime();

if($expiryDate < $now) {
    echo '{"error": "Collection expired!"}';
    exit();
}

$upload->increaseCollectionDownloadCount($collectionId);
$json_collection = json_encode($collection);
echo $json_collection;

exit();