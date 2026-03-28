<?php
// ─── AppointmentModel.php ────────────────────────────────────────────────────
// Data layer: all reads/writes go through $_SESSION['appointments'] and
// $_SESSION['patients'].  In a real app, replace session calls with PDO queries.

// ─── Seed: Patients ──────────────────────────────────────────────────────────
function model_seed_patients(): void
{
    if (isset($_SESSION['patients'])) {
        return;
    }

    $_SESSION['patients'] = [
        [
            'id'         => 1,
            'name'       => 'John Patient',
            'email'      => 'john.patient@email.com',
            'phone'      => '555-0101',
            'dob'        => '1985-06-15',
            'blood_type' => 'O+',
            'address'    => '123 Main St, Springfield',
        ],
        [
            'id'         => 2,
            'name'       => 'Emily Johnson',
            'email'      => 'emily.johnson@email.com',
            'phone'      => '555-0102',
            'dob'        => '1992-11-03',
            'blood_type' => 'A-',
            'address'    => '456 Oak Ave, Shelbyville',
        ],
        [
            'id'         => 3,
            'name'       => 'Mark Rivera',
            'email'      => 'mark.rivera@email.com',
            'phone'      => '555-0103',
            'dob'        => '1978-02-20',
            'blood_type' => 'B+',
            'address'    => '789 Pine Rd, Capital City',
        ],
        [
            'id'         => 4,
            'name'       => 'Aisha Patel',
            'email'      => 'aisha.patel@email.com',
            'phone'      => '555-0104',
            'dob'        => '1990-09-12',
            'blood_type' => 'AB+',
            'address'    => '321 Elm Blvd, Ogdenville',
        ],
        [
            'id'         => 5,
            'name'       => 'Carlos Mendez',
            'email'      => 'carlos.mendez@email.com',
            'phone'      => '555-0105',
            'dob'        => '1988-04-07',
            'blood_type' => 'O-',
            'address'    => '654 Birch Ln, North Haverbrook',
        ],
    ];
}

// ─── Seed: Appointments ──────────────────────────────────────────────────────
function model_seed_appointments(): void
{
    if (isset($_SESSION['appointments'])) {
        return;
    }

    $_SESSION['appointments'] = [
        // ── Doctor 1 (Dr. Lim) appointments ──
        [
            'id'         => 1,
            'doctor_id'  => 1,
            'patient_id' => 1,
            'date'       => '2025-03-26',
            'time'       => '9:00 AM',
            'name'       => 'John Patient',
            'reason'     => 'General check-up',
            'duration'   => '30 min',
            'status'     => 'Confirmed',
            'comments'   => [
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
            'id'         => 2,
            'doctor_id'  => 1,
            'patient_id' => 2,
            'date'       => '2025-03-26',
            'time'       => '9:30 AM',
            'name'       => 'Emily Johnson',
            'reason'     => 'Follow-up consultation',
            'duration'   => '15 min',
            'status'     => 'Pending',
            'comments'   => [
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Reviewed previous lab results with patient.',
                    'date'   => '2025-03-20 10:05',
                ],
            ],
        ],
        [
            'id'         => 3,
            'doctor_id'  => 1,
            'patient_id' => 3,
            'date'       => '2025-03-26',
            'time'       => '10:30 AM',
            'name'       => 'Mark Rivera',
            'reason'     => 'New patient consultation',
            'duration'   => '30 min',
            'status'     => 'Pending',
            'comments'   => [],
        ],
        [
            'id'         => 4,
            'doctor_id'  => 1,
            'patient_id' => 4,
            'date'       => '2025-03-26',
            'time'       => '11:30 AM',
            'name'       => 'Aisha Patel',
            'reason'     => 'Prescription renewal',
            'duration'   => '15 min',
            'status'     => 'Done',
            'comments'   => [
                [
                    'author' => 'Dr. Lim',
                    'text'   => 'Prescription renewed for 3 months. No new concerns.',
                    'date'   => '2025-03-26 11:45',
                ],
            ],
        ],
        // ── Doctor 1 – different date ──
        [
            'id'         => 5,
            'doctor_id'  => 1,
            'patient_id' => 1,
            'date'       => '2025-03-27',
            'time'       => '2:00 PM',
            'name'       => 'John Patient',
            'reason'     => 'Blood panel follow-up',
            'duration'   => '20 min',
            'status'     => 'Pending',
            'comments'   => [],
        ],
        // ── Doctor 2 (Dr. Chen) appointments (should NOT show for Dr. Lim) ──
        [
            'id'         => 6,
            'doctor_id'  => 2,
            'patient_id' => 5,
            'date'       => '2025-03-26',
            'time'       => '10:00 AM',
            'name'       => 'Carlos Mendez',
            'reason'     => 'Annual physical',
            'duration'   => '45 min',
            'status'     => 'Confirmed',
            'comments'   => [
                [
                    'author' => 'Dr. Chen',
                    'text'   => 'All vitals within normal range.',
                    'date'   => '2025-03-26 10:30',
                ],
            ],
        ],
    ];
}

// ─── Original CRUD functions (unchanged interface) ───────────────────────────

/**
 * Return all appointments.
 */
function model_get_all_appointments(): array
{
    model_seed_appointments();
    model_seed_patients();
    return $_SESSION['appointments'];
}

/**
 * Find a single appointment by ID.
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

// ─── NEW: Doctor-scoped query functions ──────────────────────────────────────

/**
 * Get appointments for a specific doctor on a given date.
 * Each result includes the patient name, time, reason, status, and date.
 */
function model_get_doctor_appointments_by_date(int $doctorId, string $date): array
{
    model_seed_appointments();
    model_seed_patients();

    $results = [];
    foreach ($_SESSION['appointments'] as $appt) {
        if ($appt['doctor_id'] === $doctorId && $appt['date'] === $date) {
            $results[] = [
                'id'           => $appt['id'],
                'patient_id'   => $appt['patient_id'],
                'patient_name' => $appt['name'],
                'time'         => $appt['time'],
                'reason'       => $appt['reason'],
                'duration'     => $appt['duration'],
                'status'       => $appt['status'],
                'date'         => $appt['date'],
            ];
        }
    }
    return $results;
}

/**
 * Find a single appointment by ID, but only if it belongs to the given doctor.
 */
function model_find_doctor_appointment(int $appointmentId, int $doctorId): ?array
{
    model_seed_appointments();
    foreach ($_SESSION['appointments'] as $appt) {
        if ($appt['id'] === $appointmentId && $appt['doctor_id'] === $doctorId) {
            return $appt;
        }
    }
    return null;
}

/**
 * Get a patient by ID.
 */
function model_get_patient_by_id(int $patientId): ?array
{
    model_seed_patients();
    foreach ($_SESSION['patients'] as $patient) {
        if ($patient['id'] === $patientId) {
            return $patient;
        }
    }
    return null;
}

/**
 * Get all comments a specific doctor has written on appointments with a given patient.
 */
function model_get_comments_for_doctor_patient(int $doctorId, int $patientId): array
{
    model_seed_appointments();

    $comments = [];
    foreach ($_SESSION['appointments'] as $appt) {
        if ($appt['doctor_id'] === $doctorId && $appt['patient_id'] === $patientId) {
            foreach ($appt['comments'] as $comment) {
                $comment['appointment_id'] = $appt['id'];
                $comment['appointment_reason'] = $appt['reason'];
                $comment['appointment_date'] = $appt['date'];
                $comments[] = $comment;
            }
        }
    }

    // Sort by date ascending
    usort($comments, function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    return $comments;
}

/**
 * Add a comment to an appointment, scoped to the authenticated doctor.
 * Returns the new comment or null on failure.
 */
function model_add_doctor_comment(int $appointmentId, int $doctorId, string $doctorName, string $commentText): ?array
{
    model_seed_appointments();

    foreach ($_SESSION['appointments'] as &$appt) {
        if ($appt['id'] === $appointmentId && $appt['doctor_id'] === $doctorId) {
            $newComment = [
                'author' => $doctorName,
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
