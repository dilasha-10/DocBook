<?php
$title = 'My Schedule';
ob_start();
?>

<div class="view-container">
    <div class="view-header" style="justify-content:space-between;">
        <div class="greeting">
            <h1>My Schedule</h1>
            <p>View your schedule by date</p>
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

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
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
                return '<div class="appointment-item" style="cursor:default;">'
                    + '<div class="appt-time">' + a.time + '</div>'
                    + '<div class="appt-details">'
                    + '<div class="appt-icon"><i class="fas fa-user"></i></div>'
                    + '<div class="appt-info">'
                    + '<h4>' + escHtml(a.patient_name) + '</h4>'
                    + '<p>' + escHtml(reason) + ' &middot; ' + a.duration_minutes + ' min</p>'
                    + '</div></div>'
                    + '<div class="appt-actions">'
                    + '<span class="badge ' + badgeClass + '">' + escHtml(a.status) + '</span>'
                    + '<a href="' + BASE_URL + '/doctor/chat/' + a.id + '" class="btn-sm btn-accept" style="text-decoration:none;">'
                    + '<i class="fas fa-comment-dots"></i> Chat</a>'
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

loadScheduleAppointments();
</script>
JS;

include BASE_PATH . '/app/views/layouts/app-doctor.php';