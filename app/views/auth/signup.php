<?php
$errors = $errors ?? [];
$old    = $old    ?? [];

function old(string $key, string $default = ''): string {
    global $old;
    return htmlspecialchars($old[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook – Create Account</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
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

        /* ── Top navbar ── */
        .auth-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 56px;
            background: var(--brand);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
            border-bottom: 1px solid var(--brand-dark);
        }
        .auth-nav-right {
            display: flex; align-items: center; gap: 8px;
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

        .signup-box { display: flex; width: 100vw; height: 100vh; padding-top: 56px; }

        /* ── Left panel ── */
        .left-panel {
            flex: 1.1;
            background: var(--brand);
            color: var(--text-dark);
            display: flex; flex-direction: column; justify-content: center;
            padding: 0 6%; position: relative; overflow: hidden;
        }
        .left-panel img {
            width: 100%; max-height: 300px; object-fit: contain;
            margin-bottom: 28px;
            position: relative; z-index: 1;
        }
        .hero-title    { font-size: 2rem; font-weight: 800; margin-bottom: 10px; line-height: 1.2; position: relative; z-index: 1; }
        .hero-subtitle { font-size: .95rem; opacity: .8; margin-bottom: 20px; position: relative; z-index: 1; }
        .benefit {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 12px; font-size: .97rem; font-weight: 600;
            position: relative; z-index: 1;
        }
        .benefit .check {
            width: 20px; height: 20px;
            background: rgba(255,255,255,0.7); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; color: var(--brand-deep); flex-shrink: 0;
        }

        /* ── Right panel ── */
        .right-panel {
            flex: 1; background: var(--white);
            display: flex; flex-direction: column; justify-content: center;
            padding: 30px 48px; overflow-y: auto;
        }

        .logo { font-size: 1.9rem; font-weight: 800; color: var(--text-dark); text-align: center; margin-bottom: 20px; letter-spacing: -0.5px; }
        .logo .book { color: var(--brand-deep); }

        .form-title    { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 4px; }
        .form-subtitle { color: var(--text-mid); font-size: .92rem; margin-bottom: 20px; }

        /* ── Row layout for two-col fields ── */
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .field { margin-bottom: 11px; }
        .input-label {
            display: block; font-size: .75rem; font-weight: 700;
            color: var(--text-mid); margin-bottom: 4px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }

        .input {
            width: 100%; height: 48px;
            background: var(--brand-pale);
            border: 1.5px solid var(--brand);
            border-radius: var(--radius);
            padding: 0 14px; font-size: .97rem;
            font-family: inherit; color: var(--text-dark);
            transition: border-color .2s, box-shadow .2s;
        }
        .input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,154,200,0.12); }
        .input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,0.1); }
        .input.is-valid   { border-color: var(--success); }
        .input::placeholder { color: var(--text-light); }

        /* date input fix */
        input[type="date"].input { color: var(--text-dark); }
        input[type="date"].input::-webkit-calendar-picker-indicator { opacity: 0.6; cursor: pointer; }

        .toggle-container { position: relative; }
        .toggle-container .input { padding-right: 72px; }
        .toggle-btn {
            position: absolute; right: 12px; top: 13px;
            background: none; border: none; color: var(--brand-deep);
            font-weight: 700; font-size: .78rem; cursor: pointer;
            text-transform: uppercase; letter-spacing: 0.05em; font-family: inherit;
        }

        .error { color: var(--error); font-size: .78rem; margin-top: 4px; display: flex; align-items: center; gap: 3px; }
        .error::before { content: '⚠ '; }

        .alert-danger {
            background: #fff5f5; border: 1.5px solid #fca5a5; color: #b91c1c;
            border-radius: var(--radius); padding: 12px 16px;
            font-size: .86rem; margin-bottom: 14px;
        }

        /* ── Password strength ── */
        .pw-strength { margin-top: 6px; }
        .pw-strength-bar {
            height: 4px; border-radius: 2px;
            background: var(--brand-light);
            overflow: hidden; margin-bottom: 4px;
        }
        .pw-strength-fill {
            height: 100%; border-radius: 2px;
            transition: width .3s, background .3s;
            width: 0%;
        }
        .pw-reqs { display: flex; flex-wrap: wrap; gap: 5px; }
        .pw-req {
            font-size: .72rem; padding: 2px 8px;
            border-radius: 20px; font-weight: 600;
            background: var(--brand-light); color: var(--text-light);
            transition: all .2s;
        }
        .pw-req.met { background: #d1fae5; color: #065f46; }

        /* ── Checkbox ── */
        .checkbox-group { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px; font-size: .85rem; color: var(--text-mid); }
        .checkbox-group input[type="checkbox"] {
            margin-top: 2px; flex-shrink: 0;
            width: 16px; height: 16px; accent-color: var(--brand-deep); cursor: pointer;
        }
        .checkbox-group a { color: var(--brand-deep); text-decoration: none; font-weight: 700; cursor: pointer; }
        .checkbox-group a:hover { text-decoration: underline; }

        .signup-btn {
            width: 100%; height: 50px;
            background: linear-gradient(135deg, #3aadcc, #1f8ca0);
            color: #ffffff; border: none;
            border-radius: var(--radius); font-size: 1rem; font-weight: 700;
            cursor: pointer; font-family: inherit;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(42,143,168,0.30);
            letter-spacing: 0.02em;
        }
        .signup-btn:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(42,143,168,0.40); }
        .signup-btn:disabled { opacity: .45; cursor: not-allowed; }

        .already { margin-top: 16px; text-align: center; font-size: .9rem; color: var(--text-mid); }
        .already a { color: var(--brand-deep); text-decoration: none; font-weight: 700; }
        .already a:hover { text-decoration: underline; }

        /* ── Modal ── */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; align-items: center; justify-content: center; }
        .modal.open { display: flex; }
        .modal-content {
            background: white; border-radius: 16px; padding: 32px;
            max-width: 520px; width: 90%; max-height: 80vh; overflow-y: auto;
        }
        .modal-content h3 { font-size: 1.15rem; font-weight: 800; margin-bottom: 14px; color: var(--text-dark); }
        .modal-content p, .modal-content ul { color: var(--text-mid); font-size: .88rem; line-height: 1.7; }
        .modal-content ul { margin: 10px 0 10px 20px; }
        .modal-close { float: right; background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-light); line-height: 1; }

        @media (max-width: 1024px) { .left-panel { display: none; } }
        @media (max-width: 700px)  { .row-2 { grid-template-columns: 1fr; } }
        @media (max-width: 480px)  { .right-panel { padding: 24px 20px; } }
    
        /* ── Dark mode ── */
        [data-theme="dark"] {
            --brand:       #1e1e1e;
            --brand-dark:  #4a8fc4;
            --brand-deep:  #4a8fc4;
            --brand-light: #1a1a1a;
            --brand-pale:  #161616;
            --text-dark:   #f0f0f0;
            --text-mid:    #888888;
            --text-light:  #555555;
            --white:       #111111;
            --error:       #d06060;
            --success:     #50a878;
        }
        [data-theme="dark"] html,
        [data-theme="dark"] body { background: #111111; }

        [data-theme="dark"] .auth-navbar {
            background: #0a0a0a;
            border-bottom-color: rgba(255,255,255,0.07);
        }
        [data-theme="dark"] .auth-navbar .nav-logo { color: #f0f0f0; }
        [data-theme="dark"] .auth-navbar .nav-links a { color: #888888; }
        [data-theme="dark"] .auth-navbar .nav-links a:hover { background: rgba(255,255,255,0.06); color: #f0f0f0; }

        [data-theme="dark"] .brand-panel,
        [data-theme="dark"] .left-panel {
            background: #161616;
            color: #f0f0f0;
        }
        [data-theme="dark"] .brand-panel h2,
        [data-theme="dark"] .hero-title { color: #f0f0f0; }
        [data-theme="dark"] .brand-panel p.subtext,
        [data-theme="dark"] .hero-subtitle { color: #888888; }
        [data-theme="dark"] .feature-list li,
        [data-theme="dark"] .benefit { color: #d0d0d0; }
        [data-theme="dark"] .feature-list li .check,
        [data-theme="dark"] .benefit .check {
            background: rgba(74,143,196,0.15);
            color: #4a8fc4;
        }

        [data-theme="dark"] .form-panel,
        [data-theme="dark"] .right-panel { background: #111111; }

        [data-theme="dark"] .logo,
        [data-theme="dark"] .auth-content h1,
        [data-theme="dark"] .form-title { color: #f0f0f0; }
        [data-theme="dark"] .auth-content p.sign-in-prompt,
        [data-theme="dark"] .form-subtitle { color: #888888; }
        [data-theme="dark"] .input-label { color: #777777; }

        [data-theme="dark"] .input-group input,
        [data-theme="dark"] .input {
            background: #1a1a1a;
            border-color: #2e2e2e;
            color: #f0f0f0;
        }
        [data-theme="dark"] .input-group input:focus,
        [data-theme="dark"] .input:focus {
            border-color: #4a8fc4;
            box-shadow: 0 0 0 3px rgba(74,143,196,0.12);
            background: #1e1e1e;
        }
        [data-theme="dark"] .input-group input::placeholder,
        [data-theme="dark"] .input::placeholder { color: #555555; }
        [data-theme="dark"] input[type="date"].input { color: #f0f0f0; }
        [data-theme="dark"] input[type="date"].input::-webkit-calendar-picker-indicator { filter: invert(0.6); }

        [data-theme="dark"] .login-btn,
        [data-theme="dark"] .signup-btn {
            background: #4a8fc4;
            box-shadow: none;
            color: #ffffff;
        }
        [data-theme="dark"] .login-btn:hover,
        [data-theme="dark"] .signup-btn:hover:not(:disabled) {
            background: #5a9fd4;
            opacity: 1;
            transform: translateY(-1px);
        }
        [data-theme="dark"] .signup-btn:disabled { background: #2a2a2a; color: #555555; opacity: 1; }

        [data-theme="dark"] .forgot-pass { color: #4a8fc4; }
        [data-theme="dark"] .divider { color: #444444; }
        [data-theme="dark"] .divider::before,
        [data-theme="dark"] .divider::after { border-bottom-color: #2a2a2a; }
        [data-theme="dark"] .signup-link,
        [data-theme="dark"] .already { color: #888888; }
        [data-theme="dark"] .signup-link a,
        [data-theme="dark"] .already a { color: #4a8fc4; }

        [data-theme="dark"] .alert-danger {
            background: rgba(208,96,96,0.10);
            border-color: rgba(208,96,96,0.28);
            color: #d08080;
        }
        [data-theme="dark"] .alert-success {
            background: rgba(80,168,120,0.10);
            border-color: rgba(80,168,120,0.28);
            color: #70b890;
        }
        [data-theme="dark"] .pw-req {
            background: #1a1a1a;
            color: #555555;
        }
        [data-theme="dark"] .pw-req.met { background: rgba(80,168,120,0.12); color: #70b890; }
        [data-theme="dark"] .pw-strength-bar { background: #2a2a2a; }
        [data-theme="dark"] .checkbox-group { color: #888888; }
        [data-theme="dark"] .checkbox-group a { color: #4a8fc4; }
        [data-theme="dark"] .modal-content {
            background: #1a1a1a;
            color: #f0f0f0;
        }
        [data-theme="dark"] .modal-content h3 { color: #f0f0f0; }
        [data-theme="dark"] .modal-content p,
        [data-theme="dark"] .modal-content ul { color: #888888; }
        [data-theme="dark"] .modal-close { color: #555555; }
        [data-theme="dark"] .modal-close:hover { color: #888888; }

        [data-theme="dark"] .password-toggle,
        [data-theme="dark"] .toggle-btn { color: #4a8fc4; }

        /* ── Theme toggle button ── */
        .auth-theme-toggle {
            display: flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 50%;
            border: 1.5px solid rgba(0,0,0,0.15);
            background: rgba(255,255,255,0.30);
            color: var(--text-dark); font-size: 15px;
            cursor: pointer; transition: all .18s;
        }
        .auth-theme-toggle:hover { background: rgba(255,255,255,0.50); }
        [data-theme="dark"] .auth-theme-toggle {
            border-color: #2e2e2e;
            background: rgba(255,255,255,0.03);
            color: #888888;
        }
        [data-theme="dark"] .auth-theme-toggle:hover {
            background: rgba(74,143,196,0.10);
            border-color: #4a8fc4;
            color: #4a8fc4;
        }
    </style>

<script>
(function(){
    if(localStorage.getItem('docbook-theme')==='dark'){
        document.documentElement.setAttribute('data-theme','dark');
    }
})();
</script>
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

<div class="signup-box">
    <div class="left-panel">
        <img src="https://i.pinimg.com/1200x/83/fe/b5/83feb5f6ec0f408eae4a1eac07fb2eff.jpg" alt="Doctor">
        <h1 class="hero-title">Book Doctors with Ease</h1>
        <p class="hero-subtitle">Create your free account and start booking appointments today.</p>
        <div class="benefit"><span class="check">✓</span> Find specialists across all categories</div>
        <div class="benefit"><span class="check">✓</span> Book, cancel &amp; reschedule anytime</div>
        <div class="benefit"><span class="check">✓</span> Chat directly with your doctor</div>
        <div class="benefit"><span class="check">✓</span> View your full appointment history</div>
    </div>

    <div class="right-panel">
        <div class="logo">Doc<span class="book">Book</span></div>
        <div class="form-title">Create your account</div>
        <p class="form-subtitle">Sign up to start booking appointments</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/signup" novalidate id="signupForm">

            <div class="row-2">
                <div class="field">
                    <label class="input-label" for="su-first">First Name</label>
                    <input type="text" name="first_name" id="su-first" placeholder="Jane"
                        class="input <?= !empty($errors['first_name']) ? 'is-invalid' : '' ?>"
                        value="<?= old('first_name') ?>" autocomplete="given-name">
                    <?php if (!empty($errors['first_name'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['first_name']) ?></div>
                    <?php endif; ?>
                    <div id="first-fb" class="error" style="display:none;"></div>
                </div>
                <div class="field">
                    <label class="input-label" for="su-last">Last Name</label>
                    <input type="text" name="last_name" id="su-last" placeholder="Doe"
                        class="input <?= !empty($errors['last_name']) ? 'is-invalid' : '' ?>"
                        value="<?= old('last_name') ?>" autocomplete="family-name">
                    <?php if (!empty($errors['last_name'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['last_name']) ?></div>
                    <?php endif; ?>
                    <div id="last-fb" class="error" style="display:none;"></div>
                </div>
            </div>

            <div class="field">
                <label class="input-label" for="su-email">Email Address</label>
                <input type="email" name="email" id="su-email" placeholder="you@example.com"
                    class="input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                    value="<?= old('email') ?>" autocomplete="email"
                    <?= !empty($errors['email']) ? 'data-server-error="1"' : '' ?>>
                <?php if (!empty($errors['email'])): ?>
                    <div class="error" id="email-server-err"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
                <div id="email-fb" class="error" style="display:none;"></div>
            </div>

            <div class="row-2">
                <div class="field">
                    <label class="input-label" for="su-phone">Phone (optional)</label>
                    <input type="tel" name="phone" id="su-phone" placeholder="+977 98XXXXXXXX"
                        class="input" value="<?= old('phone') ?>" autocomplete="tel">
                    <div id="phone-fb" class="error" style="display:none;"></div>
                </div>
                <div class="field">
                    <label class="input-label" for="su-dob">Date of Birth</label>
                    <input type="date" name="dob" id="su-dob"
                        class="input <?= !empty($errors['dob']) ? 'is-invalid' : '' ?>"
                        value="<?= old('dob') ?>"
                        max="<?= date('Y-m-d', strtotime('-1 year')) ?>">
                    <?php if (!empty($errors['dob'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['dob']) ?></div>
                    <?php endif; ?>
                    <div id="dob-fb" class="error" style="display:none;"></div>
                </div>
            </div>

            <div class="field">
                <label class="input-label" for="su-password">Password</label>
                <div class="toggle-container">
                    <input type="password" name="password" id="su-password"
                        placeholder="Min 12 characters"
                        class="input <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                        autocomplete="new-password">
                    <button type="button" class="toggle-btn" data-target="su-password">SHOW</button>
                </div>
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                <?php endif; ?>
                <div class="pw-strength">
                    <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-fill"></div></div>
                    <div class="pw-reqs">
                        <span class="pw-req" id="req-len">12+ chars</span>
                        <span class="pw-req" id="req-upper">Uppercase</span>
                        <span class="pw-req" id="req-lower">Lowercase</span>
                        <span class="pw-req" id="req-num">Number</span>
                        <span class="pw-req" id="req-special">Special (!@#$...)</span>
                    </div>
                </div>
                <div id="pw-fb" class="error" style="display:none;"></div>
            </div>

            <div class="field">
                <label class="input-label" for="su-confirm">Confirm Password</label>
                <div class="toggle-container">
                    <input type="password" name="confirm_password" id="su-confirm"
                        placeholder="Repeat your password"
                        class="input <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                        autocomplete="new-password">
                    <button type="button" class="toggle-btn" data-target="su-confirm">SHOW</button>
                </div>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                <?php endif; ?>
                <div id="confirm-fb" class="error" style="display:none;"></div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="terms" id="terms">
                <label for="terms">
                    I agree to the <a onclick="showModal('terms')">Terms &amp; Conditions</a> and
                    <a onclick="showModal('privacy')">Privacy Policy</a>
                </label>
            </div>
            <div id="terms-fb" class="error" style="display:none;margin-bottom:10px;"></div>

            <button type="submit" id="submitBtn" class="signup-btn" disabled>Create Account</button>
        </form>

        <div class="already">
            Already have an account? <a href="<?= BASE_URL ?>/login">Sign in</a>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <h3 id="modal-title"></h3>
        <div id="modal-body"></div>
    </div>
</div>

<script>
// ── Toggle buttons
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (input.type === 'password') { input.type = 'text';     btn.textContent = 'HIDE'; }
        else                           { input.type = 'password'; btn.textContent = 'SHOW'; }
    });
});

// ── Validation helpers
function setField(input, feedbackEl, msg) {
    if (msg) {
        input.classList.add('is-invalid'); input.classList.remove('is-valid');
        feedbackEl.textContent = msg; feedbackEl.style.display = 'flex';
    } else {
        input.classList.remove('is-invalid'); input.classList.add('is-valid');
        feedbackEl.style.display = 'none';
    }
}

const PW_RULES = {
    len:     { re: /.{12,}/,                           label: 'req-len' },
    upper:   { re: /[A-Z]/,                            label: 'req-upper' },
    lower:   { re: /[a-z]/,                            label: 'req-lower' },
    num:     { re: /[0-9]/,                            label: 'req-num' },
    special: { re: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/, label: 'req-special' }
};

function checkPwRules(val) {
    let met = 0;
    Object.entries(PW_RULES).forEach(([key, rule]) => {
        const ok = rule.re.test(val);
        document.getElementById(rule.label).classList.toggle('met', ok);
        if (ok) met++;
    });
    return met;
}

function updateStrengthBar(met) {
    const fill = document.getElementById('pw-fill');
    const pct  = (met / 5) * 100;
    fill.style.width = pct + '%';
    if (met <= 1) fill.style.background = '#e05252';
    else if (met <= 3) fill.style.background = '#f59e0b';
    else fill.style.background = '#3bba7a';
}

function validateFirstName(v) {
    v = v.trim();
    if (!v) return 'First name is required.';
    if (v.length < 2) return 'Must be at least 2 characters.';
    if (!/^[a-zA-Z\s'-]+$/.test(v)) return 'Only letters, spaces, hyphens allowed.';
    return '';
}
function validateLastName(v) {
    v = v.trim();
    if (!v) return 'Last name is required.';
    if (v.length < 2) return 'Must be at least 2 characters.';
    if (!/^[a-zA-Z\s'-]+$/.test(v)) return 'Only letters, spaces, hyphens allowed.';
    return '';
}
function validateEmail(v) {
    v = v.trim();
    if (!v) return 'Email is required.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Please enter a valid email address.';
    return '';
}
function validatePhone(v) {
    if (!v.trim()) return ''; // optional
    if (!/^[\+]?[\d\s\-\(\)]{7,15}$/.test(v.trim())) return 'Enter a valid phone number.';
    return '';
}
function validateDob(v) {
    if (!v) return 'Date of birth is required.';
    const dob  = new Date(v);
    const now  = new Date();
    const age  = (now - dob) / (1000 * 60 * 60 * 24 * 365.25);
    if (age < 1)   return 'Please enter a valid date of birth.';
    if (age > 120) return 'Please enter a valid date of birth.';
    return '';
}
function validatePassword(v) {
    if (!v) return 'Password is required.';
    const met = Object.values(PW_RULES).filter(r => r.re.test(v)).length;
    if (met < 5) return 'Password does not meet all requirements.';
    return '';
}
function validateConfirm(v, pw) {
    if (!v) return 'Please confirm your password.';
    if (v !== pw) return 'Passwords do not match.';
    return '';
}

// ── Field references
const fFirst   = document.getElementById('su-first');
const fLast    = document.getElementById('su-last');
const fEmail   = document.getElementById('su-email');
const fPhone   = document.getElementById('su-phone');
const fDob     = document.getElementById('su-dob');
const fPw      = document.getElementById('su-password');
const fConfirm = document.getElementById('su-confirm');
const fTerms   = document.getElementById('terms');
const submitBtn = document.getElementById('submitBtn');

// ── State
const state = { first: false, last: false, email: false, dob: false, pw: false, confirm: false, terms: false };
function checkSubmit() { submitBtn.disabled = !Object.values(state).every(Boolean); }

// ── Bindings
fFirst.addEventListener('input', () => {
    const e = validateFirstName(fFirst.value);
    setField(fFirst, document.getElementById('first-fb'), e);
    state.first = !e; checkSubmit();
});
fFirst.addEventListener('blur', () => fFirst.dispatchEvent(new Event('input')));

fLast.addEventListener('input', () => {
    const e = validateLastName(fLast.value);
    setField(fLast, document.getElementById('last-fb'), e);
    state.last = !e; checkSubmit();
});
fLast.addEventListener('blur', () => fLast.dispatchEvent(new Event('input')));

fEmail.addEventListener('input', () => {
    // Once user edits, clear any server-side error and switch to JS validation
    if (fEmail.dataset.serverError) {
        delete fEmail.dataset.serverError;
        const serverErr = document.getElementById('email-server-err');
        if (serverErr) serverErr.style.display = 'none';
    }
    const e = validateEmail(fEmail.value);
    setField(fEmail, document.getElementById('email-fb'), e);
    state.email = !e; checkSubmit();
});
fEmail.addEventListener('blur', () => fEmail.dispatchEvent(new Event('input')));

fPhone.addEventListener('input', () => {
    const e = validatePhone(fPhone.value);
    setField(fPhone, document.getElementById('phone-fb'), e);
});

fDob.addEventListener('change', () => {
    const e = validateDob(fDob.value);
    setField(fDob, document.getElementById('dob-fb'), e);
    state.dob = !e; checkSubmit();
});

fPw.addEventListener('input', () => {
    const met = checkPwRules(fPw.value);
    updateStrengthBar(met);
    const e = validatePassword(fPw.value);
    setField(fPw, document.getElementById('pw-fb'), e);
    state.pw = !e;
    // re-validate confirm if filled
    if (fConfirm.value) {
        const ce = validateConfirm(fConfirm.value, fPw.value);
        setField(fConfirm, document.getElementById('confirm-fb'), ce);
        state.confirm = !ce;
    }
    checkSubmit();
});

fConfirm.addEventListener('input', () => {
    const e = validateConfirm(fConfirm.value, fPw.value);
    setField(fConfirm, document.getElementById('confirm-fb'), e);
    state.confirm = !e; checkSubmit();
});

fTerms.addEventListener('change', () => {
    state.terms = fTerms.checked;
    const termsFb = document.getElementById('terms-fb');
    if (!fTerms.checked) { termsFb.textContent = 'You must accept the terms to continue.'; termsFb.style.display = 'flex'; }
    else { termsFb.style.display = 'none'; }
    checkSubmit();
});

//  Submit validation gate
document.getElementById('signupForm').addEventListener('submit', function(e) {
    // If server already flagged the email, don't re-validate it with JS
    const emailErr = fEmail.dataset.serverError ? '' : validateEmail(fEmail.value);
    const errors = [
        validateFirstName(fFirst.value),
        validateLastName(fLast.value),
        emailErr,
        validateDob(fDob.value),
        validatePassword(fPw.value),
        validateConfirm(fConfirm.value, fPw.value)
    ];
    setField(fFirst,   document.getElementById('first-fb'),   errors[0]);
    setField(fLast,    document.getElementById('last-fb'),    errors[1]);
    if (!fEmail.dataset.serverError) setField(fEmail, document.getElementById('email-fb'), errors[2]);
    setField(fDob,     document.getElementById('dob-fb'),     errors[3]);
    setField(fPw,      document.getElementById('pw-fb'),      errors[4]);
    setField(fConfirm, document.getElementById('confirm-fb'), errors[5]);
    if (!fTerms.checked) {
        const termsFb = document.getElementById('terms-fb');
        termsFb.textContent = 'You must accept the terms to continue.';
        termsFb.style.display = 'flex';
    }
    if (errors.some(Boolean) || !fTerms.checked) e.preventDefault();
});

//  Init (if returning from server validation)
<?php if (!empty($old)): ?>
fFirst.dispatchEvent(new Event('input'));
fLast.dispatchEvent(new Event('input'));
// Only re-validate email via JS if no server error is present on it
if (!fEmail.dataset.serverError) fEmail.dispatchEvent(new Event('input'));
if (fDob.value) fDob.dispatchEvent(new Event('change'));
<?php endif; ?>

//  Modals
function showModal(type) {
    document.getElementById('modal').classList.add('open');
    if (type === 'terms') {
        document.getElementById('modal-title').textContent = 'Terms & Conditions';
        document.getElementById('modal-body').innerHTML = `<p>Welcome to DocBook. By creating an account, you agree to:</p><ul><li>Provide correct and honest information about yourself.</li><li>Keep your password safe and private.</li><li>Not share your account with others.</li><li>We may suspend accounts that misuse the service.</li><li>All appointments are subject to hospital policies.</li></ul><p>Last updated: April 2026</p>`;
    } else {
        document.getElementById('modal-title').textContent = 'Privacy Policy';
        document.getElementById('modal-body').innerHTML = `<p>At DocBook, we take your privacy seriously:</p><ul><li>We collect your name, email, and phone to manage your appointments.</li><li>Your information is kept safe and secure.</li><li>We never sell your data to third parties.</li><li>You can request account deletion at any time.</li></ul><p>Contact: support@docbook.com</p>`;
    }
}
function closeModal() { document.getElementById('modal').classList.remove('open'); }
</script>

<script>
// ── Font Awesome for auth pages
(function(){
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css';
    document.head.appendChild(link);
})();

// ── Theme toggle
(function(){
    var btn  = document.getElementById('authThemeBtn');
    var icon = document.getElementById('authThemeIcon');
    function applyTheme(dark){
        if(dark){ document.documentElement.setAttribute('data-theme','dark'); icon.className='fa fa-sun'; }
        else    { document.documentElement.removeAttribute('data-theme');      icon.className='fa fa-moon'; }
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