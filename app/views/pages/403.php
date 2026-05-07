<?php
$title = 'Access Denied';
ob_start();
?>

<div class="page-content" style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="max-width:540px;text-align:center;">
        <div style="font-size:72px;font-weight:800;color:var(--blue);line-height:1;">403</div>
        <h1 style="margin:12px 0 8px;font-size:28px;">Access denied</h1>
        <p style="color:var(--muted);margin:0 0 20px;">You do not have permission to view this page.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/" class="btn-primary">Go to home</a>
            <a href="<?= BASE_URL ?>/logout" style="padding:10px 18px;border-radius:10px;border:1px solid var(--border);color:var(--text);text-decoration:none;">Switch account</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
