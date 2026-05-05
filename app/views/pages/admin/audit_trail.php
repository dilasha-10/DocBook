<?php
$title = 'Audit Trail';

ob_start();

$extra_styles = <<<CSS
<style>
/* ── Page wrap ── */
.audit-wrap {
    padding: 28px 24px 60px;
    max-width: 1200px;
    margin: 0 auto;
}
.page-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 24px;
}
.page-title    { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* ── Summary strip ── */
.audit-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
@media (max-width: 860px) { .audit-summary { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .audit-summary { grid-template-columns: 1fr; } }
.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
}
.summary-card-label {
    font-size: 11px; font-weight: 700;
    letter-spacing: .07em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 6px;
}
.summary-card-value {
    font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;
}
.summary-card-sub {
    font-size: 12px; color: var(--muted); margin-top: 4px;
}

/* ── Filter bar ── */
.filter-bar {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 18px;
    display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;
}
.filter-group { display: flex; flex-direction: column; gap: 5px; min-width: 140px; }
.filter-group.grow { flex: 1 1 200px; }
.filter-label {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--muted);
}
.filter-input, .filter-select {
    height: 36px; border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg); color: var(--text);
    font-size: 13px; font-family: inherit;
    padding: 0 10px; outline: none;
    transition: border-color .15s;
}
.filter-input:focus, .filter-select:focus { border-color: var(--primary); }
.filter-actions { display: flex; gap: 8px; align-items: flex-end; }

/* ── Buttons ── */
.btn {
    height: 36px; padding: 0 16px;
    border-radius: 8px; border: none; cursor: pointer;
    font-size: 13px; font-weight: 700; font-family: inherit;
    display: inline-flex; align-items: center; gap: 6px;
    transition: opacity .15s, background .15s;
}
.btn:disabled { opacity: .5; cursor: not-allowed; }
.btn-primary  { background: var(--primary); color: #fff; }
.btn-primary:hover:not(:disabled) { opacity: .88; }
.btn-ghost    { background: var(--surface); color: var(--text); border: 1px solid var(--border); }
.btn-ghost:hover:not(:disabled) { background: var(--hover-bg, rgba(0,0,0,.05)); }
.btn-export   { background: #16a34a; color: #fff; }
.btn-export:hover:not(:disabled) { opacity: .88; }

/* ── Table card ── */
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
}
.table-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
}
.table-card-title { font-size: 14px; font-weight: 800; color: var(--text); }
.table-count      { font-size: 12px; color: var(--muted); }

.table-scroll { overflow-x: auto; }
table.audit-table {
    width: 100%; border-collapse: collapse;
    font-size: 13px; color: var(--text);
}
.audit-table th {
    padding: 10px 14px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--muted); text-align: left;
    background: var(--hover-bg, rgba(0,0,0,.03));
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.audit-table td {
    padding: 11px 14px;
    border-bottom: 1px solid var(--border);
    vertical-align: top;
}
.audit-table tr:last-child td { border-bottom: none; }
.audit-table tr:hover td { background: var(--hover-bg, rgba(0,0,0,.025)); }

/* ── Action badge ── */
.action-badge {
    display: inline-block;
    padding: 2px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700; letter-spacing: .04em;
    white-space: nowrap;
}
.badge-auth         { background: #dbeafe; color: #1d4ed8; }
.badge-notification { background: #fef9c3; color: #854d0e; }
.badge-transaction  { background: #dcfce7; color: #166534; }
.badge-chatbot      { background: #f3e8ff; color: #7e22ce; }
.badge-user         { background: #ffe4e6; color: #be123c; }
.badge-other        { background: var(--border); color: var(--muted); }

/* ── Action label ── */
.action-label { font-weight: 700; font-size: 12px; letter-spacing: .03em; }

/* ── Detail text ── */
.detail-text { color: var(--muted); font-size: 12px; line-height: 1.4; max-width: 320px; }

/* ── Timestamp ── */
.ts { color: var(--muted); font-size: 12px; white-space: nowrap; }

/* ── User cell ── */
.user-cell-name { font-weight: 700; }
.user-cell-role { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* ── Empty / loading ── */
.table-state {
    text-align: center; padding: 48px 20px;
    color: var(--muted); font-size: 14px;
}
.table-state i { font-size: 28px; display: block; margin-bottom: 10px; opacity: .4; }

/* ── Pagination ── */
.pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 20px; border-top: 1px solid var(--border);
    flex-wrap: wrap; gap: 10px;
}
.pagination-info { font-size: 13px; color: var(--muted); }
.pagination-btns { display: flex; gap: 6px; }
.page-btn {
    height: 32px; min-width: 32px; padding: 0 10px;
    border-radius: 7px; border: 1px solid var(--border);
    background: var(--surface); color: var(--text);
    font-size: 13px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: background .12s, border-color .12s;
}
.page-btn:hover:not(:disabled) { background: var(--hover-bg, rgba(0,0,0,.05)); }
.page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn:disabled { opacity: .35; cursor: default; }
</style>
CSS;

$content = ob_get_clean();
ob_start();
?>

<div class="audit-wrap">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fa fa-shield-halved" style="color:var(--primary);margin-right:8px;"></i>
                Audit Trail
            </h1>
            <p class="page-subtitle">Full log of every admin action, login, and logout event across the platform</p>
        </div>
        <button class="btn btn-export" id="btnExport">
            <i class="fa fa-file-csv"></i> Export CSV
        </button>
    </div>

    <!-- Summary cards -->
    <div class="audit-summary" id="summaryStrip">
        <div class="summary-card">
            <div class="summary-card-label">Total Events</div>
            <div class="summary-card-value" id="sumTotal">—</div>
            <div class="summary-card-sub">in current filter</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Auth Events</div>
            <div class="summary-card-value" id="sumAuth">—</div>
            <div class="summary-card-sub">logins &amp; logouts</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Notification Events</div>
            <div class="summary-card-value" id="sumNotif">—</div>
            <div class="summary-card-sub">broadcasts &amp; targeted</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Other Admin Actions</div>
            <div class="summary-card-value" id="sumOther">—</div>
            <div class="summary-card-sub">transactions, chatbot…</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">From Date</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>
        <div class="filter-group">
            <label class="filter-label">To Date</label>
            <input type="date" class="filter-input" id="filterDateTo">
        </div>
        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="filterGroup">
                <option value="">All Categories</option>
                <option value="auth">Auth (login / logout)</option>
                <option value="notification">Notifications</option>
                <option value="transaction">Transactions</option>
                <option value="chatbot">Chatbot</option>
                <option value="user">User</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Action Type</label>
            <select class="filter-select" id="filterAction">
                <option value="">All Actions</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">User</label>
            <select class="filter-select" id="filterUser">
                <option value="">All Users</option>
            </select>
        </div>
        <div class="filter-group grow">
            <label class="filter-label">Search</label>
            <input type="text" class="filter-input" id="filterSearch"
                   placeholder="Name, action, detail, IP…">
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary" id="btnApply">
                <i class="fa fa-magnifying-glass"></i> Apply
            </button>
            <button class="btn btn-ghost" id="btnReset">Reset</button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title"><i class="fa fa-list-ul" style="margin-right:7px;opacity:.6;"></i>Event Log</span>
            <span class="table-count" id="tableCount"></span>
        </div>

        <div class="table-scroll">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Category</th>
                        <th>Action</th>
                        <th>Detail</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="auditTbody">
                    <tr>
                        <td colspan="6" class="table-state">
                            <i class="fa fa-spinner fa-spin"></i>
                            Loading events…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="paginationBar" style="display:none;">
            <span class="pagination-info" id="pageInfo"></span>
            <div class="pagination-btns" id="pageBtns"></div>
        </div>
    </div>

</div><!-- /.audit-wrap -->

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
(function () {

    /* ── State ── */
    var state = { page: 1, perPage: 50, total: 0, totalPages: 1 };

    /* ── DOM refs ── */
    var filterFrom   = document.getElementById('filterDateFrom');
    var filterTo     = document.getElementById('filterDateTo');
    var filterGroup  = document.getElementById('filterGroup');
    var filterAction = document.getElementById('filterAction');
    var filterUser   = document.getElementById('filterUser');
    var filterSearch = document.getElementById('filterSearch');
    var tbody        = document.getElementById('auditTbody');
    var tableCount   = document.getElementById('tableCount');
    var sumTotal     = document.getElementById('sumTotal');
    var sumAuth      = document.getElementById('sumAuth');
    var sumNotif     = document.getElementById('sumNotif');
    var sumOther     = document.getElementById('sumOther');
    var paginationBar= document.getElementById('paginationBar');
    var pageInfo     = document.getElementById('pageInfo');
    var pageBtns     = document.getElementById('pageBtns');

    /* ── Badge styling ── */
    var groupClass = {
        auth:         'badge-auth',
        notification: 'badge-notification',
        transaction:  'badge-transaction',
        chatbot:      'badge-chatbot',
        user:         'badge-user',
    };

    /* ── Load filter options once ── */
    fetch(BASE_URL + '/admin/api/audit-trail/filters')
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data.success) return;

            // Action dropdown
            (data.actions || []).forEach(function(a) {
                var opt = document.createElement('option');
                opt.value = a;
                opt.textContent = a.replace(/_/g, ' ');
                filterAction.appendChild(opt);
            });

            // User dropdown
            (data.users || []).forEach(function(u) {
                var opt = document.createElement('option');
                opt.value = u.user_id;
                opt.textContent = u.user_name + ' (' + (u.user_role || '?') + ')';
                filterUser.appendChild(opt);
            });
        });

    /* ── Build URL params ── */
    function buildParams(page) {
        var p = new URLSearchParams();
        if (filterFrom.value)   p.set('date_from',    filterFrom.value);
        if (filterTo.value)     p.set('date_to',      filterTo.value);
        if (filterGroup.value)  p.set('action_group', filterGroup.value);
        if (filterAction.value) p.set('action',       filterAction.value);
        if (filterUser.value)   p.set('user_id',      filterUser.value);
        if (filterSearch.value.trim()) p.set('search', filterSearch.value.trim());
        p.set('page',     page     || state.page);
        p.set('per_page', state.perPage);
        return p;
    }

    /* ── Load data ── */
    function load(page) {
        page = page || 1;
        state.page = page;

        tbody.innerHTML = '<tr><td colspan="6" class="table-state"><i class="fa fa-spinner fa-spin"></i> Loading events…</td></tr>';
        paginationBar.style.display = 'none';
        tableCount.textContent = '';

        fetch(BASE_URL + '/admin/api/audit-trail?' + buildParams(page).toString())
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    tbody.innerHTML = '<tr><td colspan="6" class="table-state"><i class="fa fa-triangle-exclamation"></i> Failed to load logs.</td></tr>';
                    return;
                }

                state.total      = data.total      || 0;
                state.totalPages = data.total_pages || 1;
                state.page       = data.page        || 1;

                // Summary strip (count within current result set)
                var authCount  = 0, notifCount = 0, otherCount = 0;
                (data.logs || []).forEach(function(l){
                    if      (l.action_group === 'auth')         authCount++;
                    else if (l.action_group === 'notification') notifCount++;
                    else                                        otherCount++;
                });
                sumTotal.textContent = state.total;
                sumAuth.textContent  = authCount;
                sumNotif.textContent = notifCount;
                sumOther.textContent = otherCount;
                tableCount.textContent = state.total + ' event' + (state.total !== 1 ? 's' : '');

                if ((data.logs || []).length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="table-state"><i class="fa fa-inbox"></i> No events found for these filters.</td></tr>';
                    return;
                }

                // Render rows
                var html = '';
                (data.logs || []).forEach(function(log) {
                    var gClass = groupClass[log.action_group] || 'badge-other';
                    var ts = log.created_at
                        ? new Date(log.created_at.replace(' ', 'T')).toLocaleString('en-GB', {
                            day:'2-digit', month:'short', year:'numeric',
                            hour:'2-digit', minute:'2-digit', second:'2-digit'
                          })
                        : '—';
                    var role = log.user_role
                        ? log.user_role.replace(/_/g, ' ')
                        : '';
                    html += '<tr>';
                    html += '<td class="ts">' + esc(ts) + '</td>';
                    html += '<td><div class="user-cell-name">' + esc(log.user_name || 'System') + '</div>'
                          + (role ? '<div class="user-cell-role">' + esc(role) + '</div>' : '') + '</td>';
                    html += '<td><span class="action-badge ' + gClass + '">' + esc(log.action_group) + '</span></td>';
                    html += '<td class="action-label">' + esc(log.action.replace(/_/g, ' ')) + '</td>';
                    html += '<td><div class="detail-text">' + esc(log.detail || '—') + '</div></td>';
                    html += '<td class="ts">' + esc(log.ip_address || '—') + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;

                // Pagination
                renderPagination();
            })
            .catch(function() {
                tbody.innerHTML = '<tr><td colspan="6" class="table-state"><i class="fa fa-wifi"></i> Network error. Please try again.</td></tr>';
            });
    }

    /* ── Pagination ── */
    function renderPagination() {
        if (state.totalPages <= 1) { paginationBar.style.display = 'none'; return; }

        var from = (state.page - 1) * state.perPage + 1;
        var to   = Math.min(state.page * state.perPage, state.total);
        pageInfo.textContent = 'Showing ' + from + '–' + to + ' of ' + state.total;

        // Build page buttons (max 7 visible)
        var pages = [];
        var p = state.page, tp = state.totalPages;
        if (tp <= 7) {
            for (var i = 1; i <= tp; i++) pages.push(i);
        } else {
            pages = [1];
            if (p > 3) pages.push('…');
            for (var j = Math.max(2, p-1); j <= Math.min(tp-1, p+1); j++) pages.push(j);
            if (p < tp - 2) pages.push('…');
            pages.push(tp);
        }

        var html = '<button class="page-btn" id="prevPage" ' + (p === 1  ? 'disabled' : '') + '>'
                 + '<i class="fa fa-chevron-left"></i></button>';
        pages.forEach(function(pg) {
            if (pg === '…') {
                html += '<button class="page-btn" disabled>…</button>';
            } else {
                html += '<button class="page-btn' + (pg === p ? ' active' : '') + '" data-pg="' + pg + '">' + pg + '</button>';
            }
        });
        html += '<button class="page-btn" id="nextPage" ' + (p === tp ? 'disabled' : '') + '>'
              + '<i class="fa fa-chevron-right"></i></button>';

        pageBtns.innerHTML = html;
        paginationBar.style.display = 'flex';

        pageBtns.querySelectorAll('[data-pg]').forEach(function(btn) {
            btn.addEventListener('click', function() { load(parseInt(this.dataset.pg)); });
        });
        var prev = document.getElementById('prevPage');
        var next = document.getElementById('nextPage');
        if (prev) prev.addEventListener('click', function() { if (state.page > 1) load(state.page - 1); });
        if (next) next.addEventListener('click', function() { if (state.page < state.totalPages) load(state.page + 1); });
    }

    /* ── Export CSV ── */
    document.getElementById('btnExport').addEventListener('click', function() {
        var url = BASE_URL + '/admin/api/audit-trail/export?' + buildParams(1).toString();
        window.location.href = url;
    });

    /* ── Filter controls ── */
    document.getElementById('btnApply').addEventListener('click', function() { load(1); });
    document.getElementById('btnReset').addEventListener('click', function() {
        filterFrom.value   = '';
        filterTo.value     = '';
        filterGroup.value  = '';
        filterAction.value = '';
        filterUser.value   = '';
        filterSearch.value = '';
        load(1);
    });
    filterSearch.addEventListener('keydown', function(e) { if (e.key === 'Enter') load(1); });

    /* ── HTML escape helper ── */
    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;');
    }

    /* ── Initial load ── */
    load(1);

})();
</script>
JS;

include BASE_PATH . '/app/views/layouts/app.php';