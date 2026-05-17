<?php

// ============================================================
//  AnnouncementModel.php
//  CRUD for system-wide announcements (D2-03).
// ============================================================

require_once __DIR__ . '/../../config/database.php';

/**
 * Get active announcements for a given role.
 * Only returns announcements that haven't expired.
 */
function get_active_announcements(string $role = 'all'): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT a.*, u.name AS created_by_name
        FROM announcements a
        JOIN users u ON u.id = a.created_by
        WHERE a.is_active = 1
          AND a.starts_at <= NOW()
          AND (a.expires_at IS NULL OR a.expires_at > NOW())
          AND (a.target_roles = 'all' OR FIND_IN_SET(?, a.target_roles) > 0)
        ORDER BY
            FIELD(a.type, 'urgent', 'warning', 'info', 'success'),
            a.created_at DESC
    ");
    $stmt->execute([$role]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all announcements (admin management).
 */
function get_all_announcements(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT a.*, u.name AS created_by_name,
               CASE
                   WHEN a.is_active = 0 THEN 'inactive'
                   WHEN a.expires_at IS NOT NULL AND a.expires_at < NOW() THEN 'expired'
                   WHEN a.starts_at > NOW() THEN 'scheduled'
                   ELSE 'active'
               END AS computed_status
        FROM announcements a
        JOIN users u ON u.id = a.created_by
        ORDER BY a.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a single announcement.
 */
function get_announcement_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Create an announcement.
 */
function create_announcement(
    string $title,
    string $message,
    string $type,
    string $target_roles,
    ?string $starts_at,
    ?string $expires_at,
    int $created_by
): int {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO announcements (title, message, type, target_roles, starts_at, expires_at, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $title,
        $message,
        $type,
        $target_roles,
        $starts_at ?: date('Y-m-d H:i:s'),
        $expires_at ?: null,
        $created_by
    ]);
    return (int) $pdo->lastInsertId();
}

/**
 * Update an announcement.
 */
function update_announcement(int $id, array $data): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        UPDATE announcements
        SET title = ?, message = ?, type = ?, target_roles = ?,
            starts_at = ?, expires_at = ?, is_active = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['title'],
        $data['message'],
        $data['type'],
        $data['target_roles'],
        $data['starts_at'],
        $data['expires_at'] ?: null,
        $data['is_active'] ? 1 : 0,
        $id
    ]);
    return $stmt->rowCount() >= 0;
}

/**
 * Delete an announcement.
 */
function delete_announcement(int $id): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

/**
 * Toggle announcement active status.
 */
function toggle_announcement(int $id): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
<<<<<<< HEAD
}
=======
}
>>>>>>> 5353f4c (Final complete work)
