<?php
$title = 'My Schedule';
ob_start();
?>

<div class="view-container">
    <div class="view-header" style="justify-content:space-between;">
        <div class="greeting">
            <h1>My Schedule</h1>
            <p>View your schedule and manage lab reports</p>
        </div>
        <div style="margin-right: 24px;">
            <input type="date" id="schedule-date-picker"
                   onchange="loadScheduleAppointments()"
                   value="<?= date('Y-m-d') ?>">
        </div>
    </div>
    <div class="schedule-section" style="margin-top:20px;">
        <div class="appointment-list" id="schedule-appointment-list">
            <p style="color:var(--muted);">Loading schedule...</p>
        </div>
    </div>
</div>

<!-- Lab Report Upload Modal -->
<div id="labReportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px 32px;width:100%;max-width:420px;margin:0 16px;position:relative;">
        <button onclick="closeLabModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        <h3 style="margin:0 0 6px;font-size:17px;font-weight:700;">Upload Lab Report</h3>
        <p id="labModalPatient" style="margin:0 0 20px;font-size:13px;color:var(--muted);"></p>

        <div id="labDropZone"
             style="border:2px dashed var(--border2);border-radius:10px;padding:28px 20px;text-align:center;cursor:pointer;transition:border-color .2s;margin-bottom:16px;"
             onclick="document.getElementById('labFileInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--blue)'"
             ondragleave="this.style.borderColor='var(--border2)'"
             ondrop="handleLabDrop(event)">
            <i class="fa fa-cloud-arrow-up" style="font-size:28px;color:var(--hint);margin-bottom:8px;display:block;"></i>
            <span id="labDropLabel" style="font-size:13px;color:var(--muted);">Click or drag &amp; drop a file here<br><span style="font-size:11px;">PDF, JPG, PNG · Max 10 MB</span></span>
        </div>
        <input type="file" id="labFileInput" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="handleLabFileSelect(event)">

        <div id="labFileInfo" style="display:none;margin-bottom:16px;padding:10px 14px;background:rgba(99,179,237,.1);border:1px solid rgba(99,179,237,.3);border-radius:8px;font-size:13px;color:var(--text);display:none;align-items:center;gap:10px;">
            <i class="fa fa-file" style="color:#63b3ed;"></i>
            <span id="labFileName"></span>
            <button onclick="clearLabFile()" style="margin-left:auto;background:none;border:none;color:var(--muted);cursor:pointer;font-size:16px;">&times;</button>
        </div>

        <div id="labUploadProgress" style="display:none;margin-bottom:12px;">
            <div style="height:4px;background:var(--border);border-radius:2px;overflow:hidden;">
                <div id="labProgressBar" style="height:100%;background:var(--blue);width:0%;transition:width .3s;"></div>
            </div>
        </div>

        <div id="labUploadMsg" style="display:none;margin-bottom:12px;font-size:13px;border-radius:8px;padding:8px 12px;"></div>

        <button id="labUploadBtn" onclick="submitLabReport()"
                style="width:100%;padding:11px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
            <i class="fa fa-upload"></i> Upload Report
        </button>
    </div>
</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<'JS'
<script>
var _labApptId   = null;
var _labFile     = null;

function loadScheduleAppointments() {
    const date = document.getElementById('schedule-date-picker').value;
    const list = document.getElementById('schedule-appointment-list');
    list.innerHTML = '<p style="color:var(--muted);">Loading...</p>';

    fetch(BASE_URL + '/doctor/api/appointments?date=' + date)
        .then(r => r.json())
        .then(d => {
            if (!d.success || !d.appointments || d.appointments.length === 0) {
                list.innerHTML = '<p style="color:var(--muted);">No appointments on this date.</p>';
                return;
            }
            list.innerHTML = d.appointments.map(a => {
                const badgeClass = a.status === 'Confirmed' ? 'badge-confirmed' : 'badge-pending';
                const reason = a.visit_reason || 'General consultation';

                // Lab report button — shown for all non-cancelled appointments
                const canUpload = (a.status !== 'Cancelled' && a.status !== 'Rescheduled');
                const labBtn = canUpload
                    ? '<button onclick="openLabModal(' + a.id + ',\'' + escHtml(a.patient_name) + '\')" '
                      + 'style="padding:5px 12px;background:rgba(99,179,237,.15);color:#63b3ed;border:1px solid rgba(99,179,237,.3);'
                      + 'border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">'
                      + '<i class="fa fa-flask"></i> Lab Report</button>'
                    : '';

                return '<div class="appointment-item" style="cursor:default;">'
                    + '<div class="appt-time">' + a.time + '</div>'
                    + '<div class="appt-details">'
                    + '<div class="appt-icon"><i class="fas fa-user"></i></div>'
                    + '<div class="appt-info">'
                    + '<h4>' + escHtml(a.patient_name) + '</h4>'
                    + '<p>' + escHtml(reason) + ' &middot; ' + a.duration_minutes + ' min</p>'
                    + '</div></div>'
                    + '<div class="appt-actions" style="gap:8px;">'
                    + '<span class="badge ' + badgeClass + '">' + escHtml(a.status) + '</span>'
                    + labBtn
                    + '</div>'
                    + '</div>';
            }).join('');
        })
        .catch(function() {
            list.innerHTML = '<p style="color:var(--muted);">Failed to load schedule.</p>';
        });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Lab Report Modal ──────────────────────────────────────────────────────────

function openLabModal(apptId, patientName) {
    _labApptId = apptId;
    _labFile   = null;
    document.getElementById('labModalPatient').textContent = 'Patient: ' + patientName + ' · Appointment #' + apptId;
    document.getElementById('labFileInput').value = '';
    document.getElementById('labDropLabel').innerHTML = 'Click or drag & drop a file here<br><span style="font-size:11px;">PDF, JPG, PNG · Max 10 MB</span>';
    document.getElementById('labFileInfo').style.display = 'none';
    document.getElementById('labUploadProgress').style.display = 'none';
    document.getElementById('labProgressBar').style.width = '0%';
    clearLabMsg();
    var modal = document.getElementById('labReportModal');
    modal.style.display = 'flex';
}

function closeLabModal() {
    document.getElementById('labReportModal').style.display = 'none';
    _labApptId = null;
    _labFile   = null;
}

function handleLabFileSelect(e) {
    var file = e.target.files[0];
    if (file) setLabFile(file);
}

function handleLabDrop(e) {
    e.preventDefault();
    document.getElementById('labDropZone').style.borderColor = 'var(--border2)';
    var file = e.dataTransfer.files[0];
    if (file) setLabFile(file);
}

function setLabFile(file) {
    var allowed = ['application/pdf','image/jpeg','image/jpg','image/png'];
    if (!allowed.includes(file.type)) {
        showLabMsg('Only PDF, JPG, and PNG files are allowed.', 'error');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        showLabMsg('File size exceeds 10 MB.', 'error');
        return;
    }
    _labFile = file;
    clearLabMsg();
    var info = document.getElementById('labFileInfo');
    document.getElementById('labFileName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    info.style.display = 'flex';
    document.getElementById('labDropLabel').innerHTML = '<span style="color:#22C55E;font-weight:600;">File selected ✓</span>';
}

function clearLabFile() {
    _labFile = null;
    document.getElementById('labFileInput').value = '';
    document.getElementById('labFileInfo').style.display = 'none';
    document.getElementById('labDropLabel').innerHTML = 'Click or drag & drop a file here<br><span style="font-size:11px;">PDF, JPG, PNG · Max 10 MB</span>';
}

function showLabMsg(text, type) {
    var el = document.getElementById('labUploadMsg');
    el.textContent = text;
    el.style.display = 'block';
    el.style.background = type === 'error' ? 'rgba(239,68,68,.1)' : 'rgba(34,197,94,.1)';
    el.style.color       = type === 'error' ? '#ef4444'             : '#22C55E';
    el.style.border      = '1px solid ' + (type === 'error' ? 'rgba(239,68,68,.3)' : 'rgba(34,197,94,.3)');
}

function clearLabMsg() {
    document.getElementById('labUploadMsg').style.display = 'none';
}

function submitLabReport() {
    if (!_labApptId) return;
    if (!_labFile) { showLabMsg('Please select a file first.', 'error'); return; }

    var btn = document.getElementById('labUploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading…';
    document.getElementById('labUploadProgress').style.display = 'block';

    var formData = new FormData();
    formData.append('appointment_id', _labApptId);
    formData.append('report', _labFile);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', BASE_URL + '/doctor/api/lab-report');

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            document.getElementById('labProgressBar').style.width = Math.round((e.loaded / e.total) * 100) + '%';
        }
    });

    xhr.onload = function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-upload"></i> Upload Report';
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                showLabMsg('Lab report uploaded successfully!', 'success');
                setTimeout(closeLabModal, 1500);
            } else {
                showLabMsg(res.message || 'Upload failed.', 'error');
            }
        } catch(e) {
            showLabMsg('Server error. Please try again.', 'error');
        }
    };

    xhr.onerror = function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-upload"></i> Upload Report';
        showLabMsg('Network error. Please try again.', 'error');
    };

    xhr.send(formData);
}

// Close modal on backdrop click
document.getElementById('labReportModal').addEventListener('click', function(e) {
    if (e.target === this) closeLabModal();
});

loadScheduleAppointments();
</script>
JS;

include BASE_PATH . '/app/views/layouts/app-doctor.php';