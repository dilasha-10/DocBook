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

    // Parse filters from URL query parameters
    $category = isset($_GET['category']) && $_GET['category'] !== '' ? $_GET['category'] : null;
    $search   = isset($_GET['search'])   && $_GET['search']   !== '' ? $_GET['search']   : null;

    // Fetch doctors based on filters and map to a UI-friendly structure
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

    // Redirect doctors and admins away from the patient dashboard
    if ($user['role'] === 'doctor') {
        redirect('/doctor/dashboard');
    }
    if ($user['role'] === 'admin') {
        redirect('/admin/dashboard');
    }

    $patient_id   = (int) $user['id'];
    $patient_name = $user['name'];

    // Load grouped appointment data and overview metrics
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

    // Validation: Name is mandatory for identity
    if ($name === '') {
        json_response(['success' => false, 'message' => 'Name is required.'], 422);
    }

    // Direct database update for basic profile info
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

    // Standard length and confirmation check
    if (strlen($new) < 8) {
        json_response(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
    }
    if ($new !== $confirm) {
        json_response(['success' => false, 'message' => 'Passwords do not match.'], 422);
    }

    // Verify the existing password before permitting change
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($current, $row['password'])) {
        json_response(['success' => false, 'message' => 'Current password is incorrect.'], 403);
    }

    // Hash the new password using BCRYPT standard
    $hash = password_hash($new, PASSWORD_BCRYPT);
    $upd  = $pdo->prepare("UPDATE users SET password = :pw WHERE id = :id");
    $upd->execute([':pw' => $hash, ':id' => $id]);

    json_response(['success' => true, 'message' => 'Password changed successfully.']);
}

//  Page: /booking/confirm
function booking_confirm_page()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Ensure user has just completed a booking flow
    if (empty($_SESSION['booking_confirmation'])) {
        redirect('/categories');
    }

    $appointment = $_SESSION['booking_confirmation'];
    // IMPORTANT: Flash data pattern - clear session data once it has been rendered
    unset($_SESSION['booking_confirmation']);

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

    // Strict validation for rescheduling inputs
    if (!$new_date || !$new_start || !$new_end) {
        json_response(['success' => false, 'message' => 'Missing required fields: date, start_time, end_time.'], 422);
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_date) || !strtotime($new_date)) {
        json_response(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
    }

    // Business Logic: No retroactive rescheduling
    if ($new_date < date('Y-m-d')) {
        json_response(['success' => false, 'message' => 'Cannot reschedule to a past date.'], 422);
    }

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

// API: POST /api/appointments
// Validates the slot, stores booking intent in the DB (not just session),
// and returns eSewa payment fields to the frontend.
// The appointment row is only INSERTed after payment succeeds.
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

    // Check the hour slot still has capacity (< 5 bookings)
    $pdo  = db_connect();
    $hour = substr($start_time, 0, 2) . ':00:00';
    $cap  = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM appointments
        WHERE doctor_id        = :did
          AND appointment_date = :date
          AND TIME_FORMAT(start_time, '%H:00:00') = :hour
          AND status NOT IN ('Cancelled','Rescheduled')
    ");
    $cap->execute([':did' => $doctor_id, ':date' => $date, ':hour' => $hour]);
    if ((int)$cap->fetchColumn() >= 5) {
        json_response(['success' => false, 'message' => 'This time slot is fully booked. Please choose another.'], 409);
    }

    $transaction_uuid = 'TXN-' . strtoupper(bin2hex(random_bytes(8)));

    // Persist booking intent to the database.
    // This is critical: the session is often lost after the browser round-trips
    // through eSewa's payment page (cross-domain redirect drops the session cookie).
    // Storing in the DB ensures PaymentController can always retrieve the intent
    // using only the transaction_uuid that eSewa echoes back in the callback.
    $pdo->prepare("
        INSERT INTO pending_bookings
            (transaction_uuid, patient_id, doctor_id, appointment_date,
             start_time, end_time, reason, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE created_at = NOW()
    ")->execute([
        $transaction_uuid,
        $patient_id,
        $doctor_id,
        $date,
        $start_time,
        $end_time,
        $reason ?: null,
    ]);

    // Also keep session as a best-effort fallback
    $_SESSION['pending_booking'] = [
        'transaction_uuid' => $transaction_uuid,
        'patient_id'       => $patient_id,
        'doctor_id'        => $doctor_id,
        'date'             => $date,
        'start_time'       => $start_time,
        'end_time'         => $end_time,
        'reason'           => $reason ?: null,
    ];

    // Build eSewa payment fields
    $amount    = number_format(500.00, 2, '.', '');
    $tax       = number_format(0.00,   2, '.', '');
    $total     = number_format(500.00, 2, '.', '');
    $signature = esewa_signature($total, $transaction_uuid);

    json_response([
        'success'   => true,
        'esewa_url' => ESEWA_GATEWAY_URL,
        'fields'    => [
            'amount'                  => $amount,
            'tax_amount'              => $tax,
            'total_amount'            => $total,
            'transaction_uuid'        => $transaction_uuid,
            'product_code'            => ESEWA_PRODUCT_CODE,
            'product_service_charge'  => '0',
            'product_delivery_charge' => '0',
            'success_url'             => BASE_URL . '/payment/success',
            'failure_url'             => BASE_URL . '/payment/failure',
            'signed_field_names'      => 'total_amount,transaction_uuid,product_code',
            'signature'               => $signature,
        ],
    ]);
}

//  Page: /appointments/{id}/reschedule 
function reschedule_page(int $appt_id)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $authUser   = require_auth();
    $patient_id = (int) $authUser['id'];

    $appt = get_appointment_by_id($appt_id);

    // Security: Check existence and ownership
    if (!$appt || (int)$appt['patient_id'] !== $patient_id) {
        http_response_code(404);
        echo '<h1>Appointment not found.</h1>';
        exit;
    }

    // Logic: Do not allow rescheduling if the lifecycle is complete/cancelled
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
    // Access control: Ensure user only sees their own data
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

    // READ TRACKING: Mark incoming doctor messages as seen by the patient
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

    // Fetch the newly created message to return the object for real-time frontend updates
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

// Page: /chat/:appointment_id
function chat_page(int $appointment_id): void
{
    $user = require_auth();

    // Security verify: Chat access restricted to the patient linked to the appointment
    $appt = get_appointment_by_id($appointment_id);
    if (!$appt || (int)$appt['patient_id'] !== (int)$user['id']) {
        http_response_code(403);
        echo '<h1>Forbidden</h1>';
        exit;
    }

    render('patient/chat', compact('user', 'appt'));
}