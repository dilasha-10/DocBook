<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook <?php echo isset($title) ? '– ' . htmlspecialchars($title) : '– Doctor Appointment Booking'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php if (isset($extra_styles)) echo $extra_styles; ?>
    <script>var BASE_URL = "<?= BASE_URL ?>";</script>
    <script>
        (function(){
            if(localStorage.getItem('docbook-theme')==='dark'){
                document.documentElement.setAttribute('data-theme','dark');
            }
        })();
    </script>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <!-- Sidebar toggle (desktop only - collapses sidebar) -->
        <button class="sidebar-toggle desktop-only" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <a href="<?= BASE_URL ?>/" class="nav-brand">Doc<span>Book</span></a>

        <!-- Desktop nav links -->
        <div class="nav-links">
            <a href="<?= BASE_URL ?>/about"      class="nav-link <?php echo request_is('/about')      ? 'active' : ''; ?>">About</a>
            <a href="<?= BASE_URL ?>/dashboard"  class="nav-link <?php echo request_is('/dashboard')  ? 'active' : ''; ?>">My Appointments</a>
            <a href="<?= BASE_URL ?>/categories" class="nav-link <?php echo request_is('/categories') ? 'active' : ''; ?>">Find Doctors</a>
            <a href="<?= BASE_URL ?>/contact"    class="nav-link <?php echo request_is('/contact')    ? 'active' : ''; ?>">Contact</a>
        </div>

        <!-- Right actions (always visible) -->
        <div class="nav-actions">
            <!-- Theme toggle (single, always in navbar) -->
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="fa fa-moon" id="themeIcon"></i>
            </button>
            <?php if (isset($user)): ?>
                <a href="<?= BASE_URL ?>/profile" class="user-chip" style="text-decoration:none;cursor:pointer;">
                    <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                    <span class="user-chip-name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                </a>
                <a href="<?= BASE_URL ?>/logout" class="btn-signout">Sign out</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login"  class="btn-signin">Sign in</a>
                <a href="<?= BASE_URL ?>/signup" class="btn-signup">Sign up</a>
            <?php endif; ?>

            <!-- Hamburger (mobile only — opens nav drawer on RIGHT) -->
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<!-- PAGE SHELL (sidebar + content) -->
<div class="page-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?php echo request_is('/dashboard') ? 'active' : ''; ?>">
                <i class="fa fa-th-large sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/dashboard#upcoming" class="sidebar-link sidebar-sub-link">
                <i class="fa fa-calendar-check sidebar-icon"></i>
                <span>Appointments</span>
            </a>
            <a href="<?= BASE_URL ?>/dashboard#past" class="sidebar-link sidebar-sub-link">
                <i class="fa fa-clock-rotate-left sidebar-icon"></i>
                <span>History</span>
            </a>
            <?php if (isset($user)): ?>
            <div class="sidebar-section-label">Account</div>
            <a href="<?= BASE_URL ?>/profile" class="sidebar-link <?php echo request_is('/profile') ? 'active' : ''; ?>">
                <i class="fa fa-user sidebar-icon"></i>
                <span>My Profile</span>
            </a>
            <?php endif; ?>
        </nav>
        <!-- Sidebar footer: NO theme toggle here — only one toggle lives in the navbar -->
        <div class="sidebar-footer">
            <?php if (isset($user)): ?>
            <div class="sidebar-user">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                    <div class="sidebar-user-role">Patient</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
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
        <li><a href="<?= BASE_URL ?>/about"      class="<?php echo request_is('/about')      ? 'active' : ''; ?>"><i class="fa fa-circle-info"></i> About</a></li>
        <li><a href="<?= BASE_URL ?>/dashboard"  class="<?php echo request_is('/dashboard')  ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> My Appointments</a></li>
        <li><a href="<?= BASE_URL ?>/categories" class="<?php echo request_is('/categories') ? 'active' : ''; ?>"><i class="fa fa-stethoscope"></i> Find Doctors</a></li>
        <li><a href="<?= BASE_URL ?>/contact"    class="<?php echo request_is('/contact')    ? 'active' : ''; ?>"><i class="fa fa-envelope"></i> Contact</a></li>
        <?php if (isset($user)): ?>
        <li><a href="<?= BASE_URL ?>/profile"    class="<?php echo request_is('/profile')    ? 'active' : ''; ?>"><i class="fa fa-user"></i> Profile &amp; Settings</a></li>
        <?php endif; ?>
    </ul>
    <div class="mobile-nav-actions">
        <?php if (isset($user)): ?>
            <div class="user-chip" style="justify-content:center;">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="btn-signout" style="text-align:center;">Sign out</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login"  class="btn-signin" style="text-align:center;">Sign in</a>
            <a href="<?= BASE_URL ?>/signup" class="btn-signup" style="text-align:center;">Sign up</a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script src="<?= BASE_URL ?>/js/main.js"></script>
<script>
(function(){
    // Single theme toggle (navbar only)
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

    // Mobile drawer (slides from RIGHT) — hamburger in nav-actions
    var hamburger    = document.getElementById('hamburgerBtn');
    var drawer       = document.getElementById('mobileDrawer');
    var drawerClose  = document.getElementById('mobileDrawerClose');
    var mobileOvly   = document.getElementById('mobileOverlay');

    function openDrawer() {
        drawer.classList.add('open');
        mobileOvly.classList.add('open');
        hamburger && hamburger.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer() {
        drawer.classList.remove('open');
        mobileOvly.classList.remove('open');
        hamburger && hamburger.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (hamburger)   hamburger.addEventListener('click', function(){ drawer.classList.contains('open') ? closeDrawer() : openDrawer(); });
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
    if (mobileOvly)  mobileOvly.addEventListener('click', closeDrawer);

    // ── Sidebar toggle (desktop: collapse/expand)
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
                isOpen ? closeSidebarMobile() : (sidebar.classList.add('open'), sidebarOverlay.classList.add('open'), document.body.style.overflow='hidden');
            }
        });
        sidebarOverlay.addEventListener('click', closeSidebarMobile);
    }
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

<?php
// Chatbot floating widget - only for logged-in patients
$_cbRole = $user['role'] ?? '';
if ($_cbRole === 'patient') {
    include BASE_PATH . '/app/views/partials/chatbot-widget.php';
}
?>

</body>
</html>