<?php
$title = 'Booking Confirmed';

$extra_styles = <<<CSS
<style>
.confirm-wrap {
    min-height: calc(100vh - 60px - 64px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
}

.confirm-card {
    width: 100%;
    max-width: 420px;
    text-align: center;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px 32px 28px;
}

.confirm-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both;
}

.confirm-check-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(34,197,94,.15);
    border: 2px solid #22C55E;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #22C55E;
}

.confirm-heading {
    font-size: 34px;
    font-weight: 800;
    letter-spacing: .05em;
    color: #22C55E;
    margin-bottom: 8px;
    animation: fadeUp .4s .08s ease both;
}

.confirm-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 28px;
    line-height: 1.5;
    animation: fadeUp .4s .14s ease both;
}

.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    text-align: left;
    margin-bottom: 20px;
    animation: fadeUp .4s .2s ease both;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 12px;
}

.summary-field-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 4px;
}

.summary-field-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.summary-field-value.status-confirmed { color: #22C55E; }
.summary-field-value.status-pending   { color: #fbbf24; }

.confirm-actions {
    display: flex;
    gap: 10px;
    animation: fadeUp .4s .26s ease both;
}

.btn-cf-primary {
    flex: 1;
    padding: 11px 16px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: background .15s;
}
.btn-cf-primary:hover { background: #3a8eef; color: #fff; }

.btn-cf-secondary {
    flex: 1;
    padding: 11px 16px;
    background: transparent;
    color: var(--muted);
    border: 1px solid var(--border2);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, color .15s;
}
.btn-cf-secondary:hover { border-color: var(--text); color: var(--text); }

@keyframes popIn {
    from { opacity: 0; transform: scale(.5); }
    to   { opacity: 1; transform: scale(1);  }
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0);    }
}

@media (max-width: 480px) {
    .confirm-actions { flex-direction: column; }
}
</style>
CSS;

ob_start();

$appt       = $appointment ?? [];
$doctorName = htmlspecialchars($appt['doctor_name']      ?? '—');
$specialty  = htmlspecialchars($appt['specialty']        ?? '—');
$apptDate   = htmlspecialchars($appt['date']             ?? '—');
$apptTime   = htmlspecialchars($appt['time']             ?? '—');
$fee        = htmlspecialchars($appt['fee']              ?? '—');
$refNumber  = htmlspecialchars($appt['reference_number'] ?? '—');
$status     = htmlspecialchars($appt['status']           ?? 'Pending');

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $appt['date'] ?? '')) {
    $apptDate = date('F j, Y', strtotime($appt['date']));
}

$statusClass = strtolower($status) === 'confirmed' ? 'status-confirmed' : 'status-pending';
?>

<div class="confirm-wrap">
    <div class="confirm-card">

        <div class="confirm-icon-wrap">
            <div class="confirm-check-circle">
                <i class="fa fa-check"></i>
            </div>
        </div>

        <div class="confirm-heading">CONFIRMED</div>
        <p class="confirm-subtitle">Your appointment has been booked.</p>

        <div class="summary-card">
            <div class="summary-grid">

                <div>
                    <div class="summary-field-label">Doctor</div>
                    <div class="summary-field-value"><?= $doctorName ?></div>
                </div>

                <div>
                    <div class="summary-field-label">Specialization</div>
                    <div class="summary-field-value"><?= $specialty ?></div>
                </div>

                <div>
                    <div class="summary-field-label">Date &amp; Time</div>
                    <div class="summary-field-value"><?= $apptDate ?> &middot; <?= $apptTime ?></div>
                </div>

                <div>
                    <div class="summary-field-label">Status</div>
                    <div class="summary-field-value <?= $statusClass ?>"><?= $status ?></div>
                </div>

                <div>
                    <div class="summary-field-label">Consultation fee</div>
                    <div class="summary-field-value">NPR <?= $fee ?></div>
                </div>

                <div>
                    <div class="summary-field-label">Reference</div>
                    <div class="summary-field-value"><?= $refNumber ?></div>
                </div>

            </div>
        </div>

        <div class="confirm-actions">
            <a href="/dashboard"  class="btn-cf-primary">View my appointments</a>

        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
(function () {
    if (window.history && window.history.replaceState) {
        window.history.replaceState({ page: 'booking-confirm' }, '', window.location.href);
    }
})();
</script>
JS;

include __DIR__ . '/../layouts/app.php';