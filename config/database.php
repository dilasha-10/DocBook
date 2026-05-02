<?php

$dotenv = parse_ini_file(__DIR__ . '/../.env');

define('GROQ_API_KEY', $dotenv['GROQ_API_KEY'] ?? '');

function db_connect() {
    $dotenv = parse_ini_file(__DIR__ . '/../.env');

    $host    = $dotenv['DB_HOST']   ?? '127.0.0.1';
    $port    = $dotenv['DB_PORT']   ?? '3306';
    $dbname  = $dotenv['DB_NAME']   ?? 'docbook';
    $user    = $dotenv['DB_USER']   ?? 'root';
    $pass    = $dotenv['DB_PASS']   ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}