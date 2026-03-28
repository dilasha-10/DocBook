<?php
$title = 'Find Doctors';

ob_start();
?>
<div style="padding: 32px 48px;">

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 4px;">Find a Doctor</h1>
        <p style="color: var(--muted); font-size: 14px;">Browse by specialty or search by name</p>
    </div>

    <div style="display:flex; gap: 10px; margin-bottom: 24px;">
        <input id="doctor-search" class="input" type="text" placeholder="Search doctors..." style="max-width: 320px;">
        <button id="search-btn" class="btn-primary"><i class="fa fa-search"></i> Search</button>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 32px;">
        <button data-category="all" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:99px;font-size:13px;border:1px solid var(--blue);background:rgba(74,158,255,0.12);color:var(--blue);cursor:pointer;">
            <i class="fa fa-th"></i> All
        </button>
        <?php foreach ($categories as $cat): ?>
        <button data-category="<?php echo htmlspecialchars($cat['slug']); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:99px;font-size:13px;border:1px solid var(--border2);background:transparent;color:var(--muted);cursor:pointer;">
            <i class="fa <?php echo htmlspecialchars($cat['icon'] ?? 'fa-stethoscope'); ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div id="doctors-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
        <div style="grid-column:1/-1;text-align:center;color:var(--muted);padding:40px 0;">Loading&hellip;</div>
    </div>
</div>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layouts/app.php';