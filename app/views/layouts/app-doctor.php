<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook <?php echo isset($title) ? '– ' . htmlspecialchars($title) : '– Doctor Portal'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/drstyle.css">
    <?php if (isset($extra_styles)) echo $extra_styles; ?>
    <script>
        (function(){
            if(localStorage.getItem('docbook-theme')==='dark'){
                document.documentElement.setAttribute('data-theme','dark');
            }
        })();
    </script>
</head>
<body>

<!-- TOP NAVBAR identical to patient portal -->
<nav class="navbar">
    <div class="navbar-inner">
        <button class="sidebar-toggle desktop-only" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <a href="<?= BASE_URL ?>/" class="nav-brand">Doc<span>Book</span></a>

        <div class="nav-links">
            <a href="<?= BASE_URL ?>/doctor/dashboard"
               class="nav-link <?php echo request_is('/doctor/dashboard') ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?= BASE_URL ?>/doctor/schedule"
               class="nav-link <?php echo request_is('/doctor/schedule') ? 'active' : ''; ?>">My Schedule</a>
            <a href="<?= BASE_URL ?>/doctor/patients"
               class="nav-link <?php echo request_is('/doctor/patients') ? 'active' : ''; ?>">Patients</a>
            <a href="<?= BASE_URL ?>/doctor/availability"
               class="nav-link <?php echo request_is('/doctor/availability') ? 'active' : ''; ?>">Availability</a>
        </div>

        <div class="nav-actions">
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="fa fa-moon" id="themeIcon"></i>
            </button>
            <?php if (isset($doctor)): ?>
                <a href="<?= BASE_URL ?>/doctor/profile" class="user-chip" style="text-decoration:none;cursor:pointer;">
                    <div class="avatar-circle">
                        <?php if (!empty($doctor['photo'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($doctor['photo']) ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <?= htmlspecialchars(strtoupper(substr($doctor['name'] ?? 'DR', 0, 2))) ?>
                        <?php endif; ?>
                    </div>
                    <span class="user-chip-name">Dr. <?= htmlspecialchars($doctor['name'] ?? '') ?></span>
                </a>
                <a href="<?= BASE_URL ?>/logout" class="btn-signout">Sign out</a>
            <?php endif; ?>
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
            <div class="sidebar-section-label">Doctor Menu</div>
            <a href="<?= BASE_URL ?>/doctor/dashboard"
               class="sidebar-link <?php echo request_is('/doctor/dashboard') ? 'active' : ''; ?>">
                <i class="fa fa-th-large sidebar-icon"></i><span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/doctor/schedule"
               class="sidebar-link <?php echo request_is('/doctor/schedule') ? 'active' : ''; ?>">
                <i class="fa fa-calendar-alt sidebar-icon"></i><span>My Schedule</span>
            </a>
            <a href="<?= BASE_URL ?>/doctor/patients"
               class="sidebar-link <?php echo request_is('/doctor/patients') ? 'active' : ''; ?>">
                <i class="fa fa-users sidebar-icon"></i><span>Patients</span>
            </a>
            <a href="<?= BASE_URL ?>/doctor/availability"
               class="sidebar-link <?php echo request_is('/doctor/availability') ? 'active' : ''; ?>">
                <i class="fa fa-clock sidebar-icon"></i><span>Availability</span>
            </a>
            <?php if (isset($doctor)): ?>
            <div class="sidebar-section-label">Account</div>
            <a href="<?= BASE_URL ?>/doctor/profile"
               class="sidebar-link <?php echo request_is('/doctor/profile') ? 'active' : ''; ?>">
                <i class="fa fa-user-circle sidebar-icon"></i><span>My Profile</span>
            </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <?php if (isset($doctor)): ?>
            <div class="sidebar-user">
                <div class="avatar-circle">
                    <?php if (!empty($doctor['photo'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($doctor['photo']) ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= htmlspecialchars(strtoupper(substr($doctor['name'] ?? 'DR', 0, 2))) ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">Dr. <?= htmlspecialchars($doctor['name'] ?? '') ?></div>
                    <div class="sidebar-user-role"><?= htmlspecialchars($doctor['specialty'] ?? 'Doctor') ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="sidebar-logout" title="Sign out"><i class="fa fa-right-from-bracket"></i></a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-wrap">
        <?php echo $content; ?>
    </main>

</div>

<!-- MOBILE NAV DRAWER -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
        <span class="mobile-drawer-brand">Doc<span>Book</span></span>
        <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
            <i class="fa fa-xmark"></i>
        </button>
    </div>
    <ul class="mobile-nav-links">
        <li><a href="<?= BASE_URL ?>/doctor/dashboard" class="<?php echo request_is('/doctor/dashboard') ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> Dashboard</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/schedule"  class="<?php echo request_is('/doctor/schedule')  ? 'active' : ''; ?>"><i class="fa fa-calendar-alt"></i> My Schedule</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/patients"  class="<?php echo request_is('/doctor/patients')  ? 'active' : ''; ?>"><i class="fa fa-users"></i> Patients</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/availability" class="<?php echo request_is('/doctor/availability') ? 'active' : ''; ?>"><i class="fa fa-clock"></i> Availability</a></li>
        <?php if (isset($doctor)): ?>
        <li><a href="<?= BASE_URL ?>/doctor/profile"   class="<?php echo request_is('/doctor/profile')   ? 'active' : ''; ?>"><i class="fa fa-user-circle"></i> Profile</a></li>
        <?php endif; ?>
    </ul>
    <div class="mobile-nav-actions">
        <?php if (isset($doctor)): ?>
            <div class="user-chip" style="justify-content:center;">
                <div class="avatar-circle"><?= htmlspecialchars(strtoupper(substr($doctor['name'] ?? 'DR', 0, 2))) ?></div>
                <span>Dr. <?= htmlspecialchars($doctor['name'] ?? '') ?></span>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="btn-signout" style="text-align:center;">Sign out</a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<script>
const AUTH_DOCTOR_ID = <?= (int)($doctor['id'] ?? 0) ?>;
var BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/js/main.js"></script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

<script>
(function(){
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

    var hamburger   = document.getElementById('hamburgerBtn');
    var drawer      = document.getElementById('mobileDrawer');
    var drawerClose = document.getElementById('mobileDrawerClose');
    var mobileOvly  = document.getElementById('mobileOverlay');
    function openDrawer()  { drawer.classList.add('open'); mobileOvly.classList.add('open'); hamburger && hamburger.classList.add('open'); document.body.style.overflow='hidden'; }
    function closeDrawer() { drawer.classList.remove('open'); mobileOvly.classList.remove('open'); hamburger && hamburger.classList.remove('open'); document.body.style.overflow=''; }
    if (hamburger)   hamburger.addEventListener('click', function(){ drawer.classList.contains('open') ? closeDrawer() : openDrawer(); });
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
    if (mobileOvly)  mobileOvly.addEventListener('click', closeDrawer);

    var sidebarToggle  = document.getElementById('sidebarToggle');
    var sidebar        = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (collapsed) { document.body.classList.add('sidebar-collapsed'); }
    function closeSidebarMobile() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('open'); document.body.style.overflow=''; }
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

</body>
</html>