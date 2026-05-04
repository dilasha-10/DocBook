<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook <?php echo isset($title) ? '– ' . htmlspecialchars($title) : '– Lab Admin'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php if (isset($extra_styles)) echo $extra_styles; ?>
    <script>var BASE_URL = "<?= BASE_URL ?>";</script>
    <script>
        (function(){
            // Default is light mode; only switch to dark if user explicitly chose it
            var saved = localStorage.getItem('docbook-theme');
            if(saved === 'dark'){
                document.documentElement.setAttribute('data-theme','dark');
            } else if(!saved) {
                // First visit: set light as default, do NOT set dark
                localStorage.setItem('docbook-theme', 'light');
            }
        })();
    </script>
</head>
<body>

<!-- TOP NAVBAR — consistent with doc & patient portals -->
<nav class="navbar">
    <div class="navbar-inner">
        <!-- Sidebar toggle (desktop only) -->
        <button class="sidebar-toggle desktop-only" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <a href="<?= BASE_URL ?>/" class="nav-brand">Doc<span>Book</span></a>

        <!-- Lab admin: show portal label, no patient nav links -->
        <div class="nav-links">
            <span class="nav-link" style="opacity:.5;cursor:default;pointer-events:none;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;">Lab Portal</span>
        </div>

        <!-- Right actions -->
        <div class="nav-actions">
            <!-- Theme toggle -->
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="fa fa-moon" id="themeIcon"></i>
            </button>

            <?php if (isset($user)): ?>
                <!-- User chip links to profile -->
                <a href="<?= BASE_URL ?>/lab-admin/profile" class="user-chip" style="text-decoration:none;cursor:pointer;">
                    <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'LA', 0, 2)); ?></div>
                    <span class="user-chip-name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                </a>
                <a href="<?= BASE_URL ?>/logout" class="btn-signout">Sign out</a>
            <?php endif; ?>

            <!-- Hamburger (mobile only) -->
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- PAGE SHELL -->
<div class="page-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Lab Portal</div>
            <a href="<?= BASE_URL ?>/lab-admin/dashboard"
               class="sidebar-link <?php echo request_is('/lab-admin/dashboard') ? 'active' : ''; ?>">
                <i class="fa fa-flask sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
            <?php if (isset($user)): ?>
            <div class="sidebar-section-label">Account</div>
            <a href="<?= BASE_URL ?>/lab-admin/profile"
               class="sidebar-link <?php echo request_is('/lab-admin/profile') ? 'active' : ''; ?>">
                <i class="fa fa-user sidebar-icon"></i>
                <span>My Profile</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Sidebar footer: user profile card (centered) -->
        <div class="sidebar-footer" style="padding:12px;">
            <?php if (isset($user)): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--hover);border-radius:10px;border:1px solid var(--border);min-width:0;">
                <div class="avatar-circle" style="width:36px;height:36px;font-size:13px;flex-shrink:0;"><?php echo strtoupper(substr($user['name'] ?? 'LA', 0, 2)); ?></div>
                <div style="min-width:0;flex:1;">
                    <div style="font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                    <div style="font-size:11px;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <i class="fa fa-envelope" style="font-size:9px;opacity:.6;flex-shrink:0;"></i>
                        <span style="overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                    </div>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- MAIN CONTENT -->
    <main class="main-wrap">
        <?php echo $content; ?>
    </main>

</div><!-- /.page-shell -->

<!-- MOBILE NAV DRAWER (slides from RIGHT) -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
        <span class="mobile-drawer-brand">Doc<span>Book</span></span>
        <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
            <i class="fa fa-xmark"></i>
        </button>
    </div>
    <ul class="mobile-nav-links">
        <li><a href="<?= BASE_URL ?>/lab-admin/dashboard" class="<?php echo request_is('/lab-admin/dashboard') ? 'active' : ''; ?>"><i class="fa fa-flask"></i> Dashboard</a></li>
        <?php if (isset($user)): ?>
        <li><a href="<?= BASE_URL ?>/lab-admin/profile"  class="<?php echo request_is('/lab-admin/profile')  ? 'active' : ''; ?>"><i class="fa fa-user"></i> My Profile</a></li>
        <?php endif; ?>
    </ul>
    <div class="mobile-nav-actions">
        <?php if (isset($user)): ?>
            <div class="user-chip" style="justify-content:center;">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'LA', 0, 2)); ?></div>
                <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="btn-signout" style="text-align:center;">Sign out</a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script src="<?= BASE_URL ?>/js/main.js"></script>
<script>
(function(){
    // Theme toggle
    var themeBtn  = document.getElementById('themeToggleBtn');
    var themeIcon = document.getElementById('themeIcon');

    function applyTheme(dark) {
        if (dark) {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (themeIcon) themeIcon.className = 'fa fa-sun';
            if (themeBtn)  themeBtn.setAttribute('title', 'Switch to light mode');
        } else {
            document.documentElement.removeAttribute('data-theme');
            if (themeIcon) themeIcon.className = 'fa fa-moon';
            if (themeBtn)  themeBtn.setAttribute('title', 'Switch to dark mode');
        }
    }

    applyTheme(localStorage.getItem('docbook-theme') === 'dark');

    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            localStorage.setItem('docbook-theme', isDark ? 'light' : 'dark');
            applyTheme(!isDark);
        });
    }

    // Mobile drawer
    var hamburger   = document.getElementById('hamburgerBtn');
    var drawer      = document.getElementById('mobileDrawer');
    var drawerClose = document.getElementById('mobileDrawerClose');
    var mobileOvly  = document.getElementById('mobileOverlay');

    function openDrawer()  { drawer.classList.add('open'); mobileOvly.classList.add('open'); hamburger && hamburger.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeDrawer() { drawer.classList.remove('open'); mobileOvly.classList.remove('open'); hamburger && hamburger.classList.remove('open'); document.body.style.overflow = ''; }

    if (hamburger)   hamburger.addEventListener('click', function(){ drawer.classList.contains('open') ? closeDrawer() : openDrawer(); });
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
    if (mobileOvly)  mobileOvly.addEventListener('click', closeDrawer);

    // Sidebar toggle (desktop collapse)
    var sidebarToggle  = document.getElementById('sidebarToggle');
    var sidebar        = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');

    var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (collapsed) { document.body.classList.add('sidebar-collapsed'); }

    function closeSidebarMobile() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(){
            if (window.innerWidth >= 768) {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));
            } else {
                var isOpen = sidebar.classList.contains('open');
                isOpen ? closeSidebarMobile() : (sidebar.classList.add('open'), sidebarOverlay.classList.add('open'), document.body.style.overflow = 'hidden');
            }
        });
        sidebarOverlay.addEventListener('click', closeSidebarMobile);
    }
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>