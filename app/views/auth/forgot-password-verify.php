<?php
$errors = $errors ?? [];
$message = $message ?? '';
$email = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Verify OTP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
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
        body::before { content: ''; position: fixed; inset: 0; background: radial-gradient(circle at top left, rgba(90,184,208,0.14), transparent 28%), radial-gradient(circle at right bottom, rgba(42,143,168,0.12), transparent 26%); pointer-events: none; }
        .wrap { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card {
            width: min(560px, 100%);
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(42,143,168,0.16);
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(42,143,168,0.10);
            padding: 34px;
        }
        .brand { font-size: 1.2rem; font-weight: 800; text-decoration: none; color: var(--text-dark); }
        .brand span { color: var(--brand-deep); }
        .eyebrow { display: inline-flex; padding: 8px 12px; border-radius: 999px; background: var(--brand-pale); color: var(--brand-deep); font-size: 0.82rem; font-weight: 700; margin: 18px 0 14px; }
        h1 { font-size: clamp(1.9rem, 4vw, 2.8rem); line-height: 1.06; letter-spacing: -0.05em; margin-bottom: 10px; }
        .sub { color: var(--text-mid); line-height: 1.65; margin-bottom: 22px; }
        .alert-danger, .alert-success { padding: 12px 14px; border-radius: 14px; margin-bottom: 14px; font-size: 0.92rem; line-height: 1.5; }
        .alert-danger { background: #fff5f5; border: 1px solid #f1a6a6; color: #b42318; }
        .alert-success { background: #f0fdf7; border: 1px solid #92e0b2; color: #166534; }
        .field { margin-bottom: 16px; }
        label { display: block; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-mid); margin-bottom: 6px; }
        input {
            width: 100%;
            height: 54px;
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
        .secondary {
            margin-top: 12px;
            width: 100%;
            height: 52px;
            border-radius: 14px;
            border: 1.5px solid #cce8f0;
            background: #fff;
            color: var(--brand-deep);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .links { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        a { color: var(--brand-deep); text-decoration: none; font-weight: 700; }
        a:hover { text-decoration: underline; }
        .note { margin-top: 18px; color: var(--text-light); line-height: 1.6; font-size: 0.9rem; }
        .code-hint { margin-top: 8px; color: var(--text-mid); font-size: 0.92rem; }
        .otp-input { letter-spacing: 0.35em; text-align: center; font-size: 1.2rem; font-weight: 800; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <a class="brand" href="<?= BASE_URL ?>/login">Doc<span>Book</span></a>
            <div class="eyebrow">Verify your email</div>
            <h1>Enter the 6-digit OTP</h1>
            <div class="sub">We sent a code to <?= $email !== '' ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : 'your email address' ?>. The OTP expires after 1 hour.</div>

            <?php if (!empty($_GET['sent'])): ?>
                <div class="alert-success">We sent a fresh OTP to your inbox.</div>
            <?php endif; ?>
            <?php if (!empty($errors['general'])): ?>
                <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/forgot-password/verify" novalidate>
                <div class="field">
                    <label for="otp">OTP code</label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="123456"
                        class="otp-input <?= !empty($errors['otp']) ? 'is-invalid' : '' ?>"
                        autocomplete="one-time-code"
                        required>
                    <?php if (!empty($errors['otp'])): ?>
                        <span class="error"><?= htmlspecialchars($errors['otp'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="button">Verify OTP</button>
            </form>

            <form method="POST" action="<?= BASE_URL ?>/forgot-password">
                <input type="hidden" name="email" value="<?= $email ?>">
                <button type="submit" class="secondary">Resend OTP</button>
            </form>

            <div class="links">
                <a href="<?= BASE_URL ?>/forgot-password">Use another email</a>
                <a href="<?= BASE_URL ?>/login">Back to login</a>
            </div>
            <div class="note">Use the latest code only. If you request a new OTP, the previous one is replaced.</div>
        </div>
    </div>
</body>
</html>
