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
global $version;

$auth = new Auth($db);
$options = new Options($db);
$upload = new Upload($db);
$version = "0.1";

function trackAction($action) {
    global $version;
    
    try {
        $ch = curl_init();
        if ($ch === false) {
            throw new Exception('Failed to initialize cURL');
        }
        
        $url = "https://ownshare.org/stats/v1/track-action?a=$action&v=$version";
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
    } catch (Exception $e) {

    }
}
