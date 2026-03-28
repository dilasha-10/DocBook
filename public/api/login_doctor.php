<?php
// ─── login_doctor.php ────────────────────────────────────────────────────────
// Helper endpoint to simulate doctor login for testing.
// GET /api/login_doctor.php         → logs in as Dr. Lim (id=1)
// GET /api/login_doctor.php?id=2    → logs in as Dr. Chen (id=2)

session_start();

define('BASE_PATH', dirname(dirname(__DIR__)));
require_once BASE_PATH . '/app/models/AppointmentModel.php';

// Seed data so sessions are populated
model_seed_appointments();
model_seed_patients();

$doctors = [
    1 => ['id' => 1, 'name' => 'Dr. Lim'],
    2 => ['id' => 2, 'name' => 'Dr. Chen'],
];

$id = (int)($_GET['id'] ?? 1);

if (!isset($doctors[$id])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid doctor ID.']);
    exit;
}

$_SESSION['doctor'] = $doctors[$id];

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Logged in as ' . $doctors[$id]['name'],
    'doctor'  => $doctors[$id],
]);
