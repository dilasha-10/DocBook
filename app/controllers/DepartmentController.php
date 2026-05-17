<?php

//  DepartmentController.php
//  Admin: Manage Departments & Specializations

require_once BASE_PATH . '/app/models/DepartmentModel.php';

// Page

function admin_departments_page(): void
{
    $user = require_admin();
    render('admin/departments', ['user' => $user]);
}

// API: Departments

function api_admin_departments_list(): void
{
    require_admin_api();
    json_response(['success' => true, 'departments' => get_all_departments()]);
}

function api_admin_department_create(): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $name = trim($data['name'] ?? '');
    $icon = trim($data['icon'] ?? 'fa-stethoscope');
    $desc = trim($data['description'] ?? '');

    if ($name === '') {
        json_response(['success' => false, 'error' => 'Department name is required.'], 422);
    }

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $result = create_department($name, $slug, $icon, $desc);

    if (isset($result['error'])) {
        json_response(['success' => false, 'error' => $result['error']], 409);
    }

    audit_log((int)$user['id'], $user['name'], $user['role'], 'DEPARTMENT_CREATED', 'admin', "Created department: {$name}");
    json_response(['success' => true, 'id' => $result['id']]);
}

function api_admin_department_update(int $id): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $name      = trim($data['name'] ?? '');
    $icon      = trim($data['icon'] ?? 'fa-stethoscope');
    $desc      = trim($data['description'] ?? '');
    $is_active = (bool)($data['is_active'] ?? true);

    if ($name === '') {
        json_response(['success' => false, 'error' => 'Department name is required.'], 422);
    }

    $slug   = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $result = update_department($id, $name, $slug, $icon, $desc, $is_active);

    if (isset($result['error'])) {
        json_response(['success' => false, 'error' => $result['error']], 409);
    }

    audit_log((int)$user['id'], $user['name'], $user['role'], 'DEPARTMENT_UPDATED', 'admin', "Updated department #{$id}: {$name}");
    json_response(['success' => true]);
}

function api_admin_department_delete(int $id): void
{
    $user   = require_admin_api();
    $dept   = get_department_by_id($id);
    $result = delete_department($id);

    if (isset($result['error'])) {
        json_response(['success' => false, 'error' => $result['error']], 409);
    }

    audit_log((int)$user['id'], $user['name'], $user['role'], 'DEPARTMENT_DELETED', 'admin', "Deleted department: " . ($dept['name'] ?? $id));
    json_response(['success' => true]);
}

// API: Specializations

function api_admin_specializations_list(): void
{
    require_admin_api();
    $dept_id = (int)($_GET['department_id'] ?? 0);
    if ($dept_id > 0) {
        json_response(['success' => true, 'specializations' => get_specializations_by_department($dept_id)]);
    }
    json_response(['success' => true, 'specializations' => get_all_specializations()]);
}

function api_admin_specialization_create(): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $category_id = (int)($data['category_id'] ?? 0);
    $name        = trim($data['name'] ?? '');

    if ($category_id <= 0 || $name === '') {
        json_response(['success' => false, 'error' => 'Department and specialization name are required.'], 422);
    }

    $result = create_specialization($category_id, $name);
    if (isset($result['error'])) {
        json_response(['success' => false, 'error' => $result['error']], 409);
    }

    audit_log((int)$user['id'], $user['name'], $user['role'], 'SPECIALIZATION_CREATED', 'admin', "Created specialization: {$name}");
    json_response(['success' => true, 'id' => $result['id']]);
}

function api_admin_specialization_update(int $id): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $name      = trim($data['name'] ?? '');
    $is_active = (bool)($data['is_active'] ?? true);

    if ($name === '') {
        json_response(['success' => false, 'error' => 'Specialization name is required.'], 422);
    }

    $result = update_specialization($id, $name, $is_active);
    if (isset($result['error'])) {
        json_response(['success' => false, 'error' => $result['error']], 409);
    }

    audit_log((int)$user['id'], $user['name'], $user['role'], 'SPECIALIZATION_UPDATED', 'admin', "Updated specialization #{$id}: {$name}");
    json_response(['success' => true]);
}

function api_admin_specialization_delete(int $id): void
{
    $user   = require_admin_api();
    $result = delete_specialization($id);

    audit_log((int)$user['id'], $user['name'], $user['role'], 'SPECIALIZATION_DELETED', 'admin', "Deleted specialization #{$id}");
    json_response(['success' => true]);
}
