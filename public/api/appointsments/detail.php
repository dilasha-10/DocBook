<?php
// ─── GET /api/appointments/detail.php?id=<appointment_id> ────────────────────
// Patient-facing endpoint: returns appointment info INCLUDING comments
// so patients can read what the doctor has written.
// No doctor auth required (patient access), but session is needed for data.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(dirname(dirname(__DIR__))));

require_once BASE_PATH . '/app/models/AppointmentModel.php';

header('Content-Type: application/json');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use GET.']);
    exit;
}

// Validate appointment ID
$appointmentId = (int)($_GET['id'] ?? 0);

if ($appointmentId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid appointment ID.']);
    exit;
}

// Fetch appointment
$appt = model_find_appointment($appointmentId);

if ($appt === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
    exit;
}

// Return appointment detail with comments included
echo json_encode([
    'success'      => true,
    'appointment'  => [
        'id'           => $appt['id'],
        'patient_name' => $appt['name'],
        'time'         => $appt['time'],
        'reason'       => $appt['reason'],
        'duration'     => $appt['duration'],
        'status'       => $appt['status'],
        'date'         => $appt['date'],
        'comments'     => $appt['comments'],
    ],
]);
