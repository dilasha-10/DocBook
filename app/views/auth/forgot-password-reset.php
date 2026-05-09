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
    <title>DocBook - Reset Password</title>
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
        .links { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        a { color: var(--brand-deep); text-decoration: none; font-weight: 700; }
        a:hover { text-decoration: underline; }
        .note { margin-top: 18px; color: var(--text-light); line-height: 1.6; font-size: 0.9rem; }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 72px; }
        .toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: 0;
            color: var(--brand-deep);
            font-size: 0.8rem;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .hint { margin-top: 8px; color: var(--text-mid); font-size: 0.92rem; line-height: 1.5; }
        @media (max-width: 640px) {
            .card { padding: 26px 20px; border-radius: 22px; }
            .links { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <a class="brand" href="<?= BASE_URL ?>/login">Doc<span>Book</span></a>
            <div class="eyebrow">Create a new password</div>
            <h1>Reset your password now</h1>
            <div class="sub">You verified the OTP for <?= $email !== '' ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : 'your account' ?>. Choose a new password and sign in again.</div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert-danger"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($message !== ''): ?>
                <div class="alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/forgot-password/reset" novalidate id="resetForm">
                <div class="field">
                    <label for="password">New password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter new password"
                            class="<?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle" id="togglePassword">Show</button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <div class="hint">Use at least 12 characters with uppercase, lowercase, number, and symbol.</div>
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm new password"
                            class="<?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                            autocomplete="new-password"
                            required>
                        <button type="button" class="toggle" id="toggleConfirmPassword">Show</button>
                    </div>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <span class="error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="button">Update password</button>
            </form>

            <div class="links">
                <a href="<?= BASE_URL ?>/forgot-password/verify">Back to OTP verification</a>
                <a href="<?= BASE_URL ?>/login">Back to login</a>
            </div>
            <div class="note">Your OTP was valid, so the reset form is ready. Once saved, the code cannot be reused.</div>
        </div>
    </div>

    <script>
    (function() {
        var password = document.getElementById('password');
        var confirmPassword = document.getElementById('confirm_password');
        var togglePassword = document.getElementById('togglePassword');
        var toggleConfirm = document.getElementById('toggleConfirmPassword');

        togglePassword.addEventListener('click', function() {
            password.type = password.type === 'password' ? 'text' : 'password';
            this.textContent = password.type === 'password' ? 'Show' : 'Hide';
        });
        toggleConfirm.addEventListener('click', function() {
            confirmPassword.type = confirmPassword.type === 'password' ? 'text' : 'password';
            this.textContent = confirmPassword.type === 'password' ? 'Show' : 'Hide';
        });
    })();
    </script>
</body>
</html>
