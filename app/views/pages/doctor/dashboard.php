<?php
$title = 'Doctor Dashboard';

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName = explode(' ', $doctor['name'])[0] ?? 'Doctor';

ob_start();
?>

<div id="dashboard-view" class="view-container">
    <div class="view-header">
        <div class="greeting">
            <h1><?= htmlspecialchars($greeting) ?>, Dr. <?= htmlspecialchars($firstName) ?></h1>
            <p>You have <?= $stat_today ?> appointment<?= $stat_today !== 1 ? 's' : '' ?> today</p>
        </div>
        <button class="btn-primary"
                onclick="window.location.href='<?= BASE_URL ?>/doctor/availability'">
            Update availability
        </button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stat_today ?></div>
            <div class="stat-label">Today's appointments</div>
            <span class="stat-status status-active">Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stat_pending ?></div>
            <div class="stat-label">Pending approvals</div>
            <span class="stat-status status-attention">Action needed</span>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stat_week ?></div>
            <div class="stat-label">This week</div>
            <span class="stat-status status-track">On track</span>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stat_total ?></div>
            <div class="stat-label">Total patients</div>
            <span class="stat-status status-all">All time</span>
        </div>
    </div>

    <div class="schedule-section">
        <h3>Today's schedule</h3>
        <div class="appointment-list" id="appointment-list">
            <?php if (empty($appointments)): ?>
                <p style="color:var(--text-muted);">No appointments today.</p>
            <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                    <?php
                        $badgeClass = 'badge-pending';
                        if ($appt['status'] === 'Confirmed') $badgeClass = 'badge-confirmed';
                        $start = strtotime($appt['start_time']);
                        $end   = strtotime($appt['end_time']);
                        $duration = ($end - $start) / 60;
                    ?>
                    <div class="appointment-item" id="appt-<?= $appt['id'] ?>"
                         data-appointment-id="<?= $appt['id'] ?>"
                         onclick="openAppointmentDetail(<?= $appt['id'] ?>, this)">
                        <div class="appt-time"><?= date('g:i A', $start) ?></div>
                        <div class="appt-details">
                            <div class="appt-icon"><i class="fas fa-user"></i></div>
                            <div class="appt-info">
                                <h4><?= htmlspecialchars($appt['patient_name']) ?></h4>
                                <p><?= htmlspecialchars($appt['visit_reason'] ?? 'General consultation') ?> &middot; <?= (int)$duration ?> min</p>
                            </div>
                        </div>
                        <div class="appt-actions">
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($appt['status']) ?></span>
                            <?php if ($appt['status'] === 'Pending'): ?>
                                <button class="btn-sm btn-accept"
                                        onclick="handleAction(event, 'appt-<?= $appt['id'] ?>', 'accept')">Accept</button>
                                <button class="btn-sm btn-reject"
                                        onclick="handleAction(event, 'appt-<?= $appt['id'] ?>', 'reject')">Reject</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Patient panel (dashboard only) -->
<div id="patient-panel" class="patient-panel hidden">
    <div class="panel-header">
        <h2>Patient Details</h2>
        <button class="btn-close" onclick="closePatientDetail()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="panel-body">
        <div class="patient-info">
            <div class="patient-avatar"><i class="fas fa-user"></i></div>
            <h3 id="panel-patient-name"></h3>
            <p id="panel-visit-reason"></p>
            <span class="badge badge-confirmed" id="panel-status"></span>
        </div>
        <div class="comment-section">
            <h4>Visit History</h4>
            <div class="comment-list" id="comment-list"></div>
        </div>
        <div class="add-comment-section">
            <h4>Add Comment</h4>
            <textarea id="comment-input" class="comment-input"
                      placeholder="Enter your notes about this visit..."></textarea>
            <button class="btn-primary" onclick="saveComment()">Save Comment</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app-doctor.php';