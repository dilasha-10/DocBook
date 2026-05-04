<?php
$title = 'Notification Centre';

$extra_styles = <<<CSS
<style>
/* ── Wrap ── */
.notif-wrap {
    padding: 28px 24px 60px;
    max-width: 1120px;
    margin: 0 auto;
}
.page-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 28px;
}
.page-title    { font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
.page-subtitle { font-size: 13px; color: var(--muted); margin: 4px 0 0; }

/* ── Tab bar ── */
.tab-bar {
    display: flex; gap: 6px;
    border-bottom: 2px solid var(--border);
    margin-bottom: 28px; padding-bottom: 0;
}
.tab-btn {
    padding: 9px 18px; font-size: 13px; font-weight: 700;
    border: none; background: none; color: var(--muted);
    cursor: pointer; border-bottom: 2px solid transparent;
    margin-bottom: -2px; border-radius: 6px 6px 0 0;
    transition: color .15s, border-color .15s;
}
.tab-btn:hover  { color: var(--text); }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

/* ── Cards ── */
.card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 14px; padding: 24px 26px; margin-bottom: 20px;
}
.card-title { font-size: 15px; font-weight: 800; color: var(--text); margin: 0 0 18px; }

/* ── Maintenance quick-send tiles ── */
.maint-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px; margin-bottom: 24px;
}
.maint-tile {
    border: 1.5px solid var(--border); border-radius: 12px;
    padding: 16px 18px; cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    background: var(--bg);
}
.maint-tile:hover { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 12%, transparent); }
.maint-tile.selected { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 7%, transparent); }
.maint-tile-icon { font-size: 22px; margin-bottom: 8px; }
.maint-tile-label { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 3px; }
.maint-tile-desc  { font-size: 12px; color: var(--muted); line-height: 1.4; }

.maint-preview {
    background: color-mix(in srgb, var(--primary) 5%, transparent);
    border: 1px dashed var(--border);
    border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; display: none;
}
.maint-preview.visible { display: block; }
.maint-preview-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
.maint-preview-title { font-size: 14px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
.maint-preview-msg   { font-size: 13px; color: var(--muted); line-height: 1.5; }

.divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }

/* ── Form rows ── */
.form-row      { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.form-row.full { grid-template-columns: 1fr; }
@media (max-width: 620px) { .form-row { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 12px; font-weight: 700; color: var(--muted); letter-spacing: .05em; text-transform: uppercase; }

.form-input, .form-select, .form-textarea {
    padding: 10px 14px;
    border: 1px solid var(--border); border-radius: 9px;
    background: var(--bg); color: var(--text);
    font-family: inherit; font-size: 14px;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent);
}
.form-textarea { resize: vertical; min-height: 100px; }

/* ── Role checkboxes ── */
.role-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.role-chip  {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px; border: 1.5px solid var(--border);
    border-radius: 20px; cursor: pointer;
    font-size: 13px; font-weight: 600; color: var(--muted);
    transition: all .15s; user-select: none;
}
.role-chip input { display: none; }
.role-chip:has(input:checked) {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    color: var(--primary);
}
.role-chip i { font-size: 12px; }

/* ── User search autocomplete ── */
.search-wrapper { position: relative; }
.user-dropdown  {
    position: absolute; z-index: 200; top: calc(100% + 4px); left: 0; right: 0;
    background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12); overflow: hidden; display: none;
}
.user-dropdown.open { display: block; }
.user-option {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; cursor: pointer; transition: background .1s;
}
.user-option:hover { background: var(--hover-bg, rgba(0,0,0,.04)); }
.user-option .u-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.user-option .u-name { font-size: 14px; font-weight: 600; color: var(--text); }
.user-option .u-meta { font-size: 12px; color: var(--muted); }
.selected-user {
    display: none; align-items: center; gap: 10px;
    padding: 9px 14px;
    border: 1.5px solid var(--primary); border-radius: 9px;
    background: color-mix(in srgb, var(--primary) 8%, transparent);
    margin-top: 8px;
}
.selected-user.visible { display: flex; }
.selected-user span { font-size: 14px; font-weight: 600; color: var(--text); flex: 1; }
.selected-user .clear-btn {
    background: none; border: none; color: var(--muted); cursor: pointer; font-size: 14px; padding: 2px;
}

/* ── Buttons ── */
.btn-send {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 22px; border: none; border-radius: 9px;
    background: var(--primary); color: #fff;
    font-weight: 700; font-size: 14px; cursor: pointer;
    transition: opacity .15s, transform .1s;
}
.btn-send:hover   { opacity: .88; }
.btn-send:active  { transform: scale(.97); }
.btn-send:disabled { opacity: .5; cursor: not-allowed; }

.btn-warning {
    background: #f59e0b;
}

/* ── Toast ── */
.toast-stack {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    display: flex; flex-direction: column; gap: 8px; pointer-events: none;
}
.toast {
    padding: 12px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
    box-shadow: 0 4px 16px rgba(0,0,0,.18); animation: slideUp .25s ease;
    pointer-events: auto; max-width: 320px;
}
.toast.success { background: #10b981; color: #fff; }
.toast.error   { background: #ef4444; color: #fff; }
@keyframes slideUp { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }

/* ── History table ── */
.history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.history-table th {
    text-align: left; padding: 10px 12px;
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
    color: var(--muted); border-bottom: 1px solid var(--border);
}
.history-table td {
    padding: 12px 12px; border-bottom: 1px solid var(--border);
    color: var(--text); vertical-align: top;
}
.history-table tr:last-child td { border-bottom: none; }
.history-table tr:hover td { background: var(--hover-bg, rgba(0,0,0,.025)); }

.badge {
    display: inline-block; padding: 2px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
}
.badge-system   { background: #fef3c7; color: #92400e; }
.badge-targeted { background: #dbeafe; color: #1e40af; }
.badge-appt     { background: #d1fae5; color: #065f46; }

.delivery-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600; color: var(--muted);
}
.delivery-pill i { color: #10b981; }

.empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
.empty-state i { font-size: 36px; margin-bottom: 12px; display: block; opacity: .4; }
.empty-state p { font-size: 14px; }

.spinner {
    display: inline-block; width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,.4);
    border-top-color: #fff; border-radius: 50%;
    animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
CSS;

ob_start();
?>

<div class="notif-wrap">

    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fa fa-bell" style="color:var(--primary);margin-right:8px;"></i>Notification Centre</h1>
            <p class="page-subtitle">Send broadcasts, maintenance alerts, targeted messages, and track delivery history.</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tab-bar">
        <button class="tab-btn active" data-tab="maintenance">
            <i class="fa fa-triangle-exclamation"></i> Maintenance
        </button>
        <button class="tab-btn" data-tab="broadcast">
            <i class="fa fa-bullhorn"></i> Broadcast
        </button>
        <button class="tab-btn" data-tab="targeted">
            <i class="fa fa-user-check"></i> Targeted
        </button>
        <button class="tab-btn" data-tab="history">
            <i class="fa fa-clock-rotate-left"></i> History
        </button>
    </div>

    <!-- ── TAB: MAINTENANCE ── -->
    <div id="tab-maintenance" class="tab-panel">
        <div class="card">
            <p class="card-title">Send Maintenance Notification</p>

            <!-- Quick-select templates -->
            <div class="maint-grid" id="maintGrid">
                <div class="maint-tile" data-title="Scheduled Maintenance Tonight" data-msg="DocBook will be undergoing scheduled maintenance tonight from 11:00 PM to 1:00 AM. Some features may be temporarily unavailable. We apologise for the inconvenience.">
                    <div class="maint-tile-icon">🔧</div>
                    <div class="maint-tile-label">Scheduled Maintenance</div>
                    <div class="maint-tile-desc">Planned downtime window alert</div>
                </div>
                <div class="maint-tile" data-title="System Back Online" data-msg="DocBook is back online. All services have been fully restored. Thank you for your patience during our maintenance window.">
                    <div class="maint-tile-icon">✅</div>
                    <div class="maint-tile-label">Back Online</div>
                    <div class="maint-tile-desc">Services restored confirmation</div>
                </div>
                <div class="maint-tile" data-title="Urgent: Service Disruption" data-msg="We are currently experiencing an unexpected service disruption. Our team is working to resolve the issue as quickly as possible. We will update you once services are restored.">
                    <div class="maint-tile-icon">🚨</div>
                    <div class="maint-tile-label">Service Disruption</div>
                    <div class="maint-tile-desc">Unplanned outage notice</div>
                </div>
                <div class="maint-tile" data-title="New Feature Available" data-msg="We have just released new features to improve your DocBook experience. Log in to explore what's new!">
                    <div class="maint-tile-icon">🚀</div>
                    <div class="maint-tile-label">New Features</div>
                    <div class="maint-tile-desc">Product update announcement</div>
                </div>
            </div>

            <!-- Live preview -->
            <div class="maint-preview" id="maintPreview">
                <div class="maint-preview-label">Preview</div>
                <div class="maint-preview-title" id="maintPreviewTitle"></div>
                <div class="maint-preview-msg"   id="maintPreviewMsg"></div>
            </div>

            <hr class="divider">

            <!-- Editable fields (pre-filled from tile selection) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" id="maint-title" class="form-input" placeholder="e.g. Scheduled Maintenance Tonight">
                </div>
                <div class="form-group">
                    <label class="form-label">Target Audience</label>
                    <div class="role-chips">
                        <label class="role-chip">
                            <input type="checkbox" name="maint-role" value="all" id="maint-role-all" checked>
                            <i class="fa fa-globe"></i> Everyone
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="maint-role" value="patient">
                            <i class="fa fa-user"></i> Patients
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="maint-role" value="doctor">
                            <i class="fa fa-user-md"></i> Doctors
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="maint-role" value="lab_admin">
                            <i class="fa fa-flask"></i> Lab Admins
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea id="maint-message" class="form-textarea" placeholder="Write the maintenance message here, or select a template above…"></textarea>
                </div>
            </div>

            <button class="btn-send btn-warning" id="maint-send-btn" onclick="sendMaintenance()">
                <i class="fa fa-triangle-exclamation"></i> Send Maintenance Alert
            </button>
        </div>
    </div>

    <!-- ── TAB: BROADCAST ── -->
    <div id="tab-broadcast" class="tab-panel" style="display:none;">
        <div class="card">
            <p class="card-title">Send System-Wide Notification</p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Notification Title</label>
                    <input type="text" id="bc-title" class="form-input" placeholder="e.g. Important Update">
                </div>
                <div class="form-group">
                    <label class="form-label">Target Audience</label>
                    <div class="role-chips">
                        <label class="role-chip">
                            <input type="checkbox" name="bc-role" value="all" id="role-all">
                            <i class="fa fa-globe"></i> Everyone
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="bc-role" value="patient">
                            <i class="fa fa-user"></i> Patients
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="bc-role" value="doctor">
                            <i class="fa fa-user-md"></i> Doctors
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="bc-role" value="lab_admin">
                            <i class="fa fa-flask"></i> Lab Admins
                        </label>
                        <label class="role-chip">
                            <input type="checkbox" name="bc-role" value="admin">
                            <i class="fa fa-shield-halved"></i> Admins
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea id="bc-message" class="form-textarea" placeholder="Write the notification message here…"></textarea>
                </div>
            </div>

            <button class="btn-send" id="bc-send-btn" onclick="sendBroadcast()">
                <i class="fa fa-paper-plane"></i> Send Broadcast
            </button>
        </div>
    </div>

    <!-- ── TAB: TARGETED ── -->
    <div id="tab-targeted" class="tab-panel" style="display:none;">
        <div class="card">
            <p class="card-title">Send Notification to a Specific User</p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Find User</label>
                    <div class="search-wrapper">
                        <input type="text" id="user-search" class="form-input" placeholder="Search by name or email…" autocomplete="off">
                        <div class="user-dropdown" id="user-dropdown"></div>
                    </div>
                    <div class="selected-user" id="selected-user-box">
                        <div class="u-avatar" id="selected-avatar"></div>
                        <span id="selected-name"></span>
                        <button class="clear-btn" onclick="clearSelectedUser()" title="Clear selection"><i class="fa fa-xmark"></i></button>
                    </div>
                    <input type="hidden" id="target-user-id">
                </div>
                <div class="form-group">
                    <label class="form-label">Notification Title</label>
                    <input type="text" id="tg-title" class="form-input" placeholder="e.g. Account Notice">
                </div>
            </div>

            <div class="form-row full">
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea id="tg-message" class="form-textarea" placeholder="Write the notification message here…"></textarea>
                </div>
            </div>

            <button class="btn-send" id="tg-send-btn" onclick="sendTargeted()">
                <i class="fa fa-paper-plane"></i> Send Notification
            </button>
        </div>
    </div>

    <!-- ── TAB: HISTORY ── -->
    <div id="tab-history" class="tab-panel" style="display:none;">
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;">
                <p class="card-title" style="margin-bottom:16px;">Broadcast History</p>
                <button class="btn-send" style="font-size:12px;padding:8px 14px;" onclick="loadHistory()">
                    <i class="fa fa-rotate-right"></i> Refresh
                </button>
            </div>
            <div id="history-container">
                <div class="empty-state"><i class="fa fa-satellite-dish"></i><p>Loading history…</p></div>
            </div>
        </div>
    </div>

</div>

<!-- Toast container -->
<div class="toast-stack" id="toast-stack"></div>

<script>
const BASE = window.BASE_URL || '';

// ── Tabs ─────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
        btn.classList.add('active');
        const panel = document.getElementById('tab-' + btn.dataset.tab);
        if (panel) panel.style.display = 'block';
        if (btn.dataset.tab === 'history') loadHistory();
    });
});

// ── Maintenance tile selection ────────────────────────────────
document.querySelectorAll('.maint-tile').forEach(tile => {
    tile.addEventListener('click', () => {
        document.querySelectorAll('.maint-tile').forEach(t => t.classList.remove('selected'));
        tile.classList.add('selected');

        const t = tile.dataset.title;
        const m = tile.dataset.msg;

        document.getElementById('maint-title').value   = t;
        document.getElementById('maint-message').value = m;

        document.getElementById('maintPreviewTitle').textContent = t;
        document.getElementById('maintPreviewMsg').textContent   = m;
        document.getElementById('maintPreview').classList.add('visible');
    });
});

// Live preview update when typing
['maint-title', 'maint-message'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        const t = document.getElementById('maint-title').value;
        const m = document.getElementById('maint-message').value;
        if (t || m) {
            document.getElementById('maintPreviewTitle').textContent = t;
            document.getElementById('maintPreviewMsg').textContent   = m;
            document.getElementById('maintPreview').classList.add('visible');
        }
    });
});

// Maintenance audience: "Everyone" toggle
document.getElementById('maint-role-all').addEventListener('change', function() {
    document.querySelectorAll('input[name="maint-role"]:not(#maint-role-all)').forEach(cb => {
        cb.checked = false;
        cb.closest('.role-chip').style.pointerEvents = this.checked ? 'none' : '';
    });
});
document.querySelectorAll('input[name="maint-role"]:not(#maint-role-all)').forEach(cb => {
    cb.addEventListener('change', () => {
        document.getElementById('maint-role-all').checked = false;
    });
});

// Broadcast audience: "Everyone" toggle
document.getElementById('role-all').addEventListener('change', function() {
    document.querySelectorAll('input[name="bc-role"]:not(#role-all)').forEach(cb => {
        cb.checked = false;
        cb.closest('.role-chip').style.pointerEvents = this.checked ? 'none' : '';
    });
});
document.querySelectorAll('input[name="bc-role"]:not(#role-all)').forEach(cb => {
    cb.addEventListener('change', () => {
        document.getElementById('role-all').checked = false;
    });
});

// ── Toast ─────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const stack = document.getElementById('toast-stack');
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    stack.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

// ── Maintenance send ──────────────────────────────────────────
async function sendMaintenance() {
    const title = document.getElementById('maint-title').value.trim();
    const msg   = document.getElementById('maint-message').value.trim();
    const boxes = [...document.querySelectorAll('input[name="maint-role"]:checked')];
    const roles = boxes.map(b => b.value);

    if (!title) { showToast('Please enter a title.', 'error'); return; }
    if (!msg)   { showToast('Please enter a message.', 'error'); return; }
    if (!roles.length) { showToast('Select at least one audience.', 'error'); return; }

    const btn = document.getElementById('maint-send-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Sending…';

    try {
        const res  = await fetch(BASE + '/admin/api/notifications/broadcast', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, message: msg, target_roles: roles, type: 'system_maintenance' }),
        });
        const data = await res.json();
        if (data.success) {
            showToast(`✓ Maintenance alert sent to ${data.total_sent} user${data.total_sent !== 1 ? 's' : ''}.`);
            document.getElementById('maint-title').value   = '';
            document.getElementById('maint-message').value = '';
            document.querySelectorAll('.maint-tile').forEach(t => t.classList.remove('selected'));
            document.getElementById('maintPreview').classList.remove('visible');
            document.getElementById('maint-role-all').checked = true;
            document.querySelectorAll('input[name="maint-role"]:not(#maint-role-all)').forEach(cb => {
                cb.checked = false;
                cb.closest('.role-chip').style.pointerEvents = 'none';
            });
        } else {
            showToast(data.error || 'Failed to send.', 'error');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-triangle-exclamation"></i> Send Maintenance Alert';
    }
}

// ── Broadcast send ────────────────────────────────────────────
async function sendBroadcast() {
    const title = document.getElementById('bc-title').value.trim();
    const msg   = document.getElementById('bc-message').value.trim();
    const boxes = [...document.querySelectorAll('input[name="bc-role"]:checked')];
    const roles = boxes.map(b => b.value);

    if (!title) { showToast('Please enter a title.', 'error'); return; }
    if (!msg)   { showToast('Please enter a message.', 'error'); return; }
    if (!roles.length) { showToast('Select at least one audience.', 'error'); return; }

    const btn = document.getElementById('bc-send-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Sending…';

    try {
        const res  = await fetch(BASE + '/admin/api/notifications/broadcast', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, message: msg, target_roles: roles }),
        });
        const data = await res.json();
        if (data.success) {
            showToast(`✓ Sent to ${data.total_sent} user${data.total_sent !== 1 ? 's' : ''}.`);
            document.getElementById('bc-title').value   = '';
            document.getElementById('bc-message').value = '';
            document.querySelectorAll('input[name="bc-role"]').forEach(c => c.checked = false);
        } else {
            showToast(data.error || 'Failed to send.', 'error');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Broadcast';
    }
}

// ── Targeted: user search ─────────────────────────────────────
let searchTimer    = null;
let selectedUserId = null;

document.getElementById('user-search').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { closeDropdown(); return; }
    searchTimer = setTimeout(() => searchUsers(q), 280);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.search-wrapper')) closeDropdown();
});

function closeDropdown() {
    const dd = document.getElementById('user-dropdown');
    dd.classList.remove('open');
    dd.innerHTML = '';
}

async function searchUsers(q) {
    const res  = await fetch(BASE + '/admin/api/notifications/search-users?q=' + encodeURIComponent(q));
    const data = await res.json();
    const dd   = document.getElementById('user-dropdown');
    dd.innerHTML = '';

    if (!data.users || !data.users.length) {
        dd.innerHTML = '<div class="user-option" style="color:var(--muted);cursor:default;">No users found</div>';
        dd.classList.add('open');
        return;
    }

    data.users.forEach(u => {
        const el       = document.createElement('div');
        el.className   = 'user-option';
        const initials = u.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
        el.innerHTML   = `
            <div class="u-avatar">${initials}</div>
            <div>
                <div class="u-name">${escHtml(u.name)}</div>
                <div class="u-meta">${escHtml(u.email)} · ${escHtml(u.role)}</div>
            </div>`;
        el.addEventListener('click', () => selectUser(u));
        dd.appendChild(el);
    });
    dd.classList.add('open');
}

function selectUser(u) {
    selectedUserId = u.id;
    document.getElementById('target-user-id').value = u.id;
    document.getElementById('user-search').value    = '';
    closeDropdown();

    const initials = u.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    document.getElementById('selected-avatar').textContent = initials;
    document.getElementById('selected-name').textContent   = `${u.name} (${u.role})`;
    document.getElementById('selected-user-box').classList.add('visible');
}

function clearSelectedUser() {
    selectedUserId = null;
    document.getElementById('target-user-id').value = '';
    document.getElementById('selected-user-box').classList.remove('visible');
    document.getElementById('user-search').value = '';
}

// ── Targeted send ─────────────────────────────────────────────
async function sendTargeted() {
    const uid   = selectedUserId;
    const title = document.getElementById('tg-title').value.trim();
    const msg   = document.getElementById('tg-message').value.trim();

    if (!uid)   { showToast('Please select a recipient.', 'error'); return; }
    if (!title) { showToast('Please enter a title.', 'error'); return; }
    if (!msg)   { showToast('Please enter a message.', 'error'); return; }

    const btn = document.getElementById('tg-send-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Sending…';

    try {
        const res  = await fetch(BASE + '/admin/api/notifications/targeted', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ recipient_id: uid, title, message: msg }),
        });
        const data = await res.json();
        if (data.success) {
            showToast('✓ Notification sent.');
            clearSelectedUser();
            document.getElementById('tg-title').value   = '';
            document.getElementById('tg-message').value = '';
        } else {
            showToast(data.error || 'Failed to send.', 'error');
        }
    } catch (e) {
        showToast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Notification';
    }
}

// ── History load ──────────────────────────────────────────────
async function loadHistory() {
    const container = document.getElementById('history-container');
    container.innerHTML = '<div class="empty-state"><i class="fa fa-rotate fa-spin"></i><p>Loading…</p></div>';

    try {
        const res  = await fetch(BASE + '/admin/api/notifications/broadcasts');
        const data = await res.json();
        const list = data.broadcasts || [];

        if (!list.length) {
            container.innerHTML = '<div class="empty-state"><i class="fa fa-satellite-dish"></i><p>No broadcasts sent yet.</p></div>';
            return;
        }

        const typeLabel = { system_maintenance: 'Maintenance', targeted: 'Targeted', appointment_booked: 'Appointment' };
        const typeBadge = { system_maintenance: 'badge-system', targeted: 'badge-targeted', appointment_booked: 'badge-appt' };

        const rows = list.map(b => {
            const roles = JSON.parse(b.target_roles || '[]').join(', ');
            const date  = new Date(b.created_at).toLocaleString();
            const badge = typeBadge[b.type] || 'badge-system';
            const label = typeLabel[b.type] || b.type;
            const readC = parseInt(b.read_count || 0);
            const sent  = parseInt(b.total_sent || 0);
            return `<tr>
                <td>
                    <div style="font-weight:700;color:var(--text);margin-bottom:2px;">${escHtml(b.title)}</div>
                    <div style="color:var(--muted);font-size:12px;max-width:300px;">${escHtml(b.message.substring(0, 100))}${b.message.length > 100 ? '…' : ''}</div>
                </td>
                <td><span class="badge ${badge}">${label}</span></td>
                <td style="color:var(--muted);">${escHtml(roles)}</td>
                <td>
                    <div class="delivery-pill"><i class="fa fa-check-double"></i> ${sent} sent</div>
                    <div class="delivery-pill" style="margin-top:3px;"><i class="fa fa-eye" style="color:var(--muted);"></i> ${readC} read</div>
                </td>
                <td style="color:var(--muted);font-size:12px;">${escHtml(b.sender_name || 'Admin')}</td>
                <td style="color:var(--muted);font-size:12px;">${date}</td>
            </tr>`;
        }).join('');

        container.innerHTML = `
            <div style="overflow-x:auto;">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Message</th><th>Type</th><th>Audience</th>
                        <th>Delivery</th><th>Sent By</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            </div>`;
    } catch (e) {
        container.innerHTML = '<div class="empty-state"><i class="fa fa-triangle-exclamation"></i><p>Failed to load history.</p></div>';
    }
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/app.php';