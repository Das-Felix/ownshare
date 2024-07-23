<?php 
    include_once("config.php");
    
    try {
        include_once("includes/database.php");
    } catch (Exception $e) {

    }

    include_once("includes/install.php");

    include_once("includes/classes/Auth.php");
    include_once("includes/classes/Options.php");
    include_once("includes/classes/Upload.php");

    header("Access-Control-Allow-Origin: " . APP_CORS_URLS);
    header("Access-Control-Allow-Headers: " . APP_CORS_URLS);
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");

    define("ROOT_DIR", dirname(__FILE__));

    if(APP_SETUP_COMPLETE == true) {
        echo '{"error": "setup-already-complete"}';
        exit();
    }

    if(!isset($_POST["action"])) {
        echo '{"error": "no action set!"}';
        exit();
    }

    $action = $_POST["action"];

    global $db;
    global $auth;
    global $options;
    global $upload;

    switch($action) {
        case "set_db_credentials":
            if(!isset($_POST["db_host"]) || !isset($_POST["db_name"]) || !isset($_POST["db_user"]) || !isset($_POST["db_password"])) {
                echo '{"error": "All fields must be set!"}';
                exit();
            }

            if($_POST["db_host"] == "" || $_POST["db_user"] == "" || $_POST["db_name"] == "" || $_POST["db_password"] == "") {
                echo '{"error": "All fields must be filled!"}';
                exit();
            }

            $credsValid = check_mysql_credentials($_POST["db_host"], $_POST["db_user"], $_POST["db_password"], $_POST["db_name"]);

            if(!$credsValid) {
                echo '{"error": "Invalid credentials!"}';
                exit();
            }

            $path = ROOT_DIR . "/config.php";
            updateLineInFile($path, 'define("APP_DB_HOST", "' . $_POST["db_host"] . '");', 5);
            updateLineInFile($path, 'define("APP_DB_NAME", "' . $_POST["db_name"] . '");', 6);
            updateLineInFile($path, 'define("APP_DB_PASSWORD", "' . $_POST["db_password"] . '");', 7);
            updateLineInFile($path, 'define("APP_DB_USER", "' . $_POST["db_user"] . '");', 8);

            echo '{"message": "Updated mysql credentials"}';

            break;
        case "init_app":
            $tablesErr = createTables($db);

            if($tablesErr != "") {
                echo '{"error": "Error while creating database tables!"}';
                exit();
            }

            $options = new Options($db);
            $options->setDefaultOptions();

            echo '{"message": "Creating database tables <br> Setting default options <br> Install complete!"}';
            break;
        case "create_admin_user":
            if(!isset($_POST["username"]) || !isset($_POST["email"]) || !isset($_POST["password"]) || !isset($_POST["password_repeat"])) {
                echo '{"error": "All fields must be set!"}';
                exit();
            }

            if($_POST["username"] == "" || $_POST["email"] == "" || $_POST["password"] == "" || $_POST["password_repeat"] == "") {
                echo '{"error": "All fields must be filled!"}';
                exit();
            }
            
            if($_POST["password"] != $_POST["password_repeat"]) {
                echo '{"error": "Password do not match!"}';
                exit();
            }

            $auth = new Auth($db);

            if($auth->usernameTaken($_POST["username"])) {
                echo '{"error": "Username already taken!"}';
                exit();
            }

            $auth->createUser($_POST["username"], $_POST["email"], $_POST["password"], "admin");
            echo '{"message": "Created admin user!"}';
            exit();
        case "finish_setup":
            if(APP_SETUP_COMPLETE == true) {
                echo '{"error": "Setup already complete!"}';
                exit();
            }

            try {
                updateLineInFile(ROOT_DIR . "/config.php", 'define("APP_SETUP_COMPLETE", true);', 2);
                echo '{"message": "Setup complete!"}';
            } catch(Exception $e) {
                echo '{"error": "error while completing last setup step!"}';
            }
    }

    function updateLineInFile($filePath, $newLineContent, $lineNumber) {
        $fileLines = file($filePath, FILE_IGNORE_NEW_LINES);
    
        if ($lineNumber < 1 || $lineNumber > count($fileLines)) {
            throw new Exception("Invalid line number: $lineNumber");
        }
    
        $fileLines[$lineNumber - 1] = $newLineContent;
    
        $fileContents = implode(PHP_EOL, $fileLines);
        file_put_contents($filePath, $fileContents);
    }

    function check_mysql_credentials($host, $username, $password, $database) {
        error_reporting(E_ERROR | E_PARSE);

        try {
            $mysqli = new mysqli($host, $username, $password, $database);
    
            if ($mysqli->connect_error) {
                return false;
            } else {
                $mysqli->close();
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    