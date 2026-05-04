<?php
$title = 'My Profile';

$extra_styles = <<<CSS
<style>
.page-content { padding: 0; max-width: 900px; margin: 0 auto; width: 100%; }

.section-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 28px;
    margin-bottom: 24px;
    max-width: 860px;
}
.section-card h2    { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
.section-card .sub  { font-size: 13px; color: var(--muted); margin-bottom: 24px; }

.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label           { font-size: 13px; font-weight: 600; color: var(--muted); }
.form-group .input          { width: 100%; box-sizing: border-box; }
.form-group .input:disabled { opacity: .45; cursor: not-allowed; }

.avatar-lg {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--blue);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; font-weight: 700; color: #fff; flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37,99,235,.3);
}
.profile-hero {
    display: flex; align-items: center; gap: 20px;
    margin-bottom: 28px; max-width: 860px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 24px 28px;
}
.profile-hero .meta              { display: flex; flex-direction: column; gap: 6px; }
.profile-hero .meta strong       { font-size: 20px; font-weight: 700; color: var(--text); }
.profile-hero .meta span         { font-size: 14px; color: var(--muted); }
.role-badge {
    display: inline-block; padding: 4px 14px; border-radius: 999px;
    font-size: 12px; font-weight: 700;
    background: rgba(77,166,232,.15); color: var(--blue);
    text-transform: capitalize; border: 1px solid rgba(77,166,232,.3);
}

.save-row { display: flex; align-items: center; gap: 12px; margin-top: 8px; }
.msg      { font-size: 13px; display: none; }
.msg.ok   { color: #34d399; display: inline; }
.msg.err  { color: #f87171; display: inline; }

.danger-zone        { border-color: rgba(239,68,68,.3); }
.danger-zone h2     { color: #f87171; }

@media (max-width: 640px) {
    .form-row      { grid-template-columns: 1fr; }
    .page-content  { padding: 16px 12px; }
}
</style>
CSS;

ob_start();
?>
<div class="page-content">

    <div class="profile-hero">
        <div class="avatar-lg"><?php echo strtoupper(substr($user['name'] ?? 'LA', 0, 2)); ?></div>
        <div class="meta">
            <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong>
            <span><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
            <span class="role-badge">Lab Admin</span>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="section-card">
        <h2>Personal Information</h2>
        <p class="sub">Update your name and contact details.</p>

        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input id="prof-name" class="input" type="text" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input id="prof-phone" class="input" type="text" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+92 300 0000000">
            </div>
        </div>
        <div class="form-group">
            <label>Email address</label>
            <input class="input" type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
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

</div>

<?php
$content = ob_get_clean();

$extra_scripts = <<<JS
<script>
function saveProfile() {
    var name  = document.getElementById('prof-name').value.trim();
    var phone = document.getElementById('prof-phone').value.trim();
    var msg   = document.getElementById('prof-msg');

    if (!name) { setMsg(msg, 'err', 'Name is required.'); return; }

    fetch(BASE_URL + '/api/profile/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, phone: phone })
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success) setMsg(msg, 'ok', 'Profile updated.');
        else              setMsg(msg, 'err', data.message || 'Update failed.');
    })
    .catch(function(){ setMsg(msg, 'err', 'Network error. Please try again.'); });
}

function changePassword() {
    var current = document.getElementById('pw-current').value;
    var pw      = document.getElementById('pw-new').value;
    var confirm = document.getElementById('pw-confirm').value;
    var msg     = document.getElementById('pw-msg');

    if (!current || !pw) { setMsg(msg, 'err', 'All fields are required.'); return; }
    if (pw !== confirm)  { setMsg(msg, 'err', 'Passwords do not match.'); return; }
    if (pw.length < 8)   { setMsg(msg, 'err', 'Password must be at least 8 characters.'); return; }

    fetch(BASE_URL + '/api/profile/change-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: current, new_password: pw })
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success) {
            setMsg(msg, 'ok', 'Password updated.');
            document.getElementById('pw-current').value = '';
            document.getElementById('pw-new').value     = '';
            document.getElementById('pw-confirm').value = '';
        } else {
            setMsg(msg, 'err', data.message || 'Could not update password.');
        }
    })
    .catch(function(){ setMsg(msg, 'err', 'Network error. Please try again.'); });
}

function setMsg(el, type, text) {
    if (!el) return;
    el.className   = 'msg' + (type ? ' ' + type : '');
    el.textContent = text;
}
</script>
JS;
include BASE_PATH . '/app/views/layouts/app-lab-admin.php';