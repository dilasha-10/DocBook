<?php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$appointmentId = isset($input['appointment_id']) ? (int)$input['appointment_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';

if (!$appointmentId || !in_array($status, ['Confirmed', 'Rejected'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid input']);
    exit;
}

try {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = 1");
    $stmt->execute([$status, $appointmentId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
