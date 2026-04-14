<?php

require_once BASE_PATH . '/config/database.php';

// ── User lookup ──────────────────────────────────────────────────────────────

function find_user_by_email(string $email): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, phone, created_at FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function find_user_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT id, name, email, role, phone, created_at FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function email_exists(string $email): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return (int) $stmt->fetchColumn() > 0;
}

function create_user(string $name, string $email, string $hashedPassword, string $role, string $phone = ''): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password, role, phone, created_at)
         VALUES (:name, :email, :password, :role, :phone, NOW())"
    );
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hashedPassword,
        ':role'     => $role,
        ':phone'    => $phone,
    ]);
    $id = (int) $pdo->lastInsertId();
    return find_user_by_id($id);
}