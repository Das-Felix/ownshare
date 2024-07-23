<?php 
    function createTables($mysqli) {
        //Users
        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `users` ( 
                `id` int(11) NOT NULL AUTO_INCREMENT, 
                `username` varchar(50) NOT NULL, 
                `email` varchar(100) NOT NULL, 
                `password` varchar(255) NOT NULL,
                `role` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                PRIMARY KEY (`id`), 
                UNIQUE KEY `username` (`username`), 
                UNIQUE KEY `email` (`email`) 
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        //User Permissions

        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `user_permissions` ( 
                `permission` varchar(50) NOT NULL, 
                `user_id` int(11) NOT NULL,
                `issuer` int(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        //Sessions
        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `sessions` ( 
                `id` int(11) NOT NULL AUTO_INCREMENT, 
                `token` varchar(255) NOT NULL, 
                `user_id` int(11) NOT NULL, 
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                `valid_until` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        //Options

        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `options` ( 
                `id` int(11) NOT NULL AUTO_INCREMENT, 
                `name` varchar(255) NOT NULL, 
                `value` varchar(255) NOT NULL, 
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        //File Collections
        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `file_collections` ( 
                `id` int(11) NOT NULL AUTO_INCREMENT, 
                `collection_id` varchar(255) NOT NULL,
                `title` varchar(255) NOT NULL,
                `totalSize` int NOT NULL DEFAULT 0,
                `totalFiles` int NOT NULL DEFAULT 0,
                `comment` varchar(255) NOT NULL,
                `path` varchar(255) NOT NULL,
                `password` varchar(255) NOT NULL,
                `max_downloads` int(11) NOT NULL, 
                `downloads` int(11) NOT NULL, 
                `uploaded_by` int(11) NOT NULL, 
                `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                `save_until` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        //File Registry
        $stmt = $mysqli->prepare("
            CREATE TABLE IF NOT EXISTS `file_registry` ( 
                `id` int(11) NOT NULL AUTO_INCREMENT, 
                `name` varchar(255) NOT NULL,
                `size` bigint NOT NULL,
                `location` varchar(255) NOT NULL,
                `collection_id` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
        "); 

        if ($stmt->execute()) {} else { return $stmt->error(); } 

        // Close the connection 
        $stmt->close();
        return "";
    }