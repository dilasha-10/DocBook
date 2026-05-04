<?php
$title = 'My Patients';
ob_start();
?>

<style>
.patient-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
}
.patient-card:hover { border-color: var(--blue); box-shadow: 0 4px 16px rgba(74,144,226,.12); }
.patient-card h3 { font-size: 15px; font-weight: 700; margin: 0 0 4px; }
.patient-card p  { font-size: 13px; color: var(--muted); margin: 2px 0; }
.patient-card .last-visit { font-size: 11px; color: var(--hint); margin-top: 8px; }

/* Side panel */
.doctor-side-panel {
    position: fixed; top: 0; right: -480px; width: 480px; height: 100vh;
    background: var(--surface); border-left: 1px solid var(--border);
    z-index: 500; overflow-y: auto; transition: right .3s ease;
    display: flex; flex-direction: column;
}
.doctor-side-panel.open { right: 0; }
.dsp-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between;
    position: sticky; top: 0; background: var(--surface); z-index: 1;
}
.dsp-header h2 { font-size: 17px; font-weight: 700; margin: 0 0 3px; }
.dsp-header p  { font-size: 13px; color: var(--muted); margin: 0; }
.dsp-close {
    background: none; border: none; font-size: 20px;
    cursor: pointer; color: var(--muted); padding: 2px 6px;
    border-radius: 4px; line-height: 1;
}
.dsp-close:hover { color: var(--text); }
.dsp-body { padding: 20px 24px; flex: 1; }

/* Appointment selector */
.appt-selector { margin-bottom: 20px; }
.appt-selector label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--hint); display: block; margin-bottom: 6px; }
.appt-selector select {
    width: 100%; padding: 9px 12px;
    background: var(--bg); border: 1px solid var(--border2);
    border-radius: 8px; color: var(--text); font-size: 13px; font-weight: 600;
    cursor: pointer;
}

/* Lab report status box */
.lab-status-box {
    border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;
    border: 1px solid;
}
.lab-status-box.uploaded {
    background: rgba(99,179,237,.07); border-color: rgba(99,179,237,.3);
}
.lab-status-box.pending {
    background: rgba(234,179,8,.07); border-color: rgba(234,179,8,.3);
}
.lab-status-box .lab-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 5px;
}
.lab-status-box.uploaded .lab-title { color: #63b3ed; }
.lab-status-box.pending  .lab-title { color: #d69e2e; }

/* Thread area */
.thread-area { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.thread-header {
    padding: 10px 14px; border-bottom: 1px solid var(--border);
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--hint);
    background: var(--bg);
}
.thread-body { padding: 14px; }
.thread-empty { font-size: 13px; color: var(--hint); padding: 4px 0; }

/* Doctor comment box */
.doctor-comment-entry { margin-bottom: 16px; }
.doctor-comment-entry textarea {
    width: 100%; padding: 10px 12px;
    background: var(--bg); border: 1px solid var(--border2);
    border-radius: 8px; color: var(--text); font-size: 13px;
    resize: vertical; min-height: 72px; box-sizing: border-box;
    font-family: inherit; line-height: 1.5;
}
.doctor-comment-entry textarea:focus { outline: none; border-color: var(--blue); }
.btn-post-comment {
    margin-top: 8px; padding: 8px 16px;
    background: var(--blue); color: #fff; border: none;
    border-radius: 7px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    transition: background .15s;
}
.btn-post-comment:hover { background: #3a8eef; }
.btn-post-comment:disabled { opacity: .6; cursor: not-allowed; }

/* Comment bubbles */
.comment-bubble {
    padding: 10px 12px; border-radius: 10px; margin-bottom: 10px;
}
.comment-bubble.doctor {
    background: rgba(34,197,94,.07); border: 1px solid rgba(34,197,94,.2);
}
.comment-bubble.patient {
    background: rgba(99,102,241,.07); border: 1px solid rgba(99,102,241,.2);
    margin-left: 18px;
}
.comment-bubble .bubble-meta {
    font-size: 11px; font-weight: 700; margin-bottom: 4px;
}
.comment-bubble.doctor  .bubble-meta { color: #22C55E; }
.comment-bubble.patient .bubble-meta { color: #818cf8; }
.comment-bubble .bubble-text { font-size: 13px; color: var(--text); line-height: 1.5; }

.msg-box {
    margin-top: 10px; padding: 8px 12px; border-radius: 7px;
    font-size: 12px; display: none;
}
.msg-box.error   { background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.3); }
.msg-box.success { background: rgba(34,197,94,.1);  color: #22C55E; border: 1px solid rgba(34,197,94,.3); }
</style>

<div class="view-container">
    <div class="view-header">
        <div class="greeting">
            <h1>My Patients</h1>
            <p>Patient directory and appointment notes</p>
        </div>
    </div>
    <div id="patient-list-container"
         style="display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));margin-top:20px;">
        <p style="color:var(--muted);">Loading patients…</p>
    </div>
</div>

<!-- Side panel -->
<div id="doctorSidePanel" class="doctor-side-panel">
    <div class="dsp-header">
        <div>
            <h2 id="dspPatientName">—</h2>
            <p id="dspPatientContact">—</p>
        </div>
        <button class="dsp-close" onclick="closeDsp()">×</button>
    </div>
    <div class="dsp-body">
        <!-- Appointment picker -->
        <div class="appt-selector">
            <label><i class="fa fa-calendar" style="margin-right:4px;"></i>Select Appointment</label>
            <select id="dspApptSelect" onchange="loadApptThread()">
                <option value="">— choose an appointment —</option>
            </select>
        </div>

        <!-- Appointment detail section (shown after picking) -->
        <div id="dspApptDetail" style="display:none;">

            <!-- Lab report status -->
            <div id="dspLabStatus" class="lab-status-box pending">
                <div class="lab-title"><i class="fa fa-flask" style="margin-right:4px;"></i>Lab Report</div>
                <div id="dspLabText" style="font-size:13px;color:var(--muted);">No report uploaded yet.</div>
            </div>

            <!-- Thread -->
            <div class="thread-area" style="margin-bottom:16px;">
                <div class="thread-header"><i class="fa fa-comments" style="margin-right:5px;"></i>Notes &amp; Replies</div>
                <div class="thread-body" id="dspThreadBody">
                    <p class="thread-empty">No notes yet.</p>
                </div>
            </div>

            <!-- Doctor comment entry -->
            <div class="doctor-comment-entry" id="dspCommentEntry">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--hint);display:block;margin-bottom:6px;">
                    <i class="fa fa-lock" style="margin-right:4px;"></i>Add Note
                </label>
                <textarea id="dspCommentInput" placeholder="Write your clinical note for this patient…"></textarea>
                <div>
                    <button id="dspPostBtn" class="btn-post-comment" onclick="postDoctorComment()">
                        <i class="fa fa-paper-plane"></i> Post Note
                    </button>
                </div>
                <div id="dspCommentMsg" class="msg-box"></div>
            </div>

        </div>
    </div>
</div>
<div id="dspOverlay" onclick="closeDsp()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:499;"></div>

<?php
$content = ob_get_clean();

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
                labText.innerHTML = '<strong style="color:#63b3ed;"><i class="fa fa-check-circle"></i> Report uploaded</strong>'
                    + ' &mdash; <span style="color:var(--muted);">' + escHtml(appt.lab_report.original_name) + '</span>'
                    + '<br><span style="font-size:11px;color:var(--hint);">Uploaded ' + fmtDt(appt.lab_report.uploaded_at) + '</span>';
            } else {
                labBox.className = 'lab-status-box pending';
                labText.innerHTML = '<span style="color:#d69e2e;"><i class="fa fa-triangle-exclamation"></i> No lab report uploaded yet.</span>'
                    + '<br><span style="font-size:11px;color:var(--hint);">Upload from the Schedule page before adding notes.</span>';
            }

            // Comment thread
            renderThread(appt.comments || [], appt.lab_report);

            // Comment entry label
            var label = document.querySelector('#dspCommentEntry label');
            if (!appt.lab_report) {
                label.innerHTML = '<i class="fa fa-lock" style="margin-right:4px;color:#d69e2e;"></i><span style="color:#d69e2e;">Upload lab report first to add notes</span>';
                document.getElementById('dspCommentInput').disabled = true;
                document.getElementById('dspPostBtn').disabled = true;
            } else {
                label.innerHTML = '<i class="fa fa-pen" style="margin-right:4px;"></i>Add Clinical Note';
                document.getElementById('dspCommentInput').disabled = false;
                document.getElementById('dspPostBtn').disabled = false;
            }
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
    if (!_dspLabUploaded) { showMsg('dspCommentMsg', 'Please upload the lab report before adding notes.', 'error'); return; }

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

loadPatients();
</script>
JS;

include BASE_PATH . '/app/views/layouts/app-doctor.php';