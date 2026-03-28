<?php
// ─── AppointmentModel.php ────────────────────────────────────────────────────
// Data layer: all reads/writes go through $_SESSION['appointments'].
// In a real app, replace session calls with PDO queries.

function model_seed_appointments(): void
{
    if (isset($_SESSION['appointments'])) {
        return; // already seeded
    }

    $_SESSION['appointments'] = [
        [
            'id'       => 1,
            'time'     => '9:00 AM',
            'name'     => 'John Patient',
            'reason'   => 'General check-up',
            'duration' => '30 min',
            'status'   => 'Confirmed',
            'comments' => [
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Patient reported mild fatigue. Vitals normal.',
                    'date'   => '2025-03-25 09:15',
                ],
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Recommended blood panel. Follow-up in 2 weeks.',
                    'date'   => '2025-03-25 09:28',
                ],
            ],
        ],
        [
            'id'       => 2,
            'time'     => '9:30 AM',
            'name'     => 'Emily Johnson',
            'reason'   => 'Follow-up consultation',
            'duration' => '15 min',
            'status'   => 'Pending',
            'comments' => [
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Reviewed previous lab results with patient.',
                    'date'   => '2025-03-20 10:05',
                ],
            ],
        ],
        [
            'id'       => 3,
            'time'     => '10:30 AM',
            'name'     => 'Mark Rivera',
            'reason'   => 'New patient consultation',
            'duration' => '30 min',
            'status'   => 'Pending',
            'comments' => [],
        ],
        [
            'id'       => 4,
            'time'     => '11:30 AM',
            'name'     => 'Aisha Patel',
            'reason'   => 'Prescription renewal',
            'duration' => '15 min',
            'status'   => 'Done',
            'comments' => [
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Prescription renewed for 3 months. No new concerns.',
                    'date'   => '2025-03-26 11:45',
                ],
            ],
        ],
    ];
}

/**
 * Return all appointments.
 *
 * @return array<int, array>
 */
function model_get_all_appointments(): array
{
    model_seed_appointments();
    return $_SESSION['appointments'];
}

/**
 * Find a single appointment by ID.
 *
 * @return array|null
 */
function model_find_appointment(int $id): ?array
{
    model_seed_appointments();
    foreach ($_SESSION['appointments'] as $appt) {
        if ($appt['id'] === $id) {
            return $appt;
        }
    }
    return null;
}

/**
 * Append a comment to an appointment.
 *
 * @return array|null  The new comment on success, null on failure.
 */
function model_add_comment(int $id, string $commentText): ?array
{
    model_seed_appointments();

    foreach ($_SESSION['appointments'] as &$appt) {
        if ($appt['id'] === $id) {
            $newComment = [
                'author' => 'Dr. Lim',
                'text'   => $commentText,
                'date'   => date('Y-m-d H:i'),
            ];
            $appt['comments'][] = $newComment;
            return $newComment;
        }
    }
    unset($appt);

    return null;
}

/**
 * Update the status of an appointment.
 *
 * @param  string $status  One of: Confirmed | Rejected | Done
 * @return bool
 */
function model_update_appointment_status(int $id, string $status): bool
{
    $allowed = ['Confirmed', 'Rejected', 'Done'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }

    model_seed_appointments();

    foreach ($_SESSION['appointments'] as &$appt) {
        if ($appt['id'] === $id) {
            $appt['status'] = $status;
            return true;
        }
    }
    unset($appt);

    return false;
}
