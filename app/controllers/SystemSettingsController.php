<?php
// app/controllers/SystemSettingsController.php

require_once BASE_PATH . '/app/models/SystemSettingsModel.php';
require_once BASE_PATH . '/app/models/AuditLogModel.php';

// Render the admin system-settings page
function admin_system_settings_page(): void
{
    $user = require_admin();
    render('admin/system_settings', ['user' => $user]);
}

// GET /admin/api/system-settings — returns all settings rows and holidays
function api_admin_settings_get(): void
{
    require_admin_api();
    json_response([
        'success'  => true,
        'settings' => get_all_settings_full(),
        'holidays' => get_holidays(),
    ]);
}

// POST /admin/api/system-settings — save one or more setting key/value pairs
// Body: JSON object, e.g. { "payments": "0", "chatbot": "1" }
function api_admin_settings_save(): void
{
    $admin = require_admin_api();
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($body)) {
        json_response(['error' => 'No settings provided.'], 400);
        exit;
    }

    // Whitelist of accepted keys — rejects any unknown or dangerous keys
    $allowed  = ['work_start', 'work_end', 'slot_minutes', 'max_per_day',
                 'lab_reports', 'payments', 'chatbot'];
    $filtered = [];
    foreach ($body as $k => $v) {
        if (in_array($k, $allowed, true)) {
            $filtered[$k] = (string) $v;
        }
    }

    if (empty($filtered)) {
        json_response(['error' => 'No valid settings keys found.'], 400);
        exit;
    }

    // work_start must be strictly earlier than work_end when both are present
    if (isset($filtered['work_start'], $filtered['work_end'])) {
        if (strtotime($filtered['work_start']) >= strtotime($filtered['work_end'])) {
            json_response(['error' => 'Clinic open time must be before close time.'], 422);
            exit;
        }
    }

    try {
        save_settings_batch($filtered);
    } catch (InvalidArgumentException $e) {
        json_response(['error' => $e->getMessage()], 422);
        exit;
    }

    $changed_keys = implode(', ', array_keys($filtered));
    audit_log(
        user_id:      (int) $admin['id'],
        user_name:    $admin['name'],
        user_role:    $admin['role'],
        action:       'SETTINGS_UPDATED',
        action_group: 'settings',
        detail:       "Updated system settings: $changed_keys"
    );

    json_response(['success' => true, 'updated' => array_keys($filtered)]);
}

// POST /admin/api/system-settings/holidays — add a blocked date
// Body: { "holiday_date": "YYYY-MM-DD", "label": "..." }
function api_admin_holiday_add(): void
{
    $admin = require_admin_api();
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];

    $date  = trim($body['holiday_date'] ?? '');
    $label = trim($body['label']        ?? '');

    if ($date === '' || $label === '') {
        json_response(['error' => 'holiday_date and label are required.'], 400);
        exit;
    }

    try {
        $id = add_holiday($date, $label);
    } catch (InvalidArgumentException $e) {
        json_response(['error' => $e->getMessage()], 422);
        exit;
    }

    if ($id === 0) {
        json_response(['error' => 'That date is already marked as a holiday.'], 409);
        exit;
    }

    audit_log(
        user_id:      (int) $admin['id'],
        user_name:    $admin['name'],
        user_role:    $admin['role'],
        action:       'HOLIDAY_ADDED',
        action_group: 'settings',
        detail:       "Holiday added: $date – $label"
    );

    json_response(['success' => true, 'id' => $id]);
}

// DELETE /admin/api/system-settings/holidays/{id} — remove a blocked date
function api_admin_holiday_delete(int $id): void
{
    $admin = require_admin_api();

    $deleted = delete_holiday($id);
    if (!$deleted) {
        json_response(['error' => 'Holiday not found.'], 404);
        exit;
    }

    audit_log(
        user_id:      (int) $admin['id'],
        user_name:    $admin['name'],
        user_role:    $admin['role'],
        action:       'HOLIDAY_DELETED',
        action_group: 'settings',
        detail:       "Holiday id=$id removed."
    );

    json_response(['success' => true]);
}