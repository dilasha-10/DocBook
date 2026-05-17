<?php

//  AppointmentAuditController.php
//  Admin: View and Cancel Appointments

require_once BASE_PATH . '/app/models/AppointmentModel.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';

// Admin Page

function admin_appointments_page(): void
{
    $user = require_admin();
    render('admin/appointments', ['user' => $user]);
}

// Admin API: List appointments with filters

function api_admin_appointments_list(): void
{
    require_admin_api();

    $pdo    = db_connect();
    $where  = [];
    $params = [];

    $doctor_id = trim($_GET['doctor_id'] ?? '');
    $date_from = trim($_GET['date_from'] ?? '');
    $date_to   = trim($_GET['date_to']   ?? '');
    $status    = trim($_GET['status']    ?? '');
    $search    = trim($_GET['search']    ?? '');

    if ($doctor_id !== '' && ctype_digit($doctor_id)) {
        $where[]              = 'a.doctor_id = :doctor_id';
        $params[':doctor_id'] = (int) $doctor_id;
    }

    if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        $where[]              = 'a.appointment_date >= :date_from';
        $params[':date_from'] = $date_from;
    }

    if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $where[]            = 'a.appointment_date <= :date_to';
        $params[':date_to'] = $date_to;
    }

    if ($status !== '' && in_array($status, ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rescheduled'])) {
        $where[]           = 'a.status = :status';
        $params[':status'] = $status;
    }

    if ($search !== '') {
        $where[]           = '(pu.name LIKE :search OR du.name LIKE :search OR a.reference_number LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            a.visit_reason,
            a.reference_number,
            pu.name             AS patient_name,
            pu.email            AS patient_email,
            du.name             AS doctor_name,
            d.specialty,
            c.name              AS category_name,
            a.created_at
        FROM appointments a
        JOIN users     pu ON pu.id = a.patient_id
        JOIN doctors   d  ON d.id  = a.doctor_id
        JOIN users     du ON du.id = d.user_id
        JOIN categories c ON c.id  = d.category_id
        {$whereSql}
        ORDER BY a.appointment_date DESC, a.start_time DESC
        LIMIT 500
    ");
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary stats
    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*)                                                          AS total,
            SUM(CASE WHEN a.status = 'Confirmed' THEN 1 ELSE 0 END)         AS confirmed,
            SUM(CASE WHEN a.status = 'Pending'   THEN 1 ELSE 0 END)         AS pending,
            SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END)         AS cancelled,
            SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END)         AS completed
        FROM appointments a
        JOIN users     pu ON pu.id = a.patient_id
        JOIN doctors   d  ON d.id  = a.doctor_id
        JOIN users     du ON du.id = d.user_id
        JOIN categories c ON c.id  = d.category_id
        {$whereSql}
    ");
    $statsStmt->execute($params);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success'      => true,
        'appointments' => $appointments,
        'stats'        => $stats,
    ]);
}

// Admin API: Get list of doctors (for filter dropdown)

function api_admin_doctors_list(): void
{
    require_admin_api();
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT d.id, u.name, d.specialty
        FROM doctors d
        JOIN users u ON u.id = d.user_id
        ORDER BY u.name
    ");
    json_response(['success' => true, 'doctors' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// Admin API: Cancel appointment with reason

function api_admin_cancel_appointment(int $id): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $reason = trim($data['reason'] ?? '');
    if ($reason === '') {
        json_response(['success' => false, 'error' => 'A cancellation reason is required.'], 422);
    }

    $pdo = db_connect();

    // Fetch appointment details
    $stmt = $pdo->prepare("
        SELECT a.*, pu.name AS patient_name, pu.id AS patient_user_id,
               du.name AS doctor_name, d.user_id AS doctor_user_id
        FROM appointments a
        JOIN users pu ON pu.id = a.patient_id
        JOIN doctors d ON d.id = a.doctor_id
        JOIN users du ON du.id = d.user_id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        json_response(['success' => false, 'error' => 'Appointment not found.'], 404);
    }

    if ($appt['status'] === 'Cancelled') {
        json_response(['success' => false, 'error' => 'This appointment is already cancelled.'], 409);
    }

    if ($appt['status'] === 'Completed') {
        json_response(['success' => false, 'error' => 'Completed appointments cannot be cancelled.'], 409);
    }

    // Cancel the appointment
    $upd = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
    $upd->execute([$id]);

    $ref      = $appt['reference_number'] ?? '';
    $apptDate = date('D, d M Y', strtotime($appt['appointment_date']));

    // Notify patient
    notification_insert(
        recipient_id:   (int) $appt['patient_user_id'],
        target_role:    null,
        sender_id:      (int) $user['id'],
        type:           'appointment_cancelled',
        title:          'Appointment Cancelled by Admin',
        message:        "Your appointment on {$apptDate} (Ref: {$ref}) with Dr. {$appt['doctor_name']} has been cancelled by the admin. Reason: {$reason}",
        appointment_id: $id
    );

    // Notify doctor
    notification_insert(
        recipient_id:   (int) $appt['doctor_user_id'],
        target_role:    null,
        sender_id:      (int) $user['id'],
        type:           'appointment_cancelled',
        title:          'Appointment Cancelled by Admin',
        message:        "The appointment with {$appt['patient_name']} on {$apptDate} (Ref: {$ref}) has been cancelled by the admin. Reason: {$reason}",
        appointment_id: $id
    );

    // Audit log
    audit_log(
        (int) $user['id'], $user['name'], $user['role'],
        'APPOINTMENT_CANCELLED', 'admin',
        "Cancelled appointment #{$id} (Ref: {$ref}). Reason: {$reason}"
    );

    json_response(['success' => true]);
}
