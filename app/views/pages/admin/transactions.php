<?php
$title = 'Transactions';

ob_start();

$extra_styles = <<<CSS
<style>
/* ── Page layout ── */
.admin-wrap {
    padding: 28px 24px 48px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

.page-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    font-size: 13px;
    color: var(--muted);
    margin: 4px 0 0;
}

/* ── Summary cards ── */
.summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}

@media (max-width: 900px) { .summary-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .summary-row { grid-template-columns: 1fr; } }

.summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
}

.summary-card-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 6px;
}

.summary-card-value {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.1;
}

.summary-card-value.green { color: #1a6644; }
[data-theme="dark"] .summary-card-value.green { color: #4ddb96; }

/* ── Filter bar ── */
.filter-bar {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1 1 160px;
    min-width: 140px;
}

.filter-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--muted);
}

.filter-input,
.filter-select {
    height: 38px;
    padding: 0 12px;
    border: 1.5px solid var(--border2);
    border-radius: 8px;
    background: var(--surface2);
    color: var(--text);
    font-size: 13px;
    font-family: inherit;
    outline: none;
    transition: border-color .15s;
}

.filter-input:focus,
.filter-select:focus {
    border-color: var(--blue);
}

.filter-actions {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    flex-shrink: 0;
}

.btn-filter {
    height: 38px;
    padding: 0 20px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}

.btn-filter:hover { background: var(--blue-dark); }

.btn-reset {
    height: 38px;
    padding: 0 16px;
    background: var(--surface2);
    color: var(--muted);
    border: 1.5px solid var(--border2);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s, color .15s;
    white-space: nowrap;
}

.btn-reset:hover { background: var(--border); color: var(--text); }

/* ── Table ── */
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}

.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    gap: 12px;
    flex-wrap: wrap;
}

.table-count {
    font-size: 13px;
    color: var(--muted);
}

.table-count strong {
    color: var(--text);
}

.txn-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.txn-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.txn-table th {
    background: var(--surface2);
    color: var(--muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 10px 14px;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid var(--border);
}

.txn-table td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    vertical-align: middle;
    white-space: nowrap;
}

.txn-table tr:last-child td { border-bottom: none; }

.txn-table tr:hover td { background: var(--surface2); }

.txn-id {
    font-family: monospace;
    font-size: 12px;
    color: var(--muted);
}

.txn-amount {
    font-weight: 700;
    color: var(--text);
}

/* ── State views ── */
.table-loading,
.table-empty {
    text-align: center;
    padding: 56px 24px;
    color: var(--muted);
}

.table-loading-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid var(--border2);
    border-top-color: var(--blue);
    border-radius: 50%;
    animation: spin .7s linear infinite;
    margin: 0 auto 14px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.table-empty-icon {
    font-size: 36px;
    margin-bottom: 12px;
    opacity: .4;
}

.table-empty-text {
    font-size: 14px;
    font-weight: 600;
}

.table-empty-sub {
    font-size: 12px;
    margin-top: 4px;
    opacity: .7;
}
</style>
CSS;

$content = ob_get_clean();
ob_start();
?>

<div class="admin-wrap">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-receipt" style="color:var(--blue);margin-right:8px;"></i>Transactions</h1>
            <p class="page-subtitle">All eSewa payment records · filter by date, status, or name</p>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="summary-row" id="summaryRow">
        <div class="summary-card">
            <div class="summary-card-label">Total</div>
            <div class="summary-card-value" id="sumTotal">—</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Revenue (NPR)</div>
            <div class="summary-card-value green" id="sumRevenue">—</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Paid</div>
            <div class="summary-card-value" id="sumPaid">—</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-label">Failed / Pending</div>
            <div class="summary-card-value" id="sumFailed">—</div>
        </div>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label" for="filterDateFrom">Date From</label>
            <input type="date" id="filterDateFrom" class="filter-input">
        </div>
        <div class="filter-group">
            <label class="filter-label" for="filterDateTo">Date To</label>
            <input type="date" id="filterDateTo" class="filter-input">
        </div>
        <div class="filter-group">
            <label class="filter-label" for="filterStatus">Status</label>
            <select id="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label" for="filterSearch">Search</label>
            <input type="text" id="filterSearch" class="filter-input" placeholder="Patient, doctor, or TXN ID…">
        </div>
        <div class="filter-actions">
            <button class="btn-filter" id="btnApply">
                <i class="fa fa-filter" style="margin-right:6px;"></i>Apply
            </button>
            <button class="btn-reset" id="btnReset">Reset</button>
        </div>
    </div>

    <!-- Table card -->
    <div class="table-card">
        <div class="table-toolbar">
            <span class="table-count" id="tableCount">&nbsp;</span>
        </div>
        <div class="txn-table-wrap">

            <!-- Loading state -->
            <div class="table-loading" id="stateLoading">
                <div class="table-loading-spinner"></div>
                Loading transactions…
            </div>

            <!-- Empty state -->
            <div class="table-empty" id="stateEmpty" style="display:none;">
                <div class="table-empty-icon">🔍</div>
                <div class="table-empty-text">No transactions found</div>
                <div class="table-empty-sub">Try adjusting your filters</div>
            </div>

            <!-- Data table -->
            <table class="txn-table" id="txnTable" style="display:none;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date &amp; Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Appt. Date</th>
                        <th>Reference</th>
                        <th>TXN UUID</th>
                        <th>eSewa Ref</th>
                        <th>Amount (NPR)</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="txnBody"></tbody>
            </table>

        </div>
    </div>

</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
(function () {

    /* ── DOM refs ── */
    var inputFrom   = document.getElementById('filterDateFrom');
    var inputTo     = document.getElementById('filterDateTo');
    var inputStatus = document.getElementById('filterStatus');
    var inputSearch = document.getElementById('filterSearch');
    var btnApply    = document.getElementById('btnApply');
    var btnReset    = document.getElementById('btnReset');

    var stateLoading = document.getElementById('stateLoading');
    var stateEmpty   = document.getElementById('stateEmpty');
    var txnTable     = document.getElementById('txnTable');
    var txnBody      = document.getElementById('txnBody');
    var tableCount   = document.getElementById('tableCount');

    var sumTotal   = document.getElementById('sumTotal');
    var sumRevenue = document.getElementById('sumRevenue');
    var sumPaid    = document.getElementById('sumPaid');
    var sumFailed  = document.getElementById('sumFailed');

    /* ── Helpers ── */
    function fmt(num) {
        return Number(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(str) {
        if (!str) return '—';
        var d = new Date(str);
        return isNaN(d) ? str : d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
    }

    function fmtDateTime(str) {
        if (!str) return '—';
        var d = new Date(str);
        if (isNaN(d)) return str;
        return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
             + ' '
             + d.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' });
    }

    function statusBadge(status) {
        var map = {
            paid:    'badge badge-confirmed',
            failed:  'badge badge-cancelled',
            pending: 'badge badge-pending',
        };
        var cls = map[status] || 'badge';
        return '<span class="' + cls + '">' + (status || '—') + '</span>';
    }

    function esc(str) {
        if (str == null) return '—';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ── Fetch & render ── */
    function load() {
        stateLoading.style.display = '';
        stateEmpty.style.display   = 'none';
        txnTable.style.display     = 'none';
        txnBody.innerHTML          = '';
        tableCount.textContent     = '';

        var params = new URLSearchParams();
        if (inputFrom.value)   params.set('date_from', inputFrom.value);
        if (inputTo.value)     params.set('date_to',   inputTo.value);
        if (inputStatus.value) params.set('status',    inputStatus.value);
        if (inputSearch.value.trim()) params.set('search', inputSearch.value.trim());

        fetch(BASE_URL + '/admin/api/transactions?' + params.toString(), {
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            stateLoading.style.display = 'none';

            var s = data.summary || {};
            sumTotal.textContent   = Number(s.total_count  || 0).toLocaleString();
            sumRevenue.textContent = 'Rs. ' + fmt(s.total_revenue || 0);
            sumPaid.textContent    = Number(s.paid_count   || 0).toLocaleString();
            sumFailed.textContent  = Number((s.failed_count || 0) + (s.pending_count || 0)).toLocaleString();

            var rows = data.transactions || [];

            tableCount.innerHTML = 'Showing <strong>' + rows.length + '</strong> transaction' + (rows.length !== 1 ? 's' : '');

            if (rows.length === 0) {
                stateEmpty.style.display = '';
                return;
            }

            var html = '';
            rows.forEach(function (t, i) {
                html += '<tr>'
                    + '<td class="txn-id">' + (i + 1) + '</td>'
                    + '<td>' + fmtDateTime(t.paid_at || t.created_at) + '</td>'
                    + '<td>' + esc(t.patient_name) + '</td>'
                    + '<td>' + esc(t.doctor_name)  + '</td>'
                    + '<td>' + fmtDate(t.appointment_date) + '</td>'
                    + '<td><span class="txn-id">' + esc(t.reference_number) + '</span></td>'
                    + '<td><span class="txn-id">' + esc(t.esewa_txn_uuid)   + '</span></td>'
                    + '<td><span class="txn-id">' + esc(t.esewa_ref_id)     + '</span></td>'
                    + '<td class="txn-amount">Rs. ' + fmt(t.total_amount)   + '</td>'
                    + '<td>' + esc(t.payment_method) + '</td>'
                    + '<td>' + statusBadge(t.status) + '</td>'
                    + '</tr>';
            });

            txnBody.innerHTML      = html;
            txnTable.style.display = '';
        })
        .catch(function () {
            stateLoading.style.display = 'none';
            stateEmpty.style.display   = '';
            tableCount.textContent     = 'Failed to load — please try again.';
        });
    }

    /* ── Events ── */
    btnApply.addEventListener('click', load);

    btnReset.addEventListener('click', function () {
        inputFrom.value   = '';
        inputTo.value     = '';
        inputStatus.value = '';
        inputSearch.value = '';
        load();
    });

    inputSearch.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') load();
    });

    /* ── Initial load ── */
    load();

})();
</script>
JS;

// Render using the app layout
include BASE_PATH . '/app/views/layouts/app.php';