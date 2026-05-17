<?php
$errors  = $errors  ?? [];
$message = $message ?? '';
$email   = $email   ?? ($_SESSION['signup_pending']['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook – Verify Your Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand:      #cce8f0;
            --brand-dark: #5ab8d0;
            --brand-deep: #2a8fa8;
            --brand-pale: #eef8fc;
            --text-dark:  #1a2a3a;
            --text-mid:   #4a6070;
            --text-light: #8aa3b8;
            --white:      #ffffff;
            --error:      #e05252;
            --success:    #3bba7a;
            --radius:     12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: "Nunito", sans-serif; background: var(--brand-pale); }

        .auth-navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: 56px; background: var(--brand);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; border-bottom: 1px solid var(--brand-dark);
        }
        .auth-navbar .nav-logo { font-size: 1.35rem; font-weight: 800; color: var(--text-dark); text-decoration: none; letter-spacing: -.5px; }
        .auth-navbar .nav-logo span { color: var(--brand-deep); }

        .page-wrap {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding-top: 56px;
        }

        .card {
            background: var(--white); border: 1px solid var(--brand);
            border-radius: 18px; padding: 44px 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 12px 40px rgba(42,143,168,.09);
        }

        .icon-wrap {
            width: 64px; height: 64px; background: var(--brand-pale);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 2rem;
        }

        h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); text-align: center; margin-bottom: 6px; }
        .subtitle { color: var(--text-mid); font-size: .93rem; text-align: center; margin-bottom: 26px; line-height: 1.55; }
        .email-badge {
            display: inline-block; background: var(--brand-pale);
            border: 1px solid var(--brand-dark); border-radius: 6px;
            padding: 2px 10px; font-weight: 700; color: var(--brand-deep); font-size: .9rem;
        }

        .alert-success {
            background: #f0fdf4; border: 1.5px solid #86efac; color: #166534;
            border-radius: var(--radius); padding: 12px 16px;
            font-size: .86rem; margin-bottom: 18px; text-align: center;
        }
        .alert-danger {
            background: #fff5f5; border: 1.5px solid #fca5a5; color: #b91c1c;
            border-radius: var(--radius); padding: 12px 16px;
            font-size: .86rem; margin-bottom: 18px;
        }

        .field { margin-bottom: 18px; }
        .input-label {
            display: block; font-size: .75rem; font-weight: 700;
            color: var(--text-mid); margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: .05em;
        }

        /* Big OTP boxes */
        .otp-row { display: flex; gap: 10px; justify-content: center; margin-bottom: 6px; }
        .otp-box {
            width: 52px; height: 58px;
            text-align: center; font-size: 1.6rem; font-weight: 700;
            border: 2px solid var(--brand-dark); border-radius: 10px;
            background: var(--brand-pale); color: var(--text-dark);
            transition: border-color .2s, box-shadow .2s;
        }
        .otp-box:focus { outline: none; border-color: var(--brand-deep); box-shadow: 0 0 0 3px rgba(90,184,208,.18); }
        .otp-box.is-invalid { border-color: var(--error); }

        /* Hidden combined input for form submit */
        #otp-combined { display: none; }

        .error { color: var(--error); font-size: .79rem; margin-top: 5px; text-align: center; }
        .error::before { content: '⚠ '; }

        .btn-primary {
            width: 100%; height: 50px; background: var(--brand-deep); color: #fff;
            border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 700;
            font-family: inherit; cursor: pointer; transition: background .2s;
            margin-top: 4px;
        }
        .btn-primary:hover { background: #1f7a93; }
        .btn-primary:disabled { opacity: .55; cursor: not-allowed; }

        .resend-row { text-align: center; margin-top: 18px; font-size: .88rem; color: var(--text-mid); }
        .resend-row form { display: inline; }
        .resend-btn {
            background: none; border: none; color: var(--brand-deep); font-weight: 700;
            font-size: .88rem; font-family: inherit; cursor: pointer; text-decoration: underline;
            padding: 0;
        }
        .resend-btn:hover { color: #1f7a93; }

        .back-link { display: block; text-align: center; margin-top: 14px; font-size: .87rem; color: var(--text-mid); text-decoration: none; }
        .back-link:hover { color: var(--brand-deep); text-decoration: underline; }

        .timer { font-size: .8rem; color: var(--text-light); margin-top: 4px; text-align: center; }
    </style>
</head>
<body>

<nav class="auth-navbar">
    <a href="<?= BASE_URL ?>/" class="nav-logo">Doc<span>Book</span></a>
</nav>

<div class="page-wrap">
    <div class="card">

        <div class="icon-wrap">📧</div>
        <h1>Check your email</h1>
        <p class="subtitle">
            We sent a 6-digit verification code to<br>
            <span class="email-badge"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
        </p>

        <?php if ($message): ?>
            <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/signup/verify-email" id="otp-form">
            <div class="field">
                <label class="input-label">Verification Code</label>
                <div class="otp-row" id="otp-boxes-row">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp0">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp1">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp2">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp3">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp4">
                    <input class="otp-box <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>" type="text" inputmode="numeric" maxlength="1" autocomplete="off" id="otp5">
                </div>
                <input type="hidden" name="otp" id="otp-combined">
                <?php if (!empty($errors['otp'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['otp'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary" id="verify-btn">Verify Email</button>
        </form>

        <div class="resend-row">
            Didn't get a code?
            <form method="POST" action="<?= BASE_URL ?>/signup/resend-otp">
                <button type="submit" class="resend-btn" id="resend-btn">Resend code</button>
            </form>
            <div class="timer" id="resend-timer"></div>
        </div>

        <a href="<?= BASE_URL ?>/signup" class="back-link">← Back to sign up</a>
    </div>
</div>

<script>
// ── OTP box helpers ──────────────────────────────────────────────────────────
const boxes = Array.from({length: 6}, (_, i) => document.getElementById('otp' + i));
const combined = document.getElementById('otp-combined');

boxes.forEach((box, idx) => {
    box.addEventListener('input', e => {
        const v = box.value.replace(/\D/g, '');
        box.value = v.slice(-1);
        if (v && idx < 5) boxes[idx + 1].focus();
        syncCombined();
    });

    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !box.value && idx > 0) boxes[idx - 1].focus();
    });

    box.addEventListener('paste', e => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        paste.split('').slice(0, 6).forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
        boxes[Math.min(paste.length, 5)].focus();
        syncCombined();
    });
});

function syncCombined() {
    combined.value = boxes.map(b => b.value).join('');
}

document.getElementById('otp-form').addEventListener('submit', e => {
    syncCombined();
    if (combined.value.length < 6) {
        e.preventDefault();
        boxes.forEach(b => b.classList.add('is-invalid'));
    }
});

// Auto-focus first empty box
const firstEmpty = boxes.find(b => !b.value);
if (firstEmpty) firstEmpty.focus();

// ── Resend cooldown ──────────────────────────────────────────────────────────
const resendBtn   = document.getElementById('resend-btn');
const resendTimer = document.getElementById('resend-timer');
let cooldown = 60;

function startCooldown() {
    resendBtn.disabled = true;
    const iv = setInterval(() => {
        cooldown--;
        resendTimer.textContent = `You can resend in ${cooldown}s`;
        if (cooldown <= 0) {
            clearInterval(iv);
            resendBtn.disabled = false;
            resendTimer.textContent = '';
            cooldown = 60;
        }
    }, 1000);
}

<?php if (!empty($_GET['resent'])): ?>
startCooldown();
<?php else: ?>
// Start immediately so user can't spam on first load
startCooldown();
<?php endif; ?>
</script>
</body>
</html>
