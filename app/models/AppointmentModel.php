<?php

require_once __DIR__ . '/../../config/database.php';

// Upcoming appointments (today or future, not cancelled/completed)
function get_upcoming_appointments($patient_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.appointment_date   AS date,
            a.start_time         AS time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            u.name               AS doctor_name,
            d.specialty,
            c.name               AS category,
            d.fee
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users     u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        WHERE a.patient_id = :pid
          AND a.appointment_date >= CURDATE()
          AND a.status NOT IN ('Cancelled', 'Completed')
        ORDER BY a.appointment_date ASC, a.start_time ASC
    ");
    $stmt->execute([':pid' => $patient_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Past appointments (before today OR completed/cancelled)
function get_past_appointments($patient_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.appointment_date   AS date,
            a.start_time         AS time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            u.name               AS doctor_name,
            d.specialty,
            c.name               AS category,
            d.fee
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users     u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        WHERE a.patient_id = :pid
          AND (
              a.appointment_date < CURDATE()
              OR a.status IN ('Completed', 'Cancelled')
          )
        ORDER BY a.appointment_date DESC, a.start_time DESC
    ");
    $stmt->execute([':pid' => $patient_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Aggregate stats for dashboard header cards
function get_appointment_stats($patient_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN appointment_date >= CURDATE()
                      AND status NOT IN ('Cancelled','Completed') THEN 1 ELSE 0 END) AS upcoming,
            COUNT(*)                                                                   AS total,
            SUM(CASE WHEN status = 'Pending'                      THEN 1 ELSE 0 END) AS pending
        FROM appointments
        WHERE patient_id = :pid
    ");
    $stmt->execute([':pid' => $patient_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['upcoming' => 0, 'total' => 0, 'pending' => 0];
}