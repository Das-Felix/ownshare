<?php
include_once("../functions.php");


if($_SERVER['REQUEST_METHOD'] != "GET") {
    echo '{"error": "wrong request method! expeted GET"}';
    exit();
}

$theme = $options->GetOption("themes_current_theme");

echo '{"currentTheme": "' . $theme . '"}';

exit();