<?php
require_once 'db.php';

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>