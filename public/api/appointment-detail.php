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
            a.start_time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.patient_id,
            d.id as doctor_id,
            u.name as doctor_name,
            d.specialty as doctor_specialty
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
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
        SELECT id, message, created_at
        FROM appointment_comments
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
            'text' => $comment['message'],
            'date' => date('M j, Y', strtotime($comment['created_at']))
        ];
    }

    // Success response
    http_response_code(200);
    // Calculate duration from start and end times
    $start_time = strtotime($appointment['start_time']);
    $end_time = strtotime($appointment['end_time']);
    $duration_minutes = ($end_time - $start_time) / 60;
    
    echo json_encode([
        'success' => true,
        'appointment' => [
            'id' => (int)$appointment['id'],
            'date' => $appointment['appointment_date'],
            'time' => date('g:i A', $start_time),
            'status' => $appointment['status'],
            'visit_reason' => $appointment['visit_reason'],
            'duration_minutes' => (int)$duration_minutes,
            'doctor' => [
                'id' => (int)$appointment['doctor_id'],
                'name' => $appointment['doctor_name'],
                'specialty' => $appointment['doctor_specialty']
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