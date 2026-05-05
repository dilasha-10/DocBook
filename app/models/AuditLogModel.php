<?php

// ============================================================
//  AuditLogModel.php
//  All database operations for the audit_logs table.
// ============================================================

// ── Write ────────────────────────────────────────────────────

/**
 * Record one admin action.
 *
 * @param  int|null  $user_id
 * @param  string|null $user_name
 * @param  string|null $user_role
 * @param  string  $action        SHORT_SCREAMING_SNAKE label  e.g. LOGIN
 * @param  string  $action_group  auth | notification | transaction | chatbot | user
 * @param  string|null $detail    Human-readable sentence
 */
function audit_log(
    ?int    $user_id,
    ?string $user_name,
    ?string $user_role,
    string  $action,
    string  $action_group,
    ?string $detail = null
): void {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs
                (user_id, user_name, user_role, action, action_group, detail, ip_address, user_agent)
            VALUES
                (:user_id, :user_name, :user_role, :action, :action_group, :detail, :ip, :ua)
        ");
        $stmt->execute([
            ':user_id'      => $user_id,
            ':user_name'    => $user_name,
            ':user_role'    => $user_role,
            ':action'       => strtoupper($action),
            ':action_group' => strtolower($action_group),
            ':detail'       => $detail,
            ':ip'           => $_SERVER['REMOTE_ADDR']          ?? null,
            ':ua'           => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
        ]);
    } catch (\Throwable $e) {
        // Never let audit failures crash the app – just silently ignore.
        error_log('[AuditLog] Write failed: ' . $e->getMessage());
    }
}

// ── Read ─────────────────────────────────────────────────────

/**
 * Fetch audit logs with optional filters + pagination.
 *
 * @param  array{
 *   date_from?: string,
 *   date_to?:   string,
 *   user_id?:   int|string,
 *   action_group?: string,
 *   action?:    string,
 *   search?:    string,
 *   page?:      int,
 *   per_page?:  int,
 * } $filters
 * @return array{ logs: array, total: int, page: int, per_page: int, total_pages: int }
 */
function get_audit_logs(array $filters = []): array
{
    $pdo    = db_connect();
    $where  = [];
    $params = [];

    $date_from    = trim($filters['date_from']    ?? '');
    $date_to      = trim($filters['date_to']      ?? '');
    $user_id      = $filters['user_id']      ?? '';
    $action_group = trim($filters['action_group'] ?? '');
    $action       = trim($filters['action']       ?? '');
    $search       = trim($filters['search']       ?? '');
    $page         = max(1, (int)($filters['page']     ?? 1));
    $per_page     = min(200, max(10, (int)($filters['per_page'] ?? 50)));

    if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
        $where[]               = 'DATE(al.created_at) >= :date_from';
        $params[':date_from']  = $date_from;
    }

    if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
        $where[]             = 'DATE(al.created_at) <= :date_to';
        $params[':date_to']  = $date_to;
    }

    if ($user_id !== '' && ctype_digit((string) $user_id)) {
        $where[]             = 'al.user_id = :user_id';
        $params[':user_id']  = (int) $user_id;
    }

    if ($action_group !== '') {
        $where[]                  = 'al.action_group = :action_group';
        $params[':action_group']  = $action_group;
    }

    if ($action !== '') {
        $where[]           = 'al.action = :action';
        $params[':action'] = strtoupper($action);
    }

    if ($search !== '') {
        $where[] = '(al.user_name LIKE :search OR al.action LIKE :search OR al.detail LIKE :search OR al.ip_address LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs al {$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $total_pages = max(1, (int) ceil($total / $per_page));
    $page        = min($page, $total_pages);
    $offset      = ($page - 1) * $per_page;

    // Rows
    $stmt = $pdo->prepare("
        SELECT
            al.id,
            al.user_id,
            al.user_name,
            al.user_role,
            al.action,
            al.action_group,
            al.detail,
            al.ip_address,
            al.user_agent,
            al.created_at
        FROM audit_logs al
        {$whereSql}
        ORDER BY al.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit',  $per_page, \PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,   \PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    return compact('logs', 'total', 'page', 'per_page', 'total_pages');
}

/**
 * Return distinct action values for the filter dropdown.
 */
function get_audit_action_options(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action");
    return $stmt->fetchAll(\PDO::FETCH_COLUMN);
}

/**
 * Return distinct (user_id, user_name) pairs for the user filter.
 */
function get_audit_user_options(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT DISTINCT user_id, user_name, user_role
        FROM audit_logs
        WHERE user_id IS NOT NULL
        ORDER BY user_name
    ");
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}