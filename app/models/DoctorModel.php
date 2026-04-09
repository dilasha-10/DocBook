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
            d.bio,
            d.experience_years,
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
            d.id, u.name, d.photo, d.specialty, d.bio, d.experience_years,
            c.name, c.slug
        ORDER BY u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$row) {
        $row['id'] = (int) $row['id'];
    }
    unset($row);

    return $rows;
}

// ── Fetch a single doctor by their doctors.id ────────────────────────────────
function get_doctor_by_id(int $id): ?array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT
            d.id,
            d.bio,
            d.specialty,
            d.experience_years,
            d.photo,
            u.name          AS name,
            c.name          AS category_name,
            c.slug          AS category_slug,
            c.avg_slot_minutes
        FROM  doctors     d
        JOIN  users       u  ON u.id = d.user_id
        JOIN  categories  c  ON c.id = d.category_id
        WHERE d.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

// ── Fetch availability days for a doctor ─────────────────────────────────────
function get_doctor_availability(int $doctor_id): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT day_of_week, start_time, end_time, break_minutes
        FROM   doctor_availability
        WHERE  doctor_id = :id
        ORDER BY FIELD(day_of_week,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')
    ");
    $stmt->execute([':id' => $doctor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Fetch already-booked slots for a doctor on a specific date ───────────────
function get_booked_slots(int $doctor_id, string $date): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT start_time
        FROM   appointments
        WHERE  doctor_id        = :did
          AND  appointment_date = :date
          AND  status NOT IN ('Cancelled', 'Rescheduled')
    ");
    $stmt->execute([':did' => $doctor_id, ':date' => $date]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'start_time');
}