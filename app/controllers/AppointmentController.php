<?php
// ─── AppointmentController.php ───────────────────────────────────────────────
// Controller layer: validates input, calls model functions, returns
// structured responses consumed by the router (index.php) or the view.

class AppointmentController
{
    /**
     * Return every appointment for the dashboard view.
     *
     * @return array<int, array>
     */
    public function getAll(): array
    {
        return model_get_all_appointments();
    }

    /**
     * Handle AJAX: add a clinical note to an appointment.
     *
     * @return array  JSON-serialisable response.
     */
    public function saveComment(int $appointmentId, string $comment): array
    {
        // Validate
        if ($appointmentId <= 0) {
            return ['success' => false, 'message' => 'Invalid appointment ID.'];
        }

        if ($comment === '') {
            return ['success' => false, 'message' => 'Comment cannot be empty.'];
        }

        if (strlen($comment) > 2000) {
            return ['success' => false, 'message' => 'Comment is too long (max 2000 characters).'];
        }

        if (model_find_appointment($appointmentId) === null) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }

        // Persist
        $newComment = model_add_comment($appointmentId, $comment);

        if ($newComment === null) {
            return ['success' => false, 'message' => 'Could not save comment.'];
        }

        return ['success' => true, 'comment' => $newComment];
    }

    /**
     * Handle AJAX: change the status of an appointment.
     *
     * @return array  JSON-serialisable response.
     */
    public function updateStatus(int $appointmentId, string $status): array
    {
        $allowed = ['Confirmed', 'Rejected', 'Done'];

        if ($appointmentId <= 0) {
            return ['success' => false, 'message' => 'Invalid appointment ID.'];
        }

        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid status value.'];
        }

        if (model_find_appointment($appointmentId) === null) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }

        $updated = model_update_appointment_status($appointmentId, $status);

        if (!$updated) {
            return ['success' => false, 'message' => 'Could not update status.'];
        }

        return ['success' => true, 'status' => $status];
    }
}
