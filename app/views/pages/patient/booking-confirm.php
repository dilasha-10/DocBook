<?php
$title = 'Booking Confirmed';

// Styling section: layout for the confirmation card and CSS3 animations
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

/* Keyframe animations for a polished "Success" feel */
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

/* Summary Grid: Displays appointment metadata */
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

/* Dynamic status coloring */
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

// Data preparation
$appt          = $appointment ?? [];
$doctorName    = htmlspecialchars($appt['doctor_name']      ?? '—');
$specialty     = htmlspecialchars($appt['specialty']        ?? '—');
$apptDate      = htmlspecialchars($appt['date']             ?? '—');
$apptTime      = htmlspecialchars($appt['time']             ?? '—');
$refNumber     = htmlspecialchars($appt['reference_number'] ?? '—');
$status        = htmlspecialchars($appt['status']           ?? 'Pending');
$amountPaid    = $appt['amount_paid']    ?? null;
$transactionId = $appt['transaction_id'] ?? null;
$alreadyPaid   = ($amountPaid !== null);

// Format date to readable string (e.g., January 1, 2024)
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
                    <div class="summary-field-label">Reference</div>
                    <div class="summary-field-value"><?= $refNumber ?></div>
                </div>

            </div>
        </div>

        <?php if ($alreadyPaid): ?>
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.3);border-radius:12px;padding:14px 18px;margin-bottom:18px;text-align:left;animation:fadeUp .4s .22s ease both;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#22C55E;margin-bottom:6px;"><i class="fa fa-shield-check"></i> Payment Verified</div>
            <div style="font-size:13px;color:var(--text);">Amount paid: <strong>Rs <?= number_format((float)$amountPaid, 2) ?></strong></div>
            <?php if ($transactionId): ?>
            <div style="font-size:11px;color:var(--muted);font-family:monospace;margin-top:2px;"><?= htmlspecialchars($transactionId) ?></div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div id="esewaBlock" style="margin-bottom:16px;animation:fadeUp .4s .22s ease both;">
            <div style="font-size:11px;color:var(--muted);margin-bottom:8px;text-align:left;">Complete payment to confirm your appointment:</div>
            <button onclick="initiateEsewa()" id="esewaBtn"
                style="width:100%;padding:12px;background:#60BB46;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                <svg width="22" height="22" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#fff"/>
                    <text x="50" y="67" text-anchor="middle" font-size="52" font-weight="900" fill="#60BB46" font-family="Arial">e</text>
                </svg>
                Pay Rs 500 with eSewa
            </button>
            <div id="esewaError" style="display:none;color:#ef4444;font-size:12px;margin-top:8px;"></div>
        </div>
        <form id="esewaForm" method="POST" style="display:none;"></form>
        <?php endif; ?>

        <div class="confirm-actions">
            <a href="<?= BASE_URL ?>/dashboard"  class="btn-cf-primary">View my appointments</a>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
// Prevent re-submission issues on page refresh
(function () {
    if (window.history && window.history.replaceState) {
        window.history.replaceState({ page: 'booking-confirm' }, '', window.location.href);
    }
})();

const APPT_ID = {$appt['appointment_id']};

async function initiateEsewa() {
    if (!APPT_ID) { alert('Appointment ID missing. Please try booking again.'); return; }
    const btn = document.getElementById('esewaBtn');
    const errEl = document.getElementById('esewaError');
    
    btn.disabled = true;
    btn.textContent = 'Initiating payment…';
    errEl.style.display = 'none';

    try {
        const res = await fetch(BASE_URL + '/api/payment/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ appointment_id: APPT_ID }),
        });
        const data = await res.json();
        
        if (!data.success) throw new Error(data.message || 'Failed to initiate payment.');

        // Populate the hidden form and submit to eSewa
        const form = document.getElementById('esewaForm');
        form.action = data.esewa_url;
        form.innerHTML = '';
        
        for (const [k, v] of Object.entries(data.fields)) {
            const inp = document.createElement('input');
            inp.type = 'hidden'; 
            inp.name = k; 
            inp.value = v;
            form.appendChild(inp);
        }
        form.submit();
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<svg width="22" height="22" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="50" fill="#fff"/><text x="50" y="67" text-anchor="middle" font-size="52" font-weight="900" fill="#60BB46" font-family="Arial">e</text></svg> Pay Rs 500 with eSewa';
    }
}
</script>
JS;

include __DIR__ . '/../../layouts/app.php';