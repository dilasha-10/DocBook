<?php

require_once __DIR__ . '/../../config/database.php';

// Auto-mark confirmed appointments as Completed if their end time has passed
function auto_complete_past_appointments(): void
{
    $pdo = db_connect();
    $pdo->prepare("
        UPDATE appointments
        SET status = 'Completed'
        WHERE status = 'Confirmed'
          AND CONCAT(appointment_date, ' ', end_time) < NOW()
    ")->execute();
}

// Upcoming appointments: end_time is still in the future, not cancelled/completed
function get_upcoming_appointments($patient_id) {
    auto_complete_past_appointments();
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
            c.name               AS category
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users      u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        WHERE a.patient_id = :pid
          AND CONCAT(a.appointment_date, ' ', a.end_time) > NOW()
          AND a.status NOT IN ('Cancelled', 'Completed', 'Rescheduled')
        ORDER BY a.appointment_date ASC, a.start_time ASC
    ");
    $stmt->execute([':pid' => $patient_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Past appointments: end_time has passed OR status is completed/cancelled/rescheduled
function get_past_appointments($patient_id) {
    auto_complete_past_appointments();
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
            c.name               AS category
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users      u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        WHERE a.patient_id = :pid
          AND (
              CONCAT(a.appointment_date, ' ', a.end_time) <= NOW()
              OR a.status IN ('Completed', 'Cancelled', 'Rescheduled')
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
            d.specialty
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
              AND status NOT IN ('Cancelled', 'Rescheduled')
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
        $refChk = $pdo->prepare("SELECT id FROM appointments WHERE reference_number = :r");
        do {
            $ref = 'DBK-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $refChk->execute([':r' => $ref]);
        } while ($refChk->fetch());

        // Mark old appointment as Rescheduled
        $upd = $pdo->prepare("UPDATE appointments SET status = 'Rescheduled' WHERE id = :id");
        $upd->execute([':id' => $id]);

        // Carry forward reschedule count
        $newRescheduleCount = (int)($appt['reschedule_count'] ?? 0) + 1;

        // Create new appointment
        $ins = $pdo->prepare("
            INSERT INTO appointments
                (patient_id, doctor_id, appointment_date, start_time, end_time, reference_number, status, visit_reason, reschedule_count)
            VALUES
                (:pid, :did, :date, :start, :end, :ref, 'Confirmed', :reason, :rcount)
        ");
        $ins->execute([
            ':pid'    => $appt['patient_id'],
            ':did'    => $appt['doctor_id'],
            ':date'   => $new_date,
            ':start'  => $new_start,
            ':end'    => $new_end,
            ':ref'    => $ref,
            ':reason' => $appt['visit_reason'],
            ':rcount' => $newRescheduleCount,
        ]);

        $pdo->commit();
        return ['success' => true, 'reference_number' => $ref];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'An error occurred. Please try again.', 'status' => 500];
    }
}

// Cancel: update status and write audit log in one transaction
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
        $upd = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = :id");
        $upd->execute([':id' => $id]);

        // Write audit log entry (best-effort)
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
    auto_complete_past_appointments();
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN CONCAT(appointment_date, ' ', end_time) > NOW()
                      AND status NOT IN ('Cancelled','Completed','Rescheduled') THEN 1 ELSE 0 END) AS upcoming,
            COUNT(*)                                                                               AS total,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END)                                  AS pending
        FROM appointments
        WHERE patient_id = :pid
    ");
    $stmt->execute([':pid' => $patient_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['upcoming' => 0, 'total' => 0, 'pending' => 0];
}

// Detail view with lab report and doctor comment
function get_appointment_detail_with_comment(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.patient_id,
            a.appointment_date  AS date,
            a.start_time        AS time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            u.name              AS doctor_name,
            d.specialty,
            c.name              AS category,
            lr.file_path        AS lab_report_path,
            (SELECT ac.message
               FROM appointment_comments ac
               JOIN doctors dx ON dx.user_id = ac.user_id
              WHERE ac.appointment_id = a.id
                AND dx.id = a.doctor_id
                AND ac.parent_id IS NULL
              ORDER BY ac.created_at DESC
              LIMIT 1)          AS doctor_comment
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users      u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN lab_reports lr ON lr.appointment_id = a.id
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Full threaded comment list for an appointment
function get_appointment_comments(int $appointment_id): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT ac.id, ac.parent_id, ac.message, ac.created_at,
               ac.author_role, u.name
        FROM appointment_comments ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.appointment_id = :id
        ORDER BY ac.created_at ASC
    ");
    $stmt->execute([':id' => $appointment_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Patient posts a reply to a doctor comment
function create_appointment_comment(int $appointment_id, int $user_id, string $message, ?int $parent_id = null): array
{
    $pdo = db_connect();

    // Enforce max 2 patient replies
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM appointment_comments WHERE appointment_id = ? AND author_role = 'patient'");
    $cnt->execute([$appointment_id]);
    if ((int)$cnt->fetchColumn() >= 2) {
        return ['error' => 'You have reached the maximum of 2 replies for this appointment.'];
    }

    // parent_id must reference a doctor comment
    if ($parent_id) {
        $par = $pdo->prepare("SELECT id FROM appointment_comments WHERE id = ? AND appointment_id = ? AND author_role = 'doctor'");
        $par->execute([$parent_id, $appointment_id]);
        if (!$par->fetch()) {
            return ['error' => 'Invalid parent comment.'];
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO appointment_comments (appointment_id, user_id, message, parent_id, author_role)
        VALUES (:appt_id, :user_id, :msg, :parent_id, 'patient')
    ");
    $stmt->execute([
        ':appt_id'   => $appointment_id,
        ':user_id'   => $user_id,
        ':msg'       => $message,
        ':parent_id' => $parent_id,
    ]);
    $new_id = $pdo->lastInsertId();

    $fetch = $pdo->prepare("
        SELECT ac.id, ac.parent_id, ac.message, ac.created_at, ac.author_role, u.name
        FROM appointment_comments ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.id = :id
    ");
    $fetch->execute([':id' => $new_id]);
    return $fetch->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Full appointments list split into upcoming/past — used by appointments page
function get_patient_appointments_list(int $patient_id): array
{
    auto_complete_past_appointments();
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.appointment_date  AS date,
            a.start_time        AS time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            u.name              AS doctor_name,
            d.specialty,
            c.name              AS category,
            CASE
                WHEN CONCAT(a.appointment_date, ' ', a.end_time) > NOW()
                 AND a.status NOT IN ('Cancelled','Completed','Rescheduled')
                THEN 'upcoming'
                ELSE 'past'
            END AS list_type
        FROM appointments a
        JOIN doctors    d ON a.doctor_id   = d.id
        JOIN users      u ON d.user_id     = u.id
        JOIN categories c ON d.category_id = c.id
        WHERE a.patient_id = :pid
        ORDER BY
            CASE WHEN CONCAT(a.appointment_date, ' ', a.end_time) > NOW()
                  AND a.status NOT IN ('Cancelled','Completed','Rescheduled')
                 THEN 0 ELSE 1 END ASC,
            a.appointment_date ASC,
            a.start_time ASC
    ");
    $stmt->execute([':pid' => $patient_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $upcoming = array_filter($rows, fn($r) => $r['list_type'] === 'upcoming');
    $past     = array_filter($rows, fn($r) => $r['list_type'] === 'past');

    return [
        'upcoming' => array_values($upcoming),
        'past'     => array_values($past),
    ];
}