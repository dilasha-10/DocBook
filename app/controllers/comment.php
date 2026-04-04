<?php
/**
 * Doctor Comment API (Procedural - No Functions)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Simulated authenticated doctor ID
$doctorId = 1;

// Get appointment ID from FormData (POST body)
$appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
$commentText = isset($_POST['comment_text']) ? $_POST['comment_text'] : '';

if (!$appointmentId) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Appointment ID is required']);
    exit;
}

if (empty(trim($commentText))) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Comment text is required']);
    exit;
}

$commentText = trim($commentText);

// Database connection from config.php
try {
    global $pdo;

    // Verify appointment belongs to this doctor
    $stmt = $pdo->prepare("
        SELECT id, patient_id, doctor_id, visit_reason, appointment_date, appointment_time
        FROM appointments 
        WHERE id = ? AND doctor_id = ?
    ");
    $stmt->execute([$appointmentId, $doctorId]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Appointment not found or unauthorized']);
        exit;
    }

    // Insert comment
    $stmt = $pdo->prepare("
        INSERT INTO comments (appointment_id, comment_text, created_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$appointmentId, $commentText]);
    $commentId = (int)$pdo->lastInsertId();

    // Fetch new comment
    $stmt = $pdo->prepare("
        SELECT id, comment_text, created_at
        FROM comments
        WHERE id = ?
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Comment saved successfully',
        'comment' => [
            'id' => (int)$comment['id'],
            'text' => $comment['comment_text'],
            'author' => 'Dr. Sarah Lim',
            'date' => date('M j, Y', strtotime($comment['created_at'])),
            'appointment_id' => $appointmentId,
            'visit_reason' => $appointment['visit_reason'],
            'appointment_date' => $appointment['appointment_date'],
            'appointment_time' => date('g:i A', strtotime($appointment['appointment_time']))
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