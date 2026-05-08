<?php
$title = 'Admin Dashboard';

ob_start();

$extra_styles = <<<CSS
<style>

.adm-wrap {
    padding: 40px 24px 60px;
    max-width: 960px;
    margin: 0 auto;
}

.page-header   { margin-bottom: 32px; }
.page-title    { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* Two-column grid that collapses to one column on narrow screens */
.nav-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
@media (max-width: 560px) { .nav-grid { grid-template-columns: 1fr; } }

/* Card-style anchor tag so keyboard nav and right-click open-in-new-tab work without JS */
.nav-tile {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px 26px;
    text-decoration: none;
    display: flex;
    align-items: flex-start;
    gap: 18px;
    transition: border-color .15s, box-shadow .15s;
}
.nav-tile:hover {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 10%, transparent);
}

.nav-tile-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}

/* Colour variants — one per section so each tile has a distinct visual identity */
.icon-blue   { background: #dbeafe; color: #1d4ed8; }
.icon-yellow { background: #fef9c3; color: #854d0e; }
.icon-purple { background: #f3e8ff; color: #7e22ce; }
.icon-teal   { background: #ccfbf1; color: #0f766e; }
.icon-sky    { background: #e0f2fe; color: #0369a1; }

/* min-width: 0 prevents the flex child from overflowing the tile on long labels */
.nav-tile-body  { min-width: 0; }
.nav-tile-label { font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 5px; }
.nav-tile-desc  { font-size: 13px; color: var(--muted); line-height: 1.5; }
.nav-tile-arrow { font-size: 12px; color: var(--blue); font-weight: 700; margin-top: 10px; display: block; }

</style>
CSS;

$content = ob_get_clean();
ob_start();
?>

<div class="adm-wrap">

    <div class="page-header">
        <h1 class="page-title">
            <i class="fa fa-th-large" style="color:var(--blue); margin-right:8px;"></i>
            Admin Dashboard
        </h1>
        <p class="page-subtitle">Select a section to manage</p>
    </div>

    <div class="nav-grid">

        <!-- View and filter all eSewa payment records -->
        <a href="<?= BASE_URL ?>/admin/transactions" class="nav-tile">
            <div class="nav-tile-icon icon-blue"><i class="fa fa-receipt"></i></div>
            <div class="nav-tile-body">
                <div class="nav-tile-label">Transactions</div>
                <div class="nav-tile-desc">View and filter all eSewa payment records across the platform.</div>
                <span class="nav-tile-arrow">Go to Transactions →</span>
            </div>
        </a>

        <!-- Send broadcast or targeted messages to users by role or individually -->
        <a href="<?= BASE_URL ?>/admin/notifications" class="nav-tile">
            <div class="nav-tile-icon icon-yellow"><i class="fa fa-bullhorn"></i></div>
            <div class="nav-tile-body">
                <div class="nav-tile-label">Notification Centre</div>
                <div class="nav-tile-desc">Send broadcast or targeted notifications to users by role.</div>
                <span class="nav-tile-arrow">Go to Notifications →</span>
            </div>
        </a>

        <!-- Review patient chatbot conversations the AI could not resolve -->
        <a href="<?= BASE_URL ?>/admin/chatbot-escalations" class="nav-tile">
            <div class="nav-tile-icon icon-purple"><i class="fa fa-robot"></i></div>
            <div class="nav-tile-body">
                <div class="nav-tile-label">Chatbot Escalations</div>
                <div class="nav-tile-desc">Review and resolve support tickets escalated from the chatbot.</div>
                <span class="nav-tile-arrow">Go to Escalations →</span>
            </div>
        </a>

        <!-- Searchable log of every recorded platform action, filterable and exportable as CSV -->
        <a href="<?= BASE_URL ?>/admin/audit-trail" class="nav-tile">
            <div class="nav-tile-icon icon-teal"><i class="fa fa-shield-halved"></i></div>
            <div class="nav-tile-body">
                <div class="nav-tile-label">Audit Trail</div>
                <div class="nav-tile-desc">Full log of every admin action, login, and logout event across the platform.</div>
                <span class="nav-tile-arrow">Go to Audit Trail →</span>
            </div>
        </a>

        <!-- Configure working hours, slot duration, max appointments, holidays, and feature toggles (D3-05) -->
        <a href="<?= BASE_URL ?>/admin/system-settings" class="nav-tile">
            <div class="nav-tile-icon icon-sky"><i class="fa fa-sliders"></i></div>
            <div class="nav-tile-body">
                <div class="nav-tile-label">System Settings</div>
                <div class="nav-tile-desc">Configure working hours, slot duration, max appointments, holidays, and feature toggles.</div>
                <span class="nav-tile-arrow">Go to Settings →</span>
            </div>
        </a>

    </div>

</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app.php';
?>