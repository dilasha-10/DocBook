<?php
$title = 'My Patients';
ob_start();
?>

<div class="view-container">
    <div class="view-header">
        <div class="greeting">
            <h1>My Patients</h1>
            <p>View your patient directory</p>
        </div>
    </div>
    <div id="patient-list-container"
         style="display:grid; gap:1.5rem; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); margin-top:20px;">
        <!-- Filled by JS -->
    </div>
</div>

<!-- Patient Detail Panel -->
<div id="patient-detail-panel" class="patient-panel hidden">
    <div class="panel-header">
        <h2>Patient Details</h2>
        <button class="btn-close" onclick="closePatientDetailPanel()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="panel-body">
        <div class="patient-info">
            <div class="patient-avatar"><i class="fas fa-user"></i></div>
            <h3 id="detail-patient-name"></h3>
            <p id="detail-patient-email" style="color:#7f8c8d; font-size:14px;"></p>
            <p id="detail-patient-phone" style="color:#7f8c8d; font-size:14px;"></p>
        </div>
        <div class="comment-section">
            <h4>Visit History &amp; Comments</h4>
            <div class="comment-list" id="detail-comment-list">
                <p style="color:var(--text-muted); font-size:14px;">Loading...</p>
            </div>
        </div>
        <div class="add-comment-section">
            <h4>Add Comment</h4>
            <textarea id="detail-comment-input" class="comment-input"
                      placeholder="Enter your notes about this patient..."></textarea>
            <button class="btn-primary" onclick="savePatientComment()">Save Comment</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
<<<<<<< HEAD
=======

$extra_scripts = <<<'JS'
<script>
var _dspPatientId  = null;
var _dspApptId     = null;
var _dspLabUploaded = false;

// ── Helpers ────────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDt(s) {
    if (!s) return '';
    return new Date(s.replace(' ','T')).toLocaleString('en-US',{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
}
function showMsg(id, text, type) {
    var el = document.getElementById(id);
    el.textContent = text; el.className = 'msg-box ' + type; el.style.display = 'block';
}
function hideMsg(id) { document.getElementById(id).style.display = 'none'; }

// ── Load patient list ─────────────────────────────────────────────────────
function loadPatients() {
    fetch(BASE_URL + '/doctor/api/patients')
        .then(r => r.json())
        .then(d => {
            var grid = document.getElementById('patient-list-container');
            if (!d.success || !d.patients || !d.patients.length) {
                grid.innerHTML = '<p style="color:var(--muted);">No patients yet.</p>';
                return;
            }
            grid.innerHTML = d.patients.map(p => {
                var initials = (p.name||'?').split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2);
                var lastVisit = p.last_visit ? 'Last visit: ' + new Date(p.last_visit+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : 'No visits yet';
                return '<div class="patient-card" onclick="openDsp(' + p.id + ')">'
                    + '<div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">'
                    + '<div style="width:40px;height:40px;border-radius:50%;background:var(--blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;">' + initials + '</div>'
                    + '<div><h3>' + escHtml(p.name) + '</h3><p>' + escHtml(p.email||'') + '</p></div></div>'
                    + (p.phone ? '<p><i class="fa fa-phone" style="width:14px;color:var(--hint);"></i> ' + escHtml(p.phone) + '</p>' : '')
                    + '<div class="last-visit"><i class="fa fa-clock" style="margin-right:4px;"></i>' + lastVisit + '</div>'
                    + '</div>';
            }).join('');
        });
}

// ── Open / close side panel ───────────────────────────────────────────────
function openDsp(patientId) {
    _dspPatientId = patientId;
    _dspApptId    = null;
    document.getElementById('dspApptSelect').value = '';
    document.getElementById('dspApptDetail').style.display = 'none';
    document.getElementById('dspThreadBody').innerHTML = '<p class="thread-empty">Select an appointment above.</p>';
    document.getElementById('dspCommentInput').value = '';
    hideMsg('dspCommentMsg');

    fetch(BASE_URL + '/doctor/api/patients?id=' + patientId)
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            var p = d.patient;
            document.getElementById('dspPatientName').textContent = p.name;
            document.getElementById('dspPatientContact').textContent = (p.email||'') + (p.phone ? ' · ' + p.phone : '');

            var sel = document.getElementById('dspApptSelect');
            sel.innerHTML = '<option value="">— choose an appointment —</option>';
            (d.appointments||[]).forEach(function(a) {
                var dt = new Date(a.date+'T00:00:00').toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
                var opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = dt + ' · ' + a.time + ' · ' + a.status;
                sel.appendChild(opt);
            });
        });

    document.getElementById('doctorSidePanel').classList.add('open');
    document.getElementById('dspOverlay').style.display = 'block';
}

function closeDsp() {
    document.getElementById('doctorSidePanel').classList.remove('open');
    document.getElementById('dspOverlay').style.display = 'none';
    _dspPatientId = null;
    _dspApptId    = null;
}

// ── Load appointment thread ───────────────────────────────────────────────
function loadApptThread() {
    var sel = document.getElementById('dspApptSelect');
    var apptId = parseInt(sel.value);
    if (!apptId) {
        document.getElementById('dspApptDetail').style.display = 'none';
        return;
    }
    _dspApptId = apptId;
    document.getElementById('dspApptDetail').style.display = 'block';
    document.getElementById('dspThreadBody').innerHTML = '<p class="thread-empty">Loading…</p>';
    hideMsg('dspCommentMsg');

    fetch(BASE_URL + '/doctor/api/appointment-detail?id=' + apptId)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { document.getElementById('dspThreadBody').innerHTML = '<p class="thread-empty">Failed to load.</p>'; return; }
            var appt = d.appointment;
            _dspLabUploaded = !!appt.lab_report;

            // Lab status box
            var labBox  = document.getElementById('dspLabStatus');
            var labText = document.getElementById('dspLabText');
            if (appt.lab_report) {
                labBox.className = 'lab-status-box uploaded';
                var reportUrl = BASE_URL + '/lab-report/' + appt.id;
                labText.innerHTML = '<strong style="color:#63b3ed;"><i class="fa fa-check-circle"></i> Report uploaded</strong>'
                    + ' &mdash; <a href="' + reportUrl + '" target="_blank" style="color:#63b3ed;font-weight:600;">'
                    + '<i class="fa fa-download" style="margin-right:3px;"></i>' + escHtml(appt.lab_report.original_name) + '</a>'
                    + '<br><span style="font-size:11px;color:var(--hint);">Uploaded ' + fmtDt(appt.lab_report.uploaded_at) + '</span>';
            } else {
                labBox.className = 'lab-status-box pending';
                labText.innerHTML = '<span style="color:#d69e2e;"><i class="fa fa-triangle-exclamation"></i> No lab report uploaded yet.</span>'
                    + '<br><span style="font-size:11px;color:var(--hint);">Lab Admin will upload the report after the appointment.</span>';
            }

            // Comment thread
            renderThread(appt.comments || [], appt.lab_report);

            // Comment entry - always enabled for doctor
            var label = document.querySelector('#dspCommentEntry label');
            label.innerHTML = '<i class="fa fa-pen" style="margin-right:4px;"></i>Add Clinical Note';
            document.getElementById('dspCommentInput').disabled = false;
            document.getElementById('dspPostBtn').disabled = false;
        });
}

// ── Render thread ─────────────────────────────────────────────────────────
function renderThread(comments, labReport) {
    var body = document.getElementById('dspThreadBody');
    if (!comments.length) {
        body.innerHTML = labReport
            ? '<p class="thread-empty">No notes yet. Add the first note below.</p>'
            : '<p class="thread-empty">Upload a lab report to enable clinical notes.</p>';
        return;
    }

    // Group: doctor top-level + their patient replies
    var doctorComments  = comments.filter(c => c.author_role === 'doctor' && !c.parent_id);
    var patientReplies  = comments.filter(c => c.author_role === 'patient');
    var totalPatReplies = patientReplies.length;

    if (!doctorComments.length) {
        body.innerHTML = '<p class="thread-empty">No doctor notes yet.</p>';
        return;
    }

    body.innerHTML = doctorComments.map(function(dc) {
        var replies = comments.filter(c => parseInt(c.parent_id) === parseInt(dc.id));
        var repliesHtml = replies.map(function(r) {
            return '<div class="comment-bubble patient">'
                + '<div class="bubble-meta"><i class="fa fa-user" style="margin-right:4px;"></i>Patient · ' + fmtDt(r.created_at) + '</div>'
                + '<div class="bubble-text">' + escHtml(r.message) + '</div>'
                + '</div>';
        }).join('');

        var replyInfo = totalPatReplies >= 2
            ? '<p style="font-size:11px;color:var(--hint);margin:6px 0 0 18px;"><i class="fa fa-info-circle" style="margin-right:3px;"></i>Patient has used all 2 replies.</p>'
            : totalPatReplies === 1
                ? '<p style="font-size:11px;color:var(--hint);margin:6px 0 0 18px;"><i class="fa fa-info-circle" style="margin-right:3px;"></i>Patient has 1 reply remaining.</p>'
                : (labReport
                    ? '<p style="font-size:11px;color:var(--hint);margin:6px 0 0 18px;"><i class="fa fa-info-circle" style="margin-right:3px;"></i>Patient may reply up to 2 times.</p>'
                    : '');

        return '<div class="comment-bubble doctor">'
            + '<div class="bubble-meta"><i class="fa fa-stethoscope" style="margin-right:4px;"></i>You · ' + fmtDt(dc.created_at) + '</div>'
            + '<div class="bubble-text">' + escHtml(dc.message) + '</div>'
            + '</div>'
            + repliesHtml
            + replyInfo;
    }).join('');
}

// ── Post doctor comment ───────────────────────────────────────────────────
function postDoctorComment() {
    if (!_dspApptId) return;
    var inp = document.getElementById('dspCommentInput');
    var msg = inp.value.trim();
    if (!msg) { showMsg('dspCommentMsg', 'Note cannot be empty.', 'error'); return; }
    var btn = document.getElementById('dspPostBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';
    hideMsg('dspCommentMsg');

    var form = new FormData();
    form.append('appointment_id', _dspApptId);
    form.append('comment_text', msg);

    fetch(BASE_URL + '/doctor/api/comment', { method: 'POST', body: form })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Post Note';
            if (res.success) {
                inp.value = '';
                showMsg('dspCommentMsg', 'Note saved.', 'success');
                loadApptThread(); // Refresh thread
            } else {
                showMsg('dspCommentMsg', res.message || 'Failed to save note.', 'error');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane"></i> Post Note';
            showMsg('dspCommentMsg', 'Network error.', 'error');
        });
}

// Deep link from notification: ?appt_id=X → open patient panel + select appointment
(function() {
    var params = new URLSearchParams(window.location.search);
    var apptId = params.get('appt_id');
    if (!apptId) { loadPatients(); return; }

    // Fetch appointment detail first to get patient_id
    fetch(BASE_URL + '/doctor/api/appointment-detail?id=' + apptId)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            loadPatients();
            window.history.replaceState({}, '', window.location.pathname);
            if (!d.success || !d.appointment) return;

            var patientId = d.appointment.patient_id;

            // Wait for patient cards to render, then open the panel
            setTimeout(function() {
                // openDsp fetches appointments async — we intercept after it populates the select
                openDsp(patientId);

                // Poll until the select has options (max 3s), then select the right appointment
                var attempts = 0;
                var interval = setInterval(function() {
                    attempts++;
                    var sel = document.getElementById('dspApptSelect');
                    // Check if options are loaded (more than just the placeholder)
                    if (sel && sel.options.length > 1) {
                        clearInterval(interval);
                        for (var i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value == apptId) {
                                sel.value = apptId;
                                sel.dispatchEvent(new Event('change'));
                                break;
                            }
                        }
                    } else if (attempts > 30) {
                        clearInterval(interval); // give up after 3s
                    }
                }, 100);
            }, 500);
        })
        .catch(function() { loadPatients(); });
})();
</script>
JS;

>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
include BASE_PATH . '/app/views/layouts/app-doctor.php';