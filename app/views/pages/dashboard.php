<?php
$title = 'Dashboard';

$extra_styles = <<<CSS
<style>
.page-layout   { display:flex; min-height:calc(100vh - 60px); }
.sidebar       { width:220px; flex-shrink:0; background:var(--surface); border-right:1px solid var(--border); padding:24px 0; position:sticky; top:60px; height:calc(100vh - 60px); overflow:hidden; align-self:flex-start; }
.sidebar-label { display:block; font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
                 color:var(--hint); padding:18px 22px 6px; }
.sidebar-link  { display:flex; align-items:center; gap:10px; padding:10px 22px; font-size:14px;
                 color:var(--muted); text-decoration:none; border-left:3px solid transparent;
                 transition:all .15s; }
.sidebar-link:hover  { color:var(--text); background:rgba(255,255,255,.04); }
.sidebar-link.active { color:#818cf8; border-left-color:#818cf8; background:rgba(99,102,241,.08); font-weight:600; }
.sidebar-link i      { width:16px; text-align:center; font-size:13px; }
.page-content  { flex:1; padding:32px 36px; overflow:auto; }

.stats-grid    { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px; }
.stat-card     { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:22px 24px; }
.stat-number   { font-size:36px; font-weight:700; line-height:1; margin-bottom:6px; }
.stat-label    { font-size:13px; color:var(--muted); margin-bottom:12px; }

.section-head  { display:flex; align-items:center; justify-content:space-between;
                 padding:16px 22px; border-bottom:1px solid var(--border); }
.section-head h2 { font-size:15px; font-weight:600; }
.count-badge   { background:var(--accent); color:#fff; border-radius:999px;
                 font-size:11px; font-weight:700; padding:2px 8px; }

.appt-table         { width:100%; border-collapse:collapse; table-layout:fixed; }
.appt-table th      { text-align:left; padding:11px 22px; font-size:11px; font-weight:600;
                      letter-spacing:.06em; text-transform:uppercase; color:var(--hint); }
.appt-table td      { padding:14px 22px; font-size:14px; border-top:1px solid var(--border); vertical-align:middle; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.appt-table tr:hover td { background:rgba(255,255,255,.025); }
.appt-actions       { display:flex; gap:8px; }
/* Fixed column widths shared across both upcoming and past tables */
.appt-table col.col-doctor  { width:22%; }
.appt-table col.col-spec    { width:20%; }
.appt-table col.col-date    { width:24%; }
.appt-table col.col-status  { width:14%; }
.appt-table col.col-actions { width:20%; }
.btn-sm             { padding:5px 13px; font-size:12px; font-weight:600; border-radius:6px;
                      border:none; cursor:pointer; text-decoration:none; transition:opacity .15s; }
.btn-sm:hover       { opacity:.8; }
.btn-reschedule     { background:rgba(99,102,241,.18); color:var(--accent); }
.btn-cancel         { background:rgba(239,68,68,.15); color:#f87171; }

.badge              { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; }
.badge-confirmed    { background:rgba(16,185,129,.15); color:#34d399; }
.badge-pending      { background:rgba(245,158,11,.15);  color:#fbbf24; }
.badge-cancelled    { background:rgba(239,68,68,.15);   color:#f87171; }
.badge-completed    { background:rgba(99,102,241,.15);  color:#a5b4fc; }
.badge-rescheduled  { background:rgba(14,165,233,.15);  color:#38bdf8; }

.month-group        { margin-bottom:0; }
.month-toggle       { display:flex; align-items:center; justify-content:space-between;
                      padding:12px 22px; cursor:pointer; border-top:1px solid var(--border);
                      font-size:13px; font-weight:600; color:var(--muted);
                      user-select:none; }
.month-toggle:hover { background:rgba(255,255,255,.03); }
.month-toggle .chevron { transition:transform .2s; font-size:11px; }
.month-toggle.open .chevron { transform:rotate(180deg); }
.month-body         { overflow:hidden; }
.month-body.collapsed { display:none; }

.empty-state        { text-align:center; padding:48px 24px; color:var(--muted); }
.empty-state i      { font-size:32px; margin-bottom:12px; opacity:.4; display:block; }
.empty-state p      { margin-bottom:16px; font-size:14px; }

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr 1fr !important; gap:10px; }
  .stat-card  { padding:16px; }
  .stat-number { font-size:28px; }
  .page-content { padding:16px 12px !important; }
  .appt-table         { display:block; width:100%; }
  .appt-table thead   { display:none; }
  .appt-table tbody   { display:block; }
  .appt-table tr      { display:flex; flex-direction:column; gap:5px;
                        padding:14px 16px; border-top:1px solid var(--border); }
  .appt-table td      { display:block; padding:0; border:none; font-size:13px; }
  .appt-actions       { flex-wrap:wrap; gap:8px; padding-top:4px; }
  .btn-sm             { flex:1; text-align:center; padding:8px 12px; font-size:13px; }
  .section-head       { padding:14px 16px; }
  .month-toggle       { padding:12px 16px; }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr !important; }
}
/* ── Cancel modal ───────────────────────────────────────────────────────── */
.modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 28px 28px 24px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.modal-box h3 { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
.modal-box p  { font-size: 14px; color: var(--muted); margin-bottom: 24px; line-height: 1.5; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.modal-actions .btn-sm { padding: 8px 18px; font-size: 13px; }
</style>
CSS;

// Group past appointments by month
$grouped = [];
foreach ($past as $appt) {
    $key = isset($appt['date']) ? date('F Y', strtotime($appt['date'])) : 'Unknown';
    $grouped[$key][] = $appt;
}

// Pending count
$pending_count = $stats['pending'] ?? 0;
if (!isset($stats['pending'])) {
    foreach ($upcoming as $a) {
        if (strtolower($a['status'] ?? '') === 'pending') $pending_count++;
    }
}

ob_start();
?>
<div class="page-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <span class="sidebar-label">Menu</span>
        <a href="/dashboard" class="sidebar-link active">
            <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="/dashboard#upcoming" class="sidebar-link">
            <i class="fa fa-calendar-check"></i> Appointments
        </a>
        <a href="/dashboard#past" class="sidebar-link">
            <i class="fa fa-clock-rotate-left"></i> History
        </a>

        <span class="sidebar-label">Account</span>
        <a href="/profile" class="sidebar-link">
            <i class="fa fa-user"></i> Profile &amp; Settings
        </a>
    </aside>

    <!-- Main content -->
    <div class="page-content">

        <!-- Header row -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;">
            <div>
                <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Welcome back, <?php echo htmlspecialchars($patient_name); ?></h1>
                <p style="color:var(--muted);font-size:14px;">Here&rsquo;s a summary of your appointments</p>
            </div>
            <a href="/categories" class="btn-primary">Book appointment</a>
        </div>

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['upcoming'] ?? count($upcoming); ?></div>
                <div class="stat-label">Upcoming appointments</div>
                <span class="badge badge-confirmed" style="font-size:11px;">This month</span>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total'] ?? (count($upcoming) + count($past)); ?></div>
                <div class="stat-label">Total appointments</div>
                <span class="badge badge-completed" style="font-size:11px;">All time</span>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending confirmations</div>
                <span class="badge badge-pending" style="font-size:11px;">
                    <?php echo $pending_count > 0 ? 'Action needed' : 'All clear'; ?>
                </span>
            </div>
        </div>

        <!-- Upcoming appointments -->
        <div class="card" style="padding:0;overflow:hidden;margin-bottom:28px;" id="upcoming">
            <div class="section-head">
                <h2>Upcoming appointments</h2>
                <span class="count-badge"><?php echo count($upcoming); ?></span>
            </div>

            <?php if (count($upcoming) > 0): ?>
            <table class="appt-table">
                <colgroup>
                    <col class="col-doctor">
                    <col class="col-spec">
                    <col class="col-date">
                    <col class="col-status">
                    <col class="col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date &amp; Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming as $appt):
                        $s      = strtolower($appt['status'] ?? 'pending');
                        $date   = isset($appt['date']) ? date('M j, Y', strtotime($appt['date'])) : '—';
                        $time   = isset($appt['time']) ? date('g:i A', strtotime($appt['time'])) : '—';
                        $spec   = $appt['specialty'] ?? $appt['category'] ?? '—';
                        $canAct = !in_array($s, ['cancelled']);
                    ?>
                    <tr>
                        <td style="font-weight:500;"><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                        <td style="color:var(--muted);"><?php echo htmlspecialchars($spec); ?></td>
                        <td style="color:var(--muted);"><?php echo $date; ?> &middot; <?php echo $time; ?></td>
                        <td><span class="badge badge-<?php echo $s; ?>"><?php echo ucfirst($s); ?></span></td>
                        <td>
                            <?php if ($canAct): ?>
                            <div class="appt-actions">
                                <a href="/appointments/<?php echo $appt['id'] ?? ''; ?>/reschedule"
                                   class="btn-sm btn-reschedule">Reschedule</a>
                                <button onclick="cancelAppt(<?php echo $appt['id'] ?? 0; ?>)"
                                        class="btn-sm btn-cancel">Cancel</button>
                            </div>
                            <?php else: ?>
                            <span style="font-size:12px;color:var(--hint);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-calendar-xmark"></i>
                <p>No upcoming appointments.</p>
                <a href="/categories" class="btn-primary">Book now</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Past appointments grouped by month -->
        <div class="card" style="padding:0;overflow:hidden;" id="past">
            <div class="section-head">
                <h2>Past appointments</h2>
                <span class="count-badge" style="background:var(--border);color:var(--muted);"><?php echo count($past); ?></span>
            </div>

            <?php if (count($past) > 0): ?>

            <?php foreach ($grouped as $month => $appts): ?>
            <div class="month-group">
                <div class="month-toggle open" onclick="toggleMonth(this)">
                    <span><?php echo htmlspecialchars($month); ?> <span style="font-weight:400;opacity:.6;">(<?php echo count($appts); ?>)</span></span>
                    <i class="fa fa-chevron-down chevron"></i>
                </div>
                <div class="month-body">
                    <table class="appt-table">
                        <colgroup>
                            <col class="col-doctor">
                            <col class="col-spec">
                            <col class="col-date">
                            <col class="col-status">
                            <col class="col-actions">
                        </colgroup>
                        <tbody>
                            <?php foreach ($appts as $appt):
                                $s    = strtolower($appt['status'] ?? 'completed');
                                $date = isset($appt['date']) ? date('M j, Y', strtotime($appt['date'])) : '—';
                                $time = isset($appt['time']) ? date('g:i A', strtotime($appt['time'])) : '—';
                                $spec = $appt['specialty'] ?? $appt['category'] ?? '—';
                            ?>
                            <tr>
                                <td style="font-weight:500;"><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                <td style="color:var(--muted);"><?php echo htmlspecialchars($spec); ?></td>
                                <td style="color:var(--muted);"><?php echo $date; ?> &middot; <?php echo $time; ?></td>
                                <td><span class="badge badge-<?php echo $s; ?>"><?php echo ucfirst($s); ?></span></td>
                                <td>
                                    <a href="/categories" class="btn-sm btn-reschedule">Book again</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-clock-rotate-left"></i>
                <p>No past appointments yet.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Cancel confirmation modal -->
<div class="modal-backdrop" id="cancelModal">
    <div class="modal-box">
        <h3>Cancel appointment?</h3>
        <p>This action cannot be undone. The slot will be released and you'll need to book again if needed.</p>
        <div class="modal-actions">
            <button class="btn-sm btn-reschedule" onclick="closeCancelModal()">Keep it</button>
            <button class="btn-sm btn-cancel" id="modalConfirmBtn" onclick="confirmCancel()">Yes, cancel</button>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
var _cancelId = null;

function toggleMonth(el) {
    el.classList.toggle('open');
    el.nextElementSibling.classList.toggle('collapsed');
}

function showToast(msg, type) {
    var t = document.getElementById('dashToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'dashToast';
        t.style.cssText = 'position:fixed;bottom:28px;right:28px;padding:14px 20px;border-radius:10px;'
            + 'font-size:14px;font-weight:500;color:#fff;z-index:9999;opacity:0;'
            + 'transform:translateY(10px);transition:opacity .25s,transform .25s;pointer-events:none;max-width:340px;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = (type === 'error') ? '#dc2626' : '#059669';
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
    setTimeout(function () { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; }, 3500);
}

function cancelAppt(id) {
    if (!id) return;
    _cancelId = id;
    document.getElementById('cancelModal').classList.add('open');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.remove('open');
    _cancelId = null;
}

function confirmCancel() {
    if (!_cancelId) return;
    var btn = document.getElementById('modalConfirmBtn');
    btn.disabled = true;
    btn.textContent = 'Cancelling\u2026';

    fetch('/api/appointments/' + _cancelId + '/cancel', { method: 'PATCH' })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            closeCancelModal();
            if (res.data.success) {
                showToast('Appointment cancelled.', 'success');
                setTimeout(function () { location.reload(); }, 900);
            } else {
                showToast(res.data.message || 'Could not cancel. Try again.', 'error');
            }
        })
        .catch(function () {
            closeCancelModal();
            showToast('Network error. Please try again.', 'error');
        });
}

// Close modal on backdrop click
document.getElementById('cancelModal').addEventListener('click', function (e) {
    if (e.target === this) closeCancelModal();
});
</script>
JS;

include __DIR__ . '/../layouts/app.php';