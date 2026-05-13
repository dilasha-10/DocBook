<?php
$title = 'Appointment Auditing';
ob_start();
$extra_styles = <<<CSS
<style>
.appt-wrap { padding: 30px 24px 60px; max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 24px; }
.page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

.stat-row { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.stat-card { flex: 1; min-width: 120px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px 18px; text-align: center; }
.stat-val { font-size: 28px; font-weight: 800; color: var(--text); }
.stat-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
.stat-card.confirmed .stat-val { color: #2563eb; }
.stat-card.pending .stat-val { color: #ea580c; }
.stat-card.cancelled .stat-val { color: #dc2626; }
.stat-card.completed .stat-val { color: #16a34a; }

.filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.filter-bar input, .filter-bar select { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px; font-family: inherit; background: var(--surface); color: var(--text); }
.filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--blue); }

.table-wrap { overflow-x: auto; }
.appt-table { width: 100%; border-collapse: collapse; }
.appt-table th { text-align: left; padding: 10px 12px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid var(--border); white-space: nowrap; }
.appt-table td { padding: 12px; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border); }
.appt-table tr:hover td { background: var(--hover-bg, rgba(0,0,0,.02)); }

.badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.badge.Confirmed { background: #dbeafe; color: #1d4ed8; }
.badge.Pending { background: #fff7ed; color: #c2410c; }
.badge.Cancelled { background: #fef2f2; color: #991b1b; }
.badge.Completed { background: #f0fdf4; color: #15803d; }
.badge.Rescheduled { background: #f5f3ff; color: #6d28d9; }

.btn-cancel { background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 4px; }
.btn-cancel:hover { background: #dc2626; }
.btn-cancel:disabled { opacity: .4; cursor: not-allowed; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: 16px; padding: 28px; width: 90%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.modal-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 8px; }
.modal-subtitle { font-size: 13px; color: var(--muted); margin-bottom: 18px; }
.modal-field { margin-bottom: 14px; }
.modal-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.modal-field textarea { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; background: var(--surface); color: var(--text); min-height: 100px; resize: vertical; }
.modal-field textarea:focus { outline: none; border-color: var(--blue); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; }
.warn-notice { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #991b1b; margin-bottom: 14px; display: flex; align-items: flex-start; gap: 8px; line-height: 1.5; }
.warn-notice i { margin-top: 2px; }

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

<div class="appt-wrap">
    <div class="page-header">
        <h1 class="page-title"><i class="fa fa-calendar-check" style="color:var(--blue);margin-right:8px;"></i>Appointment Auditing</h1>
        <p class="page-subtitle">View the appointment schedule, filter by doctor/date/status, and cancel bookings when needed</p>
    </div>

    <div class="stat-row" id="statsRow">
        <div class="stat-card"><div class="stat-val" id="statTotal">–</div><div class="stat-label">Total</div></div>
        <div class="stat-card confirmed"><div class="stat-val" id="statConfirmed">–</div><div class="stat-label">Confirmed</div></div>
        <div class="stat-card pending"><div class="stat-val" id="statPending">–</div><div class="stat-label">Pending</div></div>
        <div class="stat-card completed"><div class="stat-val" id="statCompleted">–</div><div class="stat-label">Completed</div></div>
        <div class="stat-card cancelled"><div class="stat-val" id="statCancelled">–</div><div class="stat-label">Cancelled</div></div>
    </div>

    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="Search patient, doctor, ref #...">
        <select id="doctorFilter"><option value="">All Doctors</option></select>
        <input type="date" id="dateFrom" title="From date">
        <input type="date" id="dateTo" title="To date">
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Confirmed">Confirmed</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Rescheduled">Rescheduled</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="appt-table" id="apptTable" style="display:none;">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="apptBody"></tbody>
        </table>
    </div>
    <div id="apptEmpty" class="empty-state" style="display:none;">
        <i class="fa fa-calendar-check"></i>
        <p>No appointments match your filters.</p>
    </div>
    <div id="apptLoading" style="text-align:center;padding:40px;color:var(--muted);"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
</div>

<!-- Cancel Modal -->
<div class="modal-overlay" id="cancelModal">
    <div class="modal-box">
        <h2 class="modal-title">Cancel Appointment</h2>
        <div class="modal-subtitle" id="cancelInfo"></div>
        <div class="warn-notice">
            <i class="fa fa-triangle-exclamation"></i>
            <div>This action is irreversible. The patient and doctor will both be notified of the cancellation with the reason you provide.</div>
        </div>
        <input type="hidden" id="cancelApptId">
        <div class="modal-field">
            <label for="cancelReason">Cancellation Reason (required)</label>
            <textarea id="cancelReason" placeholder="e.g. Doctor Emergency, Clinic Maintenance..."></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeCancelModal()">Go Back</button>
            <button class="btn-cancel" onclick="confirmCancel()" style="padding:10px 20px;border-radius:10px;font-size:13px;"><i class="fa fa-ban"></i> Cancel Appointment</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var allAppts = [];

function toast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(function(){ t.className = 'toast'; }, 3000);
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function fmtDate(d) {
    var dt = new Date(d);
    return dt.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
}
function fmtTime(t) {
    var parts = t.split(':');
    var h = parseInt(parts[0]); var m = parts[1];
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return h + ':' + m + ' ' + ampm;
}

// Load doctor filter options
fetch(BASE_URL + '/admin/api/appointments/doctors')
    .then(function(r){ return r.json(); })
    .then(function(d) {
        var sel = document.getElementById('doctorFilter');
        (d.doctors || []).forEach(function(doc) {
            var opt = document.createElement('option');
            opt.value = doc.id;
            opt.textContent = doc.name + ' (' + doc.specialty + ')';
            sel.appendChild(opt);
        });
    });

function loadAppointments() {
    var params = new URLSearchParams();
    var search    = document.getElementById('searchInput').value;
    var doctorId  = document.getElementById('doctorFilter').value;
    var dateFrom  = document.getElementById('dateFrom').value;
    var dateTo    = document.getElementById('dateTo').value;
    var status    = document.getElementById('statusFilter').value;

    if (search)   params.set('search', search);
    if (doctorId) params.set('doctor_id', doctorId);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);
    if (status)   params.set('status', status);

    fetch(BASE_URL + '/admin/api/appointments?' + params.toString())
        .then(function(r){ return r.json(); })
        .then(function(d) {
            allAppts = d.appointments || [];
            var stats = d.stats || {};
            document.getElementById('statTotal').textContent     = stats.total || 0;
            document.getElementById('statConfirmed').textContent  = stats.confirmed || 0;
            document.getElementById('statPending').textContent    = stats.pending || 0;
            document.getElementById('statCompleted').textContent  = stats.completed || 0;
            document.getElementById('statCancelled').textContent  = stats.cancelled || 0;
            renderAppts();
        });
}

function renderAppts() {
    var loading = document.getElementById('apptLoading');
    var table   = document.getElementById('apptTable');
    var empty   = document.getElementById('apptEmpty');
    var body    = document.getElementById('apptBody');
    loading.style.display = 'none';

    if (!allAppts.length) { table.style.display = 'none'; empty.style.display = 'block'; return; }
    empty.style.display = 'none'; table.style.display = 'table';

    body.innerHTML = allAppts.map(function(a) {
        var canCancel = a.status !== 'Cancelled' && a.status !== 'Completed';
        return '<tr>' +
            '<td style="font-weight:700;">' + escHtml(a.reference_number) + '</td>' +
            '<td>' + escHtml(a.patient_name) + '</td>' +
            '<td>' + escHtml(a.doctor_name) + '</td>' +
            '<td>' + escHtml(a.category_name) + '</td>' +
            '<td style="white-space:nowrap;">' + fmtDate(a.appointment_date) + '</td>' +
            '<td style="white-space:nowrap;">' + fmtTime(a.start_time) + '</td>' +
            '<td><span class="badge ' + a.status + '">' + a.status + '</span></td>' +
            '<td>' + (canCancel
                ? '<button class="btn-cancel" onclick="openCancelModal(' + a.id + ')"><i class="fa fa-ban"></i> Cancel</button>'
                : '<span style="font-size:12px;color:var(--muted);">—</span>') +
            '</td>' +
        '</tr>';
    }).join('');
}

function openCancelModal(id) {
    var a = allAppts.find(function(x){ return x.id == id; });
    if (!a) return;
    document.getElementById('cancelApptId').value = a.id;
    document.getElementById('cancelInfo').innerHTML =
        '<strong>' + escHtml(a.reference_number) + '</strong> — ' +
        escHtml(a.patient_name) + ' with Dr. ' + escHtml(a.doctor_name) +
        ' on ' + fmtDate(a.appointment_date);
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelModal').classList.add('open');
}

function closeCancelModal() { document.getElementById('cancelModal').classList.remove('open'); }

function confirmCancel() {
    var id     = document.getElementById('cancelApptId').value;
    var reason = document.getElementById('cancelReason').value.trim();
    if (!reason) { toast('Cancellation reason is required', true); return; }

    fetch(BASE_URL + '/admin/api/appointments/' + id + '/cancel', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ reason: reason })
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            closeCancelModal();
            loadAppointments();
            toast('Appointment cancelled — patient & doctor notified');
        } else {
            toast(d.error || 'Failed to cancel', true);
        }
    });
}

var debounce;
document.getElementById('searchInput').addEventListener('input', function(){ clearTimeout(debounce); debounce = setTimeout(loadAppointments, 300); });
document.getElementById('doctorFilter').addEventListener('change', loadAppointments);
document.getElementById('dateFrom').addEventListener('change', loadAppointments);
document.getElementById('dateTo').addEventListener('change', loadAppointments);
document.getElementById('statusFilter').addEventListener('change', loadAppointments);

loadAppointments();
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app.php';
