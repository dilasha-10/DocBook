<?php
$title = 'My Schedule';
ob_start();
?>

<style>
/* Side panel */
.schedule-side-panel {
    position: fixed; top: 0; right: -500px; width: 500px; height: 100vh;
    background: var(--surface); border-left: 1px solid var(--border);
    z-index: 500; overflow-y: auto; transition: right .3s ease;
    display: flex; flex-direction: column;
}
.schedule-side-panel.open { right: 0; }
.ssp-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between;
    position: sticky; top: 0; background: var(--surface); z-index: 1;
}
.ssp-header h2 { font-size: 17px; font-weight: 700; margin: 0 0 3px; }
.ssp-header p  { font-size: 13px; color: var(--muted); margin: 0; }
.ssp-close {
    background: none; border: none; font-size: 20px;
    cursor: pointer; color: var(--muted); padding: 2px 6px;
    border-radius: 4px; line-height: 1;
}
.ssp-close:hover { color: var(--text); }
.ssp-body { padding: 20px 24px; flex: 1; }

.detail-row { display: flex; gap: 8px; margin-bottom: 10px; font-size: 13px; }
.detail-label { color: var(--muted); font-weight: 600; min-width: 90px; }
.detail-value { color: var(--text); }

.lab-box {
    border-radius: 10px; padding: 12px 14px; margin: 16px 0;
    border: 1px solid;
}
.lab-box.uploaded { background: rgba(99,179,237,.07); border-color: rgba(99,179,237,.3); }
.lab-box.pending  { background: rgba(234,179,8,.07);  border-color: rgba(234,179,8,.3); }
.lab-box-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; margin-bottom: 6px;
}
.lab-box.uploaded .lab-box-title { color: #63b3ed; }
.lab-box.pending  .lab-box-title { color: #d69e2e; }

.appt-clickable { cursor: pointer; }
.appt-clickable:hover { border-color: var(--blue) !important; box-shadow: 0 4px 16px rgba(74,144,226,.12); }
</style>

<div class="view-container">
    <div class="view-header" style="justify-content:space-between;">
        <div class="greeting">
            <h1>My Schedule</h1>
            <p>Click an appointment to view details</p>
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

<!-- Side panel -->
<div id="scheduleSidePanel" class="schedule-side-panel">
    <div class="ssp-header">
        <div>
            <h2 id="sspPatientName">—</h2>
            <p id="sspDateTime">—</p>
        </div>
        <button class="ssp-close" onclick="closeSsp()">×</button>
    </div>
    <div class="ssp-body">
        <div id="sspContent"><p style="color:var(--muted);">Loading...</p></div>
    </div>
</div>
<div id="sspOverlay" onclick="closeSsp()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:499;"></div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<'JS'
<script>
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
                return '<div class="appointment-item appt-clickable" onclick="openSsp(' + a.id + ',\'' + escHtml(a.patient_name) + '\',\'' + a.time + '\',\'' + a.status + '\')">'
                    + '<div class="appt-time">' + a.time + '</div>'
                    + '<div class="appt-details">'
                    + '<div class="appt-icon"><i class="fas fa-user"></i></div>'
                    + '<div class="appt-info">'
                    + '<h4>' + escHtml(a.patient_name) + '</h4>'
                    + '<p>' + escHtml(reason) + ' &middot; ' + a.duration_minutes + ' min</p>'
                    + '</div></div>'
                    + '<div class="appt-actions" style="gap:8px;">'
                    + '<span class="badge ' + badgeClass + '">' + escHtml(a.status) + '</span>'
                    + '<span style="font-size:12px;color:var(--muted);"><i class="fa fa-chevron-right"></i></span>'
                    + '</div>'
                    + '</div>';
            }).join('');
        })
        .catch(function() {
            list.innerHTML = '<p style="color:var(--muted);">Failed to load schedule.</p>';
        });
}

function openSsp(apptId, patientName, time, status) {
    document.getElementById('sspPatientName').textContent = patientName;
    document.getElementById('sspDateTime').textContent = document.getElementById('schedule-date-picker').value + ' · ' + time + ' · ' + status;
    document.getElementById('sspContent').innerHTML = '<p style="color:var(--muted);">Loading...</p>';
    document.getElementById('scheduleSidePanel').classList.add('open');
    document.getElementById('sspOverlay').style.display = 'block';

    fetch(BASE_URL + '/doctor/api/appointment-detail?id=' + apptId)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { document.getElementById('sspContent').innerHTML = '<p style="color:var(--muted);">Failed to load.</p>'; return; }
            var a = d.appointment;
            var comments = a.comments || [];
            var labHtml;
            if (a.lab_report) {
                var reportUrl = BASE_URL + '/lab-report/' + apptId;
                labHtml = '<div class="lab-box uploaded">'
                    + '<div class="lab-box-title"><i class="fa fa-flask" style="margin-right:4px;"></i>Lab Report</div>'
                    + '<a href="' + reportUrl + '" target="_blank" style="color:#63b3ed;font-weight:600;font-size:13px;">'
                    + '<i class="fa fa-download" style="margin-right:4px;"></i>' + escHtml(a.lab_report.original_name) + '</a>'
                    + '<div style="font-size:11px;color:var(--hint);margin-top:4px;">Uploaded ' + fmtDt(a.lab_report.uploaded_at) + '</div>'
                    + '</div>';
            } else {
                labHtml = '<div class="lab-box pending">'
                    + '<div class="lab-box-title"><i class="fa fa-flask" style="margin-right:4px;"></i>Lab Report</div>'
                    + '<div style="font-size:13px;color:#d69e2e;"><i class="fa fa-triangle-exclamation" style="margin-right:4px;"></i>No report uploaded yet</div>'
                    + '<div style="font-size:11px;color:var(--hint);margin-top:4px;">Lab Admin will upload after the appointment.</div>'
                    + '</div>';
            }

            // Details
            var detailsHtml = '<div style="margin-bottom:16px;">'
                + '<div class="detail-row"><span class="detail-label">Patient</span><span class="detail-value">' + escHtml(a.patient_name || patientName) + '</span></div>'
                + '<div class="detail-row"><span class="detail-label">Reason</span><span class="detail-value">' + escHtml(a.visit_reason || 'General consultation') + '</span></div>'
                + '<div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">' + escHtml(a.status) + '</span></div>'
                + '<div class="detail-row"><span class="detail-label">Ref #</span><span class="detail-value">' + escHtml(a.reference_number || '—') + '</span></div>'
                + '</div>';

            // Comments (already loaded above)
            var commentsHtml = '';
            if (comments.length) {
                commentsHtml = '<div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-top:16px;">'
                    + '<div style="padding:10px 14px;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--hint);background:var(--bg);">'
                    + '<i class="fa fa-comments" style="margin-right:5px;"></i>Notes &amp; Replies</div>'
                    + '<div style="padding:14px;">'
                    + comments.map(c => {
                        var isDoc = c.author_role === 'doctor';
                        return '<div style="padding:10px 12px;border-radius:10px;margin-bottom:10px;'
                            + (isDoc ? 'background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.2);' : 'background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);margin-left:18px;')
                            + '">'
                            + '<div style="font-size:11px;font-weight:700;margin-bottom:4px;color:' + (isDoc ? '#22C55E' : '#818cf8') + ';">'
                            + '<i class="fa ' + (isDoc ? 'fa-stethoscope' : 'fa-user') + '" style="margin-right:4px;"></i>'
                            + (isDoc ? 'You' : 'Patient') + ' · ' + fmtDt(c.created_at)
                            + '</div>'
                            + '<div style="font-size:13px;color:var(--text);">' + escHtml(c.message) + '</div>'
                            + '</div>';
                    }).join('')
                    + '</div></div>';
            }

            document.getElementById('sspContent').innerHTML = detailsHtml + labHtml + commentsHtml;
        })
        .catch(function() {
            document.getElementById('sspContent').innerHTML = '<p style="color:var(--muted);">Failed to load details.</p>';
        });
}

function closeSsp() {
    document.getElementById('scheduleSidePanel').classList.remove('open');
    document.getElementById('sspOverlay').style.display = 'none';
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDt(s) {
    if (!s) return '';
    return new Date(s.replace(' ','T')).toLocaleString('en-US',{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
}

// ── Deep link from notification: ?appt_id=X ──────────────────────────────
(function() {
    var params = new URLSearchParams(window.location.search);
    var apptId = params.get('appt_id');
    if (!apptId) { loadScheduleAppointments(); return; }

    // Fetch the appointment to get its actual date
    fetch(BASE_URL + '/doctor/api/appointment-detail?id=' + apptId)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.appointment) {
                var appt = d.appointment;
                // Use 'date' field (appointment_date returned as 'date' from API)
                var apptDate = appt.date || appt.appointment_date || null;
                if (apptDate) {
                    document.getElementById('schedule-date-picker').value = apptDate;
                }
                // Load appointments for that date, then open the side panel
                loadScheduleAppointments();
                setTimeout(function() {
                    openSsp(
                        parseInt(apptId),
                        appt.patient_name || '',
                        appt.time || '',
                        appt.status || ''
                    );
                }, 700);
            } else {
                loadScheduleAppointments();
            }
            window.history.replaceState({}, '', window.location.pathname);
        })
        .catch(function() { loadScheduleAppointments(); });
})();
</script>
JS;

include BASE_PATH . '/app/views/layouts/app-doctor.php';