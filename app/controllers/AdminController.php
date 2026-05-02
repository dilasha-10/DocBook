<?php

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
        json_response(['error' => 'Forbidden'], 403);
    }
    return $user;
}

// Page: GET /admin/dashboard

function admin_dashboard_page()
{
    http_response_code(404);
    echo '<h1>404 - Not Found</h1>';
    exit;
}

// Page: GET /admin/transactions

function admin_transactions_page()
{
    $user = require_admin();
    render('admin/transactions', ['user' => $user]);
}

// API: GET /admin/api/transactions
// Query params:
//   date_from  YYYY-MM-DD  (optional) inclusive start date filter on paid_at
//   date_to    YYYY-MM-DD  (optional) inclusive end   date filter on paid_at
//   search     string — matches patient name, doctor name, or transaction_id

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