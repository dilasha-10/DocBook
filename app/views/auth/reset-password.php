<?php
$errors  = $errors  ?? [];
$message = $message ?? '';
$token   = htmlspecialchars($_GET['token'] ?? ($_POST['token'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook – Set New Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand:      #cce8f0; --brand-dark: #5ab8d0; --brand-deep: #2a8fa8;
            --brand-pale: #eef8fc; --text-dark: #1a2a3a; --text-mid: #4a6070;
            --text-light: #8aa3b8; --white: #ffffff; --error: #e05252; --success: #3bba7a;
            --radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: "Nunito", sans-serif; background: var(--brand-pale); }

        .auth-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 56px; background: var(--brand);
            display: flex; align-items: center; padding: 0 32px;
            border-bottom: 1px solid var(--brand-dark);
        }
        .auth-navbar .nav-logo { font-size: 1.35rem; font-weight: 800; color: var(--text-dark); text-decoration: none; letter-spacing: -.5px; }
        .auth-navbar .nav-logo span { color: var(--brand-deep); }

        .page-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding-top: 56px; }

        .card {
            background: var(--white); border: 1px solid var(--brand);
            border-radius: 18px; padding: 44px 40px;
            width: 100%; max-width: 460px;
            box-shadow: 0 12px 40px rgba(42,143,168,.09);
        }

        .icon-wrap { width: 64px; height: 64px; background: var(--brand-pale); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; }

        h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); text-align: center; margin-bottom: 6px; }
        .subtitle { color: var(--text-mid); font-size: .93rem; text-align: center; margin-bottom: 26px; }

        .alert-danger { background: #fff5f5; border: 1.5px solid #fca5a5; color: #b91c1c; border-radius: var(--radius); padding: 12px 16px; font-size: .86rem; margin-bottom: 18px; }

        .field { margin-bottom: 16px; }
        .input-label { display: block; font-size: .75rem; font-weight: 700; color: var(--text-mid); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em; }
        .toggle-container { position: relative; }
        .input {
            width: 100%; height: 48px; background: var(--brand-pale); border: 1.5px solid var(--brand);
            border-radius: var(--radius); padding: 0 48px 0 14px; font-size: .97rem;
            font-family: inherit; color: var(--text-dark); transition: border-color .2s, box-shadow .2s;
        }
        .input:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,154,200,.12); }
        .input.is-invalid { border-color: var(--error); box-shadow: 0 0 0 3px rgba(224,82,82,.1); }
        .toggle-btn { position: absolute; right: 12px; top: 13px; background: none; border: none; color: var(--brand-deep); font-weight: 700; font-size: .78rem; cursor: pointer; text-transform: uppercase; letter-spacing: .05em; font-family: inherit; }
        .error { color: var(--error); font-size: .78rem; margin-top: 4px; }
        .error::before { content: '⚠ '; }

        /* Strength bar */
        .strength-bar { height: 4px; border-radius: 2px; background: #e2e8f0; margin-top: 7px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 2px; width: 0; transition: width .3s, background .3s; }
        .strength-label { font-size: .73rem; color: var(--text-light); margin-top: 4px; }

        .requirements { font-size: .75rem; color: var(--text-mid); margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px; }
        .req { padding: 2px 8px; border-radius: 4px; background: var(--brand-pale); border: 1px solid var(--brand); }
        .req.met { border-color: var(--success); color: var(--success); background: #f0fdf4; }

        .btn-primary { width: 100%; height: 50px; background: var(--brand-deep); color: #fff; border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: background .2s; margin-top: 4px; }
        .btn-primary:hover { background: #1f7a93; }

        .back-link { display: block; text-align: center; margin-top: 14px; font-size: .87rem; color: var(--text-mid); text-decoration: none; }
        .back-link:hover { color: var(--brand-deep); text-decoration: underline; }
    </style>
</head>
<body>

<nav class="auth-navbar">
    <a href="<?= BASE_URL ?>/" class="nav-logo">Doc<span>Book</span></a>
</nav>

<div class="page-wrap">
    <div class="card">

        <div class="icon-wrap">🔑</div>
        <h1>Set a new password</h1>
        <p class="subtitle">Choose a strong password for your DocBook account.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/reset-password" id="reset-form">
            <input type="hidden" name="token" value="<?= $token ?>">

            <div class="field">
                <label class="input-label" for="rp-password">New Password</label>
                <div class="toggle-container">
                    <input type="password" name="password" id="rp-password" placeholder="At least 12 characters"
                           class="input <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                           autocomplete="new-password">
                    <button type="button" class="toggle-btn" onclick="togglePw('rp-password',this)">Show</button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="str-fill"></div></div>
                <div class="strength-label" id="str-label"></div>
                <div class="requirements" id="reqs">
                    <span class="req" id="req-len">12+ chars</span>
                    <span class="req" id="req-upper">Uppercase</span>
                    <span class="req" id="req-lower">Lowercase</span>
                    <span class="req" id="req-num">Number</span>
                    <span class="req" id="req-sym">Symbol</span>
                </div>
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="field">
                <label class="input-label" for="rp-confirm">Confirm Password</label>
                <div class="toggle-container">
                    <input type="password" name="confirm_password" id="rp-confirm" placeholder="Re-enter password"
                           class="input <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                           autocomplete="new-password">
                    <button type="button" class="toggle-btn" onclick="togglePw('rp-confirm',this)">Show</button>
                </div>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary">Set New Password</button>
        </form>

        <a href="<?= BASE_URL ?>/login" class="back-link">← Back to login</a>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const hidden = inp.type === 'password';
    inp.type = hidden ? 'text' : 'password';
    btn.textContent = hidden ? 'Hide' : 'Show';
}

const pwInput = document.getElementById('rp-password');
const fill    = document.getElementById('str-fill');
const label   = document.getElementById('str-label');
const reqs    = {
    len:   document.getElementById('req-len'),
    upper: document.getElementById('req-upper'),
    lower: document.getElementById('req-lower'),
    num:   document.getElementById('req-num'),
    sym:   document.getElementById('req-sym'),
};

const colors = ['#e05252','#f97316','#eab308','#3bba7a'];
const labels = ['Weak','Fair','Good','Strong'];

pwInput.addEventListener('input', () => {
    const v = pwInput.value;
    const checks = {
        len:   v.length >= 12,
        upper: /[A-Z]/.test(v),
        lower: /[a-z]/.test(v),
        num:   /[0-9]/.test(v),
        sym:   /[!@#$%^&*()\-_=+\[\]{};:'",.< >?\/\\`~|]/.test(v),
    };
    let score = Object.values(checks).filter(Boolean).length;
    Object.entries(checks).forEach(([k, ok]) => reqs[k].classList.toggle('met', ok));
    const pct = score === 0 ? 0 : (score / 5) * 100;
    fill.style.width = pct + '%';
    fill.style.background = colors[Math.min(score - 1, 3)] || '#e05252';
    label.textContent = score > 0 ? labels[Math.min(score - 1, 3)] : '';
});
</script>
</body>
</html>