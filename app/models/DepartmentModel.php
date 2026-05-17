<?php

//  DepartmentModel.php
//  CRUD for categories (departments) and specializations.

require_once __DIR__ . '/../../config/database.php';

// Categories (Departments) 

function get_all_departments(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT c.id, c.name, c.slug, c.icon, c.description, c.is_active, 
               COUNT(s.id) AS specialization_count
        FROM categories c
        LEFT JOIN specializations s ON s.category_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_department_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function create_department(string $name, string $slug, string $icon, string $description): array
{
    $pdo = db_connect();

    // Check for duplicates
    $chk = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
    $chk->execute([$name]);
    if ($chk->fetch()) {
        return ['error' => 'A department with this name already exists.'];
    }

    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $icon, $description]);
    $id = (int) $pdo->lastInsertId();

    return ['success' => true, 'id' => $id];
}

function update_department(int $id, string $name, string $slug, string $icon, string $description, bool $is_active): array
{
    $pdo = db_connect();

    // Check for duplicate name (exclude self)
    $chk = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) AND id != ?");
    $chk->execute([$name, $id]);
    if ($chk->fetch()) {
        return ['error' => 'A department with this name already exists.'];
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, icon = ?, description = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name, $slug, $icon, $description, $is_active ? 1 : 0, $id]);

    return ['success' => true];
}

function delete_department(int $id): array
{
    $pdo = db_connect();

    // Check if doctors are assigned
    $chk = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE category_id = ?");
    $chk->execute([$id]);
    if ((int) $chk->fetchColumn() > 0) {
        return ['error' => 'Cannot delete: doctors are still assigned to this department.'];
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return ['success' => true];
}

// Specializations

function get_specializations_by_department(int $category_id): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM specializations WHERE category_id = ? ORDER BY name ASC");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_specializations(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT s.*, c.name AS department_name
        FROM specializations s
        JOIN categories c ON c.id = s.category_id
        ORDER BY c.name, s.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function create_specialization(int $category_id, string $name): array
{
    $pdo  = db_connect();
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));

    // Duplicate check within department
    $chk = $pdo->prepare("SELECT id FROM specializations WHERE category_id = ? AND LOWER(name) = LOWER(?)");
    $chk->execute([$category_id, $name]);
    if ($chk->fetch()) {
        return ['error' => 'This specialization already exists in the department.'];
    }

    $stmt = $pdo->prepare("INSERT INTO specializations (category_id, name, slug) VALUES (?, ?, ?)");
    $stmt->execute([$category_id, $name, $slug]);
    return ['success' => true, 'id' => (int) $pdo->lastInsertId()];
}

function update_specialization(int $id, string $name, bool $is_active): array
{
    $pdo  = db_connect();
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));

    // Get the category_id first
    $spec = $pdo->prepare("SELECT category_id FROM specializations WHERE id = ?");
    $spec->execute([$id]);
    $row = $spec->fetch(PDO::FETCH_ASSOC);
    if (!$row) return ['error' => 'Specialization not found.'];

    // Duplicate check
    $chk = $pdo->prepare("SELECT id FROM specializations WHERE category_id = ? AND LOWER(name) = LOWER(?) AND id != ?");
    $chk->execute([$row['category_id'], $name, $id]);
    if ($chk->fetch()) {
        return ['error' => 'This specialization already exists in the department.'];
    }

    $stmt = $pdo->prepare("UPDATE specializations SET name = ?, slug = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name, $slug, $is_active ? 1 : 0, $id]);
    return ['success' => true];
}

function delete_specialization(int $id): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM specializations WHERE id = ?");
    $stmt->execute([$id]);
    return ['success' => true];
}