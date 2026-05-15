<?php

require_once __DIR__ . '/../../config/database.php';

function admin_get_status_counts(PDO $pdo, string $startDate, string $endDate): array
{
    $stmt = $pdo->prepare(
        "SELECT
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_count,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled_count
         FROM appointments
         WHERE appointment_date BETWEEN :start_date AND :end_date"
    );
    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'completed' => (int)($row['completed_count'] ?? 0),
        'cancelled' => (int)($row['cancelled_count'] ?? 0),
    ];
}

function admin_get_demo_dashboard_data(): array
{
    return [
        'demo' => true,
        'total_doctors' => 14,
        'total_patients' => 86,
        'appointment_stats' => [
            'today' => ['completed' => 6, 'cancelled' => 1],
            'week' => ['completed' => 28, 'cancelled' => 5],
            'month' => ['completed' => 112, 'cancelled' => 17],
        ],
        'activity' => [
            [
                'title' => 'Completed appointment',
                'details' => 'Sara Miles with Dr. Emily Stone',
                'status' => 'Completed',
                'reference' => 'DBK-2026-1024',
                'time' => date('Y-m-d 09:30:00'),
            ],
            [
                'title' => 'Cancelled appointment',
                'details' => 'Marco Lee with Dr. Sophia Patel',
                'status' => 'Cancelled',
                'reference' => 'DBK-2026-2118',
                'time' => date('Y-m-d 11:15:00'),
            ],
            [
                'title' => 'Confirmed appointment',
                'details' => 'Leon Ortiz with Dr. Michael Green',
                'status' => 'Confirmed',
                'reference' => 'DBK-2026-3652',
                'time' => date('Y-m-d 14:05:00'),
            ],
            [
                'title' => 'Completed appointment',
                'details' => 'Priya Shah with Dr. Oliver Ross',
                'status' => 'Completed',
                'reference' => 'DBK-2026-4021',
                'time' => date('Y-m-d 15:40:00'),
            ],
            [
                'title' => 'Cancelled appointment',
                'details' => 'Nina Park with Dr. Grace Lee',
                'status' => 'Cancelled',
                'reference' => 'DBK-2026-4488',
                'time' => date('Y-m-d 16:20:00'),
            ],
            [
                'title' => 'Completed appointment',
                'details' => 'Carlos Vega with Dr. Emma Clark',
                'status' => 'Completed',
                'reference' => 'DBK-2026-4722',
                'time' => date('Y-m-d 17:05:00'),
            ],
            [
                'title' => 'Confirmed appointment',
                'details' => 'Maya Singh with Dr. Zoe Adams',
                'status' => 'Confirmed',
                'reference' => 'DBK-2026-4900',
                'time' => date('Y-m-d 17:40:00'),
            ],
            [
                'title' => 'Completed appointment',
                'details' => 'Jacob Reed with Dr. Dylan Hart',
                'status' => 'Completed',
                'reference' => 'DBK-2026-5129',
                'time' => date('Y-m-d 18:15:00'),
            ],
            [
                'title' => 'Cancelled appointment',
                'details' => 'Aria Patel with Dr. Sam Bennett',
                'status' => 'Cancelled',
                'reference' => 'DBK-2026-5384',
                'time' => date('Y-m-d 19:00:00'),
            ],
            [
                'title' => 'Completed appointment',
                'details' => 'Ethan Brooks with Dr. Olivia Cruz',
                'status' => 'Completed',
                'reference' => 'DBK-2026-5599',
                'time' => date('Y-m-d 19:35:00'),
            ],
        ],
        'appointments' => [
            [
                'id' => 101,
                'date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '09:30:00',
                'end_time' => '10:00:00',
                'status' => 'Pending',
                'reference_number' => 'DBK-2026-1024',
                'visit_reason' => 'General checkup',
                'patient_name' => 'Sara Miles',
                'doctor_name' => 'Dr. Emily Stone',
                'specialty' => 'Cardiology',
                'category' => 'Heart Care',
            ],
            [
                'id' => 102,
                'date' => date('Y-m-d', strtotime('+2 days')),
                'start_time' => '13:00:00',
                'end_time' => '13:30:00',
                'status' => 'Confirmed',
                'reference_number' => 'DBK-2026-2118',
                'visit_reason' => 'Follow-up review',
                'patient_name' => 'Marco Lee',
                'doctor_name' => 'Dr. Sophia Patel',
                'specialty' => 'Dermatology',
                'category' => 'Skin Health',
            ],
            [
                'id' => 103,
                'date' => date('Y-m-d', strtotime('+3 days')),
                'start_time' => '15:15:00',
                'end_time' => '15:45:00',
                'status' => 'Completed',
                'reference_number' => 'DBK-2026-3652',
                'visit_reason' => 'Medication refill',
                'patient_name' => 'Leon Ortiz',
                'doctor_name' => 'Dr. Michael Green',
                'specialty' => 'General Practice',
                'category' => 'Primary Care',
            ],
        ],
    ];
}

function admin_get_dashboard_data(): array
{
    try {
        $pdo = db_connect();
    } catch (Exception $e) {
        return admin_get_demo_dashboard_data();
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
        $totalDoctors = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
        $stmt->execute([':role' => 'patient']);
        $totalPatients = (int) $stmt->fetchColumn();

        $stmt = $pdo->query(
            "SELECT
                 a.id,
                 a.appointment_date AS date,
                 a.start_time,
                 a.end_time,
                 a.status,
                 a.reference_number,
                 a.visit_reason,
                 p.name AS patient_name,
                 u.name AS doctor_name,
                 d.specialty,
                 c.name AS category
             FROM appointments a
             LEFT JOIN users p ON p.id = a.patient_id
             LEFT JOIN doctors d ON d.id = a.doctor_id
             LEFT JOIN users u ON u.id = d.user_id
             LEFT JOIN categories c ON c.id = d.category_id
             ORDER BY a.appointment_date DESC, a.start_time ASC"
        );
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $stats = [
            'today' => admin_get_status_counts($pdo, $today, $today),
            'week' => admin_get_status_counts($pdo, $weekStart, $weekEnd),
            'month' => admin_get_status_counts($pdo, $monthStart, $monthEnd),
        ];

        $stmt = $pdo->query(
            "SELECT
                 a.reference_number,
                 a.status,
                 a.appointment_date,
                 a.start_time,
                 p.name AS patient_name,
                 u.name AS doctor_name
             FROM appointments a
             LEFT JOIN users p ON p.id = a.patient_id
             LEFT JOIN doctors d ON d.id = a.doctor_id
             LEFT JOIN users u ON u.id = d.user_id
             ORDER BY a.appointment_date DESC, a.start_time DESC
             LIMIT 10"
        );
        $activityRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activity = [];
        foreach ($activityRows as $row) {
            $status = $row['status'] ?? 'Pending';
            $label = $status ? $status . ' appointment' : 'Appointment update';
            $details = trim(($row['patient_name'] ?? 'Patient') . ' with ' . ($row['doctor_name'] ?? 'Doctor'));
            $time = trim(($row['appointment_date'] ?? '') . ' ' . ($row['start_time'] ?? ''));
            $activity[] = [
                'title' => $label,
                'details' => $details,
                'status' => $status,
                'reference' => $row['reference_number'] ?? null,
                'time' => $time,
            ];
        }

    } catch (Exception $e) {
        return admin_get_demo_dashboard_data();
    }

    if ($totalDoctors === 0 && $totalPatients === 0 && count($appointments) === 0) {
        return admin_get_demo_dashboard_data();
    }

    foreach ($appointments as &$appointment) {
        $appointment['id'] = isset($appointment['id']) ? (int) $appointment['id'] : 0;
    }
    unset($appointment);

    return [
        'demo' => false,
        'total_doctors' => $totalDoctors,
        'total_patients' => $totalPatients,
        'appointment_stats' => $stats ?? [
            'today' => ['completed' => 0, 'cancelled' => 0],
            'week' => ['completed' => 0, 'cancelled' => 0],
            'month' => ['completed' => 0, 'cancelled' => 0],
        ],
        'activity' => $activity ?? [],
        'appointments' => $appointments,
    ];
}
