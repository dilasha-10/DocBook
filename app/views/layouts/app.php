<?php
$role = $user['role'] ?? 'guest';
$isAdmin = $role === 'admin';
$isDoctor = $role === 'doctor';
$isPatient = $role === 'patient' || $role === 'guest';

$navLinks = [];
if ($isAdmin) {
    $navLinks = [
        ['href' => '/admin', 'label' => 'Dashboard'],
        ['href' => '/about', 'label' => 'About'],
        ['href' => '/contact', 'label' => 'Contact'],
    ];
} elseif ($isDoctor) {
    $navLinks = [
        ['href' => '/doctor/dashboard', 'label' => 'Dashboard'],
        ['href' => '/doctor/schedule', 'label' => 'Schedule'],
        ['href' => '/doctor/patients', 'label' => 'Patients'],
    ];
} else {
    $navLinks = [
        ['href' => '/about', 'label' => 'About'],
        ['href' => '/dashboard', 'label' => 'My Appointments'],
        ['href' => '/categories', 'label' => 'Find Doctors'],
        ['href' => '/contact', 'label' => 'Contact'],
    ];
}

$userHome = '/dashboard';
if ($isAdmin) {
    $userHome = '/admin';
} elseif ($isDoctor) {
    $userHome = '/doctor/dashboard';
}
?>
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

<<<<<<< HEAD
<!-- ══ TOP NAVBAR ══════════════════════════════════════════ -->
=======
<!-- Broadcast Notification Banner -->
<?php if (isset($user)): ?>
<div id="broadcastBanner" style="display:none; background:#fef08a; color:#713f12; padding:10px 48px 10px 20px; text-align:center; font-size:14px; font-weight:600; position:relative; z-index:1000; border-bottom:1px solid #fde047;"><i class="fa fa-bullhorn" style="margin-right:8px;opacity:.8;"></i>
    <span id="broadcastBannerText"></span>
    <button onclick="dismissBroadcastBanner()" style="background:none;border:none;color:#713f12;font-size:18px;cursor:pointer;position:absolute;right:16px;top:50%;transform:translateY(-50%);opacity:.7;">&#x2715;</button>
</div>
<?php endif; ?>

<!-- TOP NAVBAR -->
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
<nav class="navbar">
    <div class="navbar-inner">
        <!-- Sidebar toggle (desktop only — collapses sidebar) -->
        <button class="sidebar-toggle desktop-only" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fa fa-bars"></i>
        </button>

        <a href="<?= BASE_URL ?>/" class="nav-brand">Doc<span>Book</span></a>

        <!-- Desktop nav links -->
        <div class="nav-links">
<<<<<<< HEAD
            <?php foreach ($navLinks as $link): ?>
                <a href="<?= BASE_URL . $link['href'] ?>" class="nav-link <?php echo request_is($link['href']) ? 'active' : ''; ?>">
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            <?php endforeach; ?>
=======
            <a href="<?= BASE_URL ?>/about"      class="nav-link <?php echo request_is('/about')      ? 'active' : ''; ?>">About</a>
            <?php if (($user['role'] ?? '') !== 'admin'): ?>
            <a href="<?= BASE_URL ?>/dashboard"  class="nav-link <?php echo request_is('/dashboard')  ? 'active' : ''; ?>">My Appointments</a>
            <a href="<?= BASE_URL ?>/categories" class="nav-link <?php echo request_is('/categories') ? 'active' : ''; ?>">Find Doctors</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/contact"    class="nav-link <?php echo request_is('/contact')    ? 'active' : ''; ?>">Contact</a>
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
        </div>

        <!-- Right actions (always visible) -->
        <div class="nav-actions">
            <!-- Theme toggle (single, always in navbar) -->
            <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="fa fa-moon" id="themeIcon"></i>
            </button>
            <?php if (isset($user)): ?>
                <a href="<?= BASE_URL . $userHome ?>" class="user-chip" style="text-decoration:none;cursor:pointer;">
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

<!-- ══ PAGE SHELL (sidebar + content) ══════════════════════ -->
<div class="page-shell">

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
<<<<<<< HEAD
=======
            <?php
            $_sidebarRole = $user['role'] ?? '';
            if ($_sidebarRole === 'admin'): ?>
            <div class="sidebar-section-label">Admin Portal</div>
            <a href="<?= BASE_URL ?>/admin/dashboard" class="sidebar-link <?php echo request_is('/admin/dashboard') ? 'active' : ''; ?>">
                <i class="fa fa-th-large sidebar-icon"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/transactions" class="sidebar-link <?php echo request_is('/admin/transactions') ? 'active' : ''; ?>">
                <i class="fa fa-receipt sidebar-icon"></i>
                <span>Transactions</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/notifications" class="sidebar-link <?php echo request_is('/admin/notifications') ? 'active' : ''; ?>">
                <i class="fa fa-bullhorn sidebar-icon"></i>
                <span>Notification Centre</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/chatbot-escalations" class="sidebar-link <?php echo request_is('/admin/chatbot-escalations') ? 'active' : ''; ?>">
                <i class="fa fa-robot sidebar-icon"></i>
                <span>Chatbot Escalations</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/audit-trail" class="sidebar-link <?php echo request_is('/admin/audit-trail') ? 'active' : ''; ?>">
                <i class="fa fa-shield-halved sidebar-icon"></i>
                <span>Audit Trail</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/departments" class="sidebar-link <?php echo request_is('/admin/departments') ? 'active' : ''; ?>">
                <i class="fa fa-hospital sidebar-icon"></i>
                <span>Departments</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/appointments" class="sidebar-link <?php echo request_is('/admin/appointments') ? 'active' : ''; ?>">
                <i class="fa fa-calendar-check sidebar-icon"></i>
                <span>Appointments</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/support-tickets" class="sidebar-link <?php echo request_is('/admin/support-tickets') ? 'active' : ''; ?>">
                <i class="fa fa-headset sidebar-icon"></i>
                <span>Support Tickets</span>
            </a>
            <?php else: ?>
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
            <div class="sidebar-section-label">Main</div>
            <?php if ($isAdmin): ?>
                <a href="<?= BASE_URL ?>/admin" class="sidebar-link <?php echo request_is('/admin') ? 'active' : ''; ?>">
                    <i class="fa fa-th-large sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
            <?php elseif ($isDoctor): ?>
                <a href="<?= BASE_URL ?>/doctor/dashboard" class="sidebar-link <?php echo request_is('/doctor/dashboard') ? 'active' : ''; ?>">
                    <i class="fa fa-th-large sidebar-icon"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= BASE_URL ?>/doctor/schedule" class="sidebar-link <?php echo request_is('/doctor/schedule') ? 'active' : ''; ?>">
                    <i class="fa fa-calendar-check sidebar-icon"></i>
                    <span>Schedule</span>
                </a>
                <a href="<?= BASE_URL ?>/doctor/patients" class="sidebar-link <?php echo request_is('/doctor/patients') ? 'active' : ''; ?>">
                    <i class="fa fa-user-group sidebar-icon"></i>
                    <span>Patients</span>
                </a>
            <?php else: ?>
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
            <?php endif; ?>
        </nav>
        <!-- Sidebar footer: NO theme toggle here — only one toggle lives in the navbar -->
        <div class="sidebar-footer">
            <?php if (isset($user)): ?>
            <div class="sidebar-user">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                    <div class="sidebar-user-role"><?php echo htmlspecialchars(strtoupper($role)); ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="sidebar-logout" title="Sign out"><i class="fa fa-right-from-bracket"></i></a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── MAIN CONTENT ────────────────────────────────────── -->
    <main class="main-wrap">
        <?php echo $content; ?>
    </main>

</div><!-- /.page-shell -->

<!-- ══ MOBILE NAV DRAWER (slides from RIGHT) ══════════════ -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
        <span class="mobile-drawer-brand">Doc<span>Book</span></span>
        <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
            <i class="fa fa-xmark"></i>
        </button>
    </div>
    <ul class="mobile-nav-links">
        <?php foreach ($navLinks as $link): ?>
            <li>
                <a href="<?= BASE_URL . $link['href'] ?>" class="<?php echo request_is($link['href']) ? 'active' : ''; ?>">
                    <i class="fa fa-circle-info"></i> <?= htmlspecialchars($link['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        <?php if (isset($user) && $isPatient): ?>
        <li><a href="<?= BASE_URL ?>/profile" class="<?php echo request_is('/profile') ? 'active' : ''; ?>"><i class="fa fa-user"></i> Profile &amp; Settings</a></li>
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
    // ── Single theme toggle (navbar only)
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

    // ── Mobile drawer (slides from RIGHT) — hamburger in nav-actions
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
<<<<<<< HEAD
=======

    // ── Notification Bell ─────────────────────────────────────
    <?php if (isset($user)): ?>
    var bellBtn     = document.getElementById('notifBellBtn');
    var dropdown    = document.getElementById('notifDropdown');
    var badge       = document.getElementById('notifBadge');
    var sidebarBadge = document.getElementById('sidebarNotifBadge');
    var listEl      = document.getElementById('notifList');
    var markAllBtn  = document.getElementById('notifMarkAll');
    var dropdownOpen = false;
    var notifLoaded  = false;

    // Icon map per notification type
    var typeIcon = {
        appointment_booked:    'fa-calendar-check',
        appointment_cancelled: 'fa-calendar-xmark',
        appointment_confirmed: 'fa-calendar-circle-user',
        appointment_rescheduled: 'fa-calendar-pen',
        lab_report_uploaded:   'fa-file-medical',
        system_maintenance:    'fa-triangle-exclamation',
        targeted:              'fa-bell',
    };

    function timeAgo(dateStr) {
        var diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)   return 'Just now';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400)return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
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

    function notifRedirectUrl(n) {
        var apptId = n.appointment_id ? parseInt(n.appointment_id) : null;
        var type   = (n.type || '').trim();

        // Lab report uploaded → open appointment panel scrolled to lab section
        if ((type === 'lab_report_uploaded' || type === 'lab_report_ready') && apptId) {
            return BASE_URL + '/dashboard?lab=' + apptId;
        }
        // Appointment booked / confirmed / reminder → open appointment panel
        if ((type === 'appointment_booked' || type === 'appointment_confirmed' ||
             type === 'appointment_pending' || type === 'appointment_reminder') && apptId) {
            return BASE_URL + '/dashboard?appt=' + apptId;
        }
        // Cancelled / rescheduled / completed
        if (type === 'appointment_cancelled' || type === 'appointment_rescheduled' ||
            type === 'appointment_completed') {
            return BASE_URL + '/dashboard' + (apptId ? '?appt=' + apptId : '');
        }
        // Payment
        if (type === 'payment_received' || type === 'payment_failed' || type === 'refund') {
            return BASE_URL + '/dashboard' + (apptId ? '?appt=' + apptId : '');
        }
        // System / broadcast → notifications page
        if (type === 'system_maintenance' || type === 'targeted') {
            return BASE_URL + '/notifications';
        }
        // Admin types
        if (type === 'chatbot_escalation') return BASE_URL + '/admin/chatbot-escalations';
        if (type === 'support_ticket')     return BASE_URL + '/admin/support-tickets';
        // Any other appointment-related type with an apptId → dashboard
        if (apptId) return BASE_URL + '/dashboard?appt=' + apptId;
        return BASE_URL + '/notifications';
    }

    function renderNotifications(items) {
        if (!items || !items.length) {
            listEl.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i>No notifications yet</div>';
            return;
        }
        listEl.innerHTML = items.slice(0, 15).map(function(n) {
            var icon  = typeIcon[n.type] || 'fa-bell';
            var unread = n.is_read == 0;
            return '<div class="notif-item ' + (unread ? 'unread' : '') + '" data-id="' + n.id + '" data-url="' + notifRedirectUrl(n) + '" style="cursor:pointer;">' +
                '<div class="notif-icon type-' + n.type + '"><i class="fa ' + icon + '"></i></div>' +
                '<div class="notif-body">' +
                    '<div class="notif-title">' + escHtml(n.title) + '</div>' +
                    '<div class="notif-msg">'   + escHtml(n.message) + '</div>' +
                    '<div class="notif-time">'  + timeAgo(n.created_at) + '</div>' +
                '</div>' +
                '<div class="notif-dot"></div>' +
            '</div>';
        }).join('');

        // Click: mark read then redirect
        listEl.querySelectorAll('.notif-item').forEach(function(el) {
            el.addEventListener('click', function() {
                var id  = parseInt(el.dataset.id);
                var url = el.dataset.url;
                el.classList.remove('unread');
                el.querySelector('.notif-dot').style.display = 'none';
                fetch(BASE_URL + '/api/notifications/read', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({id: id})
                }).finally(function() {
                    if (url) window.location.href = url;
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
                notifLoaded = true;
            })
            .catch(function(){});
    }

    // Poll unread count every 60 seconds
    function pollBadge() {
        fetch(BASE_URL + '/api/notifications/unread-count')
            .then(function(r){ return r.json(); })
            .then(function(d){ setBadge(d.count || 0); })
            .catch(function(){});
    }
    pollBadge();
    setInterval(pollBadge, 60000);

    // Toggle dropdown
    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownOpen = !dropdownOpen;
        dropdown.classList.toggle('open', dropdownOpen);
        if (dropdownOpen && !notifLoaded) loadNotifications();
        else if (dropdownOpen) loadNotifications(); // refresh on open
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (dropdownOpen && !dropdown.contains(e.target) && e.target !== bellBtn) {
            dropdownOpen = false;
            dropdown.classList.remove('open');
        }
    });

    // Mark all read
    markAllBtn.addEventListener('click', function() {
        fetch(BASE_URL + '/api/notifications/read', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({all: true})
        }).then(function() {
            setBadge(0);
            listEl.querySelectorAll('.notif-item').forEach(function(el) {
                el.classList.remove('unread');
                el.querySelector('.notif-dot').style.display = 'none';
            });
        });
    });


    // ── Broadcast Banner ─────────────────────────────────────
    (function() {
        var DISMISS_HOURS = 24;
        fetch(BASE_URL + '/api/notifications?limit=10')
            .then(function(r){ return r.json(); })
            .then(function(d) {
                var notifs = d.notifications || [];
                var broadcast = notifs.find(function(n) {
                    return (n.type === 'system_maintenance' || n.type === 'targeted') && n.is_read == 0;
                });
                if (!broadcast) return;

                var dismissKey = 'broadcastDismissed_' + broadcast.id;
                var dismissedAt = localStorage.getItem(dismissKey);
                if (dismissedAt) {
                    var hoursSince = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60);
                    if (hoursSince < DISMISS_HOURS) return;
                    localStorage.removeItem(dismissKey);
                }

                var banner = document.getElementById('broadcastBanner');
                var text   = document.getElementById('broadcastBannerText');
                if (banner && text) {
                    text.textContent = broadcast.title + ': ' + broadcast.message;
                    banner.style.display = 'block';
                    banner.dataset.notifId = broadcast.id;
                }
            }).catch(function(){});
    })();

    window.dismissBroadcastBanner = function() {
        var banner = document.getElementById('broadcastBanner');
        var notifId = banner ? banner.dataset.notifId : null;
        if (notifId) localStorage.setItem('broadcastDismissed_' + notifId, Date.now().toString());
        if (banner) banner.style.display = 'none';
    };
    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    <?php endif; ?>
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>