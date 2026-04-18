<?php

function db_connect() {
    $host = "127.0.0.1";
    $port = "3306";
    $dbname = "docbook";
    $user = "root";
    $pass = "root";
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}