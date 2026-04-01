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
          AND a.status NOT IN ('Cancelled', 'Completed', 'Rescheduled')
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

// Fetch a single appointment by ID (with doctor info)
function get_appointment_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.patient_id,
            a.doctor_id,
            a.appointment_date  AS date,
            a.start_time        AS time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            u.name              AS doctor_name,
            d.specialty,
            d.fee
        FROM appointments a
        JOIN doctors  d ON a.doctor_id = d.id
        JOIN users    u ON d.user_id   = u.id
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Reschedule: atomically release old slot and book new one
function reschedule_appointment(int $id, string $new_date, string $new_start, string $new_end, int $patient_id): array
{
    $pdo = db_connect();

    $appt = get_appointment_by_id($id);
    if (!$appt) {
        return ['success' => false, 'message' => 'Appointment not found.', 'status' => 404];
    }
    if ((int)$appt['patient_id'] !== $patient_id) {
        return ['success' => false, 'message' => 'Forbidden.', 'status' => 403];
    }
    if (in_array($appt['status'], ['Cancelled', 'Completed', 'Rescheduled'])) {
        return ['success' => false, 'message' => 'This appointment cannot be rescheduled.', 'status' => 409];
    }

    $pdo->beginTransaction();
    try {
        // Check new slot availability (excluding the current appointment)
        $chk = $pdo->prepare("
            SELECT id FROM appointments
            WHERE doctor_id        = :did
              AND appointment_date = :date
              AND start_time       = :start
              AND status NOT IN ('Cancelled')
              AND id != :id
        ");
        $chk->execute([
            ':did'   => $appt['doctor_id'],
            ':date'  => $new_date,
            ':start' => $new_start,
            ':id'    => $id,
        ]);
        if ($chk->fetch()) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'That time slot is already booked.', 'status' => 409];
        }

        // Generate unique reference number for new appointment
        do {
            $ref = 'DBK-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $refChk = $pdo->prepare("SELECT id FROM appointments WHERE reference_number = :r");
            $refChk->execute([':r' => $ref]);
        } while ($refChk->fetch());

        // Mark old appointment as Rescheduled
        $upd = $pdo->prepare("UPDATE appointments SET status = 'Rescheduled' WHERE id = :id");
        $upd->execute([':id' => $id]);

        // Create new appointment with Pending status
        $ins = $pdo->prepare("
            INSERT INTO appointments
                (patient_id, doctor_id, appointment_date, start_time, end_time, reference_number, status, visit_reason)
            VALUES
                (:pid, :did, :date, :start, :end, :ref, 'Pending', :reason)
        ");
        $ins->execute([
            ':pid'    => $appt['patient_id'],
            ':did'    => $appt['doctor_id'],
            ':date'   => $new_date,
            ':start'  => $new_start,
            ':end'    => $new_end,
            ':ref'    => $ref,
            ':reason' => $appt['visit_reason'],
        ]);

        $pdo->commit();
        return ['success' => true, 'reference_number' => $ref];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'An error occurred. Please try again.', 'status' => 500];
    }
}

// Cancel: update status, free slot, write audit log — all in one transaction
function cancel_appointment(int $id, int $patient_id): array
{
    $pdo = db_connect();

    $appt = get_appointment_by_id($id);
    if (!$appt) {
        return ['success' => false, 'message' => 'Appointment not found.', 'status' => 404];
    }
    if ((int)$appt['patient_id'] !== $patient_id) {
        return ['success' => false, 'message' => 'Forbidden.', 'status' => 403];
    }
    if (in_array($appt['status'], ['Cancelled', 'Completed'])) {
        return ['success' => false, 'message' => 'This appointment is already ' . strtolower($appt['status']) . '.', 'status' => 409];
    }

    $pdo->beginTransaction();
    try {
        // Update status to Cancelled
        $upd = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = :id");
        $upd->execute([':id' => $id]);

        // Write audit log entry (best-effort — table may not exist yet)
        try {
            $log = $pdo->prepare("
                INSERT INTO appointment_audit_log
                    (appointment_id, action, performed_by, performed_at)
                VALUES
                    (:appt_id, 'Cancelled', :user_id, NOW())
            ");
            $log->execute([':appt_id' => $id, ':user_id' => $patient_id]);
        } catch (Exception $e) {
            // audit log table not yet created — skip silently
        }

        $pdo->commit();
        return ['success' => true];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'An error occurred. Please try again.', 'status' => 500];
    }
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