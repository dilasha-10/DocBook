<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';

// ── Helper: get current logged-in user for navbar ─────────────────────────────
function current_user(): ?array
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $id = $_SESSION['user_id'] ?? 1; // default to patient id=1 while auth is stubbed

    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ── Page: /categories ─────────────────────────────────────────────────────────
function categories_page()
{
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
            'rating'    => $d['avg_rating'] ?? '—',
            'fee'       => number_format($d['fee'], 2),
            'available' => !empty($d['next_available_date']),
            'next_date' => $d['next_available_date'] ?? null,
        ];
    }, $raw);

    render('categories', [
        'user'       => current_user(),
        'categories' => $categories,
        'doctors'    => $doctors,
        'selected'   => $_GET['category'] ?? 'all',
        'search'     => $_GET['search']   ?? '',
    ]);
}

// ── Page: /dashboard ──────────────────────────────────────────────────────────
function dashboard_page()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $patient_id   = $_SESSION['user_id']   ?? 1;
    $patient_name = $_SESSION['user_name'] ?? 'Patient';

    $upcoming = get_upcoming_appointments($patient_id);
    $past     = get_past_appointments($patient_id);
    $stats    = get_appointment_stats($patient_id);

    render('dashboard', [
        'user'         => current_user(),
        'patient_name' => $patient_name,
        'upcoming'     => $upcoming,
        'past'         => $past,
        'stats'        => $stats,
    ]);
}

// ── Page: /profile ────────────────────────────────────────────────────────────
function profile_page()
{
    $user = current_user();

    render('profile', [
        'user' => $user,
    ]);
}

// ── API: POST /api/profile ────────────────────────────────────────────────────
function api_update_profile()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id   = $_SESSION['user_id'] ?? 1;
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

// ── API: POST /api/settings/password ─────────────────────────────────────────
function api_change_password()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id   = $_SESSION['user_id'] ?? 1;
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

// ── Page: /booking/confirm ────────────────────────────────────────────────────
function booking_confirm_page()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $appointment = $_SESSION['booking_confirmation'] ?? [
        'reference_number' => 'DBK-STUB-001',
        'doctor_name'      => 'Dr. Sarah Lim',
        'specialty'        => 'General Physician',
        'date'             => '2025-03-25',
        'time'             => '10:00 AM',
        'fee'              => '50.00',
        'status'           => 'Pending',
    ];

    render('booking-confirm', [
        'user'        => current_user(),
        'appointment' => $appointment,
    ]);
}

// ── API: GET /api/categories ──────────────────────────────────────────────────
function api_get_categories()
{
    $categories = get_all_categories();
    json_response(['success' => true, 'data' => $categories]);
}

// ── API: GET /api/doctors?category=<slug>&search=<n> ─────────────────────────
function api_get_doctors()
{
    $category = isset($_GET['category']) && $_GET['category'] !== ''
                    ? $_GET['category'] : null;
    $search   = isset($_GET['search'])   && $_GET['search']   !== ''
                    ? $_GET['search']   : null;

    $doctors = get_filtered_doctors($category, $search);
    json_response(['success' => true, 'data' => $doctors]);
}

// ── API stubs (other devs) ────────────────────────────────────────────────────
function api_reschedule_appointment($id) {}
function api_cancel_appointment($id)    {}
function api_book_appointment()         {}