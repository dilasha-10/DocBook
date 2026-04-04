<?php
/**
 * Doctor Availability API (Procedural)
 */

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Simulated authenticated doctor ID
$doctorId = 1;

try {
    // Use global PDO connection from config.php
    global $pdo;

    // ===================== POST: SAVE AVAILABILITY =====================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $input = json_decode(file_get_contents('php://input'), true);
        $schedule = isset($input['schedule']) ? $input['schedule'] : [];

        if (empty($schedule)) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Schedule data is required']);
            exit;
        }

        // Delete existing availability
        $stmt = $pdo->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?");
        $stmt->execute([$doctorId]);

        // Insert new schedule
        $stmt = $pdo->prepare("
            INSERT INTO doctor_availability 
            (doctor_id, day_of_week, is_active, start_time, end_time, break_minutes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($schedule as $dayConfig) {
            $day = isset($dayConfig['day']) ? $dayConfig['day'] : null;
            $startTime = isset($dayConfig['start_time']) ? $dayConfig['start_time'] : '09:00';
            $endTime = isset($dayConfig['end_time']) ? $dayConfig['end_time'] : '17:00';
            $breakMinutes = isset($dayConfig['break_minutes']) ? $dayConfig['break_minutes'] : 5;

            if ($day) {
                $stmt->execute([
                    $doctorId,
                    ucfirst($day),
                    1, // true
                    $startTime,
                    $endTime,
                    $breakMinutes
                ]);
            }
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Availability saved successfully'
        ]);
        exit;

    // ===================== GET: FETCH AVAILABILITY =====================
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $stmt = $pdo->prepare("
            SELECT day_of_week, is_active, start_time, end_time, break_minutes
            FROM doctor_availability
            WHERE doctor_id = ?
            ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ");
        $stmt->execute([$doctorId]);
        $availability = $stmt->fetchAll();

        $formatted = [];

        foreach ($availability as $day) {
            $formatted[] = [
                'day' => strtolower($day['day_of_week']),
                'is_active' => (bool)$day['is_active'],
                'start_time' => $day['start_time'],
                'end_time' => $day['end_time'],
                'break_minutes' => (int)$day['break_minutes']
            ];
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'doctor_id' => $doctorId,
            'schedule' => $formatted
        ]);
        exit;

    // ===================== INVALID METHOD =====================
    } else {
        http_response_code(405);
        echo json_encode(['error' => true, 'message' => 'Method not allowed']);
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>