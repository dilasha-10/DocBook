<?php
// ─── auth_check.php ──────────────────────────────────────────────────────────
// Include this file at the top of any doctor-protected API endpoint.
// It verifies that a doctor is logged in (via session) and exposes
// $current_doctor = ['id' => int, 'name' => string].
// If not authenticated, it sends a 401 JSON response and exits.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['doctor']) || !is_array($_SESSION['doctor'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$current_doctor = $_SESSION['doctor'];
