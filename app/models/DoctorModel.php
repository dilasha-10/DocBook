<?php

require_once __DIR__ . '/../../config/database.php';

function get_filtered_doctors(?string $category = null, ?string $search = null): array
{
    $pdo = db_connect();

    $sql = "
        SELECT
            d.id,
            u.name                              AS name,
            d.photo,
            d.specialty,
            d.avg_rating,
            d.fee,
            c.name                              AS category_name,
            c.slug                              AS category_slug,
            MIN(
                CASE da.day_of_week
                    WHEN 'Sunday'    THEN DATE_ADD(CURDATE(), INTERVAL (1 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Monday'    THEN DATE_ADD(CURDATE(), INTERVAL (2 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Tuesday'   THEN DATE_ADD(CURDATE(), INTERVAL (3 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Wednesday' THEN DATE_ADD(CURDATE(), INTERVAL (4 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Thursday'  THEN DATE_ADD(CURDATE(), INTERVAL (5 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Friday'    THEN DATE_ADD(CURDATE(), INTERVAL (6 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                    WHEN 'Saturday'  THEN DATE_ADD(CURDATE(), INTERVAL (7 - DAYOFWEEK(CURDATE()) + 7) % 7 DAY)
                END
            )                                   AS next_available_date
        FROM      doctors             d
        JOIN      users               u   ON u.id  = d.user_id
        JOIN      categories          c   ON c.id  = d.category_id
        LEFT JOIN doctor_availability da  ON da.doctor_id = d.id
        WHERE 1 = 1
    ";

    $params = [];

    if (!empty($category) && $category !== 'all') {
        $sql .= " AND c.slug = :category ";
        $params[':category'] = strtolower(trim($category));
    }

    if (!empty($search)) {
        $sql .= " AND u.name LIKE :search ";
        $params[':search'] = '%' . trim($search) . '%';
    }

    $sql .= "
        GROUP BY
            d.id, u.name, d.photo, d.specialty, d.avg_rating, d.fee,
            c.name, c.slug
        ORDER BY d.avg_rating DESC, u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['id']         = (int)   $row['id'];
        $row['fee']        = (float) $row['fee'];
        $row['avg_rating'] = $row['avg_rating'] !== null ? (float) $row['avg_rating'] : null;
    }
    unset($row);

    return $rows;
}