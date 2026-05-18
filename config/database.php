<?php
define('GROQ_API_KEY', '');

// Set timezone to Nepal Time
date_default_timezone_set('Asia/Kathmandu');

function db_connect() {
    $host    = '127.0.0.1';
    $port    = '3306';
    $dbname  = 'docbook';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->exec("SET time_zone = '+05:45'");
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
