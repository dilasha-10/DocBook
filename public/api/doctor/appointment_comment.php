<?php
// ─── POST /api/doctor/appointment_comment.php ────────────────────────────────
// Stores a new doctor comment on an appointment and returns the created record.
// Body: appointment_id (int), comment (string)

define('BASE_PATH', dirname(dirname(dirname(__DIR__))));

require_once BASE_PATH . '/app/models/AppointmentModel.php';
require_once BASE_PATH . '/app/core/auth_check.php';   // sets $current_doctor or 401

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

// Parse input (support both form-data and JSON body)
$appointmentId = 0;
$commentText   = '';

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/json') !== false) {
    $body = json_decode(file_get_contents('php://input'), true);
    $appointmentId = (int)($body['appointment_id'] ?? 0);
    $commentText   = trim($body['comment'] ?? '');
} else {
    $appointmentId = (int)($_POST['appointment_id'] ?? 0);
    $commentText   = trim($_POST['comment'] ?? '');
}

// Validate
if ($appointmentId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
    exit;
}

if ($commentText === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit;
}

if (strlen($commentText) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 2000 characters).']);
    exit;
}

// Verify appointment exists AND belongs to this doctor
$appt = model_find_doctor_appointment($appointmentId, $current_doctor['id']);

if ($appt === null) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Appointment not found or not yours.']);
    exit;
}

// Store the comment
$newComment = model_add_doctor_comment(
    $appointmentId,
    $current_doctor['id'],
    $current_doctor['name'],
    $commentText
);

if ($newComment === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save comment.']);
    exit;
}

echo json_encode(['success' => true, 'comment' => $newComment]);
