<?php
$title = 'Announcements';
ob_start();
$extra_styles = <<<CSS
<style>
.ann-wrap { padding: 30px 24px 60px; max-width: 1100px; margin: 0 auto; }
.page-header { margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.page-title { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

.btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 6px; transition: opacity .15s; }
.btn-primary:hover { opacity: .85; }
.btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 8px; }
.btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-outline:hover { background: color-mix(in srgb, var(--blue) 8%, transparent); }
.btn-danger { background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; }
.btn-danger:hover { background: #dc2626; }
.btn-toggle { background: none; border: 1.5px solid var(--border); border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; color: var(--text); }
.btn-toggle:hover { border-color: var(--blue); }

/* Announcement cards */
.ann-list { display: flex; flex-direction: column; gap: 14px; }
.ann-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 22px; transition: border-color .15s; }
.ann-card:hover { border-color: var(--blue); }
.ann-card-header { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 10px; }
.ann-type-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.ann-type-icon.info    { background: #dbeafe; color: #1d4ed8; }
.ann-type-icon.warning { background: #fef3c7; color: #92400e; }
.ann-type-icon.success { background: #dcfce7; color: #15803d; }
.ann-type-icon.urgent  { background: #fef2f2; color: #991b1b; }

.ann-card-title { font-size: 16px; font-weight: 800; color: var(--text); }
.ann-card-meta { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 4px; }
.ann-meta-tag { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 5px; }
.ann-meta-tag.active { background: #dcfce7; color: #166534; }
.ann-meta-tag.expired { background: #fef2f2; color: #991b1b; }
.ann-meta-tag.scheduled { background: #ede9fe; color: #5b21b6; }
.ann-meta-tag.inactive { background: #f5f5f5; color: #737373; }
.ann-meta-text { font-size: 11px; color: var(--muted); }

.ann-card-msg { font-size: 14px; color: var(--text); line-height: 1.6; margin-bottom: 14px; white-space: pre-wrap; }
.ann-card-actions { display: flex; gap: 8px; }

/* Modal */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: 16px; padding: 28px; width: 90%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.2); max-height: 90vh; overflow-y: auto; }
.modal-title { font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 18px; }
.modal-field { margin-bottom: 14px; }
.modal-field label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.modal-field input, .modal-field textarea, .modal-field select { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; background: var(--surface); color: var(--text); }
.modal-field textarea { min-height: 100px; resize: vertical; }
.modal-field input:focus, .modal-field textarea:focus, .modal-field select:focus { outline: none; border-color: var(--blue); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
.modal-hint { font-size: 11px; color: var(--muted); margin-top: 4px; }

/* Preview banner */
.preview-banner { border-radius: 10px; padding: 14px 18px; margin-bottom: 16px; font-size: 14px; font-weight: 600; display: none; }
.preview-banner.info    { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.preview-banner.warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.preview-banner.success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.preview-banner.urgent  { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

.toast { position: fixed; bottom: 24px; right: 24px; background: #166534; color: #fff; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; z-index: 99999; display: none; box-shadow: 0 4px 20px rgba(0,0,0,.15); }
.toast.error { background: #991b1b; }
.toast.show { display: block; animation: slidein .3s ease; }
@keyframes slidein { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state i { font-size: 40px; margin-bottom: 12px; display: block; opacity: .3; }
</style>
CSS;
$content = ob_get_clean();
ob_start();
?>

<div class="ann-wrap">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-bullhorn" style="color:var(--blue);margin-right:8px;"></i>System Announcements</h1>
            <p class="page-subtitle">Post banners visible on Doctor and Patient dashboards</p>
        </div>
        <button class="btn-primary" onclick="openCreateModal()"><i class="fa fa-plus"></i> New Announcement</button>
    </div>

    <div id="annLoading" style="text-align:center;padding:40px;color:var(--muted);"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
    <div id="annList" class="ann-list" style="display:none;"></div>
    <div id="annEmpty" class="empty-state" style="display:none;">
        <i class="fa fa-bullhorn"></i>
        <p>No announcements yet. Click "New Announcement" to create one.</p>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="annModal">
    <div class="modal-box">
        <h2 class="modal-title" id="modalTitle">New Announcement</h2>
        <input type="hidden" id="editId">

        <div id="previewBanner" class="preview-banner"></div>

        <div class="modal-field">
            <label for="annTitle">Title</label>
            <input type="text" id="annTitle" placeholder="e.g. Closed for Holi" oninput="updatePreview()">
        </div>
        <div class="modal-field">
            <label for="annMessage">Message</label>
            <textarea id="annMessage" placeholder="The announcement message that users will see..." oninput="updatePreview()"></textarea>
        </div>
        <div class="modal-field">
            <label for="annType">Type</label>
            <select id="annType" onchange="updatePreview()">
                <option value="info">ℹ️ Info</option>
                <option value="warning">⚠️ Warning</option>
                <option value="success">✅ Success</option>
                <option value="urgent">🚨 Urgent</option>
            </select>
        </div>
        <div class="modal-field">
            <label for="annRoles">Target Audience</label>
            <select id="annRoles">
                <option value="all">All Users</option>
                <option value="patient">Patients Only</option>
                <option value="doctor">Doctors Only</option>
                <option value="patient,doctor">Patients & Doctors</option>
            </select>
        </div>
        <div class="modal-field">
            <label for="annStartsAt">Starts At</label>
            <input type="datetime-local" id="annStartsAt">
            <div class="modal-hint">Leave empty to start immediately</div>
        </div>
        <div class="modal-field">
            <label for="annExpiresAt">Expires At</label>
            <input type="datetime-local" id="annExpiresAt">
            <div class="modal-hint">Leave empty for no expiration</div>
        </div>
        <div class="modal-actions">
            <button class="btn-outline" onclick="closeModal()" style="padding:10px 20px;border-radius:10px;">Cancel</button>
            <button class="btn-primary" onclick="saveAnnouncement()"><i class="fa fa-paper-plane"></i> Publish</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var announcements = [];

function toast(msg, isError) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(function(){ t.className = 'toast'; }, 3000);
}
function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function fmtDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

var typeIcons = { info: 'fa-circle-info', warning: 'fa-triangle-exclamation', success: 'fa-circle-check', urgent: 'fa-fire' };

function loadAnnouncements() {
    fetch(BASE_URL + '/admin/api/announcements')
        .then(function(r){ return r.json(); })
        .then(function(d) {
            announcements = d.announcements || [];
            render();
        });
}

function render() {
    var list    = document.getElementById('annList');
    var loading = document.getElementById('annLoading');
    var empty   = document.getElementById('annEmpty');
    loading.style.display = 'none';

    if (!announcements.length) { list.style.display = 'none'; empty.style.display = 'block'; return; }
    empty.style.display = 'none'; list.style.display = 'flex';

    list.innerHTML = announcements.map(function(a) {
        var icon = typeIcons[a.type] || 'fa-circle-info';
        var statusTag = '<span class="ann-meta-tag ' + a.computed_status + '">' + a.computed_status.charAt(0).toUpperCase() + a.computed_status.slice(1) + '</span>';
        var toggleLabel = a.is_active == 1 ? 'Deactivate' : 'Activate';
        var rolesLabel = a.target_roles === 'all' ? 'All Users' : a.target_roles;

        return '<div class="ann-card">' +
            '<div class="ann-card-header">' +
                '<div class="ann-type-icon ' + a.type + '"><i class="fa ' + icon + '"></i></div>' +
                '<div style="flex:1;">' +
                    '<div class="ann-card-title">' + escHtml(a.title) + '</div>' +
                    '<div class="ann-card-meta">' +
                        statusTag +
                        '<span class="ann-meta-text">Type: ' + a.type + '</span>' +
                        '<span class="ann-meta-text">Audience: ' + escHtml(rolesLabel) + '</span>' +
                        '<span class="ann-meta-text">By: ' + escHtml(a.created_by_name) + '</span>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="ann-card-msg">' + escHtml(a.message) + '</div>' +
            '<div class="ann-card-meta" style="margin-bottom:12px;">' +
                '<span class="ann-meta-text"><i class="fa fa-clock"></i> Starts: ' + fmtDate(a.starts_at) + '</span>' +
                '<span class="ann-meta-text"><i class="fa fa-hourglass-end"></i> Expires: ' + (a.expires_at ? fmtDate(a.expires_at) : 'Never') + '</span>' +
            '</div>' +
            '<div class="ann-card-actions">' +
                '<button class="btn-outline btn-sm" onclick="openEditModal(' + a.id + ')"><i class="fa fa-pen"></i> Edit</button>' +
                '<button class="btn-toggle btn-sm" onclick="toggleAnn(' + a.id + ')">' + toggleLabel + '</button>' +
                '<button class="btn-danger btn-sm" onclick="deleteAnn(' + a.id + ')"><i class="fa fa-trash"></i></button>' +
            '</div>' +
        '</div>';
    }).join('');
}

function updatePreview() {
    var title = document.getElementById('annTitle').value.trim();
    var msg   = document.getElementById('annMessage').value.trim();
    var type  = document.getElementById('annType').value;
    var prev  = document.getElementById('previewBanner');
    if (title || msg) {
        prev.style.display = 'block';
        prev.className = 'preview-banner ' + type;
        prev.innerHTML = '<strong>' + escHtml(title) + '</strong>' + (msg ? ' — ' + escHtml(msg.substring(0, 120)) : '');
    } else {
        prev.style.display = 'none';
    }
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'New Announcement';
    document.getElementById('editId').value = '';
    document.getElementById('annTitle').value = '';
    document.getElementById('annMessage').value = '';
    document.getElementById('annType').value = 'info';
    document.getElementById('annRoles').value = 'all';
    document.getElementById('annStartsAt').value = '';
    document.getElementById('annExpiresAt').value = '';
    document.getElementById('previewBanner').style.display = 'none';
    document.getElementById('annModal').classList.add('open');
}

function openEditModal(id) {
    var a = announcements.find(function(x){ return x.id == id; });
    if (!a) return;
    document.getElementById('modalTitle').textContent = 'Edit Announcement';
    document.getElementById('editId').value = a.id;
    document.getElementById('annTitle').value = a.title;
    document.getElementById('annMessage').value = a.message;
    document.getElementById('annType').value = a.type;
    document.getElementById('annRoles').value = a.target_roles;
    document.getElementById('annStartsAt').value = a.starts_at ? a.starts_at.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('annExpiresAt').value = a.expires_at ? a.expires_at.replace(' ', 'T').substring(0, 16) : '';
    updatePreview();
    document.getElementById('annModal').classList.add('open');
}

function closeModal() { document.getElementById('annModal').classList.remove('open'); }

function saveAnnouncement() {
    var id = document.getElementById('editId').value;
    var payload = {
        title:        document.getElementById('annTitle').value.trim(),
        message:      document.getElementById('annMessage').value.trim(),
        type:         document.getElementById('annType').value,
        target_roles: document.getElementById('annRoles').value,
        starts_at:    document.getElementById('annStartsAt').value.replace('T', ' '),
        expires_at:   document.getElementById('annExpiresAt').value.replace('T', ' '),
        is_active:    true
    };

    if (!payload.title || !payload.message) { toast('Title and message are required', true); return; }

    var url    = id ? BASE_URL + '/admin/api/announcements/' + id : BASE_URL + '/admin/api/announcements';
    var method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        if (d.success) {
            closeModal();
            loadAnnouncements();
            toast(id ? 'Announcement updated' : 'Announcement published');
        } else {
            toast(d.error || 'Failed', true);
        }
    });
}

function toggleAnn(id) {
    fetch(BASE_URL + '/admin/api/announcements/' + id + '/toggle', { method: 'POST' })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            if (d.success) { loadAnnouncements(); toast('Announcement toggled'); }
        });
}

function deleteAnn(id) {
    if (!confirm('Delete this announcement?')) return;
    fetch(BASE_URL + '/admin/api/announcements/' + id, { method: 'DELETE' })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            if (d.success) { loadAnnouncements(); toast('Announcement deleted'); }
        });
}

loadAnnouncements();
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app.php';
