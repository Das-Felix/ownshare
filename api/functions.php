<?php 

include_once("config.php");
include_once("includes/database.php");

include_once("includes/classes/Auth.php");
include_once("includes/classes/Options.php");
include_once("includes/classes/Upload.php");

header("Access-Control-Allow-Origin: " . APP_CORS_URLS);
header("Access-Control-Allow-Headers: " . APP_CORS_URLS);
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

define("ROOT_DIR", dirname(__FILE__));

global $auth;
global $options;
global $upload;

$auth = new Auth($db);
$options = new Options($db);
$upload = new Upload($db);