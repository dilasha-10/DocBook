<?php
$host = 'localhost';
$dbname = 'docbook';
$username = 'root';      // change if needed
$password = '';          // change if needed

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}
?>