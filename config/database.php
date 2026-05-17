<?php

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'docbook');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function db_connect() {
<<<<<<< HEAD
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );
=======
    $dotenv = parse_ini_file(__DIR__ . '/../.env');

    $host    = $dotenv['DB_HOST']   ?? '127.0.0.1';
    $port    = $dotenv['DB_PORT']   ?? '3306';
    $dbname  = $dotenv['DB_NAME']   ?? 'docbook';
    $user    = $dotenv['DB_USER']   ?? '';
    $pass    = $dotenv['DB_PASS']   ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
>>>>>>> 5353f4c (Final complete work)

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        throw $e;
    }
}
