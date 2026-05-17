<?php
$errors = $errors ?? [];
$message = $message ?? '';
$email = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>DocBook – Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand:#cce8f0; --brand-dark:#5ab8d0; --brand-deep:#2a8fa8; --brand-light:#ddf3f8;
            --brand-pale:#eef8fc; --text-dark:#1a2a3a; --text-mid:#4a6070; --text-light:#8aa3b8;
            --white:#ffffff; --error:#e05252; --success:#3bba7a; --radius:12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; width: 100%; font-family: "Nunito", sans-serif; background: var(--white); }

        .auth-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 56px; background: var(--brand);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; border-bottom: 1px solid var(--brand-dark);
        }
        .auth-nav-right { display: flex; align-items: center; gap: 8px; }
        .auth-navbar .nav-logo { font-size: 1.35rem; font-weight: 800; color: var(--text-dark); text-decoration: none; letter-spacing: -0.5px; }
        .auth-navbar .nav-logo span { color: var(--brand-deep); }
        .auth-navbar .nav-links { display: flex; gap: 8px; list-style: none; }
        .auth-navbar .nav-links a { font-size: .9rem; font-weight: 600; color: var(--text-dark); text-decoration: none; padding: 6px 14px; border-radius: 6px; transition: background .15s; }
        .auth-navbar .nav-links a:hover { background: var(--brand-dark); }

        .split-container { display: flex; height: 100vh; width: 100vw; padding-top: 56px; }

        .brand-panel {
            flex: 1.1; background: var(--brand); color: var(--text-dark);
            display: flex; flex-direction: column; justify-content: center;
            padding: 0 6%; position: relative; overflow: hidden;
        }
        .brand-panel h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; line-height: 1.2; }
        .brand-panel p.subtext { font-size: 1rem; opacity: .8; margin-bottom: 28px; }
        .tips-list { list-style: none; }
        .tips-list li { margin-bottom: 13px; display: flex; align-items: flex-start; font-size: 1rem; font-weight: 600; }
        .tips-list li .check {
            width: 22px; height: 22px; background: rgba(255,255,255,0.7); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-right: 12px; font-size: .75rem; color: var(--brand-deep); flex-shrink: 0;
        }

        .form-panel {
            flex: 1; background: var(--white);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 40px; overflow-y: auto;
        }
        .auth-content { width: 100%; max-width: 420px; }
        .logo { font-size: 2rem; font-weight: 800; margin-bottom: 36px; color: var(--text-dark); text-align: center; letter-spacing: -0.5px; }
        .logo span { color: var(--brand-deep); }
        .auth-content h1 { font-size: 1.75rem; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
        .auth-content p.form-sub { color: var(--text-mid); font-size: .97rem; margin-bottom: 26px; }

        .input-group { margin-bottom: 14px; position: relative; }
        .input-label { display: block; font-size: .78rem; font-weight: 700; color: var(--text-mid); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.05em; }
        .input-group input {
            width: 100%; height: 52px; background: var(--brand-pale); border: 1.5px solid var(--brand);
            border-radius: var(--radius); padding: 0 16px; font-size: 1rem;
            font-family: inherit; color: var(--text-dark); transition: border-color .2s, box-shadow .2s;
        }
        .input-group input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,154,200,0.12); }
        .input-group input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,0.1); }
        .input-group input::placeholder { color: var(--text-light); }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 72px; }
        .password-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: var(--brand-deep);
            font-weight: 700; font-size: .8rem; cursor: pointer;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .error-msg { color: var(--error); font-size: .8rem; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .error-msg::before { content: '⚠ '; font-size: .75rem; }
        .hint { margin-top: 6px; color: var(--text-mid); font-size: .85rem; line-height: 1.5; }

        .alert-danger { background: #fff5f5; border: 1.5px solid #fca5a5; color: #b91c1c; border-radius: var(--radius); padding: 12px 16px; font-size: .88rem; margin-bottom: 18px; }
        .alert-success { background: #f0fdf7; border: 1.5px solid #86efac; color: #166534; border-radius: var(--radius); padding: 12px 16px; font-size: .88rem; margin-bottom: 18px; }

        .submit-btn {
            width: 100%; height: 52px; background: linear-gradient(135deg, #3aadcc, #1f8ca0);
            color: #ffffff; border: none; border-radius: var(--radius); font-size: 1.05rem; font-weight: 700;
            cursor: pointer; margin-top: 6px; transition: opacity .2s, transform .15s, box-shadow .2s;
            font-family: inherit; box-shadow: 0 4px 16px rgba(42,143,168,0.30); letter-spacing: 0.02em;
        }
        .submit-btn:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(42,143,168,0.40); }
        .submit-btn:active { transform: translateY(0); }

        .form-links { display: flex; justify-content: space-between; margin-top: 16px; flex-wrap: wrap; gap: 8px; }
        .form-links a { color: var(--brand-deep); text-decoration: none; font-size: .92rem; font-weight: 600; }
        .form-links a:hover { text-decoration: underline; }
        .note { margin-top: 18px; font-size: .88rem; color: var(--text-light); line-height: 1.6; text-align: center; }

        @media (max-width: 1024px) { .brand-panel { display: none; } }
        @media (max-width: 480px) { .form-panel { padding: 24px 20px; } }

        [data-theme="dark"] { --brand:#1e1e1e; --brand-dark:#4a8fc4; --brand-deep:#4a8fc4; --brand-light:#1a1a1a; --brand-pale:#161616; --text-dark:#f0f0f0; --text-mid:#888888; --text-light:#555555; --white:#2c2c2c; --error:#d06060; --success:#50a878; }
        [data-theme="dark"] body { background: #2c2c2c; }
        [data-theme="dark"] .auth-navbar { background: #242424; border-bottom-color: rgba(255,255,255,0.07); }
        [data-theme="dark"] .auth-navbar .nav-logo { color: #f0f0f0; }
        [data-theme="dark"] .auth-navbar .nav-links a { color: #888888; }
        [data-theme="dark"] .auth-navbar .nav-links a:hover { background: rgba(255,255,255,0.06); color: #f0f0f0; }
        [data-theme="dark"] .brand-panel { background: #161616; color: #f0f0f0; }
        [data-theme="dark"] .brand-panel h2 { color: #f0f0f0; }
        [data-theme="dark"] .brand-panel p.subtext { color: #888888; }
        [data-theme="dark"] .tips-list li { color: #d0d0d0; }
        [data-theme="dark"] .tips-list li .check { background: rgba(74,143,196,0.15); color: #4a8fc4; }
        [data-theme="dark"] .form-panel { background: #2c2c2c; }
        [data-theme="dark"] .logo { color: #f0f0f0; }
        [data-theme="dark"] .auth-content h1 { color: #f0f0f0; }
        [data-theme="dark"] .auth-content p.form-sub { color: #888888; }
        [data-theme="dark"] .input-label { color: #777777; }
        [data-theme="dark"] .input-group input { background: #1a1a1a; border-color: #2e2e2e; color: #f0f0f0; }
        [data-theme="dark"] .input-group input:focus { border-color: #4a8fc4; box-shadow: 0 0 0 3px rgba(74,143,196,0.12); background: #1e1e1e; }
        [data-theme="dark"] .input-group input::placeholder { color: #555555; }
        [data-theme="dark"] .submit-btn { background: #4a8fc4; box-shadow: none; }
        [data-theme="dark"] .submit-btn:hover { background: #5a9fd4; opacity: 1; transform: translateY(-1px); }
        [data-theme="dark"] .password-toggle { color: #4a8fc4; }
        [data-theme="dark"] .form-links a { color: #4a8fc4; }
        [data-theme="dark"] .alert-danger { background: rgba(208,96,96,0.10); border-color: rgba(208,96,96,0.28); color: #d08080; }
        [data-theme="dark"] .alert-success { background: rgba(80,168,120,0.10); border-color: rgba(80,168,120,0.28); color: #70b890; }

        .auth-theme-toggle {
            display: flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 50%;
            border: 1.5px solid rgba(0,0,0,0.15); background: rgba(255,255,255,0.30);
            color: var(--text-dark); font-size: 15px; cursor: pointer; transition: all .18s;
        }
        .auth-theme-toggle:hover { background: rgba(255,255,255,0.50); }
        [data-theme="dark"] .auth-theme-toggle { border-color: #2e2e2e; background: rgba(255,255,255,0.03); color: #888888; }
        [data-theme="dark"] .auth-theme-toggle:hover { background: rgba(74,143,196,0.10); border-color: #4a8fc4; color: #4a8fc4; }
    </style>
    <script>(function(){ if(localStorage.getItem('docbook-theme')==='dark'){ document.documentElement.setAttribute('data-theme','dark'); } })();</script>
</head>
<body>

<nav class="auth-navbar">
    <a href="<?= BASE_URL ?>/login" class="nav-logo">Doc<span>Book</span></a>
    <div class="auth-nav-right">
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/about">About</a></li>
            <li><a href="<?= BASE_URL ?>/contact">Contact</a></li>
        </ul>
        <button class="auth-theme-toggle" id="authThemeBtn" aria-label="Toggle dark mode"><i class="fa fa-moon" id="authThemeIcon"></i></button>
    </div>
</nav>

<div class="split-container">
    <aside class="brand-panel">
        <h2>Set a new password</h2>
        <p class="subtext">Choose something strong that you haven't used before.</p>
        <ul class="tips-list">
            <li><span class="check">✓</span> At least 12 characters long</li>
            <li><span class="check">✓</span> Mix of uppercase and lowercase letters</li>
            <li><span class="check">✓</span> At least one number and one symbol</li>
            <li><span class="check">✓</span> Different from your previous passwords</li>
        </ul>
    </aside>

    <main class="form-panel">
        <div class="auth-content">
            <div class="logo">Doc<span>Book</span></div>
            <h1>Reset your password</h1>
            <p class="form-sub">OTP verified for <?= $email !== '' ? $email : 'your account' ?>. Choose a new password below.</p>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/forgot-password/reset" novalidate id="resetForm">
                <div class="input-group">
                    <label class="input-label" for="password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                            placeholder="Enter new password"
                            class="<?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            autocomplete="new-password" required>
                        <button type="button" class="password-toggle" id="togglePassword">Show</button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <p class="hint">At least 12 characters with uppercase, lowercase, number, and symbol.</p>
                </div>

                <div class="input-group">
                    <label class="input-label" for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirm new password"
                            class="<?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                            autocomplete="new-password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">Show</button>
                    </div>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-btn">Update Password</button>
            </form>

            <div class="form-links">
                <a href="<?= BASE_URL ?>/forgot-password/verify">Back to OTP verification</a>
                <a href="<?= BASE_URL ?>/login">Back to login</a>
            </div>
            <p class="note">Once saved, the OTP code cannot be reused.</p>
        </div>
    </main>
</div>

<script>
(function(){
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css';
    document.head.appendChild(link);
})();
(function(){
    var btn = document.getElementById('authThemeBtn');
    var icon = document.getElementById('authThemeIcon');
    function applyTheme(dark){
        if(dark){ document.documentElement.setAttribute('data-theme','dark'); icon.className='fa fa-sun'; }
        else { document.documentElement.removeAttribute('data-theme'); icon.className='fa fa-moon'; }
    }
    applyTheme(localStorage.getItem('docbook-theme')==='dark');
    btn.addEventListener('click',function(){
        var isDark = document.documentElement.getAttribute('data-theme')==='dark';
        localStorage.setItem('docbook-theme', isDark ? 'light' : 'dark');
        applyTheme(!isDark);
    });
})();

document.getElementById('togglePassword').addEventListener('click', function() {
    var input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? 'Show' : 'Hide';
});
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    var input = document.getElementById('confirm_password');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? 'Show' : 'Hide';
});
</script>
</body>
</html>