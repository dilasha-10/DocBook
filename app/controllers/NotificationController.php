<?php

require_once BASE_PATH . '/app/models/NotificationModel.php';

// ════════════════════════════════════════════════════════════
//  NotificationController — routes & API handlers
// ════════════════════════════════════════════════════════════

// ── Admin page ───────────────────────────────────────────────

/**
 * GET /admin/notifications
 * Renders the full admin notification centre page.
 */
function admin_notifications_page(): void
{
    $user = require_admin();
    render('admin/notifications', ['user' => $user]);
}

// ── Admin API: send broadcast ─────────────────────────────────

/**
 * POST /admin/api/notifications/broadcast
 * Body (JSON): { title, message, target_roles: ["patient","doctor",...] }
 */
function api_admin_notifications_broadcast(): void
{
    $admin = require_admin_api();

    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $title = trim($body['title']   ?? '');
    $msg   = trim($body['message'] ?? '');
    $roles = $body['target_roles'] ?? [];

    $valid_roles = ['patient', 'doctor', 'admin', 'lab_admin', 'all'];

    if ($title === '' || $msg === '') {
        json_response(['error' => 'title and message are required'], 422);
    }
    if (empty($roles) || count(array_diff($roles, $valid_roles)) > 0) {
        json_response(['error' => 'target_roles must be a non-empty array of valid roles'], 422);
    }

    $result = notify_broadcast(
        sender_id:    (int) $admin['id'],
        title:        $title,
        message:      $msg,
        target_roles: $roles,
        type:         'system_maintenance'
    );

    json_response(['success' => true, 'broadcast_id' => $result['broadcast_id'], 'total_sent' => $result['total_sent']]);
}

// ── Admin API: send targeted notification ────────────────────

/**
 * POST /admin/api/notifications/targeted
 * Body (JSON): { recipient_id, title, message }
 */
function api_admin_notifications_targeted(): void
{
    $admin = require_admin_api();

    $body         = json_decode(file_get_contents('php://input'), true) ?? [];
    $recipient_id = (int) ($body['recipient_id'] ?? 0);
    $title        = trim($body['title']          ?? '');
    $msg          = trim($body['message']        ?? '');

    if ($recipient_id <= 0 || $title === '' || $msg === '') {
        json_response(['error' => 'recipient_id, title, and message are required'], 422);
    }

    $notif_id = notify_targeted(
        sender_id:    (int) $admin['id'],
        recipient_id: $recipient_id,
        title:        $title,
        message:      $msg
    );

    json_response(['success' => true, 'notification_id' => $notif_id]);
}

// ── Admin API: user search (for targeted UI) ─────────────────

/**
 * GET /admin/api/notifications/search-users?q=...
 */
function api_admin_notifications_search_users(): void
{
    require_admin_api();
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        json_response(['users' => []]);
    }
    json_response(['users' => notification_search_users($q)]);
}

// ── Admin API: broadcast history ─────────────────────────────

/**
 * GET /admin/api/notifications/broadcasts
 */
function api_admin_notifications_broadcasts(): void
{
    require_admin_api();
    json_response(['broadcasts' => notification_broadcasts_list(100)]);
}

// ── User API: fetch my notifications ─────────────────────────

/**
 * GET /api/notifications?limit=30&offset=0
 */
function api_user_notifications(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user) { json_response(['error' => 'Unauthenticated'], 401); }

    $limit  = max(1, min(100, (int) ($_GET['limit']  ?? 30)));
    $offset = max(0,          (int) ($_GET['offset'] ?? 0));

    json_response([
        'notifications'  => notifications_for_user((int) $user['id'], $limit, $offset),
        'unread_count'   => notifications_unread_count((int) $user['id']),
    ]);
}

// ── User API: mark read ───────────────────────────────────────

/**
 * POST /api/notifications/read
 * Body (JSON): { id: <int> } or { all: true }
 */
function api_user_notifications_read(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user) { json_response(['error' => 'Unauthenticated'], 401); }

    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    if (!empty($body['all'])) {
        $count = notifications_mark_all_read((int) $user['id']);
        json_response(['success' => true, 'marked' => $count]);
    } elseif (!empty($body['id'])) {
        $ok = notification_mark_read((int) $body['id'], (int) $user['id']);
        json_response(['success' => $ok]);
    } else {
        json_response(['error' => 'Provide id or all:true'], 422);
    }
}

// ── Unread badge count (called by layout header) ─────────────

/**
 * GET /api/notifications/unread-count
 */
function api_user_notifications_unread_count(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = auth_user();
    if (!$user) { json_response(['count' => 0]); }
    json_response(['count' => notifications_unread_count((int) $user['id'])]);
}