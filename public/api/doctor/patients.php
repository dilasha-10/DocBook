<?php
// ─── GET /api/doctor/patients.php?id=<patient_id> ────────────────────────────
// Returns full patient info and the comment history between the authenticated
// doctor and this patient.

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

// Validate patient ID
$patientId = (int)($_GET['id'] ?? 0);

if ($patientId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid patient ID.']);
    exit;
}

// Fetch patient
$patient = model_get_patient_by_id($patientId);

if ($patient === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Patient not found.']);
    exit;
}

// Fetch comment history between this doctor and patient
$comments = model_get_comments_for_doctor_patient($current_doctor['id'], $patientId);

echo json_encode([
    'success'  => true,
    'patient'  => $patient,
    'comments' => $comments,
]);
