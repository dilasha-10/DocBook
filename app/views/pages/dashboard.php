<?php
$title = 'Dashboard';

// Group past appointments by month
$grouped = [];
foreach ($past as $appt) {
    $key = isset($appt['date']) ? date('F Y', strtotime($appt['date'])) : 'Unknown';
    $grouped[$key][] = $appt;
}

$pending_count = $stats['pending'] ?? 0;
if (!isset($stats['pending'])) {
    foreach ($upcoming as $a) {
        if (strtolower($a['status'] ?? '') === 'pending') $pending_count++;
    }
}

ob_start();

$extra_styles = <<<CSS
<style>
/* ── Dashboard action buttons ── */
.appt-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.appt-actions .btn-sm {
    flex-shrink: 0;
    white-space: nowrap;
}
/* Chat btn consistent style */
.btn-chat {
    background: #edfaf4;
    color: #1a6644;
    border: 1.5px solid #86ddb0 !important;
}
[data-theme="dark"] .btn-chat {
    background: rgba(48,204,144,0.10);
    color: #3acc88;
    border-color: rgba(48,204,144,0.28) !important;
}

/* Mobile table: give action buttons room to breathe */
@media (max-width: 768px) {
    .appt-table td[data-label="Actions"] {
        padding-top: 6px !important;
    }
    .appt-actions {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
    }
    .appt-actions .btn-sm {
        flex: 1 1 auto;
        min-width: 80px;
        text-align: center;
        padding: 9px 10px;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
}
</style>
CSS;
?>
<div class="page-content">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;">
        <div>
            <h1 style="font-size:24px;font-weight:700;margin-bottom:4px;">Welcome back, <?= htmlspecialchars($patient_name) ?></h1>
            <p style="color:var(--muted);font-size:14px;">Here&rsquo;s a summary of your appointments</p>
        </div>
        <a href="/categories" class="btn-primary">Book appointment</a>
    </div>

    <!-- Stat cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['upcoming'] ?? count($upcoming) ?></div>
            <div class="stat-label">Upcoming appointments</div>
            <span class="badge badge-confirmed" style="font-size:11px;">This month</span>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total'] ?? (count($upcoming) + count($past)) ?></div>
            <div class="stat-label">Total appointments</div>
            <span class="badge badge-completed" style="font-size:11px;">All time</span>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $pending_count ?></div>
            <div class="stat-label">Pending confirmations</div>
            <span class="badge badge-pending" style="font-size:11px;"><?= $pending_count > 0 ? 'Action needed' : 'All clear' ?></span>
        </div>
    </div>

    <!-- Upcoming appointments -->
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:28px;" id="upcoming">
        <div class="section-head">
            <h2>Upcoming appointments</h2>
            <span class="count-badge"><?= count($upcoming) ?></span>
        </div>

        <?php if (count($upcoming) > 0): ?>
        <table class="appt-table">
            <colgroup>
                <col class="col-doctor"><col class="col-spec"><col class="col-date"><col class="col-status"><col class="col-actions">
            </colgroup>
            <thead>
                <tr><th>Doctor</th><th>Specialization</th><th>Date &amp; Time</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $appt):
                    $s      = strtolower($appt['status'] ?? 'pending');
                    $date   = isset($appt['date']) ? date('M j, Y', strtotime($appt['date'])) : '—';
                    $time   = isset($appt['time']) ? date('g:i A',  strtotime($appt['time'])) : '—';
                    $spec   = $appt['specialty'] ?? $appt['category'] ?? '—';
                    $canAct = !in_array($s, ['cancelled']);
                ?>
                <tr data-id="<?= $appt['id'] ?? '' ?>" class="appt-row">
                    <td style="font-weight:500;" data-label="Doctor"><?= htmlspecialchars($appt['doctor_name']) ?></td>
                    <td style="color:var(--muted);" data-label="Specialization"><?= htmlspecialchars($spec) ?></td>
                    <td style="color:var(--muted);" data-label="Date & Time"><?= $date ?> &middot; <?= $time ?></td>
                    <td data-label="Status"><span class="badge badge-<?= $s ?>"><?= ucfirst($s) ?></span></td>
                    <td>
                        <?php if ($canAct): ?>
                        <div class="appt-actions">
                            <a href="/appointments/<?= $appt['id'] ?? '' ?>/reschedule" class="btn-sm btn-reschedule">Reschedule</a>
                            <button onclick="cancelAppt(<?= $appt['id'] ?? 0 ?>)" class="btn-sm btn-cancel">Cancel</button>
                            <a href="/chat/<?= $appt['id'] ?? '' ?>" class="btn-sm btn-chat">
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
            <span class="count-badge" style="background:var(--border);color:var(--muted);"><?= count($past) ?></span>
        </div>

        <?php if (count($past) > 0): ?>
        <?php foreach ($grouped as $month => $appts): ?>
        <div class="month-group">
            <div class="month-toggle open" onclick="toggleMonth(this)">
                <span><?= htmlspecialchars($month) ?> <span style="font-weight:400;opacity:.6;">(<?= count($appts) ?>)</span></span>
                <i class="fa fa-chevron-down chevron"></i>
            </div>
            <div class="month-body">
                <table class="appt-table">
                    <colgroup>
                        <col class="col-doctor"><col class="col-spec"><col class="col-date"><col class="col-status"><col class="col-actions">
                    </colgroup>
                    <tbody>
                        <?php foreach ($appts as $appt):
                            $s    = strtolower($appt['status'] ?? 'completed');
                            $date = isset($appt['date']) ? date('M j, Y', strtotime($appt['date'])) : '—';
                            $time = isset($appt['time']) ? date('g:i A',  strtotime($appt['time'])) : '—';
                            $spec = $appt['specialty'] ?? $appt['category'] ?? '—';
                        ?>
                        <tr data-id="<?= $appt['id'] ?? '' ?>" class="appt-row">
                            <td style="font-weight:500;"><?= htmlspecialchars($appt['doctor_name']) ?></td>
                            <td style="color:var(--muted);"><?= htmlspecialchars($spec) ?></td>
                            <td style="color:var(--muted);"><?= $date ?> &middot; <?= $time ?></td>
                            <td><span class="badge badge-<?= $s ?>"><?= ucfirst($s) ?></span></td>
                            <td>
                                <div class="appt-actions">
                                    <a href="/categories" class="btn-sm btn-reschedule">Book again</a>
                                    <a href="/chat/<?= $appt['id'] ?? '' ?>" class="btn-sm btn-chat">
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

<!-- Cancel modal -->
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

<!-- Detail panel -->
<div class="detail-panel-backdrop" id="detailPanel">
    <div class="detail-panel" id="detailPanelInner">
        <div class="detail-panel-header">
            <h3>Appointment Details</h3>
            <button class="detail-close-btn" id="detailCloseBtn" aria-label="Close"><i class="fa fa-xmark"></i></button>
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

<script>
var _cancelId = null, _detailId = null;

function showToast(msg, type) {
    var t = document.getElementById('dashToast');
    if (!t) {
        t = document.createElement('div'); t.id = 'dashToast';
        t.style.cssText = 'position:fixed;bottom:28px;right:28px;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;color:#fff;z-index:9999;opacity:0;transform:translateY(10px);transition:opacity .25s,transform .25s;pointer-events:none;max-width:340px;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = (type === 'error') ? '#dc2626' : '#059669';
    t.style.opacity = '1'; t.style.transform = 'translateY(0)';
    clearTimeout(t._timer);
    t._timer = setTimeout(function(){ t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; }, 3500);
}

function toggleMonth(el) {
    el.classList.toggle('open');
    el.nextElementSibling.classList.toggle('collapsed');
}

function cancelAppt(id) { if (!id) return; _cancelId = id; document.getElementById('cancelModal').classList.add('open'); }
function closeCancelModal() { document.getElementById('cancelModal').classList.remove('open'); _cancelId = null; }
function confirmCancel() {
    if (!_cancelId) return;
    var btn = document.getElementById('modalConfirmBtn');
    btn.disabled = true; btn.textContent = 'Cancelling\u2026';
    fetch('/api/appointments/' + _cancelId + '/cancel', { method: 'PATCH' })
        .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
        .then(function(res){
            closeCancelModal();
            if (res.data.success) { showToast('Appointment cancelled.', 'success'); setTimeout(function(){ location.reload(); }, 900); }
            else { showToast(res.data.message || 'Could not cancel. Try again.', 'error'); }
        })
        .catch(function(){ closeCancelModal(); showToast('Network error. Please try again.', 'error'); });
}
document.getElementById('cancelModal').addEventListener('click', function(e){ if (e.target === this) closeCancelModal(); });

function openDetailPanel(apptId) {
    _detailId = apptId;
    var body = document.getElementById('detailPanelBody');
    body.innerHTML = '<div class="detail-skeleton"><div class="skeleton-line" style="width:55%"></div><div class="skeleton-line" style="width:40%"></div><div class="skeleton-line" style="width:70%"></div></div>';
    document.getElementById('detailPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
    Promise.all([
        fetch('/api/appointments/' + apptId).then(function(r){ return r.json(); }),
        fetch('/api/appointments/' + apptId + '/comments').then(function(r){ return r.json(); })
    ]).then(function(results){
        var detail = results[0], comments = results[1];
        if (!detail.success) { body.innerHTML = '<p style="color:var(--muted);padding:20px;">Could not load details.</p>'; return; }
        renderDetailPanel(detail.data, comments.success ? comments.data : []);
    }).catch(function(){ body.innerHTML = '<p style="color:var(--muted);padding:20px;">Network error.</p>'; });
}

function closeDetailPanel() {
    document.getElementById('detailPanel').classList.remove('open');
    document.body.style.overflow = '';
    _detailId = null;
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renderDetailPanel(appt, comments) {
    var body = document.getElementById('detailPanelBody');
    var statusClass = 'badge-' + (appt.status || 'pending').toLowerCase();
    function fmt(s) { if (!s) return '—'; var d = new Date(s+'T00:00:00'); return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }
    function fmtT(t) { if (!t) return '—'; var p = t.split(':'), h = parseInt(p[0]); return (h%12||12) + ':' + p[1] + ' ' + (h>=12?'PM':'AM'); }

    var chatHTML = '';
    if (comments && comments.length) {
        comments.forEach(function(c){
            var side = (c.role === 'doctor') ? 'doctor' : 'patient';
            var label = (c.role === 'doctor') ? 'Dr. ' + escHtml(c.name) : 'You';
            var time = new Date(c.created_at).toLocaleString('en-US',{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
            chatHTML += '<div class="chat-msg ' + side + '"><div class="chat-bubble">' + escHtml(c.message) + '</div><span class="chat-meta">' + label + ' · ' + time + '</span></div>';
        });
    } else { chatHTML = '<p class="chat-empty">No messages yet. Send the first one!</p>'; }

    body.innerHTML =
        '<div class="detail-field"><span class="detail-field-label">Doctor</span><span class="detail-field-value" style="font-weight:600;">' + escHtml(appt.doctor_name) + '</span><span style="font-size:13px;color:var(--muted);">' + escHtml(appt.specialty || appt.category || '') + '</span></div>'
        + '<div class="detail-field"><span class="detail-field-label">Date &amp; Time</span><span class="detail-field-value">' + fmt(appt.date) + ' · ' + fmtT(appt.time) + '</span></div>'
        + '<div class="detail-field"><span class="detail-field-label">Status</span><span class="badge ' + statusClass + '" style="margin-top:3px;">' + escHtml(appt.status) + '</span></div>'
        + (appt.reference_number ? '<div class="detail-field"><span class="detail-field-label">Reference</span><span class="detail-field-value" style="font-family:monospace;">' + escHtml(appt.reference_number) + '</span></div>' : '')
        + (appt.visit_reason ? '<div class="detail-field"><span class="detail-field-label">Visit Reason</span><span class="detail-field-value">' + escHtml(appt.visit_reason) + '</span></div>' : '')
        + '<div class="detail-notes-box"><span class="detail-field-label"><i class="fa fa-stethoscope" style="margin-right:5px;"></i>Doctor\'s Notes</span>'
        + (appt.doctor_comment ? '<p class="detail-notes-text">' + escHtml(appt.doctor_comment) + '</p>' : '<p class="detail-notes-empty">No notes from your doctor yet.</p>') + '</div>'
        + '<div class="chat-section"><span class="chat-section-title"><i class="fa fa-comments" style="margin-right:5px;"></i>Messages</span>'
        + '<div class="chat-messages" id="chatMessages">' + chatHTML + '</div>'
        + '<div class="chat-input-row"><textarea class="chat-input" id="chatInput" placeholder="Send a message to your doctor\u2026" rows="1"></textarea>'
        + '<button class="chat-send-btn" id="chatSendBtn" onclick="sendChatMessage()"><i class="fa fa-paper-plane"></i></button></div></div>';

    setTimeout(function(){ var m = document.getElementById('chatMessages'); if (m) m.scrollTop = m.scrollHeight; }, 50);

    var inp = document.getElementById('chatInput');
    if (inp) {
        inp.addEventListener('input', function(){ this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 100) + 'px'; });
        inp.addEventListener('keydown', function(e){ if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChatMessage(); } });
    }
}

function sendChatMessage() {
    if (!_detailId) return;
    var inp = document.getElementById('chatInput'), btn = document.getElementById('chatSendBtn');
    if (!inp) return;
    var msg = inp.value.trim(); if (!msg) return;
    inp.disabled = true; btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    fetch('/api/appointments/' + _detailId + '/comments', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: msg })
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            inp.value = ''; inp.style.height = 'auto';
            var msgs = document.getElementById('chatMessages');
            var empty = msgs ? msgs.querySelector('.chat-empty') : null; if (empty) empty.remove();
            var c = res.data, time = new Date(c.created_at).toLocaleString('en-US',{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
            var div = document.createElement('div'); div.className = 'chat-msg patient';
            div.innerHTML = '<div class="chat-bubble">' + escHtml(c.message) + '</div><span class="chat-meta">You · ' + time + '</span>';
            if (msgs) { msgs.appendChild(div); msgs.scrollTop = msgs.scrollHeight; }
        } else { showToast(res.message || 'Could not send message.', 'error'); }
    })
    .catch(function(){ showToast('Network error.', 'error'); })
    .finally(function(){ inp.disabled = false; btn.disabled = false; btn.innerHTML = '<i class="fa fa-paper-plane"></i>'; inp.focus(); });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('tr.appt-row[data-id]').forEach(function(row){
        row.addEventListener('click', function(e){
            if (e.target.closest('.appt-actions') || e.target.closest('button') || e.target.closest('a')) return;
            var id = this.getAttribute('data-id');
            if (id) openDetailPanel(parseInt(id, 10));
        });
    });
    document.getElementById('detailPanel').addEventListener('click', function(e){ if (e.target === this) closeDetailPanel(); });
    document.getElementById('detailCloseBtn').addEventListener('click', closeDetailPanel);
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { closeDetailPanel(); closeCancelModal(); } });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';