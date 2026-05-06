<?php
$title = 'Admin Dashboard';
$dashboard = $dashboard ?? ['demo' => true, 'total_doctors' => 0, 'total_patients' => 0, 'appointments' => []];
$demoMode = !empty($dashboard['demo']);

ob_start();

$extra_styles = <<<CSS
<style>
    .admin-header { display:flex; flex-wrap:wrap; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:24px; }
    .admin-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin-bottom:26px; }
    .admin-card { padding:22px; border-radius:18px; border:1px solid var(--border); background:var(--surface); box-shadow:0 10px 30px rgba(24,32,47,.05); }
    .admin-card h3 { margin:0 0 10px; font-size:14px; color:var(--muted); letter-spacing:.01em; text-transform:uppercase; }
    .admin-card .card-value { font-size:42px; font-weight:800; line-height:1; }
    .admin-notice { display:flex; align-items:center; gap:10px; padding:16px 20px; border-radius:14px; background:rgba(254,243,199,.7); border:1px solid rgba(245,158,11,.20); color:#92400e; margin-bottom:20px; }
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
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
