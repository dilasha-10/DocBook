<?php
$errors = $errors ?? [];
$message = $message ?? '';
$email = htmlspecialchars($email ?? ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Forgot Password</title>
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
