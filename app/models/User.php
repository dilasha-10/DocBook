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
    $stmt = $pdo->prepare("SELECT id, name, email, role, phone, patient_unique_id, created_at FROM users WHERE id = :id LIMIT 1");
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

/**
 * Generate a random unique patient ID like "PAT-A1B2C3".
 * Retries up to 10 times to guarantee uniqueness.
 */
function generate_patient_unique_id(object $pdo): string
{
    for ($i = 0; $i < 10; $i++) {
        $chars    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $suffix   = '';
        for ($j = 0; $j < 6; $j++) {
            $suffix .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $pid = 'PAT-' . $suffix;

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE patient_unique_id = ?");
        $check->execute([$pid]);
        if ((int) $check->fetchColumn() === 0) {
            return $pid;
        }
    }
    // Fallback: timestamp-based (virtually impossible collision)
    return 'PAT-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

function create_user(string $name, string $email, string $hashedPassword, string $role, string $phone = ''): array
{
    $pdo = db_connect();

    // Only patients get a unique ID
    $patientUniqueId = null;
    if ($role === 'patient') {
        $patientUniqueId = generate_patient_unique_id($pdo);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password, role, phone, patient_unique_id, created_at)
         VALUES (:name, :email, :password, :role, :phone, :patient_unique_id, NOW())"
    );
    $stmt->execute([
        ':name'              => $name,
        ':email'             => $email,
        ':password'          => $hashedPassword,
        ':role'              => $role,
        ':phone'             => $phone,
        ':patient_unique_id' => $patientUniqueId,
    ]);
    $id = (int) $pdo->lastInsertId();
    return find_user_by_id($id);
}