<?php
$title = 'Admin Dashboard';
$dashboard = $dashboard ?? ['demo' => true, 'total_doctors' => 0, 'total_patients' => 0, 'appointments' => []];
$demoMode = !empty($dashboard['demo']);
$stats = $dashboard['appointment_stats'] ?? [
    'today' => ['completed' => 0, 'cancelled' => 0],
    'week' => ['completed' => 0, 'cancelled' => 0],
    'month' => ['completed' => 0, 'cancelled' => 0],
];
$activity = $dashboard['activity'] ?? [];

ob_start();

$extra_styles = <<<CSS
<style>
    .admin-header { display:flex; flex-wrap:wrap; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:24px; }
    .admin-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-bottom:26px; }
    .admin-card { padding:22px; border-radius:18px; border:1px solid var(--border); background:var(--surface); box-shadow:0 10px 30px rgba(24,32,47,.05); }
    .admin-card h3 { margin:0 0 10px; font-size:14px; color:var(--muted); letter-spacing:.01em; text-transform:uppercase; }
    .admin-card .card-value { font-size:42px; font-weight:800; line-height:1; }
    .admin-notice { display:flex; align-items:center; gap:10px; padding:16px 20px; border-radius:14px; background:rgba(254,243,199,.7); border:1px solid rgba(245,158,11,.20); color:#92400e; margin-bottom:20px; }
    .admin-grid-2 { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; margin-bottom:26px; }
    .admin-chart-card { padding:22px; }
    .chart-header { display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin-bottom:18px; }
    .chart-tabs { display:flex; gap:8px; flex-wrap:wrap; }
    .chart-tab { padding:6px 14px; border-radius:999px; border:1px solid var(--border); background:var(--surface); color:var(--muted); font-size:12px; font-weight:700; cursor:pointer; text-transform:uppercase; letter-spacing:.04em; }
    .chart-tab.is-active { background:var(--blue); border-color:var(--blue); color:#fff; }
    .chart-bars { display:grid; gap:16px; }
    .chart-bar { display:grid; gap:8px; }
    .chart-bar-label { font-size:13px; color:var(--muted); text-transform:uppercase; letter-spacing:.03em; display:flex; justify-content:space-between; align-items:center; }
    .chart-bar-label strong { font-size:16px; color:var(--text); letter-spacing:0; }
    .chart-track { width:100%; height:10px; border-radius:999px; background:var(--border); overflow:hidden; }
    .chart-fill { height:100%; width:0; border-radius:999px; transition:width .25s ease; }
    .chart-fill.completed { background:linear-gradient(90deg,#2ea07a,#63d1a5); }
    .chart-fill.cancelled { background:linear-gradient(90deg,#c94040,#f08a8a); }
    .chart-legend { font-size:12px; color:var(--muted); margin-top:8px; }
    .activity-card { padding:22px; }
    .activity-list { list-style:none; margin:0; padding:0; display:grid; gap:12px; }
    .activity-item { border:1px solid var(--border); border-radius:14px; padding:12px 14px; background:var(--surface); display:grid; gap:6px; }
    .activity-title { font-weight:700; color:var(--text); font-size:14px; }
    .activity-meta { font-size:12px; color:var(--muted); display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
    .activity-pill { font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px; border:1px solid var(--border); text-transform:uppercase; letter-spacing:.03em; }
    .activity-pill.Completed { background:#e0f2fe; color:#0c4a6e; border-color:#bae6fd; }
    .activity-pill.Cancelled { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .activity-pill.Confirmed { background:#d1fae5; color:#166534; border-color:#a7f3d0; }
    .activity-pill.Pending { background:#fcefe6; color:#b45309; border-color:#fed7aa; }
    .activity-empty { padding:20px; border:1px dashed var(--border); border-radius:14px; text-align:center; color:var(--muted); }
    .admin-table { width:100%; border-collapse:collapse; }
    .admin-table th,
    .admin-table td { padding:14px 16px; text-align:left; border-bottom:1px solid var(--border); }
    .admin-table th { font-size:13px; color:var(--muted); letter-spacing:.01em; text-transform:uppercase; }
    .admin-table td { font-size:14px; color:var(--text); }
    .badge-status { display:inline-flex; align-items:center; justify-content:center; min-width:82px; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:700; text-transform:capitalize; }
    .badge-status.Pending { background:#fcefe6; color:#b45309; }
    .badge-status.Confirmed { background:#d1fae5; color:#166534; }
    .badge-status.Cancelled { background:#fee2e2; color:#991b1b; }
    .badge-status.Completed { background:#e0f2fe; color:#0c4a6e; }
    .empty-state-admin { padding:34px 28px; border:1px dashed var(--border); border-radius:18px; text-align:center; color:var(--muted); }
</style>
CSS;
?>

<div class="page-content">
    <div class="admin-header">
        <div>
            <h1>Admin dashboard</h1>
            <p style="color:var(--muted); max-width:760px;">Live totals for doctors, patients, and appointment activity. When the database is unavailable or empty, demo content is shown instead.</p>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <a href="<?= BASE_URL ?>/" class="btn-secondary">Home</a>
            <a href="<?= BASE_URL ?>/contact" class="btn-primary">Contact Support</a>
        </div>
    </div>

    <?php if ($demoMode): ?>
    <div class="admin-notice">
        <i class="fa fa-info-circle"></i>
        <div><strong>Demo data is active.</strong> No live database results were available, so this dashboard is showing sample records instead.</div>
    </div>
    <?php endif; ?>

    <div class="admin-cards">
        <div class="admin-card">
            <h3>Total doctors</h3>
            <div class="card-value"><?= number_format($dashboard['total_doctors']) ?></div>
            <p style="margin-top:10px; color:var(--muted);">Active doctors in the system.</p>
        </div>
        <div class="admin-card">
            <h3>Total patients</h3>
            <div class="card-value"><?= number_format($dashboard['total_patients']) ?></div>
            <p style="margin-top:10px; color:var(--muted);">Registered patient accounts.</p>
        </div>
        <div class="admin-card">
            <h3>Appointments shown</h3>
            <div class="card-value"><?= number_format(count($dashboard['appointments'])) ?></div>
            <p style="margin-top:10px; color:var(--muted);">Recent appointment records loaded for review.</p>
        </div>
    </div>

    <div class="admin-grid-2">
        <div class="card admin-chart-card">
            <div class="chart-header">
                <div>
                    <h2>Appointment statistics</h2>
                    <p style="color:var(--muted); margin-top:6px;">Completed vs cancelled appointments for <span id="chart-range-label">today</span>.</p>
                </div>
                <div class="chart-tabs" role="tablist" aria-label="Appointment stats range">
                    <button class="chart-tab is-active" type="button" data-range="today">Today</button>
                    <button class="chart-tab" type="button" data-range="week">This week</button>
                    <button class="chart-tab" type="button" data-range="month">This month</button>
                </div>
            </div>
            <div class="chart-bars">
                <div class="chart-bar">
                    <div class="chart-bar-label">Completed <strong id="chart-completed-count">0</strong></div>
                    <div class="chart-track"><div class="chart-fill completed" id="chart-completed-fill"></div></div>
                </div>
                <div class="chart-bar">
                    <div class="chart-bar-label">Cancelled <strong id="chart-cancelled-count">0</strong></div>
                    <div class="chart-track"><div class="chart-fill cancelled" id="chart-cancelled-fill"></div></div>
                </div>
            </div>
            <div class="chart-legend">Bars scale relative to the larger value in the selected range.</div>
        </div>

        <div class="card activity-card">
            <div class="section-head" style="margin-bottom:18px;">
                <h2>Recent activity</h2>
                <span class="count-badge"><?= count($activity) ?></span>
            </div>
            <?php if (count($activity) > 0): ?>
                <ul class="activity-list">
                    <?php foreach ($activity as $item):
                        $status = htmlspecialchars($item['status'] ?? 'Pending');
                        $title = htmlspecialchars($item['title'] ?? 'Appointment update');
                        $details = htmlspecialchars($item['details'] ?? '');
                        $reference = htmlspecialchars($item['reference'] ?? '');
                        $time = !empty($item['time']) ? date('M j, Y g:i A', strtotime($item['time'])) : '—';
                    ?>
                    <li class="activity-item">
                        <div class="activity-title"><?= $title ?></div>
                        <div class="activity-meta">
                            <span class="activity-pill <?= $status ?>"><?= $status ?></span>
                            <?php if ($reference): ?><span><?= $reference ?></span><?php endif; ?>
                            <?php if ($details): ?><span><?= $details ?></span><?php endif; ?>
                            <span><?= htmlspecialchars($time) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="activity-empty">No recent system activity yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="section-head" style="margin-bottom:18px;">
            <h2>Appointments</h2>
            <span class="count-badge"><?= count($dashboard['appointments']) ?></span>
        </div>

        <?php if (count($dashboard['appointments']) > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Specialty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dashboard['appointments'] as $appointment):
                    $status = htmlspecialchars($appointment['status'] ?? 'Pending');
                    $date = isset($appointment['date']) ? date('M j, Y', strtotime($appointment['date'])) : '—';
                ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['reference_number'] ?? '—') ?></td>
                    <td><?= $date ?> <span style="display:block;color:var(--muted);font-size:12px;"><?= isset($appointment['start_time']) ? date('g:i A', strtotime($appointment['start_time'])) : '—' ?></span></td>
                    <td><?= htmlspecialchars($appointment['patient_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($appointment['doctor_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($appointment['specialty'] ?? $appointment['category'] ?? '—') ?></td>
                    <td><span class="badge-status <?= $status ?>"><?= $status ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state-admin">
            <i class="fa fa-calendar-xmark" style="font-size:42px;margin-bottom:10px;display:inline-block;color:var(--muted);"></i>
            <p style="font-size:16px;margin:0.5rem 0 0;">No appointment records are available yet.</p>
            <p style="margin:8px 0 0;color:var(--muted);">Check your database connection or add new appointments through the application.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stats_json = json_encode($stats, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$extra_scripts = <<<JS
<script>
(function(){
    var stats = $stats_json || {};
    var tabs = document.querySelectorAll('.chart-tab');
    var completedCount = document.getElementById('chart-completed-count');
    var cancelledCount = document.getElementById('chart-cancelled-count');
    var completedFill = document.getElementById('chart-completed-fill');
    var cancelledFill = document.getElementById('chart-cancelled-fill');
    var rangeLabel = document.getElementById('chart-range-label');
    var labels = { today: 'today', week: 'this week', month: 'this month' };

    function render(range) {
        var data = stats[range] || { completed: 0, cancelled: 0 };
        var completed = Number(data.completed || 0);
        var cancelled = Number(data.cancelled || 0);
        var maxVal = Math.max(completed, cancelled, 1);

        completedCount.textContent = completed.toString();
        cancelledCount.textContent = cancelled.toString();
        completedFill.style.width = ((completed / maxVal) * 100).toFixed(1) + '%';
        cancelledFill.style.width = ((cancelled / maxVal) * 100).toFixed(1) + '%';
        rangeLabel.textContent = labels[range] || range;
    }

    tabs.forEach(function(tab){
        tab.addEventListener('click', function(){
            tabs.forEach(function(btn){ btn.classList.remove('is-active'); });
            tab.classList.add('is-active');
            render(tab.dataset.range || 'today');
        });
    });

    render('today');
})();
</script>
JS;

$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
