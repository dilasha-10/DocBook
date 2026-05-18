<?php
$title = 'Support Tickets';
ob_start();

$extra_styles = <<<CSS
<style>
.support-wrap { padding: 30px 24px 60px; max-width: 960px; margin: 0 auto; }
.page-header { margin-bottom: 28px; display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.page-header-left {}
.page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* New Ticket Button */
.btn-new-ticket {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--blue); color: #fff;
    border: none; border-radius: 12px; padding: 11px 22px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    font-family: inherit; transition: opacity .15s, transform .1s;
    box-shadow: 0 2px 12px rgba(99,102,241,.25);
    text-decoration: none;
}
.btn-new-ticket:hover { opacity: .88; transform: translateY(-1px); }
.btn-new-ticket:active { transform: translateY(0); }

/* Ticket Cards */
.ticket-list { display: flex; flex-direction: column; gap: 14px; }

.ticket-card {
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 14px; padding: 20px 22px; cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    position: relative;
}
.ticket-card:hover { border-color: var(--blue); box-shadow: 0 4px 20px rgba(42,143,168,.08); }
.ticket-card.status-open { border-left: 4px solid #ea580c; }
.ticket-card.status-in_progress { border-left: 4px solid #2563eb; }
.ticket-card.status-resolved { border-left: 4px solid #16a34a; }

.ticket-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
.ticket-subject { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
.ticket-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px; }
.ticket-message { font-size: 13px; color: var(--muted); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ticket-footer { display: flex; align-items: center; gap: 12px; margin-top: 10px; flex-wrap: wrap; }
.ticket-time { font-size: 11px; color: var(--hint, var(--muted)); }

/* Badges */
.badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: capitalize; white-space: nowrap; }
.badge.open { background: #fff7ed; color: #c2410c; }
.badge.in_progress { background: #eff6ff; color: #1d4ed8; }
.badge.resolved { background: #f0fdf4; color: #15803d; }
[data-theme="dark"] .badge.open { background: rgba(234,88,12,.15); color: #fb923c; }
[data-theme="dark"] .badge.in_progress { background: rgba(37,99,235,.15); color: #60a5fa; }
[data-theme="dark"] .badge.resolved { background: rgba(22,163,106,.15); color: #4ade80; }

.cat-badge { font-size: 11px; color: var(--muted); background: var(--hover-bg, rgba(0,0,0,.04)); padding: 2px 8px; border-radius: 4px; }

/* Reply box on card */
.ticket-reply-box {
    margin-top: 14px; padding: 14px 16px;
    background: rgba(99,102,241,.05); border: 1px solid rgba(99,102,241,.15);
    border-radius: 10px;
}
.ticket-reply-box .reply-label { font-size: 11px; font-weight: 700; color: var(--blue); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.ticket-reply-box .reply-text { font-size: 13px; color: var(--text); line-height: 1.6; white-space: pre-wrap; }
.ticket-reply-box .reply-meta { font-size: 11px; color: var(--muted); margin-top: 6px; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box {
    background: var(--surface); border-radius: 18px; padding: 30px;
    width: 92%; max-width: 520px;
    box-shadow: 0 24px 80px rgba(0,0,0,.2);
    max-height: 90vh; overflow-y: auto;
    animation: modalIn .2s ease;
}
@keyframes modalIn { from { opacity: 0; transform: scale(.96) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

.modal-title { font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.modal-subtitle { font-size: 13px; color: var(--muted); margin-bottom: 22px; }
.modal-field { margin-bottom: 16px; }
.modal-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .04em; }
.modal-field input, .modal-field textarea, .modal-field select {
    width: 100%; padding: 11px 14px; border: 1.5px solid var(--border);
    border-radius: 10px; font-size: 14px; font-family: inherit;
    background: var(--surface); color: var(--text); box-sizing: border-box;
    transition: border-color .15s;
}
.modal-field textarea { min-height: 120px; resize: vertical; }
.modal-field input:focus, .modal-field textarea:focus, .modal-field select:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(42,143,168,.1); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }

.btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 10px; padding: 11px 22px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: opacity .15s; }
.btn-primary:hover { opacity: .85; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.btn-outline { background: transparent; color: var(--muted); border: 1.5px solid var(--border); border-radius: 10px; padding: 11px 22px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-outline:hover { border-color: var(--text); color: var(--text); }

/* Empty state */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state i { font-size: 48px; margin-bottom: 16px; display: block; opacity: .25; }
.empty-state p { font-size: 15px; margin-bottom: 16px; }

/* Filter tabs */
.filter-tabs { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-tab {
    padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700;
    border: 1.5px solid var(--border); background: var(--surface); color: var(--muted);
    cursor: pointer; transition: all .15s; font-family: inherit;
}
.filter-tab:hover { border-color: var(--text); color: var(--text); }
.filter-tab.active { background: var(--blue); color: #fff; border-color: var(--blue); }
.filter-tab .tab-count { display: inline-block; margin-left: 4px; font-size: 11px; opacity: .7; }

/* Toast */
.toast { position: fixed; bottom: 24px; right: 24px; background: #166534; color: #fff; padding: 13px 22px; border-radius: 10px; font-size: 13px; font-weight: 700; z-index: 99999; display: none; box-shadow: 0 4px 20px rgba(0,0,0,.15); }
.toast.error { background: #991b1b; }
.toast.show { display: block; animation: slidein .3s ease; }
@keyframes slidein { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* Detail Modal */
.detail-section { margin-bottom: 14px; }
.detail-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.detail-value { font-size: 14px; color: var(--text); line-height: 1.6; }
.detail-divider { border: none; border-top: 1px solid var(--border); margin: 16px 0; }

@media (max-width: 600px) {
    .support-wrap { padding: 20px 14px 40px; }
    .page-header { flex-direction: column; }
    .ticket-header { flex-direction: column; align-items: flex-start; }
}
</style>
CSS;
$content = ob_get_clean();
ob_start();
?>

<div class="support-wrap">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title"><i class="fa fa-life-ring" style="color:var(--blue);margin-right:8px;"></i>Support Tickets</h1>
            <p class="page-subtitle">Submit a ticket for account or technical issues. Our team will respond promptly.</p>
        </div>
        <button class="btn-new-ticket" onclick="openNewTicketModal()" id="btnNewTicket">
            <i class="fa fa-plus"></i> New Ticket
        </button>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs" id="filterTabs">
        <button class="filter-tab active" data-status="" onclick="filterTickets(this, '')">All <span class="tab-count" id="countAll"></span></button>
        <button class="filter-tab" data-status="open" onclick="filterTickets(this, 'open')">Open <span class="tab-count" id="countOpen"></span></button>
        <button class="filter-tab" data-status="in_progress" onclick="filterTickets(this, 'in_progress')">In Progress <span class="tab-count" id="countProgress"></span></button>
        <button class="filter-tab" data-status="resolved" onclick="filterTickets(this, 'resolved')">Resolved <span class="tab-count" id="countResolved"></span></button>
    </div>

    <!-- Loading State -->
    <div id="ticketLoading" style="text-align:center;padding:40px;color:var(--muted);"><i class="fa fa-spinner fa-spin" style="font-size:20px;"></i><br><span style="font-size:13px;">Loading your tickets...</span></div>

    <!-- Ticket List -->
    <div class="ticket-list" id="ticketList" style="display:none;"></div>

    <!-- Empty State -->
    <div id="ticketEmpty" class="empty-state" style="display:none;">
        <i class="fa fa-ticket"></i>
        <p>No support tickets yet.</p>
        <button class="btn-new-ticket" onclick="openNewTicketModal()"><i class="fa fa-plus"></i> Create your first ticket</button>
    </div>
</div>

<!-- New Ticket Modal -->
<div class="modal-overlay" id="newTicketModal">
    <div class="modal-box">
        <h2 class="modal-title"><i class="fa fa-plus-circle" style="color:var(--blue);margin-right:6px;"></i>New Support Ticket</h2>
        <p class="modal-subtitle">Describe your issue and we'll get back to you as soon as possible.</p>

        <div class="modal-field">
            <label for="ticketCategory">Category</label>
            <select id="ticketCategory">
                <option value="login_issue">Login Issue</option>
                <option value="payment_query">Payment Query</option>
                <option value="appointment_issue">Appointment Issue</option>
                <option value="account_issue">Account Issue</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="modal-field">
            <label for="ticketSubject">Subject</label>
            <input type="text" id="ticketSubject" placeholder="Brief summary of your issue..." maxlength="200">
        </div>
        <div class="modal-field">
            <label for="ticketMessage">Message</label>
            <textarea id="ticketMessage" placeholder="Please describe your issue in detail..."></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeNewTicketModal()">Cancel</button>
            <button class="btn-primary" id="submitTicketBtn" onclick="submitNewTicket()"><i class="fa fa-paper-plane"></i> Submit Ticket</button>
        </div>
    </div>
</div>

<!-- Ticket Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box" style="max-width:580px;">
        <h2 class="modal-title" id="detailTitle">Ticket Details</h2>
        <div id="detailContent"></div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeDetailModal()">Close</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var allTickets = [];
var currentFilter = '';

function toast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(function(){ t.className = 'toast'; }, 3500);
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function timeAgo(d) {
    if (!d) return '';
    var diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff/86400) + 'd ago';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function catLabel(c) {
    return {login_issue:'Login Issue', payment_query:'Payment Query', appointment_issue:'Appointment Issue', account_issue:'Account Issue', other:'Other'}[c] || c;
}

function loadTickets() {
    fetch(BASE_URL + '/api/support-tickets')
        .then(function(r){ return r.json(); })
        .then(function(d) {
            allTickets = d.tickets || [];
            updateCounts();
            renderTickets();
        })
        .catch(function() {
            document.getElementById('ticketLoading').innerHTML = '<p style="color:var(--muted);">Failed to load tickets. Please refresh.</p>';
        });
}

function updateCounts() {
    var open = 0, progress = 0, resolved = 0;
    allTickets.forEach(function(t) {
        if (t.status === 'open') open++;
        else if (t.status === 'in_progress') progress++;
        else if (t.status === 'resolved') resolved++;
    });
    document.getElementById('countAll').textContent = '(' + allTickets.length + ')';
    document.getElementById('countOpen').textContent = '(' + open + ')';
    document.getElementById('countProgress').textContent = '(' + progress + ')';
    document.getElementById('countResolved').textContent = '(' + resolved + ')';
}

function filterTickets(btn, status) {
    currentFilter = status;
    document.querySelectorAll('.filter-tab').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    renderTickets();
}

function renderTickets() {
    var loading = document.getElementById('ticketLoading');
    var list    = document.getElementById('ticketList');
    var empty   = document.getElementById('ticketEmpty');

    loading.style.display = 'none';

    var filtered = currentFilter ? allTickets.filter(function(t){ return t.status === currentFilter; }) : allTickets;

    if (!filtered.length) {
        list.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    list.style.display = 'flex';

    list.innerHTML = filtered.map(function(t) {
        var replyHTML = '';
        if (t.admin_reply) {
            replyHTML = '<div class="ticket-reply-box">' +
                '<div class="reply-label"><i class="fa fa-reply" style="margin-right:4px;"></i>Admin Reply</div>' +
                '<div class="reply-text">' + escHtml(t.admin_reply) + '</div>' +
                '<div class="reply-meta">' + (t.replied_by_name ? 'by ' + escHtml(t.replied_by_name) + ' · ' : '') + formatDate(t.replied_at) + '</div>' +
            '</div>';
        }

        return '<div class="ticket-card status-' + t.status + '" onclick="openTicketDetail(' + t.id + ')">' +
            '<div class="ticket-header">' +
                '<h3 class="ticket-subject">' + escHtml(t.subject) + '</h3>' +
                '<span class="badge ' + t.status + '">' + t.status.replace('_', ' ') + '</span>' +
            '</div>' +
            '<div class="ticket-meta">' +
                '<span class="cat-badge">' + catLabel(t.category) + '</span>' +
                '<span class="ticket-time"><i class="fa fa-clock" style="margin-right:3px;opacity:.5;"></i>' + timeAgo(t.created_at) + '</span>' +
            '</div>' +
            '<div class="ticket-message">' + escHtml(t.message) + '</div>' +
            replyHTML +
        '</div>';
    }).join('');
}

function openTicketDetail(id) {
    var t = allTickets.find(function(x){ return x.id == id; });
    if (!t) return;

    document.getElementById('detailTitle').innerHTML = '<i class="fa fa-ticket" style="color:var(--blue);margin-right:6px;"></i>Ticket #' + t.id;

    var statusBadge = '<span class="badge ' + t.status + '">' + t.status.replace('_', ' ') + '</span>';

    var html = '<div class="detail-section"><div class="detail-label">Status</div><div class="detail-value">' + statusBadge + '</div></div>' +
        '<div class="detail-section"><div class="detail-label">Category</div><div class="detail-value">' + catLabel(t.category) + '</div></div>' +
        '<div class="detail-section"><div class="detail-label">Subject</div><div class="detail-value" style="font-weight:600;">' + escHtml(t.subject) + '</div></div>' +
        '<div class="detail-section"><div class="detail-label">Submitted</div><div class="detail-value">' + formatDate(t.created_at) + '</div></div>' +
        '<hr class="detail-divider">' +
        '<div class="detail-section"><div class="detail-label">Your Message</div><div class="detail-value" style="white-space:pre-wrap;background:var(--hover-bg,rgba(0,0,0,.03));padding:12px;border-radius:8px;">' + escHtml(t.message) + '</div></div>';

    if (t.admin_reply) {
        html += '<hr class="detail-divider">' +
            '<div class="detail-section">' +
                '<div class="detail-label"><i class="fa fa-shield-halved" style="margin-right:4px;color:var(--blue);"></i>Admin Response</div>' +
                '<div class="detail-value" style="white-space:pre-wrap;background:rgba(42,143,168,.06);padding:14px;border-radius:10px;border:1px solid rgba(42,143,168,.15);">' + escHtml(t.admin_reply) + '</div>' +
                '<div style="font-size:11px;color:var(--muted);margin-top:6px;">' +
                    (t.replied_by_name ? 'Replied by ' + escHtml(t.replied_by_name) : '') +
                    (t.replied_at ? ' · ' + formatDate(t.replied_at) : '') +
                '</div>' +
            '</div>';
    } else {
        html += '<hr class="detail-divider">' +
            '<div class="detail-section"><div class="detail-label">Admin Response</div>' +
            '<div class="detail-value" style="color:var(--muted);font-style:italic;"><i class="fa fa-clock" style="margin-right:4px;"></i>Awaiting admin response...</div></div>';
    }

    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailModal').classList.add('open');
}

function closeDetailModal() { document.getElementById('detailModal').classList.remove('open'); }

// New Ticket Modal
function openNewTicketModal() {
    document.getElementById('ticketSubject').value = '';
    document.getElementById('ticketMessage').value = '';
    document.getElementById('ticketCategory').value = 'other';
    document.getElementById('submitTicketBtn').disabled = false;
    document.getElementById('submitTicketBtn').innerHTML = '<i class="fa fa-paper-plane"></i> Submit Ticket';
    document.getElementById('newTicketModal').classList.add('open');
    setTimeout(function(){ document.getElementById('ticketSubject').focus(); }, 100);
}

function closeNewTicketModal() { document.getElementById('newTicketModal').classList.remove('open'); }

function submitNewTicket() {
    var subject  = document.getElementById('ticketSubject').value.trim();
    var message  = document.getElementById('ticketMessage').value.trim();
    var category = document.getElementById('ticketCategory').value;
    var btn      = document.getElementById('submitTicketBtn');

    if (!subject) { toast('Please enter a subject.', true); document.getElementById('ticketSubject').focus(); return; }
    if (!message) { toast('Please describe your issue.', true); document.getElementById('ticketMessage').focus(); return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';

    fetch(BASE_URL + '/api/support-tickets', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subject: subject, message: message, category: category })
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            closeNewTicketModal();
            toast('Ticket submitted successfully! We\'ll get back to you soon.');
            loadTickets();
        } else {
            toast(d.error || 'Failed to submit ticket.', true);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Ticket';
        }
    })
    .catch(function() {
        toast('Network error. Please try again.', true);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Ticket';
    });
}

// Close modals on overlay click
document.getElementById('newTicketModal').addEventListener('click', function(e){ if (e.target === this) closeNewTicketModal(); });
document.getElementById('detailModal').addEventListener('click', function(e){ if (e.target === this) closeDetailModal(); });
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { closeNewTicketModal(); closeDetailModal(); } });

// Load on page ready
loadTickets();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
