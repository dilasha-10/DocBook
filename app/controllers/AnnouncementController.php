<?php

//  AnnouncementController.php
//  Admin: Broadcast System-Wide Announcements

require_once BASE_PATH . '/app/models/AnnouncementModel.php';

// Admin Page

function admin_announcements_page(): void
{
    $user = require_admin();
    render('admin/announcements', ['user' => $user]);
}

// Admin API

function api_admin_announcements_list(): void
{
    require_admin_api();
    json_response(['success' => true, 'announcements' => get_all_announcements()]);
}

function api_admin_announcement_create(): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $title        = trim($data['title']        ?? '');
    $message      = trim($data['message']      ?? '');
    $type         = trim($data['type']         ?? 'info');
    $target_roles = trim($data['target_roles'] ?? 'all');
    $starts_at    = trim($data['starts_at']    ?? '');
    $expires_at   = trim($data['expires_at']   ?? '');

    if ($title === '' || $message === '') {
        json_response(['success' => false, 'error' => 'Title and message are required.'], 422);
    }

    $validTypes = ['info', 'warning', 'success', 'urgent'];
    if (!in_array($type, $validTypes)) $type = 'info';

    $id = create_announcement($title, $message, $type, $target_roles, $starts_at ?: null, $expires_at ?: null, (int)$user['id']);

    audit_log((int)$user['id'], $user['name'], $user['role'], 'ANNOUNCEMENT_CREATED', 'admin',
        "Created announcement: {$title}");

    json_response(['success' => true, 'id' => $id]);
}

function api_admin_announcement_update(int $id): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $ann = get_announcement_by_id($id);
    if (!$ann) {
        json_response(['success' => false, 'error' => 'Announcement not found.'], 404);
    }

    update_announcement($id, [
        'title'        => trim($data['title']        ?? $ann['title']),
        'message'      => trim($data['message']      ?? $ann['message']),
        'type'         => trim($data['type']         ?? $ann['type']),
        'target_roles' => trim($data['target_roles'] ?? $ann['target_roles']),
        'starts_at'    => trim($data['starts_at']    ?? $ann['starts_at']),
        'expires_at'   => trim($data['expires_at']   ?? '') ?: null,
        'is_active'    => (bool)($data['is_active']  ?? $ann['is_active']),
    ]);

    audit_log((int)$user['id'], $user['name'], $user['role'], 'ANNOUNCEMENT_UPDATED', 'admin',
        "Updated announcement #{$id}");

    json_response(['success' => true]);
}

function api_admin_announcement_delete(int $id): void
{
    $user = require_admin_api();
    delete_announcement($id);

    audit_log((int)$user['id'], $user['name'], $user['role'], 'ANNOUNCEMENT_DELETED', 'admin',
        "Deleted announcement #{$id}");

    json_response(['success' => true]);
}

function api_admin_announcement_toggle(int $id): void
{
    $user = require_admin_api();
    toggle_announcement($id);

    audit_log((int)$user['id'], $user['name'], $user['role'], 'ANNOUNCEMENT_TOGGLED', 'admin',
        "Toggled announcement #{$id}");

    json_response(['success' => true]);
}

// Public API (for dashboards to fetch active banners) 

function api_active_announcements(): void
{
    $user = auth_user();
    $role = $user['role'] ?? 'patient';
    json_response(['success' => true, 'announcements' => get_active_announcements($role)]);
}