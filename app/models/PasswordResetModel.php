<?php

require_once BASE_PATH . '/config/database.php';

/**
 * Password-reset token model.
 *
 * Flow (no race condition):
 *   1. User requests reset → generate token, store SHA-256 hash in DB, email raw token as link.
 *   2. User clicks link  → look up hash, verify not consumed/expired.
 *   3. User submits new password → UPDATE password + mark consumed in ONE atomic transaction
 *      that re-checks consumed_at IS NULL with FOR UPDATE.  Only one writer can succeed.
 */

function ensure_password_reset_table(): void
{
    $pdo = db_connect();
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS password_resets (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id      INT UNSIGNED NOT NULL,
            email        VARCHAR(255) NOT NULL,
            token_hash   VARCHAR(64)  NOT NULL,
            attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
            expires_at   DATETIME NOT NULL,
            consumed_at  DATETIME DEFAULT NULL,
            created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY  uq_token_hash (token_hash),
            INDEX       idx_pr_user_id (user_id),
            INDEX       idx_pr_email   (email),
            INDEX       idx_pr_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Migrate old schema: drop otp_hash / verified_at, add token_hash if needed.
    $cols = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'otp_hash'")->fetchAll();
    if ($cols) {
        $pdo->exec("ALTER TABLE password_resets DROP COLUMN otp_hash");
    }
    $vcols = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'verified_at'")->fetchAll();
    if ($vcols) {
        $pdo->exec("ALTER TABLE password_resets DROP COLUMN verified_at");
    }
    $tcols = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'token_hash'")->fetchAll();
    if (!$tcols) {
        $pdo->exec("ALTER TABLE password_resets ADD COLUMN token_hash VARCHAR(64) NOT NULL DEFAULT '' AFTER email");
        try { $pdo->exec("ALTER TABLE password_resets ADD UNIQUE KEY uq_token_hash (token_hash)"); } catch (\Throwable $e) {}
    }
}

/**
 * Create a new reset request.  Deletes any existing open request for the user
 * so there is always at most one live token per user.
 *
 * @return string  The raw (unhashed) token to embed in the magic link.
 */
function create_password_reset_token(int $userId, string $email, string $expiresAt): string
{
    $pdo = db_connect();
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid")->execute([':uid' => $userId]);

    $rawToken  = bin2hex(random_bytes(32));   // 64 hex chars, 256-bit entropy
    $tokenHash = hash('sha256', $rawToken);

    $pdo->prepare(
        "INSERT INTO password_resets (user_id, email, token_hash, attempts, expires_at, created_at, updated_at)
         VALUES (:user_id, :email, :token_hash, 0, :expires_at, NOW(), NOW())"
    )->execute([
        ':user_id'    => $userId,
        ':email'      => $email,
        ':token_hash' => $tokenHash,
        ':expires_at' => $expiresAt,
    ]);

    return $rawToken;
}

/**
 * Look up a valid (not consumed, not expired) reset request by raw token.
 */
function find_valid_reset_by_token(string $rawToken): ?array
{
    $tokenHash = hash('sha256', $rawToken);
    $pdo       = db_connect();

    $stmt = $pdo->prepare(
        "SELECT id, user_id, email, token_hash, expires_at, consumed_at, created_at
         FROM password_resets
         WHERE token_hash = :token_hash
           AND consumed_at IS NULL
           AND expires_at  > NOW()
         LIMIT 1"
    );
    $stmt->execute([':token_hash' => $tokenHash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Atomically consume the token and update the user's password.
 * Uses FOR UPDATE + transaction so concurrent clicks produce exactly one success.
 *
 * @return bool  true on success, false if the token was already consumed / expired.
 */
function consume_token_and_reset_password(string $rawToken, string $newPasswordHash): bool
{
    $tokenHash = hash('sha256', $rawToken);
    $pdo       = db_connect();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            "SELECT id, user_id FROM password_resets
             WHERE token_hash = :token_hash
               AND consumed_at IS NULL
               AND expires_at  > NOW()
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $pdo->rollBack();
            return false;
        }

        $pdo->prepare(
            "UPDATE password_resets SET consumed_at = NOW(), updated_at = NOW() WHERE id = :id"
        )->execute([':id' => $row['id']]);

        $pdo->prepare(
            "UPDATE users SET password = :pw WHERE id = :uid"
        )->execute([':pw' => $newPasswordHash, ':uid' => $row['user_id']]);

        $pdo->commit();
        return true;

    } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Invalidate all open reset tokens for a user. */
function invalidate_all_reset_tokens(int $userId): void
{
    db_connect()
        ->prepare("DELETE FROM password_resets WHERE user_id = :uid")
        ->execute([':uid' => $userId]);
}