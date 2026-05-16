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
    <style>
    /* ── Notification Bell ── */
    .notif-wrap-btn { position:relative; }
    .notif-bell-btn { background:none;border:none;cursor:pointer;color:var(--text);font-size:18px;padding:6px;position:relative;border-radius:8px;transition:background .15s; }
    .notif-bell-btn:hover { background:var(--hover); }
    .notif-badge { position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:8px;display:none;align-items:center;justify-content:center;padding:0 3px;line-height:1; }
    .notif-badge.visible { display:flex; }
    .notif-dropdown { position:absolute;top:calc(100% + 8px);right:0;width:340px;background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.12);z-index:999;display:none;flex-direction:column;overflow:hidden; }
    .notif-dropdown.open { display:flex; }
    .notif-dropdown-header { display:flex;align-items:center;justify-content:space-between;padding:14px 16px 10px;border-bottom:1px solid var(--border); }
    .notif-dropdown-header h4 { margin:0;font-size:14px;font-weight:700; }
    .notif-mark-all-btn { background:none;border:none;cursor:pointer;font-size:12px;color:var(--blue);font-weight:600; }
    .notif-list { max-height:320px;overflow-y:auto; }
    .notif-item { display:flex;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;position:relative;transition:background .12s; }
    .notif-item:last-child { border-bottom:none; }
    .notif-item:hover { background:var(--hover); }
    .notif-item.unread { background:color-mix(in srgb,var(--primary) 6%,transparent); }
    .notif-item.unread:hover { background:color-mix(in srgb,var(--primary) 10%,transparent); }
    .notif-icon { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0; }
    .notif-icon.type-appointment_booked    { background:#d1fae5;color:#065f46; }
    .notif-icon.type-appointment_cancelled { background:#fee2e2;color:#991b1b; }
    .notif-icon.type-lab_report_uploaded   { background:#f3e8ff;color:#7e22ce; }
    .notif-icon.type-system_maintenance    { background:#fef3c7;color:#92400e; }
    .notif-icon.type-targeted              { background:#ede9fe;color:#5b21b6; }
    .notif-body { flex:1;min-width:0; }
    .notif-title { font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .notif-msg   { font-size:12px;color:var(--muted);margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
    .notif-time  { font-size:11px;color:var(--hint);margin-top:3px; }
    .notif-dot   { width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px;display:none; }
    .notif-item.unread .notif-dot { display:block; }
    .notif-empty { padding:28px;text-align:center;color:var(--muted);font-size:13px;display:flex;flex-direction:column;align-items:center;gap:8px; }
    .notif-empty i { font-size:24px;opacity:.4; }
    .notif-dropdown-footer { padding:10px 16px;border-top:1px solid var(--border);text-align:center; }
    .notif-dropdown-footer a { font-size:12px;color:var(--blue);font-weight:600;text-decoration:none; }
    .sidebar-notif-badge { background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:16px;height:16px;border-radius:8px;display:none;align-items:center;justify-content:center;padding:0 3px;margin-left:auto; }
    .sidebar-notif-badge.visible { display:flex; }
    </style>
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

<!-- Broadcast Notification Banner -->
<?php if (isset($user)): ?>
<div id="broadcastBanner" style="display:none; background:#fef08a; color:#713f12; padding:10px 48px 10px 20px; text-align:center; font-size:14px; font-weight:600; position:relative; z-index:1000; border-bottom:1px solid #fde047;">
    <i class="fa fa-bullhorn" style="margin-right:8px;opacity:.8;"></i>
    <span id="broadcastBannerText"></span>
    <button onclick="dismissBroadcastBanner()" style="background:none;border:none;color:#713f12;font-size:18px;cursor:pointer;position:absolute;right:16px;top:50%;transform:translateY(-50%);opacity:.7;">&#x2715;</button>
</div>
<?php endif; ?>

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
                            <a href="<?= BASE_URL ?>/lab-admin/notifications">View all notifications</a>
                        </div>
                    </div>
                </div>

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
            <a href="<?= BASE_URL ?>/lab-admin/notifications"
               class="sidebar-link <?php echo request_is('/lab-admin/notifications') ? 'active' : ''; ?>">
                <i class="fa fa-bell sidebar-icon"></i>
                <span>Notifications</span>
                <span class="sidebar-notif-badge" id="sidebarNotifBadge"></span>
            </a>
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
        <li><a href="<?= BASE_URL ?>/lab-admin/notifications" class="<?php echo request_is('/lab-admin/notifications') ? 'active' : ''; ?>"><i class="fa fa-bell"></i> Notifications</a></li>
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
// ── Notification deep link for lab admin ─────────────────────────────────
(function() {
    var params = new URLSearchParams(window.location.search);
    var apptId = params.get('appt_id');
    if (apptId && window.location.pathname.indexOf('/lab-admin/dashboard') === -1) {
        window.location.replace(BASE_URL + '/lab-admin/dashboard?appt_id=' + apptId);
    }
})();

<?php if (isset($user)): ?>
// ── Notification Bell ─────────────────────────────────────────────────────
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

function labNotifRedirectUrl(n) {
    var apptId = n.appointment_id ? parseInt(n.appointment_id) : null;
    var type   = (n.type || '').trim();
    // New appointment booked → dashboard to upload
    if (type === 'appointment_booked' && apptId) {
        return BASE_URL + '/lab-admin/dashboard?appt_id=' + apptId;
    }
    // System / broadcast
    if (type === 'system_maintenance' || type === 'targeted') {
        return BASE_URL + '/lab-admin/notifications';
    }
    if (apptId) return BASE_URL + '/lab-admin/dashboard?appt_id=' + apptId;
    return BASE_URL + '/lab-admin/notifications';
}

function renderNotifications(items) {
    if (!items || !items.length) {
        listEl.innerHTML = '<div class="notif-empty"><i class="fa fa-bell-slash"></i>No notifications yet</div>';
        return;
    }
    listEl.innerHTML = items.slice(0, 15).map(function(n) {
        var icon   = typeIcon[n.type] || 'fa-bell';
        var unread = n.is_read == 0;
        return '<div class="notif-item ' + (unread ? 'unread' : '') + '" data-id="' + n.id + '" data-url="' + labNotifRedirectUrl(n) + '" style="cursor:pointer;">' +
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
            var id  = parseInt(el.dataset.id);
            var url = el.dataset.url;
            el.classList.remove('unread');
            var dot = el.querySelector('.notif-dot');
            if (dot) dot.style.display = 'none';
            fetch(BASE_URL + '/api/notifications/read', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            }).finally(function() {
                if (!url) return;
                var currentPath = window.location.pathname;
                var targetPath  = url.split('?')[0];
                if (currentPath === targetPath) {
                    window.location.replace(url);
                } else {
                    window.location.href = url;
                }
            });
        });
    });
}

function loadNotifications() {
    fetch(BASE_URL + '/api/notifications?limit=15')
        .then(function(r){ return r.json(); })
        .then(function(d) {
            var notifs = d.notifications || [];
            setBadge(notifs.filter(function(n){ return n.is_read == 0; }).length);
            renderNotifications(notifs);
        }).catch(function(){});
}

if (bellBtn) {
    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownOpen = !dropdownOpen;
        dropdown.classList.toggle('open', dropdownOpen);
        if (dropdownOpen) loadNotifications();
    });
}

document.addEventListener('click', function(e) {
    if (dropdownOpen && !document.getElementById('notifWrap').contains(e.target)) {
        dropdownOpen = false;
        dropdown.classList.remove('open');
    }
});

if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
        fetch(BASE_URL + '/api/notifications/read-all', { method: 'POST' })
            .then(function(){ loadNotifications(); }).catch(function(){});
    });
}

// Poll for new notifications every 60s
loadNotifications();
setInterval(loadNotifications, 60000);

// Broadcast banner
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
<?php endif; ?>
</script>
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