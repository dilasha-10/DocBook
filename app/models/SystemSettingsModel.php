<?php
// app/models/SystemSettingsModel.php
// D3-05 – Manage Appointment Slots and System Availability


/**
 * Return all settings as an associative array  [ key => value ].
 * Values are cast to their native PHP type based on the 'type' column.
 */
function get_all_settings(): array
{
    $pdo  = db_connect();
    $rows = $pdo->query("SELECT setting_key, value, type FROM system_settings ORDER BY id")
                ->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        $out[$r['setting_key']] = cast_setting($r['value'], $r['type']);
    }
    return $out;
}

/**
 * Return all settings rows with full metadata (for the admin UI).
 */
function get_all_settings_full(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT id, setting_key, value, label, description, type, updated_at
         FROM system_settings
         ORDER BY id"
    )->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch one setting value; returns $default if key not found.
 */
function get_setting(string $key, mixed $default = null): mixed
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT value, type FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return $default;
    return cast_setting($row['value'], $row['type']);
}

/**
 * Persist one setting.  Returns true on success.
 * Validates value shape against 'type'; throws InvalidArgumentException on bad input.
 */
function save_setting(string $key, string $raw_value): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("SELECT type FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new InvalidArgumentException("Unknown setting key: $key");
    }

    validate_setting($key, $raw_value, $row['type']);

    $upd = $pdo->prepare(
        "UPDATE system_settings SET value = ? WHERE setting_key = ?"
    );
    $upd->execute([$raw_value, $key]);
    return $upd->rowCount() > 0;
}

/**
 * Batch-save multiple settings at once.
 * $data = [ key => raw_value_string, ... ]
 * Wrapped in a transaction; all-or-nothing.
 */
function save_settings_batch(array $data): void
{
    $pdo = db_connect();

    // Pre-load types for validation
    $keys        = array_keys($data);
    $in          = implode(',', array_fill(0, count($keys), '?'));
    $typeStmt    = $pdo->prepare(
        "SELECT setting_key, type FROM system_settings WHERE setting_key IN ($in)"
    );
    $typeStmt->execute($keys);
    $typeMap = [];
    foreach ($typeStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $typeMap[$r['setting_key']] = $r['type'];
    }

    foreach ($data as $k => $v) {
        if (!isset($typeMap[$k])) {
            throw new InvalidArgumentException("Unknown setting key: $k");
        }
        validate_setting($k, $v, $typeMap[$k]);
    }

    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare(
            "UPDATE system_settings SET value = ? WHERE setting_key = ?"
        );
        foreach ($data as $k => $v) {
            $upd->execute([$v, $k]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Holidays

/**
 * All holidays, ordered by date ascending.
 */
function get_holidays(): array
{
    $pdo = db_connect();
    return $pdo->query(
        "SELECT id, holiday_date, label, created_at FROM system_holidays ORDER BY holiday_date"
    )->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * All upcoming holidays (today or later) as a plain array of 'YYYY-MM-DD' strings.
 * Used by the booking engine to block dates.
 */
function get_holiday_dates_upcoming(): array
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "SELECT holiday_date FROM system_holidays WHERE holiday_date >= CURDATE() ORDER BY holiday_date"
    );
    $stmt->execute();
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'holiday_date');
}

/**
 * Check whether a given date string ('YYYY-MM-DD') is a holiday.
 */
function is_holiday(string $date): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM system_holidays WHERE holiday_date = ?"
    );
    $stmt->execute([$date]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Add a holiday.  Returns inserted row id.
 * Duplicate dates are silently ignored (returns 0).
 */
function add_holiday(string $date, string $label): int
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new InvalidArgumentException("Invalid date format: $date");
    }
    $label = trim($label);
    if ($label === '') {
        throw new InvalidArgumentException("Holiday label must not be empty.");
    }

    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO system_holidays (holiday_date, label) VALUES (?, ?)"
    );
    $stmt->execute([$date, $label]);
    return (int)$pdo->lastInsertId();
}

/**
 * Remove a holiday by id.  Returns true if a row was deleted.
 */
function delete_holiday(int $id): bool
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM system_holidays WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

// Internal helpers

function cast_setting(string $raw, string $type): mixed
{
    return match ($type) {
        'integer' => (int) $raw,
        'boolean' => (bool)(int) $raw,
        'json'    => json_decode($raw, true),
        default   => $raw, // 'time', plain string
    };
}

function validate_setting(string $key, string $value, string $type): void
{
    switch ($type) {
        case 'time':
            if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
                throw new InvalidArgumentException("Setting '$key' must be HH:MM, got: $value");
            }
            break;
        case 'integer':
            if (!ctype_digit($value) || (int)$value < 0) {
                throw new InvalidArgumentException("Setting '$key' must be a non-negative integer.");
            }
            break;
        case 'boolean':
            if (!in_array($value, ['0', '1'], true)) {
                throw new InvalidArgumentException("Setting '$key' must be 0 or 1.");
            }
            break;
        case 'json':
            json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Setting '$key' must be valid JSON.");
            }
            break;
    }
}