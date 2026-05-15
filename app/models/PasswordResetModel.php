<?php

require_once BASE_PATH . '/config/database.php';

function ensure_password_reset_table(): void
{
    $pdo = db_connect();
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS password_resets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            email VARCHAR(255) NOT NULL,
            otp_hash VARCHAR(255) NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            expires_at DATETIME NOT NULL,
            verified_at DATETIME DEFAULT NULL,
            consumed_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_password_resets_user_id (user_id),
            INDEX idx_password_resets_email (email),
            INDEX idx_password_resets_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function upsert_password_reset_request(int $userId, string $email, string $otpHash, string $expiresAt): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);

    $stmt = $pdo->prepare(
        "INSERT INTO password_resets (user_id, email, otp_hash, attempts, expires_at, created_at, updated_at)
         VALUES (:user_id, :email, :otp_hash, 0, :expires_at, NOW(), NOW())"
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':email' => $email,
        ':otp_hash' => $otpHash,
        ':expires_at' => $expiresAt,
    ]);
}

function find_active_password_reset_by_user_id(int $userId): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, user_id, email, otp_hash, attempts, expires_at, verified_at, consumed_at, created_at, updated_at
         FROM password_resets
         WHERE user_id = :user_id AND consumed_at IS NULL
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function find_verified_password_reset_by_user_id(int $userId): ?array
{
    $pdo = db_connect();
    $stmt = $pdo->prepare(
        "SELECT id, user_id, email, otp_hash, attempts, expires_at, verified_at, consumed_at, created_at, updated_at
         FROM password_resets
         WHERE user_id = :user_id AND consumed_at IS NULL AND verified_at IS NOT NULL
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function mark_password_reset_verified(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE password_resets SET verified_at = NOW(), updated_at = NOW() WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function clear_password_reset_request(int $id): void
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE password_resets SET consumed_at = NOW(), updated_at = NOW() WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

function increment_password_reset_attempts(int $id): int
{
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE password_resets SET attempts = attempts + 1, updated_at = NOW() WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $stmt = $pdo->prepare("SELECT attempts FROM password_resets WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return (int) $stmt->fetchColumn();
}
