<?php

/**
 * DoctorController.php
 *
 * Page renderers follow docbook's exact pattern:
 *   1. require_doctor_auth() — checks session role, returns doctor row
 *   2. ob_start() … HTML … $content = ob_get_clean()
 *   3. include app-doctor.php layout
 *
 * API functions return JSON via json_response().
 */

// ── Auth helpers ───────────────────────────────────────────────────────────

/**
 * For page routes: redirects to /login if not a logged-in doctor.
 * Returns the full doctor row (joined with users).
 */
function require_doctor_auth(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'doctor') {
        redirect('/login');
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT d.id, d.photo, d.specialty, d.experience_years, d.bio,
               u.name, u.email, u.phone
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        WHERE d.user_id = ?
    ");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        http_response_code(403);
        echo '<h1>Doctor profile not found. Please contact admin.</h1>';
        exit;
    }

    return $doctor;
}

/**
 * For API routes: sends 401 JSON if not authenticated as a doctor.
 * Returns the doctors.id integer.
 */
function require_doctor_auth_api(): int
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'doctor') {
        json_response(['error' => true, 'message' => 'Unauthorised'], 401);
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare('SELECT id FROM doctors WHERE user_id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        json_response(['error' => true, 'message' => 'Doctor profile not found'], 403);
    }

    return (int)$row['id'];
}

// ── Helper: render a doctor page using app-doctor.php layout ──────────────
function render_doctor(string $view, array $data = []): void
{
    extract($data);
    $file = BASE_PATH . '/app/views/pages/doctor/' . $view . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        echo '<h1>404 — View not found: ' . htmlspecialchars($view) . '</h1>';
        exit;
    }
    include $file; // view sets $content via ob_start / ob_get_clean, then includes layout
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/dashboard
// ══════════════════════════════════════════════════════════════════════════════
function doctor_dashboard_page(): void
{
    $doctor = require_doctor_auth();

    $pdo    = db_connect();
    $today  = date('Y-m-d');
    $drId   = (int)$doctor['id'];

    // Today's appointments
    $stmt = $pdo->prepare("
        SELECT a.id, a.start_time, a.end_time, a.status, a.visit_reason,
               u.id AS patient_id, u.name AS patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.appointment_date = ?
        ORDER BY a.start_time ASC
    ");
    $stmt->execute([$drId, $today]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd   = date('Y-m-d', strtotime('sunday this week'));

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('Cancelled','Rescheduled')");
    $s->execute([$drId, $today]);
    $stat_today = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Pending'");
    $s->execute([$drId]);
    $stat_pending = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date BETWEEN ? AND ? AND status NOT IN ('Cancelled','Rescheduled')");
    $s->execute([$drId, $weekStart, $weekEnd]);
    $stat_week = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = ?");
    $s->execute([$drId]);
    $stat_total = (int)$s->fetchColumn();

    render_doctor('dashboard', compact('doctor', 'appointments', 'today', 'stat_today', 'stat_pending', 'stat_week', 'stat_total'));
}

// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/schedule
// ══════════════════════════════════════════════════════════════════════════════
function doctor_schedule_page(): void
{
    $doctor = require_doctor_auth();
    render_doctor('schedule', compact('doctor'));
}

// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/patients
// ══════════════════════════════════════════════════════════════════════════════
function doctor_patients_page(): void
{
    $doctor = require_doctor_auth();
    render_doctor('patients', compact('doctor'));
}

// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/availability
// ══════════════════════════════════════════════════════════════════════════════
function doctor_availability_page(): void
{
    $doctor = require_doctor_auth();
    render_doctor('availability', compact('doctor'));
}

// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/profile
// ══════════════════════════════════════════════════════════════════════════════
function doctor_profile_page(): void
{
    $doctor = require_doctor_auth();
    render_doctor('drprofile', compact('doctor'));
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/appointments?date=YYYY-MM-DD
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_appointments(): void
{
    $doctorId = require_doctor_auth_api();
    $pdo      = db_connect();
    $date     = $_GET['date'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        json_response(['error' => true, 'message' => 'Invalid date format. Use YYYY-MM-DD'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.start_time, a.end_time,
               a.status, a.visit_reason,
               u.id AS patient_id, u.name AS patient_name,
               u.email AS patient_email, u.phone AS patient_phone
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ? AND a.appointment_date = ?
        ORDER BY a.start_time ASC
    ");
    $stmt->execute([$doctorId, $date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $appointments = [];
    foreach ($rows as $a) {
        $start    = strtotime($a['appointment_date'] . ' ' . $a['start_time']);
        $end      = strtotime($a['appointment_date'] . ' ' . $a['end_time']);
        $appointments[] = [
            'id'               => (int)$a['id'],
            'patient_id'       => (int)$a['patient_id'],
            'patient_name'     => $a['patient_name'],
            'time'             => date('g:i A', strtotime($a['start_time'])),
            'visit_reason'     => $a['visit_reason'],
            'status'           => $a['status'],
            'duration_minutes' => ($start && $end) ? (int)(($end - $start) / 60) : 30,
        ];
    }

    json_response(['success' => true, 'date' => $date, 'doctor_id' => $doctorId, 'appointments' => $appointments]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/stats
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_stats(): void
{
    $doctorId  = require_doctor_auth_api();
    $pdo       = db_connect();
    $today     = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd   = date('Y-m-d', strtotime('sunday this week'));

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status NOT IN ('Cancelled','Rescheduled')");
    $s->execute([$doctorId, $today]);
    $todayCount = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Pending'");
    $s->execute([$doctorId]);
    $pendingCount = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date BETWEEN ? AND ? AND status NOT IN ('Cancelled','Rescheduled')");
    $s->execute([$doctorId, $weekStart, $weekEnd]);
    $weekCount = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = ?");
    $s->execute([$doctorId]);
    $totalPatients = (int)$s->fetchColumn();

    json_response(['success' => true, 'today' => $todayCount, 'pending' => $pendingCount, 'week' => $weekCount, 'total_patients' => $totalPatients]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/appointment-detail?id=N
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_appointment_detail(): void
{
    $doctorId      = require_doctor_auth_api();
    $pdo           = db_connect();
    $appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$appointmentId) {
        json_response(['error' => true, 'message' => 'Appointment ID is required'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.start_time, a.end_time,
               a.status, a.visit_reason, a.patient_id,
               d.id AS doctor_id, u.name AS doctor_name, d.specialty AS doctor_specialty
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users   u ON d.user_id   = u.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointmentId, $doctorId]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        json_response(['error' => true, 'message' => 'Appointment not found'], 404);
    }

    $stmt = $pdo->prepare("SELECT id, message, created_at FROM appointment_comments WHERE appointment_id = ? ORDER BY created_at ASC");
    $stmt->execute([$appointmentId]);
    $comments = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $comments[] = ['id' => (int)$c['id'], 'text' => $c['message'], 'date' => date('M j, Y', strtotime($c['created_at']))];
    }

    $startTs = strtotime($appt['start_time']);
    $endTs   = strtotime($appt['end_time']);

    json_response(['success' => true, 'appointment' => [
        'id'               => (int)$appt['id'],
        'date'             => $appt['appointment_date'],
        'time'             => date('g:i A', $startTs),
        'status'           => $appt['status'],
        'visit_reason'     => $appt['visit_reason'],
        'duration_minutes' => (int)(($endTs - $startTs) / 60),
        'doctor'           => ['id' => (int)$appt['doctor_id'], 'name' => $appt['doctor_name'], 'specialty' => $appt['doctor_specialty']],
        'comments'         => $comments,
    ]]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: POST /doctor/api/update-status
// Body JSON: { appointment_id: N, status: "Confirmed"|"Cancelled" }
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_update_status(): void
{
    $doctorId      = require_doctor_auth_api();
    $pdo           = db_connect();
    $input         = json_decode(file_get_contents('php://input'), true) ?? [];
    $appointmentId = isset($input['appointment_id']) ? (int)$input['appointment_id'] : 0;
    $status        = $input['status'] ?? '';

    if (!$appointmentId || !in_array($status, ['Confirmed', 'Cancelled'])) {
        json_response(['error' => true, 'message' => 'Invalid input'], 400);
    }

    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$status, $appointmentId, $doctorId]);

    if ($stmt->rowCount() === 0) {
        json_response(['error' => true, 'message' => 'Appointment not found or unauthorised'], 404);
    }

    json_response(['success' => true, 'status' => $status]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET/POST /doctor/api/availability
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_availability(): void
{
    $doctorId = require_doctor_auth_api();
    $pdo      = db_connect();
    $method   = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->prepare("
            SELECT day_of_week, start_time, end_time, break_minutes,
                   COALESCE(break_interval_minutes, 90) AS break_interval_minutes,
                   COALESCE(break_duration_minutes, 10) AS break_duration_minutes
            FROM doctor_availability WHERE doctor_id = ?
            ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
        ");
        $stmt->execute([$doctorId]);
        $formatted = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $formatted[] = [
                'day'                    => strtolower($r['day_of_week']),
                'start_time'             => $r['start_time'],
                'end_time'               => $r['end_time'],
                'break_minutes'          => (int)$r['break_minutes'],
                'break_interval_minutes' => (int)$r['break_interval_minutes'],
                'break_duration_minutes' => (int)$r['break_duration_minutes'],
            ];
        }
        json_response(['success' => true, 'doctor_id' => $doctorId, 'schedule' => $formatted]);

    } elseif ($method === 'POST') {
        $input    = json_decode(file_get_contents('php://input'), true) ?? [];
        $schedule = $input['schedule'] ?? [];

        if (empty($schedule)) {
            json_response(['error' => true, 'message' => 'Schedule data is required'], 400);
        }

        $pdo->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?")->execute([$doctorId]);

        // Detect if new columns exist (graceful fallback if DB not yet migrated)
        $hasCols = !empty($pdo->query("SHOW COLUMNS FROM doctor_availability LIKE 'break_interval_minutes'")->fetchAll());
        if ($hasCols) {
            $ins = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, break_minutes, break_interval_minutes, break_duration_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        } else {
            $ins = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, break_minutes) VALUES (?, ?, ?, ?, ?)");
        }

        foreach ($schedule as $day) {
            $dayName          = $day['day']                    ?? null;
            $isActive         = (bool)($day['is_active']       ?? true);
            $startTime        = $day['start_time']             ?? '09:00';
            $endTime          = $day['end_time']               ?? '17:00';
            $breakMinutes     = (int)($day['break_minutes']    ?? 5);
            $breakIntervalMin = (int)($day['break_interval_minutes'] ?? 90);
            $breakDurationMin = (int)($day['break_duration_minutes'] ?? 10);
            if ($dayName && $isActive) {
                if ($hasCols) {
                    $ins->execute([$doctorId, ucfirst($dayName), $startTime, $endTime, $breakMinutes, $breakIntervalMin, $breakDurationMin]);
                } else {
                    $ins->execute([$doctorId, ucfirst($dayName), $startTime, $endTime, $breakMinutes]);
                }
            }
        }
        json_response(['success' => true, 'message' => 'Availability saved successfully']);

    } else {
        json_response(['error' => true, 'message' => 'Method not allowed'], 405);
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET/POST /doctor/api/profile
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_profile(): void
{
    $doctorId = require_doctor_auth_api();
    $pdo      = db_connect();
    $method   = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT d.id, u.name, u.email, u.phone, d.specialty, d.experience_years, d.bio, d.photo FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$doctorId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$doc) { json_response(['error' => true, 'message' => 'Doctor not found'], 404); }
        json_response(['success' => true, 'doctor' => ['id' => (int)$doc['id'], 'name' => $doc['name'], 'email' => $doc['email'], 'phone' => $doc['phone'], 'specialty' => $doc['specialty'], 'experience_years' => (int)$doc['experience_years'], 'bio' => $doc['bio'], 'photo' => $doc['photo']]]);

    } elseif ($method === 'POST') {
        $name             = trim($_POST['name']             ?? '');
        $email            = trim($_POST['email']            ?? '');
        $phone            = trim($_POST['phone']            ?? '');
        $specialty        = trim($_POST['specialty']        ?? '');
        $experience_years = (int)($_POST['experience_years'] ?? 0);
        $bio              = trim($_POST['bio']              ?? '');

        if (empty($name) || empty($specialty)) {
            json_response(['error' => true, 'message' => 'Name and specialty are required'], 400);
        }

        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];
            if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                json_response(['error' => true, 'message' => 'Only image files are allowed'], 400);
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                json_response(['error' => true, 'message' => 'File size exceeds 5 MB limit'], 400);
            }
            $upload_dir = BASE_PATH . '/public/uploads/profiles/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'doctor_' . $doctorId . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                json_response(['error' => true, 'message' => 'Failed to upload file'], 500);
            }
            $photo = 'uploads/profiles/' . $filename;
        }

        $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?")
            ->execute([$name, $email ?: null, $phone ?: null, (int)$row['user_id']]);

        $fields = ['specialty = ?', 'experience_years = ?', 'bio = ?'];
        $params = [$specialty, $experience_years, $bio ?: null];
        if ($photo) { $fields[] = 'photo = ?'; $params[] = $photo; }
        $params[] = $doctorId;
        $pdo->prepare("UPDATE doctors SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

        $stmt = $pdo->prepare("SELECT d.id, u.name, u.email, u.phone, d.specialty, d.experience_years, d.bio, d.photo FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$doctorId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'message' => 'Profile updated successfully', 'doctor' => ['id' => (int)$doc['id'], 'name' => $doc['name'], 'email' => $doc['email'], 'phone' => $doc['phone'], 'specialty' => $doc['specialty'], 'experience_years' => (int)$doc['experience_years'], 'bio' => $doc['bio'], 'photo' => $doc['photo']]]);

    } else {
        json_response(['error' => true, 'message' => 'Method not allowed'], 405);
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/patients          → list all unique patients
//      GET /doctor/api/patients?id=N     → single patient with history
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_patients(): void
{
    $doctorId  = require_doctor_auth_api();
    $pdo       = db_connect();
    $patientId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$patientId) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.name, u.email, u.phone,
                (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = u.id AND doctor_id = ?) AS last_visit
            FROM users u
            JOIN appointments a ON u.id = a.patient_id
            WHERE a.doctor_id = ? ORDER BY u.name ASC
        ");
        $stmt->execute([$doctorId, $doctorId]);
        json_response(['success' => true, 'patients' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    $stmt = $pdo->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$patient) { json_response(['error' => true, 'message' => 'Patient not found'], 404); }

    $dStmt = $pdo->prepare("SELECT u.name FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $dStmt->execute([$doctorId]);
    $dRow   = $dStmt->fetch(PDO::FETCH_ASSOC);
    $drName = 'Dr. ' . ($dRow['name'] ?? 'Doctor');

    $stmt = $pdo->prepare("
        SELECT ac.id, ac.message, ac.created_at, a.visit_reason, a.appointment_date, a.start_time
        FROM appointment_comments ac JOIN appointments a ON ac.appointment_id = a.id
        WHERE a.patient_id = ? AND a.doctor_id = ? ORDER BY ac.created_at DESC
    ");
    $stmt->execute([$patientId, $doctorId]);
    $comments = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $comments[] = ['id' => (int)$c['id'], 'text' => $c['message'], 'author' => $drName, 'date' => date('M j, Y', strtotime($c['created_at'])), 'visit_reason' => $c['visit_reason'], 'appointment_date' => $c['appointment_date'], 'appointment_time' => date('g:i A', strtotime($c['start_time']))];
    }

    $stmt = $pdo->prepare("SELECT id, appointment_date, start_time, end_time, visit_reason, status FROM appointments WHERE patient_id = ? AND doctor_id = ? ORDER BY appointment_date DESC, start_time DESC");
    $stmt->execute([$patientId, $doctorId]);
    $appointments = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $s = strtotime($a['start_time']); $e = strtotime($a['end_time']);
        $appointments[] = ['id' => (int)$a['id'], 'date' => $a['appointment_date'], 'time' => date('g:i A', $s), 'visit_reason' => $a['visit_reason'], 'status' => $a['status'], 'duration_minutes' => (int)(($e - $s) / 60)];
    }

    json_response(['success' => true, 'patient' => ['id' => (int)$patient['id'], 'name' => $patient['name'], 'email' => $patient['email'], 'phone' => $patient['phone'], 'created_at' => $patient['created_at']], 'comment_history' => $comments, 'appointments' => $appointments]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: POST /doctor/api/comment
// FormData: appointment_id, comment_text
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_comment(): void
{
    $doctorId      = require_doctor_auth_api();
    $pdo           = db_connect();
    $appointmentId = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    $commentText   = trim($_POST['comment_text'] ?? '');

    if (!$appointmentId) { json_response(['error' => true, 'message' => 'Appointment ID is required'], 400); }
    if ($commentText === '') { json_response(['error' => true, 'message' => 'Comment text is required'], 400); }

    $stmt = $pdo->prepare("SELECT id, patient_id, visit_reason, appointment_date, start_time FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointmentId, $doctorId]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$appt) { json_response(['error' => true, 'message' => 'Appointment not found or unauthorised'], 404); }

    $dStmt = $pdo->prepare("SELECT d.user_id, u.name FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $dStmt->execute([$doctorId]);
    $dRow  = $dStmt->fetch(PDO::FETCH_ASSOC);

    $pdo->prepare("INSERT INTO appointment_comments (appointment_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())")->execute([$appointmentId, $dRow['user_id'], $commentText]);
    $commentId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT id, message AS comment_text, created_at FROM appointment_comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response(['success' => true, 'message' => 'Comment saved successfully', 'comment' => ['id' => (int)$comment['id'], 'text' => $comment['comment_text'], 'author' => 'Dr. ' . $dRow['name'], 'date' => date('M j, Y', strtotime($comment['created_at'])), 'appointment_id' => $appointmentId, 'visit_reason' => $appt['visit_reason'], 'appointment_date' => $appt['appointment_date'], 'appointment_time' => date('g:i A', strtotime($appt['start_time']))]]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/slots?doctor_id=N&date=YYYY-MM-DD
// No doctor auth required — also called by the patient booking side.
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_slots(): void
{
    $doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    $date     = $_GET['date'] ?? date('Y-m-d');

    if (!$doctorId) { json_response(['error' => true, 'message' => 'Doctor ID is required'], 400); }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { json_response(['error' => true, 'message' => 'Invalid date format. Use YYYY-MM-DD'], 400); }

    $pdo       = db_connect();
    $dayOfWeek = date('l', strtotime($date));

    $stmt = $pdo->prepare("SELECT start_time, end_time, break_minutes,
                   COALESCE(break_interval_minutes, 90) AS break_interval_minutes,
                   COALESCE(break_duration_minutes, 10) AS break_duration_minutes
               FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
    $stmt->execute([$doctorId, $dayOfWeek]);
    $avail = $stmt->fetch(PDO::FETCH_ASSOC);

    $rawSlots = [];
    if ($avail) {
        $cur             = strtotime($avail['start_time']);
        $end             = strtotime($avail['end_time']);
        $slotMinutes     = 30; // each appointment slot is 30 min
        $breakMinutes    = (int)$avail['break_minutes'];         // short break between every slot
        $intervalMinutes = (int)$avail['break_interval_minutes']; // long break every N minutes of work
        $longBreakMin    = (int)$avail['break_duration_minutes']; // long break duration

        $workMinutesAccum = 0; // track accumulated work time for long-break logic

        while ($cur < $end) {
            // Insert long break if we've hit the interval (and long break is enabled)
            if ($intervalMinutes > 0 && $workMinutesAccum > 0 && ($workMinutesAccum % $intervalMinutes) === 0) {
                $longBreakEnd = $cur + $longBreakMin * 60;
                if ($longBreakEnd <= $end) {
                    $rawSlots[] = [
                        'time'         => date('H:i:s', $cur),
                        'display_time' => date('g:i A', $cur),
                        'is_break'     => true,
                        'break_label'  => $longBreakMin . ' min break',
                    ];
                    $cur = $longBreakEnd;
                    continue;
                }
            }

            if ($cur >= $end) break;

            // Add the appointment slot
            $rawSlots[] = [
                'time'         => date('H:i:s', $cur),
                'display_time' => date('g:i A', $cur),
                'is_break'     => false,
                'break_label'  => null,
            ];

            $cur              += $slotMinutes * 60;
            $workMinutesAccum += $slotMinutes;

            // Short break between slots (if configured and not at end)
            if ($breakMinutes > 0 && $cur < $end) {
                // Only add short break if we won't be adding a long break next
                $isLongBreakNext = ($intervalMinutes > 0 && $workMinutesAccum > 0 && ($workMinutesAccum % $intervalMinutes) === 0);
                if (!$isLongBreakNext) {
                    $rawSlots[] = [
                        'time'         => date('H:i:s', $cur),
                        'display_time' => date('g:i A', $cur),
                        'is_break'     => true,
                        'break_label'  => $breakMinutes . ' min break',
                    ];
                    $cur += $breakMinutes * 60;
                }
            }
        }
    }

    $stmt = $pdo->prepare("SELECT start_time, end_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status IN ('Pending','Confirmed')");
    $stmt->execute([$doctorId, $date]);
    $bookedTimes = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
        $s = strtotime($b['start_time']); $e = strtotime($b['end_time']);
        $n = (int)ceil(($e - $s) / (30 * 60));
        for ($i = 0; $i < $n; $i++) { $bookedTimes[] = date('H:i:s', $s + $i * 30 * 60); }
    }

    $finalSlots = [];
    foreach ($rawSlots as $slot) {
        $status = $slot['is_break'] ? 'break' : (in_array($slot['time'], $bookedTimes) ? 'booked' : 'available');
        $finalSlots[] = [
            'time'        => $slot['display_time'],
            'time_24'     => $slot['time'],
            'status'      => $status,
            'break_after' => $slot['is_break'],
            'break_label' => $slot['break_label'] ?? null,
        ];
    }

    json_response(['success' => true, 'doctor_id' => $doctorId, 'date' => $date, 'day_of_week' => $dayOfWeek, 'slots' => $finalSlots]);
}
// ══════════════════════════════════════════════════════════════════════════════
// PAGE: GET /doctor/chat/{appointment_id}
// ══════════════════════════════════════════════════════════════════════════════
function doctor_chat_page(int $appointment_id): void
{
    $doctor = require_doctor_auth();
    $pdo    = db_connect();
    $drId   = (int)$doctor['id'];

    // Fetch appointment and verify it belongs to this doctor
    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.start_time, a.end_time,
               a.status, a.visit_reason, a.reference_number,
               u.id AS patient_id, u.name AS patient_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointment_id, $drId]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        http_response_code(403);
        echo '<h1>Forbidden — Appointment not found or does not belong to you.</h1>';
        exit;
    }

    render_doctor('chat', compact('doctor', 'appt'));
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /doctor/api/messages/{appointment_id}
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_get_messages(int $appointment_id): void
{
    $doctorId = require_doctor_auth_api();
    $pdo      = db_connect();

    // Verify appointment belongs to this doctor
    $chk = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $chk->execute([$appointment_id, $doctorId]);
    if (!$chk->fetch()) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.sender_role, m.message, m.is_read,
               m.created_at, u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.appointment_id = :appt_id
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([':appt_id' => $appointment_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark patient messages as read
    $pdo->prepare("
        UPDATE messages SET is_read = 1
        WHERE appointment_id = :appt_id AND sender_role = 'patient' AND is_read = 0
    ")->execute([':appt_id' => $appointment_id]);

    json_response(['success' => true, 'data' => $messages]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: POST /doctor/api/messages/{appointment_id}
// Body JSON: { message: "..." }
// ══════════════════════════════════════════════════════════════════════════════
function api_doctor_send_message(int $appointment_id): void
{
    $doctorId = require_doctor_auth_api();
    $pdo      = db_connect();

    // Verify appointment belongs to this doctor
    $chk = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $chk->execute([$appointment_id, $doctorId]);
    if (!$chk->fetch()) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }

    $body    = json_decode(file_get_contents('php://input'), true) ?? [];
    $message = trim($body['message'] ?? '');
    if ($message === '') {
        json_response(['success' => false, 'message' => 'Message cannot be empty.'], 422);
    }

    // Get doctor's user_id for the sender_id
    $dStmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $dStmt->execute([$doctorId]);
    $dRow   = $dStmt->fetch(PDO::FETCH_ASSOC);
    $userId = (int)$dRow['user_id'];

    $stmt = $pdo->prepare("
        INSERT INTO messages (appointment_id, sender_id, sender_role, message)
        VALUES (:appt_id, :sender_id, 'doctor', :message)
    ");
    $stmt->execute([
        ':appt_id'   => $appointment_id,
        ':sender_id' => $userId,
        ':message'   => $message,
    ]);
    $new_id = (int)$pdo->lastInsertId();

    $row = $pdo->prepare("
        SELECT m.id, m.sender_id, m.sender_role, m.message, m.is_read,
               m.created_at, u.name AS sender_name
        FROM messages m JOIN users u ON u.id = m.sender_id
        WHERE m.id = ?
    ");
    $row->execute([$new_id]);
    $newMsg = $row->fetch(PDO::FETCH_ASSOC);

    json_response(['success' => true, 'data' => $newMsg]);
}