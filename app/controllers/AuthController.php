<?php

require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/PasswordResetModel.php';
require_once BASE_PATH . '/app/core/Mailer.php';

// ── Auth helpers ──────────────────────────────────────────────────────────────

function auth_user(): ?array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) return null;
    return find_user_by_id((int) $id);
}

function require_auth(): array
{
    $user = auth_user();
    if (!$user) {
        redirect('/login');
    }
    return $user;
}

function require_auth_api(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        json_response(['success' => false, 'message' => 'Unauthenticated. Please log in.'], 401);
    }
    $user = find_user_by_id((int) $id);
    if (!$user) {
        json_response(['success' => false, 'message' => 'User not found.'], 401);
    }
    return $user;
}

function require_admin(): array
{
    $user = require_auth();
    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo '<h1>Forbidden</h1>';
        exit;
    }
    return $user;
}

// ── GET /login ────────────────────────────────────────────────────────────────

function login_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) {
        redirect('/dashboard');
    }
    include BASE_PATH . '/app/views/auth/login.php';
    exit;
}

function forgot_password_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $errors = [];
    $message = '';
    $email = $_SESSION['password_reset_email'] ?? '';
    include BASE_PATH . '/app/views/auth/forgot-password.php';
    exit;
}

function forgot_password_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    ensure_password_reset_table();

    $email = trim($_POST['email'] ?? ($_SESSION['password_reset_email'] ?? ''));
    $errors = [];
    $message = '';

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    $user = empty($errors) ? find_user_by_email($email) : null;
    if (!$user && empty($errors)) {
        $errors['email'] = 'No account found for that email address.';
    }

    if (!empty($errors)) {
        include BASE_PATH . '/app/views/auth/forgot-password.php';
        exit;
    }

    $otp = (string) random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

    upsert_password_reset_request((int) $user['id'], $email, $otpHash, $expiresAt);

    $subject = 'DocBook password reset OTP';
    $htmlBody = render_password_reset_email($user['name'] ?? 'User', $otp, $expiresAt);
    $textBody = sprintf(
        "Hello %s,\n\nYour DocBook password reset OTP is %s.\nIt expires in 1 hour.\n\nIf you did not request this, ignore this email.\n",
        $user['name'] ?? 'User',
        $otp
    );

    if (!send_smtp_mail($email, $subject, $htmlBody, $textBody, MAIL_FROM_EMAIL, MAIL_FROM_NAME)) {
        $errors['general'] = 'We could not send the OTP email right now. Please try again in a moment.';
        include BASE_PATH . '/app/views/auth/forgot-password.php';
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['password_reset_user_id'] = (int) $user['id'];
    $_SESSION['password_reset_email'] = $email;
    unset($_SESSION['password_reset_verified_user_id']);

    redirect('/forgot-password/verify?sent=1');
}

function forgot_password_verify_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = (int) ($_SESSION['password_reset_user_id'] ?? 0);
    if ($userId <= 0) {
        redirect('/forgot-password');
    }

    $resetRequest = find_active_password_reset_by_user_id($userId);
    if (!$resetRequest) {
        unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password');
    }

    if (new DateTimeImmutable($resetRequest['expires_at']) < new DateTimeImmutable()) {
        clear_password_reset_request((int) $resetRequest['id']);
        unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password?expired=1');
    }

    $errors = [];
    $message = !empty($_GET['sent']) ? 'We sent a 6-digit OTP to your email address.' : '';
    $email = $_SESSION['password_reset_email'] ?? ($resetRequest['email'] ?? '');
    include BASE_PATH . '/app/views/auth/forgot-password-verify.php';
    exit;
}

function forgot_password_verify_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = (int) ($_SESSION['password_reset_user_id'] ?? 0);
    if ($userId <= 0) {
        redirect('/forgot-password');
    }

    $errors = [];
    $message = '';
    $otp = trim($_POST['otp'] ?? '');

    $resetRequest = find_active_password_reset_by_user_id($userId);
    if (!$resetRequest) {
        unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email']);
        $errors['general'] = 'Your OTP request expired. Please request a new code.';
        include BASE_PATH . '/app/views/auth/forgot-password-verify.php';
        exit;
    }

    $expiresAt = new DateTimeImmutable($resetRequest['expires_at']);
    if ($expiresAt < new DateTimeImmutable()) {
        clear_password_reset_request((int) $resetRequest['id']);
        unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password?expired=1');
    }

    if ($otp === '') {
        $errors['otp'] = 'OTP is required.';
    } elseif (!preg_match('/^\d{6}$/', $otp)) {
        $errors['otp'] = 'OTP must be a 6-digit code.';
    } elseif (!password_verify($otp, $resetRequest['otp_hash'])) {
        $attempts = increment_password_reset_attempts((int) $resetRequest['id']);
        if ($attempts >= 5) {
            clear_password_reset_request((int) $resetRequest['id']);
            unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email']);
            $errors['general'] = 'Too many incorrect attempts. Please request a new OTP.';
        } else {
            $errors['otp'] = 'The OTP you entered is incorrect.';
        }
    }

    if (!empty($errors)) {
        $email = $_SESSION['password_reset_email'] ?? ($resetRequest['email'] ?? '');
        include BASE_PATH . '/app/views/auth/forgot-password-verify.php';
        exit;
    }

    mark_password_reset_verified((int) $resetRequest['id']);
    session_regenerate_id(true);
    $_SESSION['password_reset_verified_user_id'] = $userId;
    unset($_SESSION['password_reset_user_id']);

    redirect('/forgot-password/reset');
}

function forgot_password_reset_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = (int) ($_SESSION['password_reset_verified_user_id'] ?? 0);
    if ($userId <= 0) {
        redirect('/forgot-password');
    }

    $resetRequest = find_verified_password_reset_by_user_id($userId);
    if (!$resetRequest) {
        unset($_SESSION['password_reset_verified_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password');
    }

    if (new DateTimeImmutable($resetRequest['expires_at']) < new DateTimeImmutable()) {
        clear_password_reset_request((int) $resetRequest['id']);
        unset($_SESSION['password_reset_verified_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password?expired=1');
    }

    $errors = [];
    $message = '';
    $email = $_SESSION['password_reset_email'] ?? ($resetRequest['email'] ?? '');
    include BASE_PATH . '/app/views/auth/forgot-password-reset.php';
    exit;
}

function forgot_password_reset_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = (int) ($_SESSION['password_reset_verified_user_id'] ?? 0);
    if ($userId <= 0) {
        redirect('/forgot-password');
    }

    $errors = [];
    $message = '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $resetRequest = find_verified_password_reset_by_user_id($userId);
    if (!$resetRequest) {
        unset($_SESSION['password_reset_verified_user_id'], $_SESSION['password_reset_email']);
        redirect('/forgot-password');
    }

    if ($newPassword === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($newPassword) < 12) {
        $errors['password'] = 'Password must be at least 12 characters.';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $errors['password'] = 'Password must include at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $errors['password'] = 'Password must include at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $errors['password'] = 'Password must include at least one number.';
    } elseif (!preg_match('/[!@#$%^&*()\\-_=+\\[\\]{};:\'\",.<>?\/\\\\`~|]/', $newPassword)) {
        $errors['password'] = 'Password must include at least one special character.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $email = $_SESSION['password_reset_email'] ?? ($resetRequest['email'] ?? '');
        include BASE_PATH . '/app/views/auth/forgot-password-reset.php';
        exit;
    }

    update_user_password($userId, password_hash($newPassword, PASSWORD_BCRYPT));
    clear_password_reset_request((int) $resetRequest['id']);

    unset(
        $_SESSION['password_reset_user_id'],
        $_SESSION['password_reset_verified_user_id'],
        $_SESSION['password_reset_email']
    );

    redirect('/login?reset=1');
}

// ── POST /login ───────────────────────────────────────────────────────────────

function login_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    $errors = [];

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $user = find_user_by_email($email);

        if (!$user || !password_verify($password, (string) $user['password'])) {
            $errors['general'] = 'Incorrect email or password.';
        }
    }

    if (!empty($errors)) {
        include BASE_PATH . '/app/views/auth/login.php';
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    if (($user['role'] ?? '') === 'admin') {
        redirect('/admin');
    }
    redirect('/dashboard');
}

// ── GET /signup ───────────────────────────────────────────────────────────────

function signup_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) {
        redirect('/dashboard');
    }
    include BASE_PATH . '/app/views/auth/signup.php';
    exit;
}

// ── POST /signup ──────────────────────────────────────────────────────────────

function signup_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $firstName       = trim($_POST['first_name']       ?? '');
    $lastName        = trim($_POST['last_name']        ?? '');
    $name            = trim($firstName . ' ' . $lastName);
    $email           = trim($_POST['email']            ?? '');
    $password        =      $_POST['password']         ?? '';
    $confirmPassword =      $_POST['confirm_password'] ?? '';;
    $phone           = trim($_POST['phone']            ?? '');
    $dob             = trim($_POST['dob']              ?? '');
    $role            = 'patient';

    $errors = [];

    if ($firstName === '') {
        $errors['first_name'] = 'First name is required.';
    } elseif (strlen($firstName) < 2) {
        $errors['first_name'] = 'First name must be at least 2 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/', $firstName)) {
        $errors['first_name'] = 'First name contains invalid characters.';
    }

    if ($lastName === '') {
        $errors['last_name'] = 'Last name is required.';
    } elseif (strlen($lastName) < 2) {
        $errors['last_name'] = 'Last name must be at least 2 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/', $lastName)) {
        $errors['last_name'] = 'Last name contains invalid characters.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (email_exists($email)) {
        $errors['email'] = 'This email is already registered. Please log in or use a different email.';
    }

    if ($dob === '') {
        $errors['dob'] = 'Date of birth is required.';
    } else {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate) {
            $errors['dob'] = 'Please enter a valid date of birth.';
        } else {
            $age = (new DateTime())->diff($dobDate)->y;
            if ($age < 1 || $age > 120) {
                $errors['dob'] = 'Please enter a valid date of birth.';
            }
        }
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 12) {
        $errors['password'] = 'Password must be at least 12 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must include at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must include at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must include at least one number.';
    } elseif (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.<>?\/\\\\`~|]/', $password)) {
        $errors['password'] = 'Password must include at least one special character.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $old = ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'phone' => $phone, 'dob' => $dob];
        include BASE_PATH . '/app/views/auth/signup.php';
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    create_user($name, $email, $hashedPassword, $role, $phone);

    redirect('/login?registered=1');
}

// ── GET /logout ───────────────────────────────────────────────────────────────

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect('/login');
}