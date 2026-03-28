<?php
$title = 'Profile & Settings';

$extra_styles = <<<CSS
<style>
.page-layout  { display:flex; min-height:calc(100vh - 60px); }
.sidebar      { width:220px; flex-shrink:0; background:var(--surface); border-right:1px solid var(--border);
                padding:24px 0; position:sticky; top:60px; height:calc(100vh - 60px); overflow:hidden; align-self:flex-start; }
.sidebar-label { display:block; font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
                 color:var(--hint); padding:18px 22px 6px; }
.sidebar-link  { display:flex; align-items:center; gap:10px; padding:10px 22px; font-size:14px;
                 color:var(--muted); text-decoration:none; border-left:3px solid transparent; transition:all .15s; }
.sidebar-link:hover  { color:var(--text); background:rgba(255,255,255,.04); }
.sidebar-link.active { color:#818cf8; border-left-color:#818cf8; background:rgba(99,102,241,.08); font-weight:600; }
.sidebar-link i      { width:16px; text-align:center; font-size:13px; }
.page-content { flex:1; padding:32px 36px; overflow:auto; max-width:720px; }

.section-card { background:var(--surface); border:1px solid var(--border); border-radius:12px;
                padding:28px; margin-bottom:24px; }
.section-card h2 { font-size:16px; font-weight:700; margin-bottom:4px; }
.section-card .sub { font-size:13px; color:var(--muted); margin-bottom:24px; }

.form-row   { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.form-group label { font-size:13px; font-weight:600; color:var(--muted); }
.form-group .input { width:100%; box-sizing:border-box; }
.form-group .input:disabled { opacity:.45; cursor:not-allowed; }

.avatar-lg   { width:72px; height:72px; border-radius:50%; background:var(--accent);
               display:flex; align-items:center; justify-content:center;
               font-size:26px; font-weight:700; color:#fff; flex-shrink:0; }
.profile-hero { display:flex; align-items:center; gap:20px; margin-bottom:28px; }
.profile-hero .meta { display:flex; flex-direction:column; gap:4px; }
.profile-hero .meta strong { font-size:18px; font-weight:700; }
.profile-hero .meta span   { font-size:13px; color:var(--muted); }
.role-badge  { display:inline-block; padding:2px 10px; border-radius:999px; font-size:11px;
               font-weight:700; background:rgba(99,102,241,.18); color:#818cf8; text-transform:capitalize; }

.save-row    { display:flex; align-items:center; gap:12px; margin-top:8px; }
.msg         { font-size:13px; display:none; }
.msg.ok      { color:#34d399; }
.msg.err     { color:#f87171; }

.danger-zone { border-color:rgba(239,68,68,.3); }
.danger-zone h2 { color:#f87171; }

@media (max-width:640px) {
  .form-row { grid-template-columns:1fr; }
  .page-content { padding:16px 12px; }
}
</style>
CSS;

ob_start();
?>
<div class="page-layout">

    <aside class="sidebar">
        <span class="sidebar-label">Menu</span>
        <a href="/dashboard" class="sidebar-link">
            <i class="fa fa-th-large"></i> Dashboard
        </a>
        <a href="/dashboard#upcoming" class="sidebar-link">
            <i class="fa fa-calendar-check"></i> Appointments
        </a>
        <a href="/dashboard#past" class="sidebar-link">
            <i class="fa fa-clock-rotate-left"></i> History
        </a>
        <span class="sidebar-label">Account</span>
        <a href="/profile" class="sidebar-link active">
            <i class="fa fa-user"></i> Profile &amp; Settings
        </a>
    </aside>

    <div class="page-content">

        <div class="profile-hero">
            <div class="avatar-lg"><?php echo strtoupper(substr($user['name'], 0, 2)); ?></div>
            <div class="meta">
                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
                <span class="role-badge"><?php echo htmlspecialchars($user['role']); ?></span>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="section-card">
            <h2>Personal Information</h2>
            <p class="sub">Update your name and contact details.</p>

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input id="prof-name" class="input" type="text" value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input id="prof-phone" class="input" type="text" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+1 555 000 0000">
                </div>
            </div>
            <div class="form-group">
                <label>Email address</label>
                <input class="input" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <span style="font-size:11px;color:var(--hint);margin-top:2px;">Email cannot be changed. Contact support if needed.</span>
            </div>
            <div class="form-group">
                <label>Member since</label>
                <input class="input" type="text" value="<?php echo isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'N/A'; ?>" disabled>
            </div>

            <div class="save-row">
                <button class="btn-primary" onclick="saveProfile()">Save changes</button>
                <span id="prof-msg" class="msg"></span>
            </div>
        </div>

        <!-- Change Password -->
        <div class="section-card">
            <h2>Change Password</h2>
            <p class="sub">Use a strong password of at least 8 characters.</p>

            <div class="form-group">
                <label>Current password</label>
                <input id="pw-current" class="input" type="password" placeholder="••••••••">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>New password</label>
                    <input id="pw-new" class="input" type="password" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Confirm new password</label>
                    <input id="pw-confirm" class="input" type="password" placeholder="••••••••">
                </div>
            </div>

            <div class="save-row">
                <button class="btn-primary" onclick="changePassword()">Update password</button>
                <span id="pw-msg" class="msg"></span>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="section-card danger-zone">
            <h2><i class="fa fa-triangle-exclamation"></i> Danger Zone</h2>
            <p class="sub">These actions are permanent and cannot be undone.</p>
            <button class="btn-primary"
                style="background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3);"
                onclick="if(confirm('Are you sure you want to delete your account? This cannot be undone.')) alert('Please contact support to delete your account.')">
                Delete my account
            </button>
        </div>

    </div>
</div>
<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
function showMsg(id, text, ok) {
    const el = document.getElementById(id);
    el.textContent = text;
    el.className = 'msg ' + (ok ? 'ok' : 'err');
    el.style.display = 'inline';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

function saveProfile() {
    const name  = document.getElementById('prof-name').value.trim();
    const phone = document.getElementById('prof-phone').value.trim();
    if (!name) { showMsg('prof-msg', 'Name is required.', false); return; }
    fetch('/api/profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, phone })
    })
    .then(r => r.json())
    .then(d => showMsg('prof-msg', d.message, d.success))
    .catch(() => showMsg('prof-msg', 'Network error.', false));
}

function changePassword() {
    const current = document.getElementById('pw-current').value;
    const newPw   = document.getElementById('pw-new').value;
    const confirm = document.getElementById('pw-confirm').value;
    if (!current || !newPw || !confirm) { showMsg('pw-msg', 'All fields are required.', false); return; }
    if (newPw !== confirm)              { showMsg('pw-msg', 'Passwords do not match.', false); return; }
    if (newPw.length < 8)              { showMsg('pw-msg', 'Min 8 characters required.', false); return; }
    fetch('/api/settings/password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: current, new_password: newPw, confirm_password: confirm })
    })
    .then(r => r.json())
    .then(d => {
        showMsg('pw-msg', d.message, d.success);
        if (d.success) {
            document.getElementById('pw-current').value = '';
            document.getElementById('pw-new').value = '';
            document.getElementById('pw-confirm').value = '';
        }
    })
    .catch(() => showMsg('pw-msg', 'Network error.', false));
}
</script>
JS;

include __DIR__ . '/../layouts/app.php';