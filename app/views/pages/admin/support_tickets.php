<?php
$title = 'Support Tickets';
ob_start();
$extra_styles = <<<CSS
<style>
.st-wrap { padding: 30px 24px 60px; max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* Stats row */
.stat-row { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.stat-card { flex: 1; min-width: 140px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; text-align: center; }
.stat-val { font-size: 28px; font-weight: 800; color: var(--text); }
.stat-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
.stat-card.open .stat-val { color: #ea580c; }
.stat-card.progress .stat-val { color: #2563eb; }
.stat-card.resolved .stat-val { color: #16a34a; }

/* Filters */
.filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.filter-bar input, .filter-bar select { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-family: inherit; background: var(--surface); color: var(--text); min-width: 160px; }
.filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--blue); }

/* Table */
.ticket-table { width: 100%; border-collapse: collapse; }
.ticket-table th { text-align: left; padding: 10px 12px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid var(--border); }
.ticket-table td { padding: 14px 12px; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border); vertical-align: top; }
.ticket-table tr:hover td { background: var(--hover-bg, rgba(0,0,0,.02)); }
.ticket-table .subj { font-weight: 700; color: var(--text); cursor: pointer; }
.ticket-table .subj:hover { color: var(--blue); text-decoration: underline; }
.badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: capitalize; }
.badge.open { background: #fff7ed; color: #c2410c; }
.badge.in_progress { background: #eff6ff; color: #1d4ed8; }
.badge.resolved { background: #f0fdf4; color: #15803d; }
.cat-badge { font-size: 11px; color: var(--muted); background: var(--hover-bg, rgba(0,0,0,.04)); padding: 2px 8px; border-radius: 4px; }
.time-ago { font-size: 11px; color: var(--muted); white-space: nowrap; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: 16px; padding: 28px; width: 90%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,.2); max-height: 90vh; overflow-y: auto; }
.modal-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 18px; }
.modal-field { margin-bottom: 14px; }
.modal-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.modal-field input, .modal-field textarea, .modal-field select { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; background: var(--surface); color: var(--text); }
.modal-field textarea { min-height: 100px; resize: vertical; }
.modal-field input:focus, .modal-field textarea:focus, .modal-field select:focus { outline: none; border-color: var(--blue); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-primary:hover { opacity: .85; }
.btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
.detail-section { margin-bottom: 16px; }
.detail-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 4px; }
.detail-value { font-size: 14px; color: var(--text); line-height: 1.6; }
.detail-divider { border: none; border-top: 1px solid var(--border); margin: 16px 0; }
.privacy-notice { background: #fef9c3; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #854d0e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.privacy-notice i { font-size: 14px; }

.table-wrap { overflow-x: auto; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #166534; color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; z-index: 99999; display: none; box-shadow: 0 4px 20px rgba(0,0,0,.15); }
.toast.error { background: #991b1b; }
.toast.show { display: block; animation: slidein .3s ease; }
@keyframes slidein { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: .3; }
</style>
CSS;
$content = ob_get_clean();
ob_start();
?>

<div class="st-wrap">
    <div class="page-header">
        <h1 class="page-title"><i class="fa fa-headset" style="color:var(--blue);margin-right:8px;"></i>Patient Support Tickets</h1>
        <p class="page-subtitle">View and respond to patient support inquiries — only account metadata is visible (Name, Email)</p>
    </div>

    <div class="stat-row" id="statsRow">
        <div class="stat-card"><div class="stat-val" id="statTotal">–</div><div class="stat-label">Total</div></div>
        <div class="stat-card open"><div class="stat-val" id="statOpen">–</div><div class="stat-label">Open</div></div>
        <div class="stat-card progress"><div class="stat-val" id="statProgress">–</div><div class="stat-label">In Progress</div></div>
        <div class="stat-card resolved"><div class="stat-val" id="statResolved">–</div><div class="stat-label">Resolved</div></div>
    </div>

    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search name, email, subject...">
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
        </select>
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <option value="login_issue">Login Issue</option>
            <option value="payment_query">Payment Query</option>
            <option value="appointment_issue">Appointment Issue</option>
            <option value="account_issue">Account Issue</option>
            <option value="other">Other</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="ticket-table" id="ticketTable" style="display:none;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Patient</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody id="ticketBody"></tbody>
        </table>
    </div>
    <div id="ticketEmpty" class="empty-state" style="display:none;">
        <i class="fa fa-headset"></i>
        <p>No support tickets found.</p>
    </div>
    <div id="ticketLoading" style="text-align:center;padding:40px;color:var(--muted);"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
</div>

<!-- Detail / Reply Modal -->
<div class="modal-overlay" id="ticketModal">
    <div class="modal-box">
        <h2 class="modal-title">Ticket Details</h2>
        <div class="privacy-notice"><i class="fa fa-shield-halved"></i> Privacy Guard: Only account metadata is shown. No medical records are accessible.</div>

        <div class="detail-section">
            <div class="detail-label">Patient</div>
            <div class="detail-value" id="detailPatient"></div>
        </div>
        <div class="detail-section">
            <div class="detail-label">Subject</div>
            <div class="detail-value" id="detailSubject"></div>
        </div>
        <div class="detail-section">
            <div class="detail-label">Category</div>
            <div class="detail-value" id="detailCategory"></div>
        </div>
        <div class="detail-section">
            <div class="detail-label">Message</div>
            <div class="detail-value" id="detailMessage" style="white-space:pre-wrap;"></div>
        </div>
        <hr class="detail-divider">
        <div class="detail-section" id="prevReplySection" style="display:none;">
            <div class="detail-label">Previous Admin Reply</div>
            <div class="detail-value" id="detailPrevReply" style="white-space:pre-wrap;background:var(--hover-bg,rgba(0,0,0,.03));padding:10px;border-radius:8px;"></div>
        </div>
        <hr class="detail-divider">
        <input type="hidden" id="ticketId">
        <div class="modal-field">
            <label for="replyStatus">Update Status</label>
            <select id="replyStatus">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>
        <div class="modal-field">
            <label for="replyText">Reply to Patient (sends as notification)</label>
            <textarea id="replyText" placeholder="Type your reply..."></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeTicketModal()">Cancel</button>
            <button class="btn-primary" onclick="submitReply()"><i class="fa fa-paper-plane"></i> Send Reply</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var allTickets = [];

function toast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(function(){ t.className = 'toast'; }, 3000);
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function timeAgo(d) {
    var diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

function catLabel(c) {
    return {login_issue:'Login Issue',payment_query:'Payment Query',appointment_issue:'Appointment',account_issue:'Account',other:'Other'}[c] || c;
}

function loadTickets() {
    var q = document.getElementById('searchInput').value;
    var s = document.getElementById('statusFilter').value;
    var c = document.getElementById('categoryFilter').value;
    var params = new URLSearchParams();
    if (q) params.set('search', q);
    if (s) params.set('status', s);
    if (c) params.set('category', c);

    fetch(BASE_URL + '/admin/api/support-tickets?' + params.toString())
        .then(function(r){ return r.json(); })
        .then(function(d) {
            allTickets = d.tickets || [];
            var stats = d.stats || {};
            document.getElementById('statTotal').textContent    = stats.total || 0;
            document.getElementById('statOpen').textContent      = stats.open_count || 0;
            document.getElementById('statProgress').textContent  = stats.in_progress_count || 0;
            document.getElementById('statResolved').textContent  = stats.resolved_count || 0;
            renderTickets();
        });
}

function renderTickets() {
    var loading = document.getElementById('ticketLoading');
    var table   = document.getElementById('ticketTable');
    var empty   = document.getElementById('ticketEmpty');
    var body    = document.getElementById('ticketBody');
    loading.style.display = 'none';

    if (!allTickets.length) { table.style.display = 'none'; empty.style.display = 'block'; return; }
    empty.style.display = 'none'; table.style.display = 'table';

    body.innerHTML = allTickets.map(function(t) {
        var statusClass = t.status;
        return '<tr>' +
            '<td>' + t.id + '</td>' +
            '<td><span class="subj" onclick="openTicket(' + t.id + ')">' + escHtml(t.subject) + '</span></td>' +
            '<td>' + escHtml(t.patient_name) + '<br><span style="font-size:11px;color:var(--muted);">' + escHtml(t.patient_email) + '</span></td>' +
            '<td><span class="cat-badge">' + catLabel(t.category) + '</span></td>' +
            '<td><span class="badge ' + statusClass + '">' + t.status.replace('_',' ') + '</span></td>' +
            '<td><span class="time-ago">' + timeAgo(t.created_at) + '</span></td>' +
        '</tr>';
    }).join('');
}

function openTicket(id) {
    var t = allTickets.find(function(x){ return x.id == id; });
    if (!t) return;
    document.getElementById('ticketId').value = t.id;
    document.getElementById('detailPatient').innerHTML = escHtml(t.patient_name) + ' &lt;' + escHtml(t.patient_email) + '&gt;';
    document.getElementById('detailSubject').textContent = t.subject;
    document.getElementById('detailCategory').textContent = catLabel(t.category);
    document.getElementById('detailMessage').textContent = t.message;
    document.getElementById('replyStatus').value = t.status;
    document.getElementById('replyText').value = '';

    if (t.admin_reply) {
        document.getElementById('prevReplySection').style.display = 'block';
        document.getElementById('detailPrevReply').textContent = t.admin_reply;
    } else {
        document.getElementById('prevReplySection').style.display = 'none';
    }

    document.getElementById('ticketModal').classList.add('open');
}

function closeTicketModal() { document.getElementById('ticketModal').classList.remove('open'); }

function submitReply() {
    var id     = document.getElementById('ticketId').value;
    var status = document.getElementById('replyStatus').value;
    var reply  = document.getElementById('replyText').value.trim();

    fetch(BASE_URL + '/admin/api/support-tickets/' + id, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ status: status, reply: reply })
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            closeTicketModal();
            loadTickets();
            toast(reply ? 'Reply sent & status updated' : 'Status updated');
        } else {
            toast(d.error || 'Failed', true);
        }
    });
}

// Live filter
var debounce;
document.getElementById('searchInput').addEventListener('input', function(){ clearTimeout(debounce); debounce = setTimeout(loadTickets, 300); });
document.getElementById('statusFilter').addEventListener('change', loadTickets);
document.getElementById('categoryFilter').addEventListener('change', loadTickets);

loadTickets();
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app.php';
