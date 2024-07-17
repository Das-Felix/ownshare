<?php

class Options {

    private $conn = null;

    function __construct($conn) {
        $this->conn = $conn;
    }

    public function setOption($name, $value) {

        if($this->GetOption($name) == null) {
            $stmt = $this->conn->prepare("INSERT INTO options (name, value) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $value);
            $stmt->execute();
        } else {
            $stmt = $this->conn->prepare("UPDATE options SET value = ? WHERE name = ?");
            $stmt->bind_param("ss", $value, $name);
            $stmt->execute();
        }
    }

    public function GetOption($name) {
        $stmt = $this->conn->prepare("SELECT * FROM options WHERE name = ?");
        $stmt->bind_param("s", $name);            
        $stmt->execute();
        $result = $stmt->get_result();
        $options = $result->fetch_assoc();

        if($options == null) {
            return null;
        }

        return $options["value"];
    }

    public function setOptionIfNotExists($name, $value) {
        if($this->GetOption($name) != null) {
            return;
        }
        $this->setOption($name, $value);
    }

    public function setDefaultOptions() {
        $this->setOptionIfNotExists("files_upload_folder_location", "/uploads");
        $this->setOptionIfNotExists("files_default_save_time", "30");
        $this->setOptionIfNotExists("files_default_max_downloads", "100");
        $this->setOptionIfNotExists("files_id_length", "8");
        $this->setOptionIfNotExists("files_upload_chunk_size", "40000");

        $this->setOptionIfNotExists("themes_current_theme", "ownshare_2024");
    }

}