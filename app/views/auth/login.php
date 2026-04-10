<?php
$errors   = $errors ?? [];
$oldEmail = htmlspecialchars(trim($_POST['email'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>DocBook – Patient Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand:       #B8D4EE;
            --brand-dark:  #8CB8DF;
            --brand-deep:  #5A9AC8;
            --brand-light: #E8F3FB;
            --brand-pale:  #F2F8FD;
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

        /* ── Top navbar ── */
        .auth-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 56px;
            background: var(--brand);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            border-bottom: 1px solid var(--brand-dark);
        }
        .auth-navbar .nav-logo {
            font-size: 1.35rem; font-weight: 800; color: var(--text-dark); text-decoration: none; letter-spacing: -0.5px;
        }
        .auth-navbar .nav-logo span { color: var(--brand-deep); }
        .auth-navbar .nav-links { display: flex; gap: 8px; list-style: none; }
        .auth-navbar .nav-links a {
            font-size: .9rem; font-weight: 600; color: var(--text-dark);
            text-decoration: none; padding: 6px 14px; border-radius: 6px;
            transition: background .15s;
        }
        .auth-navbar .nav-links a:hover { background: var(--brand-dark); }

        .split-container { display: flex; height: 100vh; width: 100vw; padding-top: 56px; }

        .brand-panel {
            flex: 1.1;
            background: var(--brand);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 6%;
            position: relative;
            overflow: hidden;
        }
        .brand-panel img {
            width: 100%;
            max-height: 320px;
            object-fit: contain;
            margin-bottom: 32px;
            position: relative; z-index: 1;
        }
        .brand-panel h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 12px; line-height: 1.2; position: relative; z-index: 1; }
        .brand-panel p.subtext { font-size: 1rem; opacity: .8; margin-bottom: 28px; position: relative; z-index: 1; }
        .feature-list { list-style: none; position: relative; z-index: 1; }
        .feature-list li {
            margin-bottom: 13px; display: flex; align-items: center;
            font-size: 1rem; font-weight: 600;
        }
        .feature-list li .check {
            width: 22px; height: 22px;
            background: rgba(255,255,255,0.7);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-right: 12px; font-size: .75rem;
            color: var(--brand-deep); flex-shrink: 0;
        }

        .form-panel {
            flex: 1;
            background: var(--white);
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 40px; overflow-y: auto;
        }
        .auth-content { width: 100%; max-width: 420px; }

        .logo {
            font-size: 2rem; font-weight: 800;
            margin-bottom: 36px; color: var(--text-dark);
            text-align: center; letter-spacing: -0.5px;
        }
        .logo span { color: var(--brand-deep); }

        .auth-content h1 { font-size: 1.75rem; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
        .auth-content p.sign-in-prompt { color: var(--text-mid); font-size: .97rem; margin-bottom: 26px; }

        .input-group { margin-bottom: 14px; position: relative; }
        .input-label {
            display: block; font-size: .78rem; font-weight: 700;
            color: var(--text-mid); margin-bottom: 5px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .input-group input {
            width: 100%; height: 52px;
            background: var(--brand-pale);
            border: 1.5px solid var(--brand);
            border-radius: var(--radius);
            padding: 0 16px; font-size: 1rem;
            font-family: inherit; color: var(--text-dark);
            transition: border-color .2s, box-shadow .2s;
        }
        .input-group input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,154,200,0.12); }
        .input-group input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,0.1); }
        .input-group input.is-valid   { border-color: var(--success); }
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

        .alert-danger {
            background: #fff5f5; border: 1.5px solid #fca5a5; color: #b91c1c;
            border-radius: var(--radius); padding: 12px 16px; font-size: .88rem; margin-bottom: 18px;
        }
        .alert-success {
            background: #f0fdf7; border: 1.5px solid #86efac; color: #166534;
            border-radius: var(--radius); padding: 12px 16px; font-size: .88rem; margin-bottom: 18px;
        }

        .login-btn {
            width: 100%; height: 52px;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand-deep));
            color: var(--text-dark); border: none;
            border-radius: var(--radius); font-size: 1.05rem; font-weight: 700;
            cursor: pointer; margin-top: 6px;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            font-family: inherit;
            box-shadow: 0 4px 16px rgba(140,184,223,0.4); letter-spacing: 0.02em;
        }
        .login-btn:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(140,184,223,0.5); }
        .login-btn:active { transform: translateY(0); }

        .forgot-pass { display: block; margin-top: 16px; color: var(--brand-deep); text-decoration: none; font-size: .92rem; text-align: center; font-weight: 600; }
        .forgot-pass:hover { color: var(--brand-dark); text-decoration: underline; }

        .divider { margin: 26px 0; display: flex; align-items: center; color: var(--text-light); font-size: .8rem; }
        .divider::before, .divider::after { content: ""; flex: 1; border-bottom: 1px solid var(--brand-light); }
        .divider span { padding: 0 14px; text-transform: uppercase; letter-spacing: 0.06em; }

        .signup-link { font-size: .95rem; color: var(--text-mid); text-align: center; }
        .signup-link a { color: var(--brand-deep); text-decoration: none; font-weight: 700; }
        .signup-link a:hover { text-decoration: underline; }

        @media (max-width: 1024px) { .brand-panel { display: none; } }
        @media (max-width: 480px) { .form-panel { padding: 24px 20px; } }
    </style>
</head>
<body>

<nav class="auth-navbar">
    <a href="/login" class="nav-logo">Doc<span>Book</span></a>
    <ul class="nav-links">
        <li><a href="/about">About</a></li>
        <li><a href="/contact">Contact</a></li>
    </ul>
</nav>

<div class="split-container">
    <aside class="brand-panel">
        <img src="https://i.pinimg.com/1200x/83/fe/b5/83feb5f6ec0f408eae4a1eac07fb2eff.jpg" alt="Healthcare Team">
        <h2>Welcome back!</h2>
        <p class="subtext">Manage your health appointments easily from one place.</p>
        <ul class="feature-list">
            <li><span class="check">✓</span> Book appointments with top doctors</li>
            <li><span class="check">✓</span> View your upcoming &amp; past visits</li>
            <li><span class="check">✓</span> Chat with your doctor directly</li>
            <li><span class="check">✓</span> Reschedule anytime with ease</li>
        </ul>
    </aside>

    <main class="form-panel">
        <div class="auth-content">
            <div class="logo">Doc<span>Book</span></div>

            <h1>Sign In</h1>
            <p class="sign-in-prompt">Sign in to manage your appointments</p>

            <?php if (!empty($_GET['registered'])): ?>
                <div class="alert-success">✓ Account created successfully! Please sign in below.</div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login" novalidate id="loginForm">

                <div class="input-group">
                    <label class="input-label" for="login-email">Email Address</label>
                    <input type="email" name="email" id="login-email"
                        placeholder="you@example.com"
                        value="<?= $oldEmail ?>"
                        class="<?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        autocomplete="email" required>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                    <div id="email-feedback" class="error-msg" style="display:none;"></div>
                </div>

                <div class="input-group">
                    <label class="input-label" for="login-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="login-password"
                            placeholder="Enter your password"
                            class="<?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="toggleBtn">Show</button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                    <div id="pw-feedback" class="error-msg" style="display:none;"></div>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
                <a href="#" class="forgot-pass">Forgot password?</a>
            </form>

            <div class="divider"><span>New here?</span></div>

            <p class="signup-link">
                Don't have an account? <a href="/signup">Create one</a>
            </p>
        </div>
    </main>
</div>

<script>
document.getElementById('toggleBtn').addEventListener('click', function() {
    const input = document.getElementById('login-password');
    if (input.type === 'password') { input.type = 'text'; this.textContent = 'Hide'; }
    else { input.type = 'password'; this.textContent = 'Show'; }
});

const emailInput = document.getElementById('login-email');
const pwInput    = document.getElementById('login-password');
const emailFb    = document.getElementById('email-feedback');
const pwFb       = document.getElementById('pw-feedback');

function validateEmail(val) {
    if (!val.trim()) return 'Email is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val.trim())) return 'Please enter a valid email address.';
    return '';
}
function validatePassword(val) {
    if (!val) return 'Password is required.';
    if (val.length < 8) return 'Password must be at least 8 characters.';
    return '';
}
function setFieldState(input, feedbackEl, msg) {
    if (msg) {
        input.classList.add('is-invalid'); input.classList.remove('is-valid');
        feedbackEl.textContent = msg; feedbackEl.style.display = 'flex';
    } else {
        input.classList.remove('is-invalid'); input.classList.add('is-valid');
        feedbackEl.style.display = 'none';
    }
}

emailInput.addEventListener('blur', () => setFieldState(emailInput, emailFb, validateEmail(emailInput.value)));
pwInput.addEventListener('blur',    () => setFieldState(pwInput, pwFb, validatePassword(pwInput.value)));

document.getElementById('loginForm').addEventListener('submit', function(e) {
    const eErr = validateEmail(emailInput.value);
    const pErr = validatePassword(pwInput.value);
    setFieldState(emailInput, emailFb, eErr);
    setFieldState(pwInput, pwFb, pErr);
    if (eErr || pErr) e.preventDefault();
});
</script>
</body>
</html>