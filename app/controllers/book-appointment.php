<?php
/**
 * Appointment Booking API (Procedural - No Functions)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

$doctorId = isset($input['doctor_id']) ? $input['doctor_id'] : null;
$patientId = isset($input['patient_id']) ? $input['patient_id'] : 1;
$date = isset($input['date']) ? $input['date'] : null;
$slots = isset($input['slots']) ? $input['slots'] : [];

// Validate inputs
if (!$doctorId) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Doctor ID is required']);
    exit;
}

if (!$date) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Date is required']);
    exit;
}

if (empty($slots) || !is_array($slots)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'At least one slot is required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

// Max 2 slots
if (count($slots) > 2) {
    http_response_code(422);
    echo json_encode(['error' => true, 'message' => 'You can select at most 2 slots']);
    exit;
}

// Check consecutive slots
for ($i = 1; $i < count($slots); $i++) {
    $prev = strtotime($slots[$i - 1]);
    $curr = strtotime($slots[$i]);
    $diffMinutes = ($curr - $prev) / 60;

    if ($diffMinutes !== 30) {
        http_response_code(422);
        echo json_encode(['error' => true, 'message' => 'Slots must be consecutive']);
        exit;
    }
}

$durationMinutes = count($slots) * 30;

try {
    // Use global PDO connection from config.php
    global $pdo;

    $pdo->beginTransaction();

    // Day of week
    $dayOfWeek = date('l', strtotime($date));

    // Check availability
    $stmt = $pdo->prepare("
        SELECT start_time, end_time, break_minutes
        FROM doctor_availability
        WHERE doctor_id = ? AND day_of_week = ?
    ");
    $stmt->execute([$doctorId, $dayOfWeek]);
    $availability = $stmt->fetch();

    if (!$availability) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['error' => true, 'message' => 'Doctor is not available on this day']);
        exit;
    }

    // Check break slots - use doctor_slots table
    $startTime = $slots[0];
    $endTime = end($slots);

    $stmt = $pdo->prepare("
        SELECT start_time, end_time, status
        FROM doctor_slots
        WHERE doctor_id = ? AND date = ? AND status = 'break' AND start_time <= ? AND end_time >= ?
    ");
    $stmt->execute([$doctorId, $date, $endTime, $startTime]);
    $breakSlots = $stmt->fetchAll();

    if (!empty($breakSlots)) {
        $pdo->rollBack();
        http_response_code(422);
        echo json_encode(['error' => true, 'message' => 'One or more selected slots fall within a break period']);
        exit;
    }

    // Capacity check
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT id) as count
        FROM appointments
        WHERE doctor_id = ? 
        AND appointment_date = ?
        AND status IN ('Pending', 'Confirmed')
        AND HOUR(start_time) = HOUR(?)
    ");
    $stmt->execute([$doctorId, $date, $slots[0]]);
    $result = $stmt->fetch();

    if ($result['count'] >= 2) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => true, 'message' => 'This time slot is now full. Please choose another time.']);
        exit;
    }

    // Check already booked slots
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM appointments
        WHERE doctor_id = ?
        AND appointment_date = ?
        AND start_time = ?
        AND status IN ('Pending', 'Confirmed')
    ");

    foreach ($slots as $slot) {
        $stmt->execute([$doctorId, $date, $slot]);
        $res = $stmt->fetch();

        if ($res['count'] > 0) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['error' => true, 'message' => 'One or more selected slots are already booked']);
            exit;
        }
    }

    // Visit reason
    $visitReason = isset($input['visit_reason']) ? $input['visit_reason'] : 'General consultation';

    // Calculate end time (slots[0] + durationMinutes)
    // Need to provide a full datetime context for strtotime
    $datetime = $date . " " . $slots[0];
    $startTimeObj = strtotime($datetime);
    $endTimeObj = $startTimeObj + ($durationMinutes * 60);
    $startTime = $slots[0];
    $endTime = date('H:i:s', $endTimeObj);

    // Insert appointment
    $stmt = $pdo->prepare("
        INSERT INTO appointments (patient_id, doctor_id, appointment_date, start_time, end_time, status, visit_reason)
        VALUES (?, ?, ?, ?, ?, 'Pending', ?)
    ");

    $stmt->execute([
        $patientId,
        $doctorId,
        $date,
        $startTime,
        $endTime,
        $visitReason
    ]);

    $appointmentId = (int)$pdo->lastInsertId();

    // Populate doctor_slots table for each booked slot
    $slotStmt = $pdo->prepare("
        INSERT INTO doctor_slots (doctor_id, date, start_time, end_time, status)
        VALUES (?, ?, ?, ?, 'booked')
    ");

    foreach ($slots as $slotTime) {
        // Calculate individual slot end time (each slot is 30 mins)
        $slotStart = strtotime($date . " " . $slotTime);
        $slotEnd = $slotStart + (30 * 60); // 30 minutes
        $slotEndTime = date('H:i:s', $slotEnd);
        
        $slotStmt->execute([
            $doctorId,
            $date,
            $slotTime,
            $slotEndTime
        ]);
    }

    $pdo->commit();

    // Get doctor info (from users table via doctors table)
    $stmt = $pdo->prepare("
        SELECT u.name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Appointment booked successfully',
        'appointment' => [
            'id' => $appointmentId,
            'doctor' => [
                'id' => $doctorId,
                'name' => $doctor['name']
            ],
            'date' => $date,
            'time' => $slots[0],
            'duration_minutes' => $durationMinutes,
            'status' => 'Pending',
            'reference_id' => 'DOC' . str_pad($appointmentId, 8, '0', STR_PAD_LEFT)
        ]
    ]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>