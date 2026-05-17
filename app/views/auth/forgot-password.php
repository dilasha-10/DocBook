<?php
$errors = $errors ?? [];
$message = $message ?? '';
$email = htmlspecialchars($email ?? ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Forgot Password</title>
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>DocBook – Forgot Password</title>
>>>>>>> 5353f4c (Final complete work)
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
<<<<<<< HEAD
            --brand: #cce8f0;
            --brand-dark: #5ab8d0;
            --brand-deep: #2a8fa8;
            --brand-pale: #eef8fc;
            --text-dark: #1a2a3a;
            --text-mid: #4a6070;
            --text-light: #8aa3b8;
            --white: #ffffff;
            --error: #e05252;
            --success: #3bba7a;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; font-family: "Nunito", sans-serif; background: linear-gradient(135deg, #f4fbfd 0%, #ffffff 55%, #eef8fc 100%); color: var(--text-dark); }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(90,184,208,0.14), transparent 28%),
                radial-gradient(circle at right bottom, rgba(42,143,168,0.12), transparent 26%);
            pointer-events: none;
        }
        .shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }
        .hero {
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 28px;
            text-decoration: none;
            color: var(--text-dark);
        }
        .brand span { color: var(--brand-deep); }
        .hero-card {
            max-width: 560px;
            padding: 28px;
            border-radius: 28px;
            background: rgba(255,255,255,0.8);
            border: 1px solid rgba(90,184,208,0.16);
            box-shadow: 0 24px 70px rgba(42,143,168,0.08);
            backdrop-filter: blur(10px);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--brand-pale);
            color: var(--brand-deep);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        h1 { font-size: clamp(2.2rem, 4vw, 3.8rem); line-height: 1.02; letter-spacing: -0.05em; margin-bottom: 16px; }
        .hero p { font-size: 1.02rem; line-height: 1.7; color: var(--text-mid); max-width: 46rem; }
        .points { margin-top: 24px; display: grid; gap: 12px; }
        .point {
            padding: 14px 16px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(90,184,208,0.14);
            color: var(--text-mid);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }
        .point strong { color: var(--text-dark); }
        .panel {
            padding: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(42,143,168,0.16);
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(42,143,168,0.10);
            padding: 32px;
        }
        .card h2 { font-size: 1.9rem; line-height: 1.1; margin-bottom: 10px; }
        .card .sub { color: var(--text-mid); line-height: 1.6; margin-bottom: 22px; }
        .alert-danger, .alert-success {
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 14px;
            font-size: 0.92rem;
            line-height: 1.5;
        }
        .alert-danger { background: #fff5f5; border: 1px solid #f1a6a6; color: #b42318; }
        .alert-success { background: #f0fdf7; border: 1px solid #92e0b2; color: #166534; }
        .field { margin-bottom: 16px; }
        label { display: block; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-mid); margin-bottom: 6px; }
        input {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            border: 1.5px solid #cce8f0;
            background: var(--brand-pale);
            padding: 0 16px;
            font: inherit;
            color: var(--text-dark);
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(42,143,168,0.12); }
        input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,0.08); }
        .error { display: block; margin-top: 6px; font-size: 0.82rem; color: var(--error); }
        .button {
            width: 100%;
            height: 52px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #3aadcc, #1f8ca0);
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(42,143,168,0.22);
            transition: transform .15s, opacity .15s;
        }
        .button:hover { transform: translateY(-1px); opacity: 0.96; }
        .links { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        a { color: var(--brand-deep); text-decoration: none; font-weight: 700; }
        a:hover { text-decoration: underline; }
        .note { margin-top: 18px; font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }
        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .hero { padding: 32px 20px 10px; }
            .panel { padding: 20px; }
            .hero-card { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <a class="brand" href="<?= BASE_URL ?>/login">Doc<span>Book</span></a>
            <div class="hero-card">
                <div class="eyebrow">Password recovery</div>
                <h1>We will verify you with an OTP and let you set a new password.</h1>
                <p>
                    Enter the email address tied to your DocBook account. We will send a 6-digit code from the sender
                    configured in <strong>config/mail.php</strong> through Mailtrap so you can verify the reset flow before choosing a new password.
                </p>
                <div class="points">
                    <div class="point"><strong>Step 1:</strong> Request a one-time code</div>
                    <div class="point"><strong>Step 2:</strong> Verify the OTP within 1 hour</div>
                    <div class="point"><strong>Step 3:</strong> Create a new password and sign in again</div>
                </div>
            </div>
        </section>
        <section class="panel">
            <div class="card">
                <h2>Forgot password</h2>
                <div class="sub">Request a reset OTP for your account email.</div>

                <?php if (!empty($_GET['expired'])): ?>
                    <div class="alert-danger">Your previous OTP expired. Please request a new code.</div>
                <?php endif; ?>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($message !== ''): ?>
                    <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/forgot-password" novalidate>
                    <div class="field">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= $email ?>"
                            placeholder="you@example.com"
                            class="<?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                            autocomplete="email"
                            required>
                        <?php if (!empty($errors['email'])): ?>
                            <span class="error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="button">Send OTP</button>
                </form>

                <div class="links">
                    <a href="<?= BASE_URL ?>/login">Back to login</a>
                    <a href="<?= BASE_URL ?>/signup">Create account</a>
                </div>
                <div class="note">Need help? Check that you used the same email address you registered with.</div>
            </div>
        </section>
    </div>
</body>
</html>
=======
            --brand:       #cce8f0;
            --brand-dark:  #5ab8d0;
            --brand-deep:  #2a8fa8;
            --brand-light: #ddf3f8;
            --brand-pale:  #eef8fc;
            --text-dark:   #1a2a3a;
            --text-mid:    #4a6070;
            --text-light:  #8aa3b8;
            --white:       #ffffff;
            --error:       #e05252;
            --success:     #3bba7a;
            --radius:      12px;
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
        .steps-list { list-style: none; }
        .steps-list li { margin-bottom: 13px; display: flex; align-items: flex-start; font-size: 1rem; font-weight: 600; }
        .steps-list li .step-num {
            width: 24px; height: 24px; background: rgba(255,255,255,0.7); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-right: 12px; font-size: .78rem; color: var(--brand-deep); flex-shrink: 0; font-weight: 800;
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

        .input-group { margin-bottom: 14px; }
        .input-label { display: block; font-size: .78rem; font-weight: 700; color: var(--text-mid); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.05em; }
        .input-group input {
            width: 100%; height: 52px; background: var(--brand-pale); border: 1.5px solid var(--brand);
            border-radius: var(--radius); padding: 0 16px; font-size: 1rem;
            font-family: inherit; color: var(--text-dark); transition: border-color .2s, box-shadow .2s;
        }
        .input-group input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,154,200,0.12); }
        .input-group input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,0.1); }
        .input-group input::placeholder { color: var(--text-light); }
        .error-msg { color: var(--error); font-size: .8rem; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .error-msg::before { content: '⚠ '; font-size: .75rem; }

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
        [data-theme="dark"] .steps-list li { color: #d0d0d0; }
        [data-theme="dark"] .steps-list li .step-num { background: rgba(74,143,196,0.15); color: #4a8fc4; }
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
        <h2>Recover your account</h2>
        <p class="subtext">A secure reset link will be sent to your inbox in seconds.</p>
        <ul class="steps-list">
            <li><span class="step-num">1</span> Enter the email tied to your DocBook account</li>
            <li><span class="step-num">2</span> Check your inbox for the reset link</li>
            <li><span class="step-num">3</span> Set a new password and sign back in</li>
        </ul>
    </aside>

    <main class="form-panel">
        <div class="auth-content">
            <div class="logo">Doc<span>Book</span></div>
            <h1>Forgot password?</h1>
            <p class="form-sub">We'll email you a secure single-use reset link.</p>

            <?php if (!empty($_GET['expired']) || !empty($_GET['invalid'])): ?>
                <div class="alert-danger">That reset link is invalid, expired, or was already used. Please request a new one.</div>
            <?php endif; ?>
            <?php if (!empty($errors['general'])): ?>
                <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/forgot-password" novalidate>
                <div class="input-group">
                    <label class="input-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= $email ?>"
                        placeholder="you@example.com"
                        class="<?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        autocomplete="email" required>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="submit-btn">Send Reset Link</button>
            </form>

            <div class="form-links">
                <a href="<?= BASE_URL ?>/login">Back to login</a>
                <a href="<?= BASE_URL ?>/signup">Create account</a>
            </div>
            <p class="note">Check that you used the same email address you registered with.</p>
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
</script>
</body>
</html>
>>>>>>> 5353f4c (Final complete work)
