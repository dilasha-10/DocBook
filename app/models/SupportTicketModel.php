<?php

// ============================================================
//  SupportTicketModel.php
//  CRUD for patient support tickets (D2-05).
// ============================================================

require_once __DIR__ . '/../../config/database.php';

/**
 * Create a new support ticket (called by patient).
 */
function create_support_ticket(int $patient_id, string $subject, string $message, string $category = 'other'): int
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (patient_id, subject, message, category)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$patient_id, $subject, $message, $category]);
    return (int) $pdo->lastInsertId();
}

/**
 * Get all tickets for admin view (with patient metadata only – no medical data).
 */
function get_all_support_tickets(array $filters = []): array
{
    $pdo    = db_connect();
    $where  = [];
    $params = [];

    $status   = trim($filters['status']   ?? '');
    $category = trim($filters['category'] ?? '');
    $search   = trim($filters['search']   ?? '');

    if ($status !== '' && in_array($status, ['open', 'in_progress', 'resolved'])) {
        $where[]          = 'st.status = :status';
        $params[':status'] = $status;
    }

    if ($category !== '') {
        $where[]            = 'st.category = :category';
        $params[':category'] = $category;
    }

    if ($search !== '') {
        $where[]          = '(u.name LIKE :search OR u.email LIKE :search OR st.subject LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT
            st.id,
            st.subject,
            st.message,
            st.category,
            st.status,
            st.admin_reply,
            st.replied_at,
            st.created_at,
            st.updated_at,
            u.name          AS patient_name,
            u.email         AS patient_email,
            ru.name         AS replied_by_name
        FROM support_tickets st
        JOIN users u ON u.id = st.patient_id
        LEFT JOIN users ru ON ru.id = st.replied_by
        {$whereSql}
        ORDER BY
            FIELD(st.status, 'open', 'in_progress', 'resolved'),
            st.created_at DESC
        LIMIT 500
    ");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a single ticket by ID.
 */
function get_support_ticket_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT st.*, u.name AS patient_name, u.email AS patient_email
        FROM support_tickets st
        JOIN users u ON u.id = st.patient_id
        WHERE st.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get tickets submitted by a specific patient.
 */
function get_patient_tickets(int $patient_id): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT st.*, ru.name AS replied_by_name
        FROM support_tickets st
        LEFT JOIN users ru ON ru.id = st.replied_by
        WHERE st.patient_id = ?
        ORDER BY st.created_at DESC
    ");
    $stmt->execute([$patient_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update ticket status.
 */
function update_ticket_status(int $id, string $status): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    return $stmt->rowCount() > 0;
}

/**
 * Admin replies to a ticket — updates status, sets reply, and notifies patient.
 */
function reply_to_ticket(int $id, int $admin_id, string $reply, string $status): array
{
    $pdo = db_connect();

    $ticket = get_support_ticket_by_id($id);
    if (!$ticket) return ['error' => 'Ticket not found.'];

    $stmt = $pdo->prepare("
        UPDATE support_tickets
        SET admin_reply = ?, replied_by = ?, replied_at = NOW(), status = ?
        WHERE id = ?
    ");
    $stmt->execute([$reply, $admin_id, $status, $id]);

    return ['success' => true, 'patient_id' => $ticket['patient_id']];
}

/**
 * Get ticket stats for dashboard.
 */
function get_ticket_stats(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT
            COUNT(*)                                              AS total,
            SUM(CASE WHEN status = 'open'        THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN status = 'resolved'    THEN 1 ELSE 0 END) AS resolved_count
        FROM support_tickets
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'open_count' => 0, 'in_progress_count' => 0, 'resolved_count' => 0];
<<<<<<< HEAD
}
=======
}
>>>>>>> 5353f4c (Final complete work)
