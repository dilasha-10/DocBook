<?php
/**
 * Patient-Facing Appointment Details API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Get appointment ID
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$appointmentId || !is_numeric($appointmentId)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Appointment ID is required']);
    exit;
}

$appointmentId = (int)$appointmentId;

try {
    // Use global PDO connection from config.php
    global $pdo;

    // Get appointment with doctor details
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            a.visit_reason,
            a.duration_minutes,
            a.patient_id,
            d.id as doctor_id,
            d.name as doctor_name,
            d.specialty as doctor_specialty,
            d.fee as doctor_fee
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = ?
    ");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Appointment not found']);
        exit;
    }

    // Get comments
    $stmt = $pdo->prepare("
        SELECT id, comment_text, created_at
        FROM comments
        WHERE appointment_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$appointmentId]);
    $comments = $stmt->fetchAll();

    // Format comments manually (NO array_map)
    $formattedComments = [];

    foreach ($comments as $comment) {
        $formattedComments[] = [
            'id' => (int)$comment['id'],
            'text' => $comment['comment_text'],
            'date' => date('M j, Y', strtotime($comment['created_at']))
        ];
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'appointment' => [
            'id' => (int)$appointment['id'],
            'date' => $appointment['appointment_date'],
            'time' => date('g:i A', strtotime($appointment['appointment_time'])),
            'status' => $appointment['status'],
            'visit_reason' => $appointment['visit_reason'],
            'duration_minutes' => (int)$appointment['duration_minutes'],
            'doctor' => [
                'id' => (int)$appointment['doctor_id'],
                'name' => $appointment['doctor_name'],
                'specialty' => $appointment['doctor_specialty'],
                'fee' => (float)$appointment['doctor_fee']
            ],
            'comments' => $formattedComments
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>