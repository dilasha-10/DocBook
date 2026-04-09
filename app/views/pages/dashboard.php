<?php
$title = 'Dashboard';

$extra_styles = <<<CSS
<style>
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
.appt-table td:last-child { overflow:visible; white-space:normal; }
.appt-table tr:hover td { background:rgba(255,255,255,.025); }
.appt-actions       { display:flex; gap:8px; }
/* Fixed column widths shared across both upcoming and past tables */
.appt-table col.col-doctor  { width:20%; }
.appt-table col.col-spec    { width:18%; }
.appt-table col.col-date    { width:20%; }
.appt-table col.col-status  { width:12%; }
.appt-table col.col-actions { width:30%; }
.btn-sm             { padding:5px 13px; font-size:12px; font-weight:600; border-radius:6px;
                      border:none; cursor:pointer; text-decoration:none; transition:opacity .15s; }
.btn-sm:hover       { opacity:.8; }
.btn-reschedule     { background:#eff6ff; color:#1d4ed8; border:1.5px solid #bfdbfe; }
.btn-cancel         { background:#fff0f0; color:#b91c1c; border:1.5px solid #fca5a5; }

.badge              { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; }
.badge-confirmed    { background:#d1fae5; color:#065f46; }
.badge-pending      { background:#fff3cd; color:#92400e; border:1.5px solid #f59e0b; }
.badge-cancelled    { background:#fee2e2; color:#991b1b; }
.badge-completed    { background:#ede9fe; color:#5b21b6; }
.badge-rescheduled  { background:#dbeafe; color:#1e40af; }

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
  .main-wrap { padding:16px 12px !important; }
  .appt-table         { display:block; width:100%; }
  .appt-table thead   { display:none; }
  .appt-table tbody   { display:block; }
  .appt-table tr      { display:flex; flex-direction:column; gap:5px;
                        padding:14px 16px; border-top:1px solid var(--border); }
  .appt-table td      { display:block; padding:0; border:none; font-size:13px; }
  .appt-table td[style*="font-weight:500"]::before { content: attr(data-label); display:block; font-size:11px; color:var(--hint); text-transform:uppercase; letter-spacing:.05em; margin-bottom:2px; }
  .appt-actions       { display:flex; flex-wrap:wrap; gap:8px; padding-top:8px; margin-top:4px; }
  .btn-sm             { flex:1; min-width:80px; text-align:center; padding:9px 10px; font-size:13px; font-weight:600; }
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
                    <tr data-id="<?php echo $appt['id'] ?? ''; ?>" class="appt-row">
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
                        <td style="font-weight:500;" data-label="Doctor"><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                        <td style="color:var(--muted);" data-label="Specialization"><?php echo htmlspecialchars($spec); ?></td>
                        <td style="color:var(--muted);" data-label="Date & Time"><?php echo $date; ?> &middot; <?php echo $time; ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $s; ?>"><?php echo ucfirst($s); ?></span></td>
                        <td>
                            <?php if ($canAct): ?>
                            <div class="appt-actions">
                                <a href="/appointments/<?php echo $appt['id'] ?? ''; ?>/reschedule"
                                   class="btn-sm btn-reschedule">Reschedule</a>
                                <button onclick="cancelAppt(<?php echo $appt['id'] ?? 0; ?>)"
                                        class="btn-sm btn-cancel">Cancel</button>
                                <a href="/chat/<?php echo $appt['id'] ?? ''; ?>"
                                   class="btn-sm" style="background:#f0fdf4;color:#166534;border:1.5px solid #86efac;">
                                    <i class="fa fa-comment-medical" style="font-size:11px;"></i> Chat
                                </a>
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
                            <tr data-id="<?php echo $appt['id'] ?? ''; ?>" class="appt-row">
                                <td style="font-weight:500;"><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                <td style="color:var(--muted);"><?php echo htmlspecialchars($spec); ?></td>
                                <td style="color:var(--muted);"><?php echo $date; ?> &middot; <?php echo $time; ?></td>
                                <td><span class="badge badge-<?php echo $s; ?>"><?php echo ucfirst($s); ?></span></td>
                                <td>
                                    <div class="appt-actions">
                                    <a href="/categories" class="btn-sm btn-reschedule">Book again</a>
                                    <a href="/chat/<?php echo $appt['id'] ?? ''; ?>"
                                       class="btn-sm" style="background:#f0fdf4;color:#166534;border:1.5px solid #86efac;">
                                        <i class="fa fa-comment-medical" style="font-size:11px;"></i> Chat
                                    </a>
                                    </div>
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

<!-- Appointment Detail Panel -->
<div class="detail-panel-backdrop" id="detailPanel">
    <div class="detail-panel" id="detailPanelInner">
        <div class="detail-panel-header">
            <h3>Appointment Details</h3>
            <button class="detail-close-btn" id="detailCloseBtn" aria-label="Close">
                <i class="fa fa-xmark"></i>
            </button>
        </div>
        <div class="detail-panel-body" id="detailPanelBody">
            <div class="detail-skeleton">
                <div class="skeleton-line" style="width:60%;"></div>
                <div class="skeleton-line" style="width:40%;"></div>
                <div class="skeleton-line" style="width:75%;"></div>
                <div class="skeleton-line" style="width:50%;"></div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<'ENDJS'
<script>
// ── Shared state ─────────────────────────────────────────────────────────────
var _cancelId   = null;
var _detailId   = null;

// ── Toast ─────────────────────────────────────────────────────────────────────
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
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; }, 3500);
}

// ── Month toggle ──────────────────────────────────────────────────────────────
function toggleMonth(el) {
    el.classList.toggle('open');
    el.nextElementSibling.classList.toggle('collapsed');
}

// ── Cancel modal ──────────────────────────────────────────────────────────────
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
document.getElementById('cancelModal').addEventListener('click', function (e) {
    if (e.target === this) closeCancelModal();
});

// ── Detail panel ──────────────────────────────────────────────────────────────
function openDetailPanel(apptId) {
    _detailId = apptId;
    var backdrop = document.getElementById('detailPanel');
    var body     = document.getElementById('detailPanelBody');

    // Show skeleton
    body.innerHTML = '<div class="detail-skeleton">'
        + '<div class="skeleton-line" style="width:55%"></div>'
        + '<div class="skeleton-line" style="width:40%"></div>'
        + '<div class="skeleton-line" style="width:70%"></div>'
        + '<div class="skeleton-line" style="width:35%"></div>'
        + '</div>';

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Fetch detail + comments in parallel
    Promise.all([
        fetch('/api/appointments/' + apptId).then(function(r){ return r.json(); }),
        fetch('/api/appointments/' + apptId + '/comments').then(function(r){ return r.json(); })
    ]).then(function(results) {
        var detailRes  = results[0];
        var commentRes = results[1];
        if (!detailRes.success) {
            body.innerHTML = '<p style="color:var(--muted);padding:20px;">Could not load details.</p>';
            return;
        }
        renderDetailPanel(detailRes.data, commentRes.success ? commentRes.data : []);
    }).catch(function() {
        body.innerHTML = '<p style="color:var(--muted);padding:20px;">Network error.</p>';
    });
}

function closeDetailPanel() {
    document.getElementById('detailPanel').classList.remove('open');
    document.body.style.overflow = '';
    _detailId = null;
}

function renderDetailPanel(appt, comments) {
    var body = document.getElementById('detailPanelBody');

    var statusClass = 'badge-' + (appt.status || 'pending').toLowerCase();

    function fmt(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    function fmtTime(t) {
        if (!t) return '—';
        var parts = t.split(':');
        var h = parseInt(parts[0]), m = parts[1];
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ampm;
    }

    var doctorComment = appt.doctor_comment
        ? '<p class="detail-notes-text">' + escHtml(appt.doctor_comment) + '</p>'
        : '<p class="detail-notes-empty">No notes from your doctor yet.</p>';

    // Build chat messages HTML
    var chatHTML = '';
    if (comments && comments.length > 0) {
        comments.forEach(function(c) {
            var side  = (c.role === 'doctor') ? 'doctor' : 'patient';
            var label = (c.role === 'doctor') ? 'Dr. ' + escHtml(c.name) : 'You';
            var time  = new Date(c.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
            chatHTML += '<div class="chat-msg ' + side + '">'
                + '<div class="chat-bubble">' + escHtml(c.message) + '</div>'
                + '<span class="chat-meta">' + label + ' · ' + time + '</span>'
                + '</div>';
        });
    } else {
        chatHTML = '<p class="chat-empty">No messages yet. Send the first one!</p>';
    }

    body.innerHTML =
        '<div class="detail-field">'
            + '<span class="detail-field-label">Doctor</span>'
            + '<span class="detail-field-value" style="font-weight:600;">' + escHtml(appt.doctor_name) + '</span>'
            + '<span style="font-size:13px;color:var(--muted);">' + escHtml(appt.specialty || appt.category || '') + '</span>'
        + '</div>'
        + '<div class="detail-field">'
            + '<span class="detail-field-label">Date &amp; Time</span>'
            + '<span class="detail-field-value">' + fmt(appt.date) + ' · ' + fmtTime(appt.time) + '</span>'
        + '</div>'
        + '<div class="detail-field">'
            + '<span class="detail-field-label">Status</span>'
            + '<span class="badge ' + statusClass + '" style="margin-top:3px;">' + escHtml(appt.status) + '</span>'
        + '</div>'
        + (appt.reference_number ? '<div class="detail-field"><span class="detail-field-label">Reference</span><span class="detail-field-value" style="font-family:monospace;">' + escHtml(appt.reference_number) + '</span></div>' : '')
        + (appt.visit_reason ? '<div class="detail-field"><span class="detail-field-label">Visit Reason</span><span class="detail-field-value">' + escHtml(appt.visit_reason) + '</span></div>' : '')
        + '<div class="detail-notes-box">'
            + '<span class="detail-field-label"><i class="fa fa-stethoscope" style="margin-right:5px;"></i>Doctor\'s Notes</span>'
            + doctorComment
        + '</div>'
        + '<div class="chat-section">'
            + '<span class="chat-section-title"><i class="fa fa-comments" style="margin-right:5px;"></i>Messages</span>'
            + '<div class="chat-messages" id="chatMessages">' + chatHTML + '</div>'
            + '<div class="chat-input-row">'
                + '<textarea class="chat-input" id="chatInput" placeholder="Send a message to your doctor\u2026" rows="1"></textarea>'
                + '<button class="chat-send-btn" id="chatSendBtn" onclick="sendChatMessage()"><i class="fa fa-paper-plane"></i></button>'
            + '</div>'
        + '</div>';

    // Auto-scroll chat to bottom
    setTimeout(function() {
        var msgs = document.getElementById('chatMessages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    }, 50);

    // Auto-resize textarea
    var inp = document.getElementById('chatInput');
    if (inp) {
        inp.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });
    }
}

function sendChatMessage() {
    if (!_detailId) return;
    var inp = document.getElementById('chatInput');
    var btn = document.getElementById('chatSendBtn');
    if (!inp) return;
    var msg = inp.value.trim();
    if (!msg) return;

    inp.disabled = true;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    fetch('/api/appointments/' + _detailId + '/comments', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            inp.value = '';
            inp.style.height = 'auto';
            // Append new message bubble
            var msgs = document.getElementById('chatMessages');
            var empty = msgs ? msgs.querySelector('.chat-empty') : null;
            if (empty) empty.remove();
            var c = res.data;
            var time = new Date(c.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
            var div = document.createElement('div');
            div.className = 'chat-msg patient';
            div.innerHTML = '<div class="chat-bubble">' + escHtml(c.message) + '</div>'
                + '<span class="chat-meta">You · ' + time + '</span>';
            if (msgs) {
                msgs.appendChild(div);
                msgs.scrollTop = msgs.scrollHeight;
            }
        } else {
            showToast(res.message || 'Could not send message.', 'error');
        }
    })
    .catch(function() { showToast('Network error.', 'error'); })
    .finally(function() {
        inp.disabled = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane"></i>';
        inp.focus();
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Row click → open detail panel ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr.appt-row[data-id]').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // Don't open panel if clicking an action button/link
            if (e.target.closest('.appt-actions') || e.target.closest('button') || e.target.closest('a')) return;
            var id = this.getAttribute('data-id');
            if (id) openDetailPanel(parseInt(id, 10));
        });
    });

    // Close detail panel
    document.getElementById('detailPanel').addEventListener('click', function(e) {
        if (e.target === this) closeDetailPanel();
    });
    document.getElementById('detailCloseBtn').addEventListener('click', closeDetailPanel);

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailPanel();
            closeCancelModal();
        }
    });
});
</script>
ENDJS;

include __DIR__ . '/../layouts/app.php';