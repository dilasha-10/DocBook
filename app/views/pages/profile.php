<?php
$title = 'Profile & Settings';

$extra_styles = <<<CSS
<style>
.page-content { padding:0; max-width:900px; }

.section-card { background:var(--surface); border:1px solid var(--border); border-radius:12px;
                padding:28px; margin-bottom:24px; max-width:860px; }
.section-card h2 { font-size:16px; font-weight:700; margin-bottom:4px; }
.section-card .sub { font-size:13px; color:var(--muted); margin-bottom:24px; }

.form-row   { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.form-group label { font-size:13px; font-weight:600; color:var(--muted); }
.form-group .input { width:100%; box-sizing:border-box; }
.form-group .input:disabled { opacity:.45; cursor:not-allowed; }

.avatar-lg   { width:72px; height:72px; border-radius:50%; background:var(--blue);
               display:flex; align-items:center; justify-content:center;
               font-size:26px; font-weight:700; color:#fff; flex-shrink:0;
               box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
.profile-hero { display:flex; align-items:center; gap:20px; margin-bottom:28px; max-width:860px;
                background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg);
                padding:24px 28px; }
.profile-hero .meta { display:flex; flex-direction:column; gap:6px; }
.profile-hero .meta strong { font-size:20px; font-weight:700; color:var(--text); }
.profile-hero .meta span   { font-size:14px; color:var(--muted); }
.role-badge  { display:inline-block; padding:4px 14px; border-radius:999px; font-size:12px;
               font-weight:700; background:rgba(77,166,232,0.15); color:var(--blue); text-transform:capitalize;
               border:1px solid rgba(77,166,232,0.3); }

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
            <h2>Delete Account</h2>
            <p class="sub">These actions are permanent and cannot be undone.</p>
            <button class="btn-primary"
                style="background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3);"
                onclick="if(confirm('Are you sure you want to delete your account? This cannot be undone.')) alert('Please contact support to delete your account.')">
                Delete my account
            </button>
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