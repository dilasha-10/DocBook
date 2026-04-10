<?php
/**
 * Doctor Appointments API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Simulated authenticated doctor ID
$doctorId = 1;

// Get date from query parameter
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

try {
    // Use global PDO connection from config.php
    global $pdo;

    // Query appointments with patient details
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.start_time,
            a.status,
            a.visit_reason,
            u.id as patient_id,
            u.name as patient_name,
            u.email as patient_email,
            u.phone as patient_phone
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.appointment_date = ?
        ORDER BY a.start_time ASC
    ");

    $stmt->execute([$doctorId, $date]);
    $appointments = $stmt->fetchAll();

    // Format response manually (NO array_map)
    $formattedAppointments = [];

    foreach ($appointments as $appt) {
        $formattedAppointments[] = [
            'id' => (int)$appt['id'],
            'patient_id' => (int)$appt['patient_id'],
            'patient_name' => $appt['patient_name'],
            'time' => date('g:i A', strtotime($appt['start_time'])),
            'visit_reason' => $appt['visit_reason'],
            'status' => $appt['status']
        ];
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'date' => $date,
        'doctor_id' => $doctorId,
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