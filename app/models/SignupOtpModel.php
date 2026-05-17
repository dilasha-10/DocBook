<?php

require_once BASE_PATH . '/config/database.php';

/**
 * Signup OTP model.
 *
 * Stores a hashed OTP for email verification during registration.
 * The pending registration data (name, hashed password, phone, dob) is kept
 * in the session — nothing is written to `users` until OTP is confirmed.
 */

function ensure_signup_otps_table(): void
{
    $pdo = db_connect();
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS signup_otps (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email      VARCHAR(255) NOT NULL,
            otp_hash   VARCHAR(255) NOT NULL,
            attempts   TINYINT UNSIGNED NOT NULL DEFAULT 0,
            expires_at DATETIME NOT NULL,
            verified   TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_so_email   (email),
            INDEX idx_so_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/** Delete any existing OTP for the email, insert a fresh one.
 *  Returns the raw OTP string (6 digits). */
function create_signup_otp(string $email, string $expiresAt): string
{
    $pdo = db_connect();
    $pdo->prepare("DELETE FROM signup_otps WHERE email = :email")->execute([':email' => $email]);

    $otp     = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);

    $pdo->prepare(
        "INSERT INTO signup_otps (email, otp_hash, attempts, expires_at, verified, created_at)
         VALUES (:email, :otp_hash, 0, :expires_at, 0, NOW())"
    )->execute([':email' => $email, ':otp_hash' => $otpHash, ':expires_at' => $expiresAt]);

    return $otp;
}

/** Find active (not verified, not expired) OTP record for an email. */
function find_active_signup_otp(string $email): ?array
{
    $stmt = db_connect()->prepare(
        "SELECT id, email, otp_hash, attempts, expires_at, verified
         FROM signup_otps
         WHERE email = :email AND verified = 0
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** Increment attempts, return new count. */
function increment_signup_otp_attempts(int $id): int
{
    $pdo = db_connect();
    $pdo->prepare("UPDATE signup_otps SET attempts = attempts + 1 WHERE id = :id")->execute([':id' => $id]);
    return (int) $pdo->prepare("SELECT attempts FROM signup_otps WHERE id = :id")->execute([':id' => $id]) ?: 0;
    // re-query:
}

/** Proper increment that returns the new value. */
function bump_signup_otp_attempts(int $id): int
{
    $pdo = db_connect();
    $pdo->prepare("UPDATE signup_otps SET attempts = attempts + 1 WHERE id = :id")->execute([':id' => $id]);
    $stmt = $pdo->prepare("SELECT attempts FROM signup_otps WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return (int) $stmt->fetchColumn();
}

/** Mark OTP as verified (single-use). */
function mark_signup_otp_verified(int $id): void
{
    db_connect()->prepare(
        "UPDATE signup_otps SET verified = 1 WHERE id = :id"
    )->execute([':id' => $id]);
}

/** Clean up. */
function delete_signup_otp(string $email): void
{
    db_connect()->prepare("DELETE FROM signup_otps WHERE email = :email")->execute([':email' => $email]);
}