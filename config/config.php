<?php
/**
 * Database Configuration
 * DocBook - Doctor Appointment System
 */

// Database constants
$DB_HOST = 'localhost';
$DB_NAME = 'docbook';
$DB_USER = 'root';
$DB_PASS = '';

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Simulated authenticated doctor ID (static)
$doctor_id = 1;

// Make the PDO connection available globally if needed
$GLOBALS['pdo'] = $pdo;

// No direct output or exit here so this file can be safely included
?>