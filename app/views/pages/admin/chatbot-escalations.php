<?php
$title = 'Chatbot Escalations';
ob_start();

$extra_styles = <<<CSS
<style>
.admin-wrap {
    padding: 28px 24px 60px;
    max-width: 1100px;
    margin: 0 auto;
}
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.page-title   { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle{ font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* Summary pills */
.esc-counts {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.esc-count-pill {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color .15s, background .15s;
    user-select: none;
}
.esc-count-pill.open     { background: #fef2f2; color: #b91c1c; }
.esc-count-pill.resolved { background: #f0fdf4; color: #166534; }
.esc-count-pill.dismissed{ background: var(--bg); color: var(--muted); }
.esc-count-pill.active   { border-color: currentColor; }

[data-theme="dark"] .esc-count-pill.open      { background: rgba(239,68,68,.12); }
[data-theme="dark"] .esc-count-pill.resolved  { background: rgba(34,197,94,.1); }
[data-theme="dark"] .esc-count-pill.dismissed { background: var(--surface2); }

/* Card list */
.esc-list { display: flex; flex-direction: column; gap: 12px; }

.esc-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow .15s;
}
.esc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }

.esc-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    cursor: pointer;
    user-select: none;
}
.esc-patient-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--blue, #2563eb);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px;
    flex-shrink: 0;
}
.esc-meta { flex: 1; min-width: 0; }
.esc-patient-name { font-size: 14px; font-weight: 700; color: var(--text); }
.esc-query {
    font-size: 13px;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 420px;
    margin-top: 2px;
}
.esc-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.esc-time  { font-size: 11px; color: var(--hint, #9ca3af); }

.badge-open      { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.badge-resolved  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.badge-dismissed { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }
[data-theme="dark"] .badge-open      { background: rgba(239,68,68,.14); color: #f87171; border-color: rgba(239,68,68,.28); }
[data-theme="dark"] .badge-resolved  { background: rgba(34,197,94,.10); color: #4ade80; border-color: rgba(34,197,94,.25); }
[data-theme="dark"] .badge-dismissed { background: var(--surface2); border-color: var(--border2); }

.esc-chevron { font-size: 12px; color: var(--muted); transition: transform .2s; }
.esc-card.expanded .esc-chevron { transform: rotate(180deg); }

/* Expanded body */
.esc-card-body {
    border-top: 1px solid var(--border);
    display: none;
    padding: 16px 18px;
    gap: 16px;
    flex-direction: column;
}
.esc-card.expanded .esc-card-body { display: flex; }

/* Conversation thread */
.esc-thread { display: flex; flex-direction: column; gap: 8px; }
.esc-thread-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--muted); margin-bottom: 4px;
}
.esc-bubble-wrap { display: flex; }
.esc-bubble-wrap.user  { justify-content: flex-end; }
.esc-bubble-wrap.bot   { justify-content: flex-start; }
.esc-bubble {
    max-width: 78%;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}
.esc-bubble-wrap.user .esc-bubble {
    background: var(--blue, #2563eb);
    color: #fff;
    border-bottom-right-radius: 3px;
}
.esc-bubble-wrap.bot .esc-bubble {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    border-bottom-left-radius: 3px;
}
[data-theme="dark"] .esc-bubble-wrap.bot .esc-bubble {
    background: var(--surface2);
}

/* Admin actions */
.esc-actions-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}
.esc-note-input {
    flex: 1;
    min-width: 160px;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 13px;
    font-family: inherit;
    background: var(--bg);
    color: var(--text);
    outline: none;
    resize: none;
    transition: border-color .15s;
}
.esc-note-input:focus { border-color: var(--blue, #2563eb); }
[data-theme="dark"] .esc-note-input { background: var(--surface2); border-color: var(--border2); }

.btn-resolve  { background: #f0fdf4; color: #166534; border: 1.5px solid #86efac; padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background .14s; }
.btn-resolve:hover { background: #dcfce7; }
.btn-dismiss  { background: var(--bg); color: var(--muted); border: 1.5px solid var(--border); padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .14s; }
.btn-dismiss:hover { background: var(--border); }
.btn-reopen   { background: #fef2f2; color: #b91c1c; border: 1.5px solid #fca5a5; padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .14s; }
.btn-reopen:hover { background: #fee2e2; }

[data-theme="dark"] .btn-resolve  { background: rgba(34,197,94,.1);  color: #4ade80; border-color: rgba(34,197,94,.3); }
[data-theme="dark"] .btn-dismiss  { background: var(--surface2); border-color: var(--border2); }
[data-theme="dark"] .btn-reopen   { background: rgba(239,68,68,.12); color: #f87171; border-color: rgba(239,68,68,.3); }

/* Note saved display */
.esc-saved-note {
    font-size: 12px; color: var(--muted);
    padding: 5px 10px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-style: italic;
}

/* Empty state */
.esc-empty {
    text-align: center;
    padding: 60px 24px;
    color: var(--muted);
}
.esc-empty i   { font-size: 40px; margin-bottom: 12px; opacity: .4; display: block; }
.esc-empty p   { font-size: 15px; margin: 0; }

/* Loading */
.esc-loading { text-align: center; padding: 48px; color: var(--muted); font-size: 14px; }
</style>
CSS;
?>

<div class="admin-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-robot" style="color:var(--blue);margin-right:9px;"></i>Chatbot Escalations</h1>
            <p class="page-subtitle">Conversations the bot couldn't resolve — review and follow up with patients.</p>
        </div>
    </div>

    <!-- Status filter pills -->
    <div class="esc-counts" id="escCounts">
        <button class="esc-count-pill open active"      onclick="filterEscalations('open')"      id="pill-open">Open: —</button>
        <button class="esc-count-pill resolved"         onclick="filterEscalations('resolved')"  id="pill-resolved">Resolved: —</button>
        <button class="esc-count-pill dismissed"        onclick="filterEscalations('dismissed')" id="pill-dismissed">Dismissed: —</button>
        <button class="esc-count-pill" style="background:var(--bg);color:var(--text);" onclick="filterEscalations('')" id="pill-all">All</button>
    </div>

    <!-- Escalation list -->
    <div class="esc-list" id="escList">
        <div class="esc-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    </div>
</div>

<script>
var _allEscalations = [];
var _activeFilter   = 'open';

function filterEscalations(status) {
    _activeFilter = status;
    // Update active pill
    ['open','resolved','dismissed','all'].forEach(function(s) {
        var pill = document.getElementById('pill-' + (s || 'all'));
        if (pill) pill.classList.toggle('active', (s === '' ? '' : s) === status || (status === '' && s === 'all'));
    });
    renderList(_allEscalations.filter(function(e) {
        return status === '' || e.status === status;
    }));
}

function fmtTime(str) {
    if (!str) return '—';
    var d = new Date(str.replace(' ', 'T'));
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        + ' · ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function initials(name) {
    return String(name || 'P').split(' ').map(function(w){ return w[0]; }).slice(0,2).join('').toUpperCase();
}

function renderList(items) {
    var list = document.getElementById('escList');
    if (!items.length) {
        list.innerHTML = '<div class="esc-empty"><i class="fa fa-inbox"></i><p>No escalations in this category.</p></div>';
        return;
    }

    list.innerHTML = items.map(function(e) {
        var statusClass = 'badge badge-' + e.status;
        var note = e.admin_note ? '<div class="esc-saved-note"><i class="fa fa-note-sticky" style="margin-right:5px;"></i>' + escHtml(e.admin_note) + '</div>' : '';

        // Build conversation thread HTML
        var threadHtml = (e.conversation || []).map(function(m) {
            return '<div class="esc-bubble-wrap ' + escHtml(m.role) + '">'
                + '<div class="esc-bubble">' + escHtml(m.message) + '</div>'
                + '</div>';
        }).join('');
        if (!threadHtml) threadHtml = '<p style="color:var(--muted);font-size:13px;">No conversation history.</p>';

        // Action buttons depending on status
        var actionBtns = '';
        if (e.status === 'open') {
            actionBtns = '<button class="btn-resolve" onclick="updateEscalation(' + e.id + ', \'resolved\', this)"><i class="fa fa-check" style="margin-right:5px;"></i>Mark Resolved</button>'
                       + '<button class="btn-dismiss" onclick="updateEscalation(' + e.id + ', \'dismissed\', this)">Dismiss</button>';
        } else {
            actionBtns = '<button class="btn-reopen" onclick="updateEscalation(' + e.id + ', \'open\', this)"><i class="fa fa-rotate-left" style="margin-right:5px;"></i>Reopen</button>';
        }

        return '<div class="esc-card" id="esc-card-' + e.id + '">'
            + '<div class="esc-card-head" onclick="toggleEscCard(' + e.id + ')">'
            + '<div class="esc-patient-avatar">' + escHtml(initials(e.patient_name)) + '</div>'
            + '<div class="esc-meta">'
            + '<div class="esc-patient-name">' + escHtml(e.patient_name || 'Patient #' + e.patient_id) + '</div>'
            + '<div class="esc-query">' + escHtml(e.user_query) + '</div>'
            + '</div>'
            + '<div class="esc-right">'
            + '<span class="esc-time">' + fmtTime(e.created_at) + '</span>'
            + '<span class="' + statusClass + '" style="font-size:11px;">' + escHtml(e.status) + '</span>'
            + '<i class="fa fa-chevron-down esc-chevron"></i>'
            + '</div></div>'
            + '<div class="esc-card-body">'
            + '<div class="esc-thread"><div class="esc-thread-title"><i class="fa fa-comments" style="margin-right:5px;"></i>Conversation</div>' + threadHtml + '</div>'
            + (note ? note : '')
            + '<div class="esc-actions-row">'
            + '<textarea class="esc-note-input" id="note-' + e.id + '" rows="2" placeholder="Add an admin note (optional)…">' + escHtml(e.admin_note || '') + '</textarea>'
            + actionBtns
            + '</div>'
            + '</div>'
            + '</div>';
    }).join('');
}

function toggleEscCard(id) {
    var card = document.getElementById('esc-card-' + id);
    if (card) card.classList.toggle('expanded');
}

function updateEscalation(id, status, btn) {
    var noteEl = document.getElementById('note-' + id);
    var note   = noteEl ? noteEl.value.trim() : '';
    var orig   = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving…';

    fetch(BASE_URL + '/admin/api/chatbot/escalations/' + id, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: status, admin_note: note })
    })
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (res.success) {
            loadEscalations(); // refresh
        } else {
            btn.disabled = false; btn.textContent = orig;
            alert('Failed to update. Please try again.');
        }
    })
    .catch(function(){
        btn.disabled = false; btn.textContent = orig;
        alert('Network error.');
    });
}

function loadEscalations() {
    fetch(BASE_URL + '/admin/api/chatbot/escalations')
    .then(function(r){ return r.json(); })
    .then(function(res) {
        if (!res.success) return;
        _allEscalations = res.escalations || [];

        // Update pill counts
        document.getElementById('pill-open').textContent      = 'Open: '      + res.counts.open;
        document.getElementById('pill-resolved').textContent  = 'Resolved: '  + res.counts.resolved;
        document.getElementById('pill-dismissed').textContent = 'Dismissed: ' + res.counts.dismissed;
        var total = res.counts.open + res.counts.resolved + res.counts.dismissed;
        document.getElementById('pill-all').textContent       = 'All (' + total + ')';

        filterEscalations(_activeFilter);
    })
    .catch(function(){
        document.getElementById('escList').innerHTML = '<div class="esc-empty"><i class="fa fa-triangle-exclamation"></i><p>Failed to load escalations. Please refresh.</p></div>';
    });
}

document.addEventListener('DOMContentLoaded', loadEscalations);
</script>

<?php
$content = ob_get_clean();
// Use admin layout if it exists, otherwise fall back to patient layout
$adminLayout = BASE_PATH . '/app/views/layouts/app-admin.php';
$fallback    = BASE_PATH . '/app/views/layouts/app.php';
include file_exists($adminLayout) ? $adminLayout : $fallback;