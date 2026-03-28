<?php
// ─── Bootstrap ───────────────────────────────────────────────────────────────
session_start();

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/models/AppointmentModel.php';
require_once BASE_PATH . '/app/controllers/AppointmentController.php';

// Auto-seed doctor session for the dashboard (simulates logged-in doctor)
if (!isset($_SESSION['doctor'])) {
    $_SESSION['doctor'] = ['id' => 1, 'name' => 'Dr. Lim'];
}

// ─── Simple Front Router ─────────────────────────────────────────────────────
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // AJAX routes
    header('Content-Type: application/json');
    $controller = new AppointmentController();

    if ($action === 'save_comment') {
        echo json_encode($controller->saveComment(
            (int)($_POST['appointment_id'] ?? 0),
            trim($_POST['comment'] ?? '')
        ));
    } elseif ($action === 'update_status') {
        echo json_encode($controller->updateStatus(
            (int)($_POST['appointment_id'] ?? 0),
            $_POST['status'] ?? ''
        ));
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// ─── Default GET: render dashboard ──────────────────────────────────────────
$controller   = new AppointmentController();
$appointments = $controller->getAll();
$apptJson     = json_encode($appointments);

require_once BASE_PATH . '/app/views/dashboard/dashboard.php';
