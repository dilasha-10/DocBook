<?php
$title = 'Notifications';

$extra_styles = <<<CSS
<style>
.notif-page-wrap {
    padding: 28px 24px 60px;
    max-width: 720px;
    margin: 0 auto;
}
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
}
.page-title    { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

.notif-toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.btn-mark-all {
    padding: 8px 16px; border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface); color: var(--text);
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: border-color .15s;
}
.btn-mark-all:hover { border-color: var(--primary); color: var(--primary); }

.notif-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
}

.notif-row {
    display: flex; gap: 14px; padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    cursor: default; transition: background .1s; position: relative;
}
.notif-row:last-child { border-bottom: none; }
.notif-row.unread { background: color-mix(in srgb, var(--primary) 5%, transparent); }
.notif-row:hover  { background: var(--hover-bg, rgba(0,0,0,.03)); }

.nr-icon {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.nr-icon.type-appointment_booked      { background: #d1fae5; color: #065f46; }
.nr-icon.type-appointment_cancelled   { background: #fee2e2; color: #991b1b; }
.nr-icon.type-appointment_confirmed   { background: #dbeafe; color: #1e40af; }
.nr-icon.type-appointment_rescheduled { background: #fef3c7; color: #92400e; }
.nr-icon.type-system_maintenance      { background: #fef3c7; color: #92400e; }
.nr-icon.type-targeted                { background: #ede9fe; color: #5b21b6; }

.nr-body { flex: 1; min-width: 0; }
.nr-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 3px; }
.nr-msg   { font-size: 13px; color: var(--muted); line-height: 1.5; }
.nr-time  { font-size: 11px; color: var(--hint); margin-top: 6px; }

.nr-unread-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--primary); flex-shrink: 0; margin-top: 5px;
}

.empty-state { text-align: center; padding: 56px 24px; color: var(--muted); }
.empty-state i { font-size: 40px; display: block; margin-bottom: 14px; opacity: .3; }
.empty-state p { font-size: 14px; }

.load-more-wrap { text-align: center; padding: 16px; }
.btn-load-more {
    padding: 9px 22px; border-radius: 8px;
    border: 1px solid var(--border);
    background: transparent; color: var(--muted);
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: border-color .15s, color .15s;
}
.btn-load-more:hover { border-color: var(--text); color: var(--text); }

.spinner-inline {
    display: inline-block; width: 16px; height: 16px;
    border: 2px solid var(--border); border-top-color: var(--primary);
    border-radius: 50%; animation: spin .6s linear infinite;
    vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
CSS;

ob_start();
?>

<div class="notif-page-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-bell" style="color:var(--primary);margin-right:8px;"></i>Notifications</h1>
            <p class="page-subtitle">Your recent alerts and appointment updates.</p>
        </div>
    </div>

    <div class="notif-toolbar">
        <button class="btn-mark-all" id="markAllBtn">
            <i class="fa fa-check-double"></i> Mark all as read
        </button>
    </div>

    <div class="notif-card" id="notifContainer">
        <div class="empty-state"><span class="spinner-inline"></span><p style="margin-top:12px;">Loading…</p></div>
    </div>

    <div class="load-more-wrap" id="loadMoreWrap" style="display:none;">
        <button class="btn-load-more" id="loadMoreBtn">Load more</button>
    </div>
</div>

<script>
(function(){
    var container    = document.getElementById('notifContainer');
    var markAllBtn   = document.getElementById('markAllBtn');
    var loadMoreWrap = document.getElementById('loadMoreWrap');
    var loadMoreBtn  = document.getElementById('loadMoreBtn');

    var offset    = 0;
    var limit     = 20;
    var allLoaded = false;

    var typeIcon = {
        appointment_booked:      'fa-calendar-check',
        appointment_cancelled:   'fa-calendar-xmark',
        appointment_confirmed:   'fa-calendar-circle-user',
        appointment_rescheduled: 'fa-calendar-pen',
        system_maintenance:      'fa-triangle-exclamation',
        targeted:                'fa-bell',
    };

    function timeAgo(dateStr) {
        var d    = new Date(dateStr);
        var diff = Math.floor((Date.now() - d) / 1000);
        if (diff < 60)     return 'Just now';
        if (diff < 3600)   return Math.floor(diff/60) + ' minutes ago';
        if (diff < 86400)  return Math.floor(diff/3600) + ' hours ago';
        if (diff < 604800) return Math.floor(diff/86400) + ' days ago';
        return d.toLocaleDateString('en-US', {day:'numeric', month:'short', year:'numeric'});
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function buildRow(n) {
        var icon   = typeIcon[n.type] || 'fa-bell';
        var unread = n.is_read == 0;
        var div    = document.createElement('div');
        div.className  = 'notif-row' + (unread ? ' unread' : '');
        div.dataset.id = n.id;
        div.innerHTML  =
            '<div class="nr-icon type-' + n.type + '"><i class="fa ' + icon + '"></i></div>' +
            '<div class="nr-body">' +
                '<div class="nr-title">' + escHtml(n.title)      + '</div>' +
                '<div class="nr-msg">'   + escHtml(n.message)    + '</div>' +
                '<div class="nr-time">'  + timeAgo(n.created_at) + '</div>' +
            '</div>' +
            (unread ? '<div class="nr-unread-dot"></div>' : '');

        if (unread) {
            div.addEventListener('click', function() {
                div.classList.remove('unread');
                var dot = div.querySelector('.nr-unread-dot');
                if (dot) dot.remove();
                fetch(BASE_URL + '/api/notifications/read', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: parseInt(n.id)})
                });
            });
        }
        return div;
    }

    function loadPage(reset) {
        if (reset) {
            offset    = 0;
            allLoaded = false;
            container.innerHTML = '<div class="empty-state"><span class="spinner-inline"></span><p style="margin-top:12px;">Loading…</p></div>';
        }

        fetch(BASE_URL + '/api/notifications?limit=' + limit + '&offset=' + offset)
            .then(function(r){ return r.json(); })
            .then(function(d) {
                var items = d.notifications || [];

                if (reset) container.innerHTML = '';

                if (!items.length && offset === 0) {
                    container.innerHTML = '<div class="empty-state"><i class="fa fa-bell-slash"></i><p>You have no notifications yet.</p></div>';
                    loadMoreWrap.style.display = 'none';
                    return;
                }

                items.forEach(function(n) { container.appendChild(buildRow(n)); });

                offset   += items.length;
                allLoaded = items.length < limit;
                loadMoreWrap.style.display = allLoaded ? 'none' : 'block';
            })
            .catch(function() {
                container.innerHTML = '<div class="empty-state"><i class="fa fa-triangle-exclamation"></i><p>Failed to load notifications.</p></div>';
            });
    }

    loadPage(true);

    loadMoreBtn.addEventListener('click', function() { loadPage(false); });

    markAllBtn.addEventListener('click', function() {
        fetch(BASE_URL + '/api/notifications/read', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({all: true})
        }).then(function() {
            container.querySelectorAll('.notif-row.unread').forEach(function(row) {
                row.classList.remove('unread');
                var dot = row.querySelector('.nr-unread-dot');
                if (dot) dot.remove();
            });
            // Clear navbar + sidebar badges
            var badge = document.getElementById('notifBadge');
            var sb    = document.getElementById('sidebarNotifBadge');
            if (badge) badge.classList.remove('visible');
            if (sb)    sb.classList.remove('visible');
        });
    });
})();
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app-doctor.php';