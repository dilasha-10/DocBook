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
        exit;
    }

    // Block rescheduling to an admin-marked holiday / blocked date
    require_once BASE_PATH . '/app/models/SystemSettingsModel.php';
    if (is_holiday($new_date)) {
        json_response([
            'success' => false,
            'message' => 'Cannot reschedule to this date — it has been marked as a holiday or blocked day.',
        ], 422);
        exit;
    }

    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $new_start) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $new_end)) {
        json_response(['success' => false, 'message' => 'Invalid time format. Use HH:MM.'], 422);
    }

    // Enforce per-hour capacity on rescheduling (psychiatrist=2, others=5)
    $appt = get_appointment_by_id((int)$id);
    if ($appt) {
        $pdoR   = db_connect();
        $catS   = $pdoR->prepare("SELECT c.slug FROM doctors d JOIN categories c ON c.id=d.category_id WHERE d.id=:did");
        $catS->execute([':did' => $appt['doctor_id']]);
        $catSlug = $catS->fetchColumn();
        $hourCap = ($catSlug === 'psychiatrist') ? 2 : 5;

        $hour = substr($new_start, 0, 2) . ':00:00';
        $capS = $pdoR->prepare("
            SELECT COUNT(*) FROM appointments
            WHERE doctor_id        = :did
              AND appointment_date = :date
              AND TIME_FORMAT(start_time,'%H:00:00') = :hour
              AND status NOT IN ('Cancelled','Rescheduled')
              AND id != :id
        ");
        $capS->execute([':did' => $appt['doctor_id'], ':date' => $new_date, ':hour' => $hour, ':id' => (int)$id]);
        if ((int)$capS->fetchColumn() >= $hourCap) {
            $msg = $hourCap === 2
                ? 'This psychiatrist can only take 2 appointments per hour. Please choose another time.'
                : 'This time slot is fully booked. Please choose another.';
            json_response(['success' => false, 'message' => $msg], 409);
            exit;
        }
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
// and returns eSewa payment fields to the frontend (or direct-confirms when payments off).
// The appointment row is only INSERTed after payment succeeds (or immediately when payments off).
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
        exit;
    }

    // Block booking on admin-marked holiday / blocked dates
    require_once BASE_PATH . '/app/models/SystemSettingsModel.php';
    if (is_holiday($date)) {
        json_response([
            'success' => false,
            'message' => 'Appointments cannot be booked on this date — it has been marked as a holiday or blocked day.',
        ], 422);
        exit;
    }

    // Determine per-hour capacity: psychiatrists are capped at 2, all others at 5
    $pdo = db_connect();
    $catStmt = $pdo->prepare("
        SELECT c.slug FROM doctors d
        JOIN categories c ON c.id = d.category_id
        WHERE d.id = :did
    ");
    $catStmt->execute([':did' => $doctor_id]);
    $catSlug  = $catStmt->fetchColumn();
    $hourCap  = ($catSlug === 'psychiatrist') ? 2 : 5;

    // Check the hour slot still has capacity
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
    if ((int)$cap->fetchColumn() >= $hourCap) {
        $msg = $hourCap === 2
            ? 'This psychiatrist can only take 2 appointments per hour. Please choose another time.'
            : 'This time slot is fully booked. Please choose another.';
        json_response(['success' => false, 'message' => $msg], 409);
        exit;
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
        ON DUPLICATE KEY UPDATE
            patient_id       = VALUES(patient_id),
            doctor_id        = VALUES(doctor_id),
            appointment_date = VALUES(appointment_date),
            start_time       = VALUES(start_time),
            end_time         = VALUES(end_time),
            reason           = VALUES(reason),
            created_at       = NOW()
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

    // If online payments are disabled, confirm the appointment directly without eSewa
    $paymentsEnabled = get_setting('payments', true);
    if (!$paymentsEnabled) {
        // Generate a unique reference number
        $refChk = $pdo->prepare("SELECT id FROM appointments WHERE reference_number = :r");
        do {
            $ref = 'DBK-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $refChk->execute([':r' => $ref]);
        } while ($refChk->fetch());

        $ins = $pdo->prepare("
            INSERT INTO appointments
                (patient_id, doctor_id, appointment_date, start_time, end_time, reference_number, status, visit_reason)
            VALUES
                (:pid, :did, :date, :start, :end, :ref, 'Confirmed', :reason)
        ");
        $ins->execute([
            ':pid'    => $patient_id,
            ':did'    => $doctor_id,
            ':date'   => $date,
            ':start'  => $start_time,
            ':end'    => $end_time,
            ':ref'    => $ref,
            ':reason' => $reason ?: null,
        ]);
        $appt_id = (int)$pdo->lastInsertId();

        // Clean up pending booking
        $pdo->prepare("DELETE FROM pending_bookings WHERE transaction_uuid = ?")->execute([$transaction_uuid]);

        // Store confirmation in session for the confirm page
        $_SESSION['booking_confirmation'] = [
            'reference_number' => $ref,
            'appointment_id'   => $appt_id,
            'doctor_id'        => $doctor_id,
            'date'             => $date,
            'start_time'       => $start_time,
            'end_time'         => $end_time,
        ];

        json_response([
            'success'          => true,
            'payment_required' => false,
            'redirect'         => BASE_URL . '/booking/confirm',
            'reference_number' => $ref,
        ]);
        exit;
    }

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
            'product_service_charge'  => '0.00',
            'product_delivery_charge' => '0.00',
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
        json_response(['success' => false, 'booked' => [], 'hour_cap' => 5]);
        exit;
    }

    // Inform the frontend if the requested date is a blocked holiday
    require_once BASE_PATH . '/app/models/SystemSettingsModel.php';
    if (is_holiday($date)) {
        json_response([
            'success'    => true,
            'is_holiday' => true,
            'booked'     => [],
            'hour_cap'   => 5,
        ]);
        exit;
    }

    // Determine per-hour cap: psychiatrists max 2, others max 5
    $pdo     = db_connect();
    $catStmt = $pdo->prepare("SELECT c.slug FROM doctors d JOIN categories c ON c.id=d.category_id WHERE d.id=:did");
    $catStmt->execute([':did' => $doctor_id]);
    $catSlug  = $catStmt->fetchColumn();
    $hourCap  = ($catSlug === 'psychiatrist') ? 2 : 5;

    $booked = get_booked_slots($doctor_id, $date);
    json_response(['success' => true, 'is_holiday' => false, 'booked' => $booked, 'hour_cap' => $hourCap]);
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

    $appt = get_appointment_by_id($id);
    if (!$appt || (int)$appt['patient_id'] !== (int)$patient_id) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }
    $body      = json_decode(file_get_contents('php://input'), true) ?? [];
    $message   = trim($body['message']   ?? '');
    $parent_id = isset($body['parent_id']) ? (int)$body['parent_id'] : null;
    if ($message === '') {
        json_response(['success' => false, 'message' => 'Message cannot be empty.'], 422);
    }
    $result = create_appointment_comment($id, (int)$patient_id, $message, $parent_id);
    if (isset($result['error'])) {
        json_response(['success' => false, 'message' => $result['error']], 422);
    }
    json_response(['success' => true, 'data' => $result], 201);
}


// Page: /chat/:appointment_id  — REMOVED (chat feature removed)
// function chat_page was here

// Page: GET /lab-report/:appointment_id — serve the lab report file
function lab_report_download(int $appointment_id): void
{
    $user = require_auth();
    $patient_id = (int)$user['id'];

    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT lr.file_path, lr.original_name, a.patient_id
        FROM lab_reports lr
        JOIN appointments a ON a.id = lr.appointment_id
        WHERE lr.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['patient_id'] !== $patient_id) {
        http_response_code(403);
        echo '<h1>Forbidden</h1>';
        exit;
    }

    $path = BASE_PATH . '/' . ltrim($row['file_path'], '/');
    if (!file_exists($path)) {
        http_response_code(404);
        echo '<h1>File not found</h1>';
        exit;
    }

    $mime = mime_content_type($path) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . rawurlencode($row['original_name']) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}