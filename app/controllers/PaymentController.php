<?php

// eSewa v2 Configuration

define('ESEWA_SECRET_KEY',  '8gBm/:&EnhH.1/q');
define('ESEWA_PRODUCT_CODE','EPAYTEST');
define('ESEWA_GATEWAY_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
// Production: https://epay.esewa.com.np/api/epay/main/v2/form

define('APPOINTMENT_AMOUNT', 500.00);
define('APPOINTMENT_TAX',      0.00);

// Helpers

function esewa_signature(string $total_amount, string $transaction_uuid): string
{
    $message = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code=" . ESEWA_PRODUCT_CODE;
    return base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
}

function esewa_verify_response(array $decoded): bool
{
    $signed_fields = explode(',', $decoded['signed_field_names'] ?? '');
    $parts = [];
    foreach ($signed_fields as $field) {
        $field   = trim($field);
        $parts[] = "{$field}=" . ($decoded[$field] ?? '');
    }
    $message  = implode(',', $parts);
    $expected = base64_encode(hash_hmac('sha256', $message, ESEWA_SECRET_KEY, true));
    return hash_equals($expected, $decoded['signature'] ?? '');
}

// Page: GET /payment/success

function payment_success_page()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user = require_auth();

    $raw     = $_GET['data'] ?? '';
    $decoded = [];
    if ($raw !== '') {
        $decoded = json_decode(base64_decode($raw), true) ?? [];
    }

    if (empty($decoded)) {
        redirect('/dashboard');
    }

    if (!esewa_verify_response($decoded)) {
        render_payment_error('Payment signature verification failed.');
    }

    if (($decoded['status'] ?? '') !== 'COMPLETE') {
        render_payment_error('Payment not completed. Status: ' . htmlspecialchars($decoded['status'] ?? 'unknown'));
    }

    $transaction_uuid = $decoded['transaction_uuid'] ?? '';
    $esewa_ref_id     = $decoded['transaction_code']  ?? '';

    $pdo = db_connect();

    // Guard against duplicate callbacks
    $dup = $pdo->prepare("SELECT id FROM transactions WHERE transaction_id = ? AND status = 'paid'");
    $dup->execute([$transaction_uuid]);
    if ($dup->fetch()) {
        redirect('/dashboard');
    }

    // Try session first, then fall back to the DB-persisted pending_booking.
    // The session is frequently lost after the browser round-trips through eSewa's
    // payment page because the cross-domain redirect causes the session cookie to
    // be dropped or a new session to be started. The DB row is the reliable source.
    $pending = $_SESSION['pending_booking'] ?? null;
    if (!$pending || ($pending['transaction_uuid'] ?? '') !== $transaction_uuid) {
        $pb = $pdo->prepare("SELECT * FROM pending_bookings WHERE transaction_uuid = ?");
        $pb->execute([$transaction_uuid]);
        $row = $pb->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            render_payment_error('Session expired or booking data missing. Please book again.');
        }

        $pending = [
            'transaction_uuid' => $row['transaction_uuid'],
            'patient_id'       => $row['patient_id'],
            'doctor_id'        => $row['doctor_id'],
            'date'             => $row['appointment_date'],
            'start_time'       => $row['start_time'],
            'end_time'         => $row['end_time'],
            'reason'           => $row['reason'],
        ];
    }

    // Clean up both the session entry and the DB row now that we have what we need
    unset($_SESSION['pending_booking']);
    $pdo->prepare("DELETE FROM pending_bookings WHERE transaction_uuid = ?")
        ->execute([$transaction_uuid]);

    // Generate unique reference number
    do {
        $ref = 'DBK-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $chk = $pdo->prepare("SELECT id FROM appointments WHERE reference_number = :r");
        $chk->execute([':r' => $ref]);
    } while ($chk->fetch());

    // Insert the confirmed appointment only after payment is verified
    $pdo->prepare("
        INSERT INTO appointments
            (patient_id, doctor_id, appointment_date, start_time, end_time, reference_number, status, visit_reason)
        VALUES
            (:pid, :did, :date, :start, :end, :ref, 'Confirmed', :reason)
    ")->execute([
        ':pid'    => $pending['patient_id'],
        ':did'    => $pending['doctor_id'],
        ':date'   => $pending['date'],
        ':start'  => $pending['start_time'],
        ':end'    => $pending['end_time'],
        ':ref'    => $ref,
        ':reason' => $pending['reason'],
    ]);
    $appt_id = (int) $pdo->lastInsertId();

    // Record the paid transaction
    $amount = number_format(500.00, 2, '.', '');
    $pdo->prepare("
        INSERT INTO transactions
            (appointment_id, patient_id, transaction_id, amount, tax_amount, total_amount, status, payment_method, esewa_ref_id, paid_at)
        VALUES
            (:aid, :pid, :txn, :amt, 0.00, :amt, 'paid', 'esewa', :ref_id, NOW())
    ")->execute([
        ':aid'    => $appt_id,
        ':pid'    => $pending['patient_id'],
        ':txn'    => $transaction_uuid,
        ':amt'    => $amount,
        ':ref_id' => $esewa_ref_id,
    ]);

    // Load doctor info for confirmation card
    $docStmt = $pdo->prepare("
        SELECT u.name AS doctor_name, d.specialty
        FROM doctors d JOIN users u ON u.id = d.user_id
        WHERE d.id = ?
    ");
    $docStmt->execute([$pending['doctor_id']]);
    $doc = $docStmt->fetch(PDO::FETCH_ASSOC);

    $h       = (int) substr($pending['start_time'], 0, 2);
    $hEnd    = (int) substr($pending['end_time'],   0, 2);
    $ampm    = $h    >= 12 ? 'PM' : 'AM';
    $ampmEnd = $hEnd >= 12 ? 'PM' : 'AM';
    $h12     = $h    % 12 ?: 12;
    $h12End  = $hEnd % 12 ?: 12;

    $appointment = [
        'appointment_id'   => $appt_id,
        'reference_number' => $ref,
        'doctor_name'      => $doc['doctor_name'] ?? 'Unknown',
        'specialty'        => $doc['specialty']   ?? '',
        'date'             => $pending['date'],
        'time'             => "{$h12}:00 {$ampm} - {$h12End}:00 {$ampmEnd}",
        'status'           => 'Confirmed',
        'amount_paid'      => $amount,
        'transaction_id'   => $esewa_ref_id,
    ];

    render('patient/booking-confirm', compact('user', 'appointment'));
}

// Page: GET /payment/failure

function payment_failure_page()
{
    $user = require_auth();

    $raw     = $_GET['data'] ?? '';
    $decoded = [];
    if ($raw !== '') {
        $decoded = json_decode(base64_decode($raw), true) ?? [];
    }

    $transaction_uuid = $decoded['transaction_uuid'] ?? '';

    if ($transaction_uuid) {
        $pdo = db_connect();

        // Mark the transaction as failed
        $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE transaction_id = ? AND patient_id = ?")
            ->execute([$transaction_uuid, $user['id']]);

        // Clean up the pending_booking DB row on failure too
        $pdo->prepare("DELETE FROM pending_bookings WHERE transaction_uuid = ?")
            ->execute([$transaction_uuid]);
    }

    // Leave the appointment as 'Pending' — patient can retry payment from the dashboard
    render_payment_error('Payment was cancelled or failed. Your appointment slot is still held. Please try again from your dashboard.');
}

// Shared error renderer

function render_payment_error(string $message): void
{
    http_response_code(402);
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment Error</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f6fb;font-family:sans-serif;}
  .card{background:#fff;border-radius:16px;padding:40px 32px;max-width:380px;width:100%;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.08);}
  h2{color:#f59e0b;margin:0 0 12px;}
  p{color:#555;font-size:14px;line-height:1.6;margin:0 0 24px;}
  a{display:inline-block;padding:11px 28px;background:#3b9ddd;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;}
</style>
</head>
<body>
  <div class="card">
    <div style="font-size:48px;margin-bottom:16px;">⚠️</div>
    <h2>Payment Error</h2>
    <p>{$message}</p>
    <a href="/dashboard">Go to Dashboard</a>
  </div>
</body>
</html>
HTML;
    exit;
}