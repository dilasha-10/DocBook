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
    <script>var BASE_URL = "<?= BASE_URL ?>";</script>
    <script>
        (function(){
            if(localStorage.getItem('docbook-theme')==='dark'){
                document.documentElement.setAttribute('data-theme','dark');
            }
        })();
    </script>
    <style>
    /* ── Notification Bell ── */
    .notif-bell-btn {
        position: relative;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        font-size: 17px;
        padding: 6px 8px;
        border-radius: 8px;
        transition: color .15s, background .15s;
        display: flex;
        align-items: center;
    }
    .notif-bell-btn:hover { color: var(--text); background: var(--hover-bg, rgba(0,0,0,.06)); }
    .notif-badge {
        position: absolute;
        top: 2px; right: 2px;
        min-width: 16px; height: 16px;
        background: #ef4444;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        border-radius: 8px;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 3px;
        line-height: 1;
        pointer-events: none;
    }
    .notif-badge.visible { display: flex; }

    /* ── Notification Dropdown ── */
    .notif-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 340px;
        max-height: 420px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(0,0,0,.15);
        z-index: 9999;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }
    .notif-dropdown.open { display: flex; }

    .notif-dropdown-header {
        padding: 14px 16px 10px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .notif-dropdown-header h4 {
        font-size: 14px;
        font-weight: 800;
        color: var(--text);
        margin: 0;
    }
    .notif-mark-all-btn {
        font-size: 11px;
        font-weight: 700;
        color: var(--primary);
        background: none;
        border: none;
        cursor: pointer;
        padding: 3px 6px;
        border-radius: 5px;
        transition: background .12s;
    }
    .notif-mark-all-btn:hover { background: var(--hover-bg, rgba(0,0,0,.05)); }

    .notif-list { overflow-y: auto; flex: 1; }

    .notif-item {
        display: flex;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background .1s;
        text-decoration: none;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: var(--hover-bg, rgba(0,0,0,.04)); }
    .notif-item.unread { background: color-mix(in srgb, var(--primary) 6%, transparent); }
    .notif-item.unread:hover { background: color-mix(in srgb, var(--primary) 10%, transparent); }

    .notif-icon {
        width: 34px; height: 34px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0; margin-top: 1px;
    }
    .notif-icon.type-appointment_booked    { background: #d1fae5; color: #065f46; }
    .notif-icon.type-appointment_cancelled { background: #fee2e2; color: #991b1b; }
    .notif-icon.type-appointment_confirmed { background: #dbeafe; color: #1e40af; }
    .notif-icon.type-lab_report_uploaded   { background: #f3e8ff; color: #7e22ce; }
    .notif-icon.type-system_maintenance    { background: #fef3c7; color: #92400e; }
    .notif-icon.type-targeted              { background: #ede9fe; color: #5b21b6; }

    .notif-body { flex: 1; min-width: 0; }
    .notif-title {
        font-size: 13px; font-weight: 700; color: var(--text);
        margin-bottom: 2px; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .notif-msg {
        font-size: 12px; color: var(--muted); line-height: 1.4;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
    }
    .notif-time { font-size: 10px; color: var(--hint); margin-top: 4px; white-space: nowrap; }
    .notif-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--primary); flex-shrink: 0; margin-top: 6px; display: none;
    }
    .notif-item.unread .notif-dot { display: block; }

    .notif-empty { padding: 32px 16px; text-align: center; color: var(--muted); font-size: 13px; }
    .notif-empty i { font-size: 28px; margin-bottom: 8px; display: block; opacity: .35; }

    .notif-dropdown-footer {
        padding: 10px 16px; border-top: 1px solid var(--border);
        text-align: center; flex-shrink: 0;
    }
    .notif-dropdown-footer a { font-size: 12px; font-weight: 700; color: var(--primary); text-decoration: none; }

    .notif-wrap-btn { position: relative; }

    /* Sidebar badge */
    .sidebar-notif-badge {
        margin-left: auto; background: #ef4444; color: #fff;
        border-radius: 10px; font-size: 10px; font-weight: 800;
        padding: 1px 6px; display: none; line-height: 1.4;
    }
    .sidebar-notif-badge.visible { display: inline-block; }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
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
            <!-- Theme toggle -->
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="fa fa-moon" id="themeIcon"></i>
            </button>

            <?php if (isset($doctor)): ?>
                <!-- Notification Bell -->
                <div class="notif-wrap-btn" id="notifWrap">
                    <button class="notif-bell-btn" id="notifBellBtn" aria-label="Notifications">
                        <i class="fa fa-bell"></i>
                        <span class="notif-badge" id="notifBadge"></span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown-header">
                            <h4>Notifications</h4>
                            <button class="notif-mark-all-btn" id="notifMarkAll">Mark all read</button>
                        </div>
                        <div class="notif-list" id="notifList">
                            <div class="notif-empty"><i class="fa fa-bell-slash"></i>No notifications yet</div>
                        </div>
                        <div class="notif-dropdown-footer">
                            <a href="<?= BASE_URL ?>/doctor/notifications">View all notifications</a>
                        </div>
                    </div>
                </div>

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
            <a href="<?= BASE_URL ?>/doctor/notifications"
               class="sidebar-link <?php echo request_is('/doctor/notifications') ? 'active' : ''; ?>">
                <i class="fa fa-bell sidebar-icon"></i><span>Notifications</span>
                <span class="sidebar-notif-badge" id="sidebarNotifBadge"></span>
            </a>
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
        <li><a href="<?= BASE_URL ?>/doctor/dashboard"     class="<?php echo request_is('/doctor/dashboard')     ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> Dashboard</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/schedule"      class="<?php echo request_is('/doctor/schedule')      ? 'active' : ''; ?>"><i class="fa fa-calendar-alt"></i> My Schedule</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/patients"      class="<?php echo request_is('/doctor/patients')      ? 'active' : ''; ?>"><i class="fa fa-users"></i> Patients</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/availability"  class="<?php echo request_is('/doctor/availability')  ? 'active' : ''; ?>"><i class="fa fa-clock"></i> Availability</a></li>
        <?php if (isset($doctor)): ?>
        <li><a href="<?= BASE_URL ?>/doctor/notifications" class="<?php echo request_is('/doctor/notifications') ? 'active' : ''; ?>"><i class="fa fa-bell"></i> Notifications</a></li>
        <li><a href="<?= BASE_URL ?>/doctor/profile"       class="<?php echo request_is('/doctor/profile')       ? 'active' : ''; ?>"><i class="fa fa-user-circle"></i> Profile</a></li>
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
</script>
<script src="<?= BASE_URL ?>/js/main.js"></script>
<script>
(function(){
    // ── Theme toggle ──────────────────────────────────────────
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

    // ── Mobile drawer ─────────────────────────────────────────
    var hamburger   = document.getElementById('hamburgerBtn');
    var drawer      = document.getElementById('mobileDrawer');
    var drawerClose = document.getElementById('mobileDrawerClose');
    var mobileOvly  = document.getElementById('mobileOverlay');

    function openDrawer()  { drawer.classList.add('open'); mobileOvly.classList.add('open'); hamburger && hamburger.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeDrawer() { drawer.classList.remove('open'); mobileOvly.classList.remove('open'); hamburger && hamburger.classList.remove('open'); document.body.style.overflow = ''; }

    if (hamburger)   hamburger.addEventListener('click', function(){ drawer.classList.contains('open') ? closeDrawer() : openDrawer(); });
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
    if (mobileOvly)  mobileOvly.addEventListener('click', closeDrawer);

    // ── Sidebar toggle (desktop collapse) ────────────────────
    var sidebarToggle  = document.getElementById('sidebarToggle');
    var sidebar        = document.getElementById('sidebar');
    var sidebarOverlay = document.getElementById('sidebarOverlay');

    var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (collapsed) { document.body.classList.add('sidebar-collapsed'); }

    function closeSidebarMobile() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('open'); document.body.style.overflow = ''; }

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

    // ── Notification Bell ─────────────────────────────────────
    <?php if (isset($doctor)): ?>
    var bellBtn      = document.getElementById('notifBellBtn');
    var dropdown     = document.getElementById('notifDropdown');
    var badge        = document.getElementById('notifBadge');
    var sidebarBadge = document.getElementById('sidebarNotifBadge');
    var listEl       = document.getElementById('notifList');
    var markAllBtn   = document.getElementById('notifMarkAll');
    var dropdownOpen = false;

    var typeIcon = {
        appointment_booked:      'fa-calendar-check',
        appointment_cancelled:   'fa-calendar-xmark',
        appointment_confirmed:   'fa-calendar-circle-user',
        appointment_rescheduled: 'fa-calendar-pen',
        lab_report_uploaded:     'fa-file-medical',
        system_maintenance:      'fa-triangle-exclamation',
        targeted:                'fa-bell',
    };

    function timeAgo(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)    return 'Just now';
        if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function setBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.add('visible');
            if (sidebarBadge) { sidebarBadge.textContent = count > 99 ? '99+' : count; sidebarBadge.classList.add('visible'); }
        } else {
            badge.classList.remove('visible');
            if (sidebarBadge) sidebarBadge.classList.remove('visible');
        }
    }

    function renderNotifications(items) {
        if (!items || !items.length) {
            listEl.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i>No notifications yet</div>';
            return;
        }
        listEl.innerHTML = items.slice(0, 15).map(function(n) {
            var icon   = typeIcon[n.type] || 'fa-bell';
            var unread = n.is_read == 0;
            return '<div class="notif-item ' + (unread ? 'unread' : '') + '" data-id="' + n.id + '">' +
                '<div class="notif-icon type-' + n.type + '"><i class="fa ' + icon + '"></i></div>' +
                '<div class="notif-body">' +
                    '<div class="notif-title">' + escHtml(n.title)      + '</div>' +
                    '<div class="notif-msg">'   + escHtml(n.message)    + '</div>' +
                    '<div class="notif-time">'  + timeAgo(n.created_at) + '</div>' +
                '</div>' +
                '<div class="notif-dot"></div>' +
            '</div>';
        }).join('');

        listEl.querySelectorAll('.notif-item').forEach(function(el) {
            el.addEventListener('click', function() {
                el.classList.remove('unread');
                var dot = el.querySelector('.notif-dot');
                if (dot) dot.style.display = 'none';
                fetch(BASE_URL + '/api/notifications/read', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: parseInt(el.dataset.id)})
                });
            });
        });
    }

    function loadNotifications() {
        fetch(BASE_URL + '/api/notifications?limit=15')
            .then(function(r){ return r.json(); })
            .then(function(d) {
                setBadge(d.unread_count || 0);
                renderNotifications(d.notifications || []);
            })
            .catch(function(){});
    }

    function pollBadge() {
        fetch(BASE_URL + '/api/notifications/unread-count')
            .then(function(r){ return r.json(); })
            .then(function(d){ setBadge(d.count || 0); })
            .catch(function(){});
    }
    pollBadge();
    setInterval(pollBadge, 60000);

    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownOpen = !dropdownOpen;
        dropdown.classList.toggle('open', dropdownOpen);
        if (dropdownOpen) loadNotifications();
    });

    document.addEventListener('click', function(e) {
        if (dropdownOpen && !dropdown.contains(e.target) && e.target !== bellBtn) {
            dropdownOpen = false;
            dropdown.classList.remove('open');
        }
    });

    markAllBtn.addEventListener('click', function() {
        fetch(BASE_URL + '/api/notifications/read', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({all: true})
        }).then(function() {
            setBadge(0);
            listEl.querySelectorAll('.notif-item').forEach(function(el) {
                el.classList.remove('unread');
                var dot = el.querySelector('.notif-dot');
                if (dot) dot.style.display = 'none';
            });
        });
    });
    <?php endif; ?>
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>