<?php

// ══════════════════════════════════════════════════════════════════════════════
// Lab Admin Auth Guards
// ══════════════════════════════════════════════════════════════════════════════

function require_lab_admin(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user || ($user['role'] ?? '') !== 'lab_admin') {
        redirect('/login');
    }
    return $user;
}

function require_lab_admin_api(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user || ($user['role'] ?? '') !== 'lab_admin') {
        json_response(['error' => 'Forbidden'], 403);
    }
    return $user;
}

// ══════════════════════════════════════════════════════════════════════════════
// Render helper — mirrors render_doctor / render (patient)
// ══════════════════════════════════════════════════════════════════════════════

function render_lab_admin(string $view, array $data = []): void
{
    extract($data);
    $file = BASE_PATH . '/app/views/pages/lab-admin/' . $view . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        echo '<h1>404 — View not found: ' . htmlspecialchars($view) . '</h1>';
        exit;
    }
    include $file;
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// Page: GET /lab-admin/dashboard
// ══════════════════════════════════════════════════════════════════════════════

function lab_admin_dashboard_page(): void
{
    $user = require_lab_admin();
    render_lab_admin('dashboard', ['user' => $user]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /lab-admin/api/find-patient
//
// Identifies a patient by ANY combination of:
//   - name       (partial, case-insensitive)
//   - age        (derived from dob if stored, or approximate — optional)
//   - patient_id (exact match on patient_unique_id)
//
// At least one parameter must be provided.
// Returns: matched patients with id, name, patient_unique_id, email, phone.
// ══════════════════════════════════════════════════════════════════════════════

function api_lab_admin_find_patient(): void
{
    require_lab_admin_api();
    $pdo = db_connect();

    $name      = trim($_GET['name']       ?? '');
    $patientId = trim($_GET['patient_id'] ?? '');
    $age       = trim($_GET['age']        ?? '');

    if ($name === '' && $patientId === '' && $age === '') {
        json_response(['error' => true, 'message' => 'Provide at least one search parameter: name, patient_id, or age'], 400);
    }

    $where  = ["u.role = 'patient'"];
    $params = [];

    if ($patientId !== '') {
        // Exact match on the unique patient ID (case-insensitive)
        $where[]                 = 'UPPER(u.patient_unique_id) = UPPER(:pid)';
        $params[':pid']          = $patientId;
    }

    if ($name !== '') {
        $where[]                 = 'u.name LIKE :name';
        $params[':name']         = '%' . $name . '%';
    }

    // Age filter: approximate via YEAR(NOW()) - YEAR(created_at) as a rough proxy
    // (only useful if your system stores DOB; we match ±1 year tolerance here)
    if ($age !== '' && ctype_digit($age)) {
        $ageInt = (int)$age;
        // We store created_at, not DOB — so skip DOB logic unless column exists.
        // Check if dob column exists:
        $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'dob'")->fetchAll();
        if (!empty($colCheck)) {
            $where[]             = 'ABS(TIMESTAMPDIFF(YEAR, u.dob, CURDATE()) - :age) <= 1';
            $params[':age']      = $ageInt;
        }
        // If no DOB column, age filter is silently skipped (graceful degradation)
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.email,
            u.phone,
            u.patient_unique_id,
            u.created_at
        FROM users u
        {$whereSql}
        ORDER BY u.name ASC
        LIMIT 20
    ");
    $stmt->execute($params);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mask email for privacy — show only first 2 chars + domain
    foreach ($patients as &$p) {
        $parts = explode('@', $p['email']);
        if (count($parts) === 2) {
            $p['email_masked'] = substr($parts[0], 0, 2) . '***@' . $parts[1];
        } else {
            $p['email_masked'] = '***';
        }
        unset($p['email']); // don't expose full email to lab admin
    }
    unset($p);

    json_response(['success' => true, 'patients' => $patients]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: GET /lab-admin/api/patient-appointments?patient_id=<users.id>
//
// Returns appointments for a given patient (by users.id) that have a
// Confirmed or Completed status — lab reports are only relevant for these.
// ══════════════════════════════════════════════════════════════════════════════

function api_lab_admin_patient_appointments(): void
{
    require_lab_admin_api();
    $pdo = db_connect();

    $userId = (int)($_GET['patient_id'] ?? 0);
    if (!$userId) {
        json_response(['error' => true, 'message' => 'patient_id is required'], 400);
    }

    // Confirm this user is actually a patient
    $check = $pdo->prepare("SELECT id, name, patient_unique_id FROM users WHERE id = ? AND role = 'patient' LIMIT 1");
    $check->execute([$userId]);
    $patient = $check->fetch(PDO::FETCH_ASSOC);
    if (!$patient) {
        json_response(['error' => true, 'message' => 'Patient not found'], 404);
    }

    $stmt = $pdo->prepare("
        SELECT
            a.id                AS appointment_id,
            a.reference_number,
            a.appointment_date,
            a.start_time,
            a.status,
            a.visit_reason,
            du.name             AS doctor_name,
            c.name              AS specialty,
            lr.id               AS report_id,
            lr.original_name    AS report_file,
            lr.uploaded_at      AS report_uploaded_at,
            lr.notes            AS report_notes
        FROM appointments a
        JOIN doctors d         ON d.id  = a.doctor_id
        JOIN users du          ON du.id = d.user_id
        LEFT JOIN categories c ON c.id  = d.category_id
        LEFT JOIN lab_reports lr ON lr.appointment_id = a.id
        WHERE a.patient_id = ?
          AND a.status IN ('Confirmed', 'Completed')
        ORDER BY a.appointment_date DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'success'      => true,
        'patient'      => $patient,
        'appointments' => $appointments,
    ]);
}

// ══════════════════════════════════════════════════════════════════════════════
// API: POST /lab-admin/api/upload-report
//
// FormData fields:
//   appointment_id  int     (required)
//   report          file    (required, PDF/JPG/PNG, max 5 MB)
//   notes           string  (optional)
//
// The lab admin can upload for ANY confirmed/completed appointment.
// ══════════════════════════════════════════════════════════════════════════════

function api_lab_admin_upload_report(): void
{
    $labAdmin = require_lab_admin_api();
    $pdo      = db_connect();

    $appointmentId = (int)($_POST['appointment_id'] ?? 0);
    if (!$appointmentId) {
        json_response(['error' => true, 'message' => 'appointment_id is required'], 400);
    }

    // Verify the appointment exists and belongs to a patient (confirmed/completed)
    $appt = $pdo->prepare("
        SELECT a.id, a.patient_id, a.status
        FROM appointments a
        WHERE a.id = ?
          AND a.status IN ('Confirmed', 'Completed')
        LIMIT 1
    ");
    $appt->execute([$appointmentId]);
    $apptRow = $appt->fetch(PDO::FETCH_ASSOC);
    if (!$apptRow) {
        json_response(['error' => true, 'message' => 'Appointment not found or not eligible for lab report upload'], 404);
    }

    // File validation
    if (empty($_FILES['report']) || $_FILES['report']['error'] !== UPLOAD_ERR_OK) {
        json_response(['error' => true, 'message' => 'No file uploaded or upload error'], 400);
    }

    $file         = $_FILES['report'];
    $originalName = basename($file['name']);
    $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed      = ['pdf', 'jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        json_response(['error' => true, 'message' => 'Only PDF, JPG, and PNG files are allowed'], 400);
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        json_response(['error' => true, 'message' => 'File size exceeds 5 MB limit'], 400);
    }

    $uploadDir = BASE_PATH . '/public/uploads/lab-reports/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $notes      = trim($_POST['notes'] ?? '');
    $filename   = 'report_' . $appointmentId . '_' . time() . '.' . $ext;
    $destPath   = $uploadDir . $filename;
    $publicPath = 'public/uploads/lab-reports/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        json_response(['error' => true, 'message' => 'Failed to save file'], 500);
    }

    // Remove old file if exists
    $old = $pdo->prepare("SELECT file_path FROM lab_reports WHERE appointment_id = ?");
    $old->execute([$appointmentId]);
    $oldRow = $old->fetch(PDO::FETCH_ASSOC);
    if ($oldRow) {
        $oldFile = BASE_PATH . '/' . $oldRow['file_path'];
        if (file_exists($oldFile)) @unlink($oldFile);
    }

    // Upsert lab report
    $pdo->prepare("
        INSERT INTO lab_reports (appointment_id, uploaded_by, file_path, original_name, notes, uploaded_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            uploaded_by   = VALUES(uploaded_by),
            file_path     = VALUES(file_path),
            original_name = VALUES(original_name),
            notes         = VALUES(notes),
            uploaded_at   = NOW()
    ")->execute([$appointmentId, $labAdmin['id'], $publicPath, $originalName, $notes ?: null]);

    json_response([
        'success'   => true,
        'message'   => 'Lab report uploaded successfully',
        'file_path' => $publicPath,
    ]);
}
// ══════════════════════════════════════════════════════════════════════════════
// Page: GET /lab-admin/profile
// ══════════════════════════════════════════════════════════════════════════════

function lab_admin_profile_page(): void
{
    $user = require_lab_admin();
    render_lab_admin('profile', ['user' => $user]);
}