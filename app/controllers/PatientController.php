<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';

//  Helper: get current logged-in user for navbar (delegates to AuthController)
function current_user(): ?array
{
    return auth_user();
}

//  Page: /categories 
function categories_page()
{
    $user = require_auth();
    $categories = get_all_categories();

    $category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
    $search   = isset($_GET['search'])   && $_GET['search']   !== '' ? $_GET['search']   : null;

    $raw     = get_filtered_doctors($category, $search);
    $doctors = array_map(function ($d) {
        return [
            'id'        => $d['id'],
            'name'      => $d['name'],
            'photo'     => $d['photo'],
            'specialty' => $d['specialty'],
            'category'  => $d['category_name'],
            'available' => !empty($d['next_available_date']),
            'next_date' => $d['next_available_date'] ?? null,
        ];
    }, $raw);

    render('patient/categories', [
        'user'       => $user,
        'categories' => $categories,
        'doctors'    => $doctors,
        'selected'   => $_GET['category'] ?? 'all',
        'search'     => $_GET['search']   ?? '',
    ]);
}

//  Page: /dashboard 
function dashboard_page()
{
    $user = require_auth();

    if ($user['role'] === 'doctor') {
        redirect('/doctor/dashboard');
    }

    $patient_id   = (int) $user['id'];
    $patient_name = $user['name'];

    $upcoming = get_upcoming_appointments($patient_id);
    $past     = get_past_appointments($patient_id);
    $stats    = get_appointment_stats($patient_id);

    render('patient/dashboard', [
        'user'         => current_user(),
        'patient_name' => $patient_name,
        'upcoming'     => $upcoming,
        'past'         => $past,
        'stats'        => $stats,
    ]);
}

//  Page: /profile 
function profile_page()
{
    $user = current_user();

    render('patient/profile', [
        'user' => $user,
    ]);
}

//  API: POST /api/profile 
function api_update_profile()
{
    $user = require_auth_api();
    $id   = (int) $user['id'];
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    $name  = trim($body['name']  ?? '');
    $phone = trim($body['phone'] ?? '');

    if ($name === '') {
        json_response(['success' => false, 'message' => 'Name is required.'], 422);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
    $stmt->execute([':name' => $name, ':phone' => $phone, ':id' => $id]);

    json_response(['success' => true, 'message' => 'Profile updated.']);
}

//  API: POST /api/settings/password 
function api_change_password()
{
    $user = require_auth_api();
    $id   = (int) $user['id'];
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    $current = $body['current_password'] ?? '';
    $new     = $body['new_password']     ?? '';
    $confirm = $body['confirm_password'] ?? '';

    if (strlen($new) < 8) {
        json_response(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
    }
    if ($new !== $confirm) {
        json_response(['success' => false, 'message' => 'Passwords do not match.'], 422);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($current, $row['password'])) {
        json_response(['success' => false, 'message' => 'Current password is incorrect.'], 403);
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $upd  = $pdo->prepare("UPDATE users SET password = :pw WHERE id = :id");
    $upd->execute([':pw' => $hash, ':id' => $id]);

    json_response(['success' => true, 'message' => 'Password changed successfully.']);
}

//  Page: /booking/confirm
function booking_confirm_page()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['booking_confirmation'])) {
        redirect('/categories');
    }

    $appointment = $_SESSION['booking_confirmation'];
    unset($_SESSION['booking_confirmation']); // clear after viewing so refreshing also redirects

    render('patient/booking-confirm', [
        'user'        => current_user(),
        'appointment' => $appointment,
    ]);
}

//  API: GET /api/categories 
function api_get_categories()
{
    $categories = get_all_categories();
    json_response(['success' => true, 'data' => $categories]);
}

//  API: GET /api/doctors?category=<slug>&search=<n> 
function api_get_doctors()
{
    $category = isset($_GET['category']) && $_GET['category'] !== ''
                    ? $_GET['category'] : null;
    $search   = isset($_GET['search'])   && $_GET['search']   !== ''
                    ? $_GET['search']   : null;

    $doctors = get_filtered_doctors($category, $search);
    json_response(['success' => true, 'data' => $doctors]);
}

//  API: POST /api/appointments/:id/reschedule 
function api_reschedule_appointment($id)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];
    $body       = json_decode(file_get_contents('php://input'), true) ?? [];

    $new_date  = trim($body['date']       ?? '');
    $new_start = trim($body['start_time'] ?? '');
    $new_end   = trim($body['end_time']   ?? '');

    // Validate required fields
    if (!$new_date || !$new_start || !$new_end) {
        json_response(['success' => false, 'message' => 'Missing required fields: date, start_time, end_time.'], 422);
    }

    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date) || !strtotime($new_date)) {
        json_response(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
    }

    // Must not be in the past
    if ($new_date < date('Y-m-d')) {
        json_response(['success' => false, 'message' => 'Cannot reschedule to a past date.'], 422);
    }

    // Validate time format (HH:MM or HH:MM:SS)
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $new_start) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $new_end)) {
        json_response(['success' => false, 'message' => 'Invalid time format. Use HH:MM.'], 422);
    }

    $result = reschedule_appointment((int)$id, $new_date, $new_start, $new_end, $patient_id);

    $status = $result['status'] ?? ($result['success'] ? 200 : 500);
    unset($result['status']);
    json_response($result, $status);
}

//  API: PATCH /api/appointments/:id/cancel
function api_cancel_appointment($id)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];

    $result = cancel_appointment((int)$id, $patient_id);

    $status = $result['status'] ?? ($result['success'] ? 200 : 500);
    unset($result['status']);
    json_response($result, $status);
}

//  API: POST /api/appointments 
function api_book_appointment()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];
    $body       = json_decode(file_get_contents('php://input'), true) ?? [];

    $doctor_id  = (int) ($body['doctor_id']   ?? 0);
    $date       = trim($body['date']           ?? '');
    $start_time = trim($body['start_time']     ?? '');
    $end_time   = trim($body['end_time']       ?? '');
    $reason     = trim($body['visit_reason']   ?? '');

    if (!$doctor_id || !$date || !$start_time || !$end_time) {
        json_response(['success' => false, 'message' => 'Missing required fields.'], 422);
    }

    $pdo = db_connect();

    // Generate unique reference number
    do {
        $ref = 'DBK-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $chk = $pdo->prepare("SELECT id FROM appointments WHERE reference_number = :r");
        $chk->execute([':r' => $ref]);
    } while ($chk->fetch());

    $stmt = $pdo->prepare("
        INSERT INTO appointments
            (patient_id, doctor_id, appointment_date, start_time, end_time, reference_number, status, visit_reason)
        VALUES
            (:pid, :did, :date, :start, :end, :ref, 'Confirmed', :reason)
    ");
    $stmt->execute([
        ':pid'    => $patient_id,
        ':did'    => $doctor_id,
        ':date'   => $date,
        ':start'  => $start_time,
        ':end'    => $end_time,
        ':ref'    => $ref,
        ':reason' => $reason ?: null,
    ]);

    $doc = get_doctor_by_id($doctor_id);

    $h    = (int) substr($start_time, 0, 2);
    $m    = substr($start_time, 3, 2);
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $h12  = $h % 12 ?: 12;
    $timeFormatted = "{$h12}:{$m} {$ampm}";

    $_SESSION['booking_confirmation'] = [
        'reference_number' => $ref,
        'doctor_name'      => $doc['name']      ?? 'Unknown',
        'specialty'        => $doc['specialty']  ?? '',
        'date'             => $date,
        'time'             => $timeFormatted,
        'status'           => 'Confirmed',
    ];

    json_response(['success' => true, 'redirect' => BASE_URL . '/booking/confirm']);
}
//  Page: /appointments/{id}/reschedule 
function reschedule_page(int $appt_id)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $authUser   = require_auth();
    $patient_id = (int) $authUser['id'];

    $appt = get_appointment_by_id($appt_id);

    if (!$appt || (int)$appt['patient_id'] !== $patient_id) {
        http_response_code(404);
        echo '<h1>Appointment not found.</h1>';
        exit;
    }

    if (in_array($appt['status'], ['Cancelled', 'Completed', 'Rescheduled'])) {
        redirect('/dashboard');
    }

    $doctor       = get_doctor_by_id((int)$appt['doctor_id']);
    $availability = get_doctor_availability((int)$appt['doctor_id']);

    render('patient/reschedule', [
        'user'         => current_user(),
        'appt'         => $appt,
        'doctor'       => $doctor,
        'availability' => $availability,
    ]);
}

//  Page: /doctors/{id} 
function doctor_booking_page(int $doctor_id)
{
    $authUser = require_auth();
    $doctor = get_doctor_by_id($doctor_id);

    if (!$doctor) {
        http_response_code(404);
        echo '<h1>Doctor not found.</h1>';
        exit;
    }

    $availability = get_doctor_availability($doctor_id);

    render('patient/doctor-booking', [
        'user'         => $authUser,
        'doctor'       => $doctor,
        'availability' => $availability,
    ]);
}

//  API: GET /api/slots?doctor_id=X&date=YYYY-MM-DD 
function api_get_slots()
{
    $doctor_id = (int) ($_GET['doctor_id'] ?? 0);
    $date      = trim($_GET['date']        ?? '');

    if (!$doctor_id || !$date) {
        json_response(['success' => false, 'booked' => []]);
    }

    $booked = get_booked_slots($doctor_id, $date);
    json_response(['success' => true, 'booked' => $booked]);
}
//  API: GET /api/patient/appointments 
function api_patient_appointments()
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];
    $data = get_patient_appointments_list($patient_id);
    json_response(['success' => true, 'data' => $data]);
}

//  API: GET /api/appointments/:id 
function api_get_appointment_detail(int $id)
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];
    $appt = get_appointment_detail_with_comment($id);
    if (!$appt) {
        json_response(['success' => false, 'message' => 'Not found.'], 404);
    }
    if ((int)$appt['patient_id'] !== (int)$patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }
    json_response(['success' => true, 'data' => $appt]);
}

//  API: GET /api/appointments/:id/comments 
function api_get_comments(int $id)
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];

    // Verify ownership
    $appt = get_appointment_by_id($id);
    if (!$appt || (int)$appt['patient_id'] !== (int)$patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }
    $comments = get_appointment_comments($id);
    json_response(['success' => true, 'data' => $comments]);
}

//  API: POST /api/appointments/:id/comments
function api_post_comment(int $id)
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];

    // Verify ownership
    $appt = get_appointment_by_id($id);
    if (!$appt || (int)$appt['patient_id'] !== (int)$patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }
    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $message = trim($body['message'] ?? '');
    if ($message === '') {
        json_response(['success' => false, 'message' => 'Message cannot be empty.'], 422);
    }
    $comment = create_appointment_comment($id, (int)$patient_id, $message);
    json_response(['success' => true, 'data' => $comment], 201);
}

//  API: GET /api/messages/:appointment_id 
function api_get_messages(int $appointment_id): void
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];

    $appt = get_appointment_by_id($appointment_id);
    if (!$appt || (int)$appt['patient_id'] !== $patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "SELECT m.id, m.sender_id, m.sender_role, m.message, m.is_read,
                m.created_at, u.name AS sender_name
         FROM messages m
         JOIN users u ON u.id = m.sender_id
         WHERE m.appointment_id = :appt_id
         ORDER BY m.created_at ASC"
    );
    $stmt->execute([':appt_id' => $appointment_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark unread messages as read for this patient
    $pdo->prepare(
        "UPDATE messages SET is_read = 1
         WHERE appointment_id = :appt_id AND sender_role = 'doctor' AND is_read = 0"
    )->execute([':appt_id' => $appointment_id]);

    json_response(['success' => true, 'data' => $messages]);
}

//  API: POST /api/messages/:appointment_id 
function api_send_message(int $appointment_id): void
{
    $authUser   = require_auth_api();
    $patient_id = (int) $authUser['id'];

    $appt = get_appointment_by_id($appointment_id);
    if (!$appt || (int)$appt['patient_id'] !== $patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $message = trim($body['message'] ?? '');
    if ($message === '') {
        json_response(['success' => false, 'message' => 'Message cannot be empty.'], 422);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO messages (appointment_id, sender_id, sender_role, message)
         VALUES (:appt_id, :sender_id, 'patient', :message)"
    );
    $stmt->execute([
        ':appt_id'   => $appointment_id,
        ':sender_id' => $patient_id,
        ':message'   => $message,
    ]);
    $new_id = (int) $pdo->lastInsertId();

    $row = $pdo->prepare(
        "SELECT m.id, m.sender_id, m.sender_role, m.message, m.is_read,
                m.created_at, u.name AS sender_name
         FROM messages m JOIN users u ON u.id = m.sender_id
         WHERE m.id = :id"
    );
    $row->execute([':id' => $new_id]);
    $msg = $row->fetch(PDO::FETCH_ASSOC);

    json_response(['success' => true, 'data' => $msg], 201);
}

// ── Page: /chat/:appointment_id ───────────────────────────────────────────────
function chat_page(int $appointment_id): void
{
    $user = require_auth();

    $appt = get_appointment_by_id($appointment_id);
    if (!$appt || (int)$appt['patient_id'] !== (int)$user['id']) {
        http_response_code(403);
        echo '<h1>Forbidden</h1>';
        exit;
    }

    render('patient/chat', compact('user', 'appt'));
}