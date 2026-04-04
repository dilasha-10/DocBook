<?php
/**
 * Doctor Patient Details API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Simulated authenticated doctor
$doctorId = 1;

// Get patient ID
$patientId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$patientId || !is_numeric($patientId)) {
    // If no ID is passed, fetch ALL unique patients for this doctor
    try {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.id, p.name, p.email, p.phone, p.date_of_birth,
            (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = p.id AND doctor_id = ?) as last_visit
            FROM patients p
            JOIN appointments a ON p.id = a.patient_id
            WHERE a.doctor_id = ?
            ORDER BY p.name ASC
        ");
        $stmt->execute([$doctorId, $doctorId]);
        $patients = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'patients' => $patients]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$patientId = (int)$patientId;

try {
    // Use global PDO connection from config.php
    global $pdo;

    // ================= PATIENT DETAILS =================
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, date_of_birth, address, created_at
        FROM patients 
        WHERE id = ?
    ");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['error' => true, 'message' => 'Patient not found']);
        exit;
    }

    // ================= COMMENT HISTORY =================
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.comment_text,
            c.created_at,
            a.visit_reason,
            a.appointment_date,
            a.appointment_time
        FROM comments c
        JOIN appointments a ON c.appointment_id = a.id
        WHERE a.patient_id = ? AND a.doctor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$patientId, $doctorId]);
    $comments = $stmt->fetchAll();

    $formattedComments = [];

    foreach ($comments as $comment) {
        $formattedComments[] = [
            'id' => (int)$comment['id'],
            'text' => $comment['comment_text'],
            'author' => 'Dr. Sarah Lim',
            'date' => date('M j, Y', strtotime($comment['created_at'])),
            'visit_reason' => $comment['visit_reason'],
            'appointment_date' => $comment['appointment_date'],
            'appointment_time' => date('g:i A', strtotime($comment['appointment_time']))
        ];
    }

    // ================= APPOINTMENTS =================
    $stmt = $pdo->prepare("
        SELECT id, appointment_date, appointment_time, visit_reason, status, duration_minutes
        FROM appointments 
        WHERE patient_id = ? AND doctor_id = ?
        ORDER BY appointment_date DESC, appointment_time DESC
    ");
    $stmt->execute([$patientId, $doctorId]);
    $appointments = $stmt->fetchAll();

    $formattedAppointments = [];

    foreach ($appointments as $appt) {
        $formattedAppointments[] = [
            'id' => (int)$appt['id'],
            'date' => $appt['appointment_date'],
            'time' => date('g:i A', strtotime($appt['appointment_time'])),
            'visit_reason' => $appt['visit_reason'],
            'status' => $appt['status'],
            'duration_minutes' => (int)$appt['duration_minutes']
        ];
    }

    // ================= RESPONSE =================
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'patient' => [
            'id' => (int)$patient['id'],
            'name' => $patient['name'],
            'email' => $patient['email'],
            'phone' => $patient['phone'],
            'date_of_birth' => $patient['date_of_birth'],
            'address' => $patient['address'],
            'created_at' => $patient['created_at']
        ],
        'comment_history' => $formattedComments,
        'appointments' => $formattedAppointments
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