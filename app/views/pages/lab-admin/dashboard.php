<?php
$title = 'Lab Dashboard';

$extra_styles = <<<CSS
<style>
/* ─── Page layout ─────────────────────────────────────── */
.page-content { padding: 0; max-width: 960px; margin: 0 auto; width: 100%; }

.view-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.view-header h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.view-header p  { font-size: 14px; color: var(--muted); margin: 0; }

/* ─── Cards ───────────────────────────────────────────── */
.section-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}
.section-card h2 {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 4px;
}
.section-card .sub {
    font-size: 13px;
    color: var(--muted);
    margin: 0 0 20px;
}

/* ─── Search bar ──────────────────────────────────────── */
.search-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
    margin-bottom: 0;
}
.search-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 160px;
}
.search-field label { font-size: 12px; font-weight: 600; color: var(--muted); }
.search-row .btn-primary,
.search-row .btn-reset {
    align-self: flex-end;
    white-space: nowrap;
    height: 38px;
    padding: 0 18px;
    font-size: 13px;
}
.btn-reset {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: var(--radius);
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: background 0.15s;
}
.btn-reset:hover { background: var(--hover); }

/* ─── Patient results list ────────────────────────────── */
.patient-results {
    margin-top: 16px;
    display: none;
    flex-direction: column;
    gap: 8px;
}
.patient-results.show { display: flex; }

.patient-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    gap: 16px;
    flex-wrap: wrap;
}
.patient-row:hover,
.patient-row.selected {
    border-color: var(--blue);
    background: rgba(77,166,232,.06);
}
.patient-row .pat-name  { font-weight: 700; font-size: 14px; }
.patient-row .pat-meta  { font-size: 12px; color: var(--muted); margin-top: 6px; display: flex; gap: 14px; flex-wrap: wrap; align-items: center; }
.pat-meta-item { display: flex; align-items: center; gap: 5px; }
.pat-meta-item i { font-size: 10px; opacity: .65; }
.patient-row .pat-id    { font-size: 12px; font-weight: 600; color: var(--blue); }

/* ─── Appointments table ──────────────────────────────── */
.appt-section { display: none; }
.appt-section.show { display: block; }

.appt-patient-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: rgba(77,166,232,.08);
    border: 1px solid rgba(77,166,232,.2);
    border-radius: 8px;
    margin-bottom: 16px;
}
.appt-patient-banner .avatar-circle { width: 38px; height: 38px; font-size: 14px; flex-shrink: 0; }
.appt-patient-banner .pat-name  { font-weight: 700; font-size: 14px; }
.appt-patient-banner .pat-pid   { font-size: 12px; color: var(--muted); }

.appt-list { display: flex; flex-direction: column; gap: 10px; }

.appt-card {
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    background: var(--bg);
}
.appt-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    cursor: pointer;
    gap: 12px;
    flex-wrap: wrap;
    transition: background 0.15s;
}
.appt-card-header:hover { background: var(--hover); }

.appt-info { flex: 1; min-width: 0; }
.appt-ref  { font-size: 12px; font-weight: 700; color: var(--blue); margin-bottom: 2px; }
.appt-date { font-size: 13px; font-weight: 600; }
.appt-doc  { font-size: 12px; color: var(--muted); margin-top: 1px; }

.appt-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    white-space: nowrap;
    flex-shrink: 0;
}
.badge-confirmed { background: rgba(52,211,153,.15); color: #34d399; border: 1px solid rgba(52,211,153,.3); }
.badge-completed { background: rgba(148,163,184,.15); color: var(--muted); border: 1px solid var(--border); }

.appt-chevron { color: var(--muted); font-size: 12px; transition: transform 0.2s; }
.appt-card.open .appt-chevron { transform: rotate(180deg); }

/* Upload panel */
.appt-panel {
    display: none;
    padding: 24px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface);
}
.appt-card.open .appt-panel { display: block; }

.upload-form { display: flex; flex-direction: column; gap: 18px; }
.form-group  { display: flex; flex-direction: column; gap: 5px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--muted); }
.form-group textarea.input { resize: vertical; min-height: 64px; }

.existing-report {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(52,211,153,.07);
    border: 1px solid rgba(52,211,153,.2);
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 4px;
}
.existing-report i { color: #34d399; }
.existing-report a { color: var(--blue); font-weight: 600; text-decoration: none; }
.existing-report a:hover { text-decoration: underline; }

.upload-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.upload-msg { font-size: 13px; display: none; }
.upload-msg.ok  { color: #34d399; display: inline; }
.upload-msg.err { color: #f87171; display: inline; }

/* ─── Empty / loading states ──────────────────────────── */
.state-empty {
    text-align: center;
    padding: 36px 20px;
    color: var(--muted);
    font-size: 13px;
}
.state-loading { text-align: center; padding: 24px; color: var(--muted); font-size: 13px; }

@media (max-width: 640px) {
    .search-row { flex-direction: column; }
    .search-row .btn-primary,
    .search-row .btn-reset { width: 100%; justify-content: center; }
}
</style>
CSS;

ob_start();
?>
<div class="page-content">

    <div class="view-header">
        <div>
            <h1>Lab Dashboard</h1>
            <p>Search patients and manage lab report uploads</p>
        </div>
    </div>

    <!-- Patient Search -->
    <div class="section-card">
        <h2>Find Patient</h2>
        <p class="sub">Search by name or Patient ID. At least one field is required.</p>

        <div class="search-row">
            <div class="search-field">
                <label for="s-name">Patient Name</label>
                <input id="s-name" class="input" type="text" placeholder="e.g. Ahmed Khan" autocomplete="off">
            </div>
            <div class="search-field">
                <label for="s-pid">Patient ID</label>
                <input id="s-pid" class="input" type="text" placeholder="e.g. PT-0042" autocomplete="off">
            </div>
            <button class="btn-primary" onclick="searchPatients()">Search</button>
            <button class="btn-reset"   onclick="resetAll()">Reset</button>
        </div>

        <div class="patient-results" id="patientResults">
            <!-- populated by JS -->
        </div>
    </div>

    <!-- Appointments section (shown after patient selected) -->
    <div class="section-card appt-section" id="apptSection">
        <h2>Appointments</h2>
        <p class="sub">Confirmed and completed appointments. Upload or replace a lab report for any entry.</p>

        <div class="appt-patient-banner" id="apptBanner"></div>
        <div class="appt-list" id="apptList"></div>
    </div>

</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
var _selectedPatientId = null;

/* ── Search patients ─────────────────────────────────── */
function searchPatients() {
    var name = document.getElementById('s-name').value.trim();
    var pid  = document.getElementById('s-pid').value.trim();

    if (!name && !pid) {
        showResultsMsg('Please enter a name or Patient ID to search.');
        return;
    }

    var res = document.getElementById('patientResults');
    res.innerHTML = '<div class="state-loading"><i class="fa fa-spinner fa-spin"></i> Searching…</div>';
    res.classList.add('show');

    var qs = new URLSearchParams();
    if (name) qs.set('name', name);
    if (pid)  qs.set('patient_id', pid);

    fetch(BASE_URL + '/lab-admin/api/find-patient?' + qs.toString())
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data.success || !data.patients.length) {
                res.innerHTML = '<div class="state-empty">No patients found. Try different search terms.</div>';
                return;
            }
            res.innerHTML = data.patients.map(function(p){
                var initials = p.name.substring(0,2).toUpperCase();
                // Mask phone: show last 4 digits only
                var phone = '—';
                if (p.phone) {
                    var digits = p.phone.replace(/\D/g, '');
                    phone = digits.slice(0,1) + '•••••' + digits.slice(-2);
                }
                return '<div class="patient-row" onclick="selectPatient('+p.id+', this)" data-id="'+p.id+'">'
                    + '<div>'
                    + '<div class="pat-name">' + escHtml(p.name) + '</div>'
                    + '<div class="pat-meta"><span class="pat-meta-item"><i class="fa fa-envelope"></i> ' + escHtml(p.email_masked) + '</span><span class="pat-meta-item"><i class="fa fa-phone"></i> ' + phone + '</span></div>'
                    + '</div>'
                    + '<div class="pat-id">' + escHtml(p.patient_unique_id || '—') + '</div>'
                    + '</div>';
            }).join('');
        })
        .catch(function(){ res.innerHTML = '<div class="state-empty">Request failed. Please try again.</div>'; });
}

function showResultsMsg(msg) {
    var res = document.getElementById('patientResults');
    res.innerHTML = '<div class="state-empty">' + escHtml(msg) + '</div>';
    res.classList.add('show');
}

/* ── Select patient → load appointments ──────────────── */
function selectPatient(patientId, el) {
    _selectedPatientId = patientId;
    document.querySelectorAll('.patient-row').forEach(function(r){ r.classList.remove('selected'); });
    el.classList.add('selected');
    loadAppointments(patientId);
}

function loadAppointments(patientId) {
    var sec  = document.getElementById('apptSection');
    var list = document.getElementById('apptList');
    var ban  = document.getElementById('apptBanner');

    sec.classList.add('show');
    list.innerHTML = '<div class="state-loading"><i class="fa fa-spinner fa-spin"></i> Loading appointments…</div>';
    ban.innerHTML  = '';

    fetch(BASE_URL + '/lab-admin/api/patient-appointments?patient_id=' + patientId)
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data.success) {
                list.innerHTML = '<div class="state-empty">Could not load appointments.</div>';
                return;
            }
            var p = data.patient;
            ban.innerHTML = '<div class="avatar-circle">' + p.name.substring(0,2).toUpperCase() + '</div>'
                + '<div><div class="pat-name">' + escHtml(p.name) + '</div>'
                + '<div class="pat-pid">' + escHtml(p.patient_unique_id || '') + '</div></div>';

            if (!data.appointments.length) {
                list.innerHTML = '<div class="state-empty">No confirmed or completed appointments found for this patient.</div>';
                return;
            }

            list.innerHTML = data.appointments.map(function(a){
                var badgeClass = a.status === 'Confirmed' ? 'badge-confirmed' : 'badge-completed';
                var dateStr    = a.appointment_date ? new Date(a.appointment_date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';
                var timeStr    = a.start_time ? a.start_time.substring(0,5) : '';
                var hasReport  = !!a.report_id;

                var existingHtml = '';
                if (hasReport) {
                    existingHtml = '<div class="existing-report">'
                        + '<i class="fa fa-file-medical"></i>'
                        + '<span>Report uploaded: <strong>' + escHtml(a.report_file || 'file') + '</strong>'
                        + (a.report_notes ? ' — ' + escHtml(a.report_notes) : '') + '</span>'
                        + '</div>';
                }

                return '<div class="appt-card" id="card-'+a.appointment_id+'">'
                    + '<div class="appt-card-header" onclick="toggleAppt('+a.appointment_id+')">'
                    + '<div class="appt-info">'
                    + '<div class="appt-ref">' + escHtml(a.reference_number || '#'+a.appointment_id) + '</div>'
                    + '<div class="appt-date">' + dateStr + (timeStr ? ' &middot; ' + timeStr : '') + '</div>'
                    + '<div class="appt-doc">Dr. ' + escHtml(a.doctor_name || '—') + (a.specialty ? ' &mdash; ' + escHtml(a.specialty) : '') + '</div>'
                    + '</div>'
                    + '<span class="appt-badge ' + badgeClass + '">' + escHtml(a.status) + '</span>'
                    + '<i class="fa fa-chevron-down appt-chevron"></i>'
                    + '</div>'
                    + '<div class="appt-panel">'
                    + existingHtml
                    + '<div class="upload-form">'
                    + '<div class="form-group">'
                    + '<label>Lab Report File (PDF, JPG, PNG — max 5 MB)</label>'
                    + '<input type="file" class="input" id="file-'+a.appointment_id+'" accept=".pdf,.jpg,.jpeg,.png">'
                    + '</div>'
                    + '<div class="form-group">'
                    + '<label>Notes (optional)</label>'
                    + '<textarea class="input" id="notes-'+a.appointment_id+'" placeholder="Enter any notes about this report…"></textarea>'
                    + '</div>'
                    + '<div class="upload-actions">'
                    + '<button class="btn-primary" onclick="uploadReport('+a.appointment_id+')">'
                    + (hasReport ? 'Replace Report' : 'Upload Report')
                    + '</button>'
                    + '<span class="upload-msg" id="msg-'+a.appointment_id+'"></span>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '</div>';
            }).join('');
        })
        .catch(function(){
            list.innerHTML = '<div class="state-empty">Request failed. Please try again.</div>';
        });
}

/* ── Toggle appointment panel ────────────────────────── */
function toggleAppt(apptId) {
    var card = document.getElementById('card-' + apptId);
    if (card) card.classList.toggle('open');
}

/* ── Upload report ───────────────────────────────────── */
function uploadReport(apptId) {
    var fileInput  = document.getElementById('file-' + apptId);
    var notesInput = document.getElementById('notes-' + apptId);
    var msgEl      = document.getElementById('msg-' + apptId);

    if (!fileInput || !fileInput.files.length) {
        setMsg(msgEl, 'err', 'Please select a file to upload.');
        return;
    }

    var fd = new FormData();
    fd.append('appointment_id', apptId);
    fd.append('report',         fileInput.files[0]);
    fd.append('notes',          notesInput ? notesInput.value.trim() : '');

    setMsg(msgEl, '', '');
    var btn = msgEl.previousElementSibling;
    btn.disabled    = true;
    btn.textContent = 'Uploading…';

    fetch(BASE_URL + '/lab-admin/api/upload-report', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(data){
            btn.disabled    = false;
            btn.textContent = 'Replace Report';
            if (data.success) {
                setMsg(msgEl, 'ok', 'Report uploaded successfully.');
                if (_selectedPatientId) loadAppointments(_selectedPatientId);
            } else {
                setMsg(msgEl, 'err', data.message || 'Upload failed.');
            }
        })
        .catch(function(){
            btn.disabled    = false;
            btn.textContent = 'Replace Report';
            setMsg(msgEl, 'err', 'Network error. Please try again.');
        });
}

/* ── Reset everything ────────────────────────────────── */
function resetAll() {
    document.getElementById('s-name').value = '';
    document.getElementById('s-pid').value  = '';
    var res = document.getElementById('patientResults');
    res.innerHTML = '';
    res.classList.remove('show');
    var sec = document.getElementById('apptSection');
    sec.classList.remove('show');
    document.getElementById('apptList').innerHTML   = '';
    document.getElementById('apptBanner').innerHTML = '';
    _selectedPatientId = null;
}

/* ── Helpers ─────────────────────────────────────────── */
function setMsg(el, type, text) {
    if (!el) return;
    el.className = 'upload-msg' + (type ? ' ' + type : '');
    el.textContent = text;
}
function escHtml(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Allow pressing Enter in search inputs
document.addEventListener('DOMContentLoaded', function(){
    ['s-name','s-pid'].forEach(function(id){
        var el = document.getElementById(id);
        if (el) el.addEventListener('keydown', function(e){ if (e.key === 'Enter') searchPatients(); });
    });
});
</script>
JS;
include BASE_PATH . '/app/views/layouts/app-lab-admin.php';