<?php

require_once BASE_PATH . '/app/models/AuditLogModel.php';

// Admin auth guard

function require_admin(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        redirect('/login');
    }
    return $user;
}

function require_admin_api(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        // Exit immediately so no subsequent code emits partial output
        json_response(['error' => 'Forbidden'], 403);
        exit;
    }
    return $user;
}

// Page: GET /admin/dashboard

function admin_dashboard_page()
{
    $user = require_admin();
    $pdo  = db_connect();

    // Overall platform stats
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM users WHERE role = 'patient') AS total_patients,
            (SELECT COUNT(*) FROM users WHERE role = 'doctor')  AS total_doctors,
            (SELECT COUNT(*) FROM appointments)                 AS total_appointments,
            (SELECT COUNT(*) FROM appointments WHERE status = 'confirmed') AS confirmed_appointments,
            (SELECT COUNT(*) FROM appointments WHERE status = 'cancelled') AS cancelled_appointments,
            (SELECT COUNT(*) FROM appointments WHERE DATE(created_at) = CURDATE()) AS todays_appointments,
            (SELECT COALESCE(SUM(total_amount),0) FROM transactions WHERE status = 'paid') AS total_revenue
    ")->fetch(PDO::FETCH_ASSOC);

    render('admin/dashboard', ['user' => $user, 'stats' => $stats]);
}

// Page: GET /admin/transactions

function admin_transactions_page()
{
    $user = require_admin();
    render('admin/transactions', ['user' => $user]);
}

// Page: GET /admin/audit-trail

function admin_audit_trail_page()
{
    $user = require_admin();
    render('admin/audit_trail', ['user' => $user]);
}

// API: GET /admin/api/audit-trail
//   date_from    YYYY-MM-DD
//   date_to      YYYY-MM-DD
//   user_id      integer
//   action_group auth|notification|transaction|chatbot|user
//   action       string (exact)
//   search       string
//   page         integer (default 1)
//   per_page     integer (default 50, max 200)

function api_admin_audit_trail()
{
    $user = require_admin_api();

    $result = get_audit_logs([
        'date_from'    => $_GET['date_from']    ?? '',
        'date_to'      => $_GET['date_to']      ?? '',
        'user_id'      => $_GET['user_id']      ?? '',
        'action_group' => $_GET['action_group'] ?? '',
        'action'       => $_GET['action']       ?? '',
        'search'       => $_GET['search']       ?? '',
        'page'         => (int) ($_GET['page']     ?? 1),
        'per_page'     => (int) ($_GET['per_page'] ?? 50),
    ]);

    json_response(['success' => true] + $result);
}

// API: GET /admin/api/audit-trail/export  (CSV download)

function api_admin_audit_trail_export()
{
    $user = require_admin_api();

    $result = get_audit_logs([
        'date_from'    => $_GET['date_from']    ?? '',
        'date_to'      => $_GET['date_to']      ?? '',
        'user_id'      => $_GET['user_id']      ?? '',
        'action_group' => $_GET['action_group'] ?? '',
        'action'       => $_GET['action']       ?? '',
        'search'       => $_GET['search']       ?? '',
        'page'         => 1,
        'per_page'     => 200,  // max per request; caller should paginate for large exports
    ]);

    $filename = 'audit_trail_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Timestamp', 'User ID', 'User Name', 'Role', 'Action Group', 'Action', 'Detail', 'IP Address', 'User Agent']);
    foreach ($result['logs'] as $row) {
        fputcsv($out, [
            $row['id'],
            $row['created_at'],
            $row['user_id'],
            $row['user_name'],
            $row['user_role'],
            $row['action_group'],
            $row['action'],
            $row['detail'],
            $row['ip_address'],
            $row['user_agent'],
        ]);
    }
    fclose($out);
    exit;
}

// API: GET /admin/api/audit-trail/filters  (dropdown option lists)

function api_admin_audit_trail_filters()
{
    require_admin_api();

    json_response([
        'success' => true,
        'actions' => get_audit_action_options(),
        'users'   => get_audit_user_options(),
    ]);
}

// API: GET /admin/api/transactions
//   search     string - matches patient name, doctor name, or transaction_id

function api_admin_transactions()
{
    $user = require_admin_api();

    $pdo = db_connect();

    // Build WHERE clauses dynamically based on provided filters
    $where  = [];
    $params = [];

    $date_from = trim($_GET['date_from'] ?? '');
    $date_to   = trim($_GET['date_to']   ?? '');
    $status    = trim($_GET['status']    ?? '');
    $search    = trim($_GET['search']    ?? '');

    if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        $where[]  = 'DATE(t.paid_at) >= :date_from';
        $params[':date_from'] = $date_from;
    }

    if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $where[]  = 'DATE(t.paid_at) <= :date_to';
        $params[':date_to'] = $date_to;
    }

    if (in_array($status, ['paid', 'failed', 'pending'], true)) {
        $where[]  = 't.status = :status';
        $params[':status'] = $status;
    }

    if ($search !== '') {
        $where[]  = '(pu.name LIKE :search OR du.name LIKE :search OR t.transaction_id LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT
            t.id                AS transaction_id,
            t.transaction_id    AS esewa_txn_uuid,
            t.esewa_ref_id,
            t.amount,
            t.tax_amount,
            t.total_amount,
            t.status,
            t.payment_method,
            t.paid_at,
            t.created_at,
            pu.name             AS patient_name,
            du.name             AS doctor_name,
            a.appointment_date,
            a.reference_number
        FROM transactions t
        JOIN users pu  ON pu.id  = t.patient_id
        JOIN appointments a ON a.id = t.appointment_id
        JOIN doctors d     ON d.id  = a.doctor_id
        JOIN users du  ON du.id  = d.user_id
        {$whereSql}
        ORDER BY t.paid_at DESC, t.created_at DESC
        LIMIT 500
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary totals for the current filter
    $totalStmt = $pdo->prepare("
        SELECT
            COUNT(*)                                           AS total_count,
            COALESCE(SUM(CASE WHEN t.status = 'paid'    THEN t.total_amount END), 0) AS total_revenue,
            COALESCE(SUM(CASE WHEN t.status = 'paid'    THEN 1 END), 0)             AS paid_count,
            COALESCE(SUM(CASE WHEN t.status = 'failed'  THEN 1 END), 0)             AS failed_count,
            COALESCE(SUM(CASE WHEN t.status = 'pending' THEN 1 END), 0)             AS pending_count
        FROM transactions t
        JOIN users pu  ON pu.id  = t.patient_id
        JOIN appointments a ON a.id = t.appointment_id
        JOIN doctors d     ON d.id  = a.doctor_id
        JOIN users du  ON du.id  = d.user_id
        {$whereSql}
    ");
    $totalStmt->execute($params);
    $summary = $totalStmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success'      => true,
        'summary'      => $summary,
        'transactions' => $transactions,
    ]);
}