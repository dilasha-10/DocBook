<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * GET /api/categories
 * Returns all rows from the categories table: id, name, slug, icon.
 */
function get_all_categories(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT id, name, slug, icon
        FROM   categories
        ORDER  BY id ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}