<?php
/**
 * Doctor Slots API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Get inputs
$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : null;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (!$doctorId || !is_numeric($doctorId)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Doctor ID is required']);
    exit;
}

$doctorId = (int)$doctorId;

// Validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

try {
    // Use global PDO connection from config.php
    global $pdo;

    // Day of week
    $dayOfWeek = date('l', strtotime($date));

    // Get availability
    $stmt = $pdo->prepare("
        SELECT start_time, end_time, break_minutes
        FROM doctor_availability
        WHERE doctor_id = ? AND day_of_week = ? AND is_active = 1
    ");
    $stmt->execute([$doctorId, $dayOfWeek]);
    $availability = $stmt->fetch();

    $slots = [];

    // ================= GENERATE SLOTS (INLINE LOGIC) =================
    if ($availability) {
        $currentTime = strtotime($availability['start_time']);
        $endTime = strtotime($availability['end_time']);
        $breakMinutes = (int)$availability['break_minutes'];

        while ($currentTime < $endTime) {

            // Normal slot
            $slotTime = date('H:i:s', $currentTime);
            $displayTime = date('g:i A', $currentTime);

            $slots[] = [
                'time' => $slotTime,
                'display_time' => $displayTime,
                'is_break' => false
            ];

            // Break slot
            $breakStart = $currentTime + (30 * 60);
            $breakEnd = $breakStart + ($breakMinutes * 60);

            if ($breakEnd <= $endTime) {
                $slots[] = [
                    'time' => date('H:i:s', $breakStart),
                    'display_time' => date('g:i A', $breakStart),
                    'is_break' => true
                ];
            }

            $currentTime = $breakEnd;
        }
    }

    // ================= GET BOOKED APPOINTMENTS =================
    $stmt = $pdo->prepare("
        SELECT appointment_time, duration_minutes
        FROM appointments
        WHERE doctor_id = ? AND appointment_date = ? 
        AND status IN ('Pending', 'Confirmed')
    ");
    $stmt->execute([$doctorId, $date]);
    $bookedAppointments = $stmt->fetchAll();

    $bookedTimes = [];

    foreach ($bookedAppointments as $appt) {
        $apptTime = strtotime($appt['appointment_time']);
        $duration = (int)$appt['duration_minutes'];
        $slotsCount = ceil($duration / 30);

        for ($i = 0; $i < $slotsCount; $i++) {
            $bookedTimes[] = date('H:i:s', $apptTime + ($i * 30 * 60));
        }
    }

    // ================= FINAL SLOT STATUS =================
    $finalSlots = [];

    foreach ($slots as $slot) {
        $isBooked = in_array($slot['time'], $bookedTimes);
        $isBreak = $slot['is_break'];

        $status = 'available';

        if ($isBreak) {
            $status = 'break';
        } elseif ($isBooked) {
            $status = 'booked';
        }

        $finalSlots[] = [
            'time' => $slot['display_time'],
            'time_24' => $slot['time'],
            'status' => $status,
            'break_after' => $isBreak
        ];
    }

    // ================= RESPONSE =================
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'doctor_id' => $doctorId,
        'date' => $date,
        'day_of_week' => $dayOfWeek,
        'slots' => $finalSlots
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