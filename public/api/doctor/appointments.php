<?php
// ─── GET /api/doctor/appointments.php?date=YYYY-MM-DD ────────────────────────
// Returns the authenticated doctor's appointments for the given date.
// Each appointment includes: id, patient_id, patient_name, time, reason,
// duration, status, date.

define('BASE_PATH', dirname(dirname(dirname(__DIR__))));

require_once BASE_PATH . '/app/models/AppointmentModel.php';
require_once BASE_PATH . '/app/core/auth_check.php';   // sets $current_doctor or 401

header('Content-Type: application/json');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use GET.']);
    exit;
}

// Validate date parameter
$date = trim($_GET['date'] ?? '');

if ($date === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameter: date (YYYY-MM-DD).']);
    exit;
}

// Basic date format validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.']);
    exit;
}

$appointments = model_get_doctor_appointments_by_date($current_doctor['id'], $date);

echo json_encode([
    'success'      => true,
    'date'         => $date,
    'doctor'       => $current_doctor['name'],
    'count'        => count($appointments),
    'appointments' => $appointments,
]);
