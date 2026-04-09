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
</head>
<body>

<!-- ══ TOP NAVBAR ══════════════════════════════════════════ -->
<nav class="navbar">
    <div class="navbar-inner">
        <!-- Sidebar toggle (visible always) -->
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <a href="/categories" class="nav-logo">Doc<span>Book</span></a>

        <div class="nav-links">
            <a href="/dashboard"  class="nav-link <?php echo request_is('/dashboard')  ? 'active' : ''; ?>">My Appointments</a>
            <a href="/categories" class="nav-link <?php echo request_is('/categories') ? 'active' : ''; ?>">Find Doctors</a>
            <a href="/about"      class="nav-link <?php echo request_is('/about')      ? 'active' : ''; ?>">About</a>
            <a href="/contact"    class="nav-link <?php echo request_is('/contact')    ? 'active' : ''; ?>">Contact</a>
        </div>
        <div class="nav-actions">
            <?php if (isset($user)): ?>
                <a href="/profile" class="user-chip" style="text-decoration:none;cursor:pointer;">
                    <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                    <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                </a>
                <a href="/logout" class="btn-signout">Sign out</a>
            <?php else: ?>
                <a href="/login"  class="btn-signin">Sign in</a>
                <a href="/signup" class="btn-signup">Sign up</a>
            <?php endif; ?>
        </div>

        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ══ PAGE SHELL (sidebar + content) ══════════════════════ -->
<div class="page-shell">

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-brand">Doc<span>Book</span></span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="/dashboard" class="sidebar-link <?php echo request_is('/dashboard') ? 'active' : ''; ?>">
                <i class="fa fa-th-large sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
            <a href="/dashboard#upcoming" class="sidebar-link sidebar-sub-link">
                <i class="fa fa-calendar-check sidebar-icon"></i>
                <span>Appointments</span>
            </a>
            <a href="/dashboard#past" class="sidebar-link sidebar-sub-link">
                <i class="fa fa-clock-rotate-left sidebar-icon"></i>
                <span>History</span>
            </a>
            <a href="/categories" class="sidebar-link <?php echo request_is('/categories') ? 'active' : ''; ?>">
                <i class="fa fa-stethoscope sidebar-icon"></i>
                <span>Find Doctors</span>
            </a>
            <?php if (isset($user)): ?>
            <div class="sidebar-section-label">Account</div>
            <a href="/profile" class="sidebar-link <?php echo request_is('/profile') ? 'active' : ''; ?>">
                <i class="fa fa-user sidebar-icon"></i>
                <span>My Profile</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── MAIN CONTENT ────────────────────────────────────── -->
    <main class="main-wrap">
        <?php echo $content; ?>
    </main>

</div><!-- /.page-shell -->

<!-- Mobile menu drawer (kept for hamburger fallback) -->
<div class="mobile-menu" id="mobileMenu">
    <ul class="mobile-nav-links">
        <li><a href="/dashboard"  class="<?php echo request_is('/dashboard')  ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> My Appointments</a></li>
        <li><a href="/categories" class="<?php echo request_is('/categories') ? 'active' : ''; ?>"><i class="fa fa-stethoscope"></i> Find Doctors</a></li>
        <li><a href="/about"      class="<?php echo request_is('/about')      ? 'active' : ''; ?>"><i class="fa fa-circle-info"></i> About</a></li>
        <li><a href="/contact"    class="<?php echo request_is('/contact')    ? 'active' : ''; ?>"><i class="fa fa-envelope"></i> Contact</a></li>
        <?php if (isset($user)): ?>
        <li><a href="/profile"    class="<?php echo request_is('/profile')    ? 'active' : ''; ?>"><i class="fa fa-user"></i> Profile &amp; Settings</a></li>
        <?php endif; ?>
    </ul>
    <div class="mobile-nav-actions">
        <?php if (isset($user)): ?>
            <div class="user-chip" style="justify-content:center;">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
            </div>
            <a href="/logout" class="btn-signout" style="text-align:center;">Sign out</a>
        <?php else: ?>
            <a href="/login"  class="btn-signin" style="text-align:center;">Sign in</a>
            <a href="/signup" class="btn-signup" style="text-align:center;">Sign up</a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script src="<?= BASE_URL ?>/js/main.js"></script>
<script>
(function(){
    // ── Hamburger (mobile)
    var btn     = document.getElementById('hamburgerBtn');
    var menu    = document.getElementById('mobileMenu');
    var overlay = document.getElementById('mobileOverlay');
    function closeMobile() {
        btn.classList.remove('open');
        menu.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    if (btn) {
        btn.addEventListener('click', function(){
            var isOpen = menu.classList.contains('open');
            if (isOpen) { closeMobile(); }
            else {
                btn.classList.add('open'); menu.classList.add('open');
                overlay.classList.add('open'); document.body.style.overflow = 'hidden';
            }
        });
        overlay.addEventListener('click', closeMobile);
    }

    // ── Sidebar toggle
    var sidebarToggle  = document.getElementById('sidebarToggle');
    var sidebar        = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');

    // Restore sidebar state
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
                if (isOpen) { closeSidebarMobile(); }
                else {
                    sidebar.classList.add('open');
                    sidebarOverlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            }
        });
        sidebarOverlay.addEventListener('click', closeSidebarMobile);
    }
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>