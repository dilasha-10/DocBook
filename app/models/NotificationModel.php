<?php

require_once BASE_PATH . '/config/database.php';

// ════════════════════════════════════════════════════════════
//  NotificationModel — core helpers for the notification system
// ════════════════════════════════════════════════════════════

// ── Low-level insert ─────────────────────────────────────────

/**
 * Insert a single notification row.
 * Returns the new row's ID.
 */
function notification_insert(
    ?int    $recipient_id,
    ?string $target_role,
    ?int    $sender_id,
    string  $type,
    string  $title,
    string  $message,
    ?int    $appointment_id = null
): int {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO notifications
              (recipient_id, target_role, sender_id, type, title, message, appointment_id, created_at)
        VALUES(:recipient_id, :target_role, :sender_id, :type, :title, :message, :appointment_id, NOW())
    ");
    $stmt->execute([
        ':recipient_id'   => $recipient_id,
        ':target_role'    => $target_role,
        ':sender_id'      => $sender_id,
        ':type'           => $type,
        ':title'          => $title,
        ':message'        => $message,
        ':appointment_id' => $appointment_id,
    ]);
    return (int) $pdo->lastInsertId();
}

// ── Appointment-triggered notifications ──────────────────────

/**
 * Called when a patient successfully books an appointment.
 * → Sends a notification to the doctor.
 * → Sends a confirmation notification to the patient.
 */
function notify_appointment_booked(array $appointment, array $patient, array $doctor): void
{
    $apptDate = date('D, d M Y', strtotime($appointment['appointment_date']));
    $apptTime = date('g:i A', strtotime($appointment['start_time']));
    $ref      = $appointment['reference_number'] ?? '';

    // Notify doctor
    notification_insert(
        recipient_id:   $doctor['user_id'],
        target_role:    null,
        sender_id:      null,
        type:           'appointment_booked',
        title:          'New Appointment Booked',
        message:        "A new appointment has been booked by {$patient['name']} on {$apptDate} at {$apptTime}. Reference: {$ref}.",
        appointment_id: $appointment['id']
    );

    // Notify patient (confirmation)
    notification_insert(
        recipient_id:   $patient['id'],
        target_role:    null,
        sender_id:      null,
        type:           'appointment_booked',
        title:          'Appointment Confirmed',
        message:        "Your appointment with Dr. {$doctor['name']} on {$apptDate} at {$apptTime} has been booked. Reference: {$ref}.",
        appointment_id: $appointment['id']
    );
}

/**
 * Called when an appointment is cancelled.
 * Notifies the other party (patient cancels → doctor gets notified, vice versa).
 */
function notify_appointment_cancelled(array $appointment, array $cancelledBy, array $otherParty, string $otherRole): void
{
    $apptDate = date('D, d M Y', strtotime($appointment['appointment_date']));
    $ref      = $appointment['reference_number'] ?? '';

    notification_insert(
        recipient_id:   $otherParty['id'],
        target_role:    null,
        sender_id:      $cancelledBy['id'],
        type:           'appointment_cancelled',
        title:          'Appointment Cancelled',
        message:        "The appointment on {$apptDate} (Ref: {$ref}) has been cancelled by {$cancelledBy['name']}.",
        appointment_id: $appointment['id']
    );
}

/**
 * Called when a doctor confirms an appointment.
 */
function notify_appointment_confirmed(array $appointment, array $patient, array $doctor): void
{
    $apptDate = date('D, d M Y', strtotime($appointment['appointment_date']));
    $apptTime = date('g:i A', strtotime($appointment['start_time']));
    $ref      = $appointment['reference_number'] ?? '';

    notification_insert(
        recipient_id:   $patient['id'],
        target_role:    null,
        sender_id:      $doctor['user_id'],
        type:           'appointment_confirmed',
        title:          'Appointment Confirmed by Doctor',
        message:        "Dr. {$doctor['name']} has confirmed your appointment on {$apptDate} at {$apptTime}. Reference: {$ref}.",
        appointment_id: $appointment['id']
    );
}

/**
 * Called when a lab admin uploads a report for an appointment.
 * → Notifies the patient that their report is ready.
 * → Notifies the doctor that a report has been uploaded for their patient.
 */
function notify_lab_report_uploaded(array $appointment, array $patient, array $doctor, string $labAdminName): void
{
    $apptDate = date('D, d M Y', strtotime($appointment['appointment_date']));
    $ref      = $appointment['reference_number'] ?? '';

    // Notify patient
    notification_insert(
        recipient_id:   $patient['id'],
        target_role:    null,
        sender_id:      null,
        type:           'lab_report_uploaded',
        title:          'Lab Report Ready',
        message:        "Your lab report for your appointment on {$apptDate} (Ref: {$ref}) has been uploaded and is now available. Please log in to view it.",
        appointment_id: $appointment['id']
    );

    // Notify doctor
    notification_insert(
        recipient_id:   $doctor['user_id'],
        target_role:    null,
        sender_id:      null,
        type:           'lab_report_uploaded',
        title:          'Lab Report Uploaded',
        message:        "A lab report for patient {$patient['name']} (appointment {$apptDate}, Ref: {$ref}) has been uploaded by {$labAdminName}.",
        appointment_id: $appointment['id']
    );
}

// ── Admin broadcast notifications ────────────────────────────

/**
 * Send a system-wide broadcast.
 * $target_roles: array of roles, e.g. ['patient', 'doctor'] or ['all']
 * Returns the broadcast ID and total recipients.
 */
function notify_broadcast(
    int    $sender_id,
    string $title,
    string $message,
    array  $target_roles,
    string $type = 'system_maintenance'
): array {
    $pdo = db_connect();

    // Build recipient query
    $all_roles = ['patient', 'doctor', 'admin', 'lab_admin'];
    $roles     = in_array('all', $target_roles, true) ? $all_roles : $target_roles;

    $placeholders = implode(',', array_fill(0, count($roles), '?'));
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role IN ({$placeholders})");
    $stmt->execute($roles);
    $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $count = 0;
    foreach ($recipients as $uid) {
        notification_insert(
            recipient_id:   (int) $uid,
            target_role:    null,
            sender_id:      $sender_id,
            type:           $type,
            title:          $title,
            message:        $message,
            appointment_id: null
        );
        $count++;
    }

    // Log the broadcast
    $stmt = $pdo->prepare("
        INSERT INTO notification_broadcasts (sender_id, title, message, target_roles, type, total_sent, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$sender_id, $title, $message, json_encode($target_roles), $type, $count]);
    $broadcast_id = (int) $pdo->lastInsertId();

    return ['broadcast_id' => $broadcast_id, 'total_sent' => $count];
}

/**
 * Send a targeted notification to a specific user by ID.
 */
function notify_targeted(int $sender_id, int $recipient_id, string $title, string $message): int
{
    return notification_insert(
        recipient_id:   $recipient_id,
        target_role:    null,
        sender_id:      $sender_id,
        type:           'targeted',
        title:          $title,
        message:        $message,
        appointment_id: null
    );
}

// ── Fetch notifications for a user ───────────────────────────

/**
 * Get paginated notifications for a given user.
 */
function notifications_for_user(int $user_id, int $limit = 30, int $offset = 0): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT n.*,
               u.name AS sender_name
        FROM   notifications n
        LEFT JOIN users u ON u.id = n.sender_id
        WHERE  n.recipient_id = :uid
        ORDER  BY n.created_at DESC
        LIMIT  :limit OFFSET :offset
    ");
    $stmt->bindValue(':uid',    $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit',  $limit,   PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Count unread notifications for a user.
 */
function notifications_unread_count(int $user_id): int
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = :uid AND is_read = 0");
    $stmt->execute([':uid' => $user_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Mark a single notification as read.
 */
function notification_mark_read(int $notification_id, int $user_id): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND recipient_id = :uid");
    $stmt->execute([':id' => $notification_id, ':uid' => $user_id]);
    return $stmt->rowCount() > 0;
}

/**
 * Mark all notifications as read for a user.
 */
function notifications_mark_all_read(int $user_id): int
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE recipient_id = :uid AND is_read = 0");
    $stmt->execute([':uid' => $user_id]);
    return $stmt->rowCount();
}

// ── Admin analytics ──────────────────────────────────────────

/**
 * Get all broadcasts with delivery stats.
 */
function notification_broadcasts_list(int $limit = 50): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT nb.*,
               u.name  AS sender_name,
               (SELECT COUNT(*) FROM notifications n
                WHERE  n.sender_id  = nb.sender_id
                  AND  n.title      = nb.title
                  AND  ABS(TIMESTAMPDIFF(SECOND, n.created_at, nb.created_at)) < 5
                  AND  n.is_read    = 1) AS read_count
        FROM   notification_broadcasts nb
        JOIN   users u ON u.id = nb.sender_id
        ORDER  BY nb.created_at DESC
        LIMIT  :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Search users by name for targeted notification.
 */
function notification_search_users(string $query): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT id, name, email, role
        FROM   users
        WHERE  name LIKE :q OR email LIKE :q
        LIMIT  10
    ");
    $stmt->execute([':q' => '%' . $query . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}