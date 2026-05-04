<?php
$title = 'Booking Confirmed';

$extra_styles = <<<CSS
<style>
/* ── Screen styles ── */
.confirm-wrap {
    min-height: calc(100vh - 60px - 64px);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 48px 24px 64px;
}
.invoice-shell { width: 100%; max-width: 620px; }
.invoice-success-bar {
    display: flex; align-items: center; gap: 12px;
    background: rgba(34,197,94,.10); border: 1px solid rgba(34,197,94,.35);
    border-radius: 12px; padding: 14px 18px; margin-bottom: 24px;
    animation: fadeUp .4s ease both;
}
.invoice-success-bar .chk {
    width:36px;height:36px;border-radius:50%;background:#22C55E;
    display:flex;align-items:center;justify-content:center;font-size:17px;color:#fff;flex-shrink:0;
}
.invoice-success-bar h2 { font-size:16px;font-weight:700;color:#22C55E;margin:0 0 2px; }
.invoice-success-bar p  { font-size:13px;color:var(--muted);margin:0; }
.invoice-card { background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;animation:fadeUp .4s .08s ease both; }
.invoice-header { background:var(--blue);padding:22px 28px 18px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px; }
.invoice-brand { font-size:20px;font-weight:800;color:#fff;letter-spacing:.02em; }
.invoice-brand span { opacity:.75; }
.invoice-brand-sub { font-size:10px;color:rgba(255,255,255,.65);margin-top:2px;letter-spacing:.04em;text-transform:uppercase; }
.invoice-label-block { text-align:right; }
.invoice-label-block .inv-title { font-size:11px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.08em; }
.invoice-label-block .inv-ref   { font-size:16px;font-weight:800;color:#fff;font-family:monospace;margin-top:2px; }
.invoice-label-block .inv-date  { font-size:10px;color:rgba(255,255,255,.6);margin-top:2px; }
.invoice-body { padding:20px 28px 16px; }
.inv-section-title { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--hint);margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid var(--border); }
.inv-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px 24px;margin-bottom:18px; }
.inv-field-label { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--hint);margin-bottom:2px; }
.inv-field-value { font-size:13px;font-weight:600;color:var(--text);line-height:1.3; }
.inv-field-value.green { color:#22C55E; }
.inv-table { width:100%;border-collapse:collapse;margin-bottom:14px; }
.inv-table thead th { font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--hint);padding:0 0 6px;border-bottom:1px solid var(--border);text-align:left; }
.inv-table thead th:last-child { text-align:right; }
.inv-table tbody td { padding:8px 0 3px;font-size:13px;color:var(--text);vertical-align:top; }
.inv-table tbody td:last-child { text-align:right;font-weight:600; }
.inv-table tfoot td { padding:8px 0 0;border-top:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text); }
.inv-table tfoot td:last-child { text-align:right;color:#22C55E;font-size:15px; }
.inv-payment-row { display:flex;align-items:center;justify-content:space-between;background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.25);border-radius:8px;padding:10px 14px;margin-bottom:0; }
.inv-payment-row .pay-label  { font-size:11px;font-weight:600;color:var(--muted); }
.inv-payment-row .pay-method { font-size:12px;font-weight:700;color:#22C55E; }
.inv-payment-row .pay-txn    { font-size:9px;color:var(--hint);font-family:monospace;margin-top:2px; }
.invoice-footer { border-top:1px solid var(--border);padding:12px 28px;display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--bg); }
.invoice-footer .note { font-size:10px;color:var(--hint);line-height:1.5;max-width:340px; }
.invoice-actions { display:flex;gap:10px;margin-top:20px;animation:fadeUp .4s .18s ease both; }
.btn-inv-primary { flex:1;padding:12px 16px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;text-align:center;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:7px; }
.btn-inv-primary:hover { background:#3a8eef;color:#fff; }
.btn-inv-secondary { padding:12px 20px;background:transparent;color:var(--muted);border:1px solid var(--border2);border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:border-color .15s,color .15s;display:flex;align-items:center;gap:7px;text-decoration:none; }
.btn-inv-secondary:hover { border-color:var(--text);color:var(--text); }
@keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@media(max-width:540px){
    .invoice-header{flex-direction:column}
    .invoice-label-block{text-align:left}
    .invoice-body{padding:16px}
    .invoice-footer{flex-direction:column;align-items:flex-start}
    .inv-grid{grid-template-columns:1fr}
    .invoice-actions{flex-direction:column}
}

/* ════════════════════════════════════════════════════
   PRINT / PDF  —  strict black & white, one page
   Strategy: hide the entire page, then un-hide only
   the invoice card using a fixed-position overlay.
   This avoids fighting the layout's wrapper divs.
   ════════════════════════════════════════════════════ */
@media print {
    /* 1. Silence absolutely everything */
    * {
        visibility: hidden !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 2. Reset page chrome */
    @page { margin: 14mm 12mm; }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        width: 100% !important;
    }

    /* 3. Make the invoice card and ALL its descendants visible */
    #invoiceCard,
    #invoiceCard * {
        visibility: visible !important;
    }

    /* 4. Pull it out of the layout flow and fill the page */
    #invoiceCard {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
        overflow: visible !important;
        page-break-inside: avoid;
    }

    /* 5. Header — solid black bar */
    #invoiceCard .invoice-header {
        background: #111 !important;
        padding: 14px 24px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 16px !important;
    }
    #invoiceCard .invoice-brand,
    #invoiceCard .invoice-brand span,
    #invoiceCard .invoice-brand-sub,
    #invoiceCard .inv-title,
    #invoiceCard .inv-ref,
    #invoiceCard .inv-date {
        color: #fff !important;
        opacity: 1 !important;
    }

    /* 6. Body */
    #invoiceCard .invoice-body { padding: 14px 24px 10px !important; }

    /* 7. All text → black */
    #invoiceCard .inv-field-value,
    #invoiceCard .inv-table tbody td,
    #invoiceCard .inv-table tfoot td { color: #111 !important; }

    #invoiceCard .inv-field-label,
    #invoiceCard .inv-section-title,
    #invoiceCard .inv-table thead th { color: #444 !important; }

    /* Overide green status/total with black */
    #invoiceCard .inv-field-value.green,
    #invoiceCard .inv-table tfoot td:last-child { color: #111 !important; font-weight: 700 !important; }

    /* 8. Borders → light grey */
    #invoiceCard .inv-section-title { border-bottom: 1px solid #bbb !important; }
    #invoiceCard .inv-table thead th { border-bottom: 1px solid #bbb !important; }
    #invoiceCard .inv-table tfoot td { border-top: 1px solid #bbb !important; }

    /* 9. Payment row — no colour tint */
    #invoiceCard .inv-payment-row {
        background: #f0f0f0 !important;
        border: 1px solid #bbb !important;
        border-radius: 6px !important;
    }
    #invoiceCard .inv-payment-row .pay-method,
    #invoiceCard .inv-payment-row > div:last-child { color: #111 !important; }
    #invoiceCard .inv-payment-row .pay-label,
    #invoiceCard .inv-payment-row .pay-txn { color: #444 !important; }

    /* 10. Footer */
    #invoiceCard .invoice-footer {
        background: #f5f5f5 !important;
        border-top: 1px solid #bbb !important;
        padding: 10px 24px !important;
    }
    #invoiceCard .invoice-footer .note,
    #invoiceCard .invoice-footer div { color: #444 !important; }
}
</style>
CSS;

ob_start();

$appt          = $appointment ?? [];
$doctorName    = htmlspecialchars($appt['doctor_name']      ?? '—');
$specialty     = htmlspecialchars($appt['specialty']        ?? '—');
$refNumber     = htmlspecialchars($appt['reference_number'] ?? '—');
$status        = htmlspecialchars($appt['status']           ?? 'Confirmed');
$amountPaid    = number_format((float)($appt['amount_paid'] ?? 500), 2);
$transactionId = htmlspecialchars($appt['transaction_id']   ?? '');
$issuedDate    = date('F j, Y');
$issuedTime    = date('g:i A');
$apptDate      = '—';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $appt['date'] ?? '')) {
    $apptDate = date('F j, Y', strtotime($appt['date']));
}
$apptTime     = htmlspecialchars($appt['time']  ?? '—');
$patientName  = htmlspecialchars($user['name']  ?? '—');
$patientEmail = htmlspecialchars($user['email'] ?? '—');
$patientPhone = htmlspecialchars($user['phone'] ?? '—');
?>

<div class="confirm-wrap">
<div class="invoice-shell">

    <div class="invoice-success-bar">
        <div class="chk"><i class="fa fa-check"></i></div>
        <div>
            <h2>Booking Confirmed &amp; Payment Received</h2>
            <p>Your appointment is confirmed. Print this page or save as PDF for your records.</p>
        </div>
    </div>

    <!-- id="invoiceCard" is the print target — do not rename -->
    <div class="invoice-card" id="invoiceCard">

        <div class="invoice-header">
            <div>
                <div class="invoice-brand">Doc<span>Book</span></div>
                <div class="invoice-brand-sub">Appointment Invoice</div>
            </div>
            <div class="invoice-label-block">
                <div class="inv-title">Invoice</div>
                <div class="inv-ref"><?= $refNumber ?></div>
                <div class="inv-date">Issued <?= $issuedDate ?> &middot; <?= $issuedTime ?></div>
            </div>
        </div>

        <div class="invoice-body">

            <div class="inv-section-title">Patient Details</div>
            <div class="inv-grid" style="margin-bottom:18px;">
                <div>
                    <div class="inv-field-label">Full Name</div>
                    <div class="inv-field-value"><?= $patientName ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Email</div>
                    <div class="inv-field-value"><?= $patientEmail ?></div>
                </div>
                <?php if ($patientPhone && $patientPhone !== '—'): ?>
                <div>
                    <div class="inv-field-label">Phone</div>
                    <div class="inv-field-value"><?= $patientPhone ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="inv-section-title">Appointment Details</div>
            <div class="inv-grid" style="margin-bottom:18px;">
                <div>
                    <div class="inv-field-label">Doctor</div>
                    <div class="inv-field-value"><?= $doctorName ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Specialization</div>
                    <div class="inv-field-value"><?= $specialty ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Date</div>
                    <div class="inv-field-value"><?= $apptDate ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Time</div>
                    <div class="inv-field-value"><?= $apptTime ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Reference No.</div>
                    <div class="inv-field-value" style="font-family:monospace;"><?= $refNumber ?></div>
                </div>
                <div>
                    <div class="inv-field-label">Status</div>
                    <div class="inv-field-value green"><?= $status ?></div>
                </div>
            </div>

            <div class="inv-section-title">Payment Summary</div>
            <table class="inv-table">
                <thead>
                    <tr><th>Description</th><th>Amount</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Doctor Consultation Fee</strong><br>
                            <span style="font-size:11px;color:var(--muted);"><?= $doctorName ?> &middot; <?= $specialty ?></span>
                        </td>
                        <td>Rs <?= $amountPaid ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr><td>Total Paid</td><td>Rs <?= $amountPaid ?></td></tr>
                </tfoot>
            </table>

            <div class="inv-payment-row">
                <div>
                    <div class="pay-label">Payment Method</div>
                    <div class="pay-method"><i class="fa fa-circle-check"></i> eSewa — Payment Successful</div>
                    <?php if ($transactionId): ?>
                    <div class="pay-txn">Transaction ID: <?= $transactionId ?></div>
                    <?php endif; ?>
                </div>
                <div style="font-size:20px;font-weight:800;color:#22C55E;">Rs <?= $amountPaid ?></div>
            </div>

        </div>

        <div class="invoice-footer">
            <p class="note">
                Auto-generated by DocBook. Keep ref <strong><?= $refNumber ?></strong> for queries.
                Support: <strong>support@docbook.app</strong>
            </p>
            <div style="font-size:10px;color:var(--hint);white-space:nowrap;">
                DocBook &copy; <?= date('Y') ?>
            </div>
        </div>

    </div><!-- /#invoiceCard -->

    <div class="invoice-actions">
        <button class="btn-inv-primary" onclick="window.print()">
            <i class="fa fa-print"></i> Print / Save as PDF
        </button>
        <a href="<?= BASE_URL ?>/dashboard" class="btn-inv-secondary">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
    </div>

</div>
</div>

<?php
$content = ob_get_clean();
$extra_scripts = <<<JS
<script>
(function(){if(window.history&&window.history.replaceState){window.history.replaceState({page:'booking-confirm'},'',window.location.href);}})();
</script>
JS;
include __DIR__ . '/../../layouts/app.php';