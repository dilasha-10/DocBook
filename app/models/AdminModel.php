<?php

require_once __DIR__ . '/../../config/database.php';

function admin_get_demo_dashboard_data(): array
{
    return [
        'demo' => true,
        'total_doctors' => 14,
        'total_patients' => 86,
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
        'appointments' => $appointments,
    ];
}
