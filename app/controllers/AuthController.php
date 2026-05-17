<?php

require_once BASE_PATH . '/app/models/User.php';
require_once BASE_PATH . '/app/models/PasswordResetModel.php';
require_once BASE_PATH . '/app/core/Mailer.php';

<<<<<<< HEAD
// ── Auth helpers ──────────────────────────────────────────────────────────────
=======
// ─────────────────────────────────────────────────────────────────────────────
// Brute-force / rate-limit helpers
//
// Rules (both must pass to allow a login attempt):
//   • Per-IP   : max 20 attempts in a rolling 1-hour window
//   • Per-email: max 5  attempts in a rolling 1-hour window → account lockout
//
// All attempts are stored in `login_attempts` and automatically expire
// (rows older than 1 hour are ignored; a daily cleanup is run lazily).
// ─────────────────────────────────────────────────────────────────────────────

const LOGIN_MAX_PER_IP    = 20;   // hard cap per IP per hour
const LOGIN_MAX_PER_EMAIL = 5;    // account lockout threshold per hour
const LOGIN_WINDOW_SECS   = 3600; // 1 hour rolling window

/**
 * Return the real client IP, respecting common reverse-proxy headers.
 * Falls back to REMOTE_ADDR.
 */
function get_client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'] as $h) {
        if (!empty($_SERVER[$h])) {
            // X-Forwarded-For may be a comma-separated list; take the first.
            return trim(explode(',', $_SERVER[$h])[0]);
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Count recent failed attempts for $column value within the rolling window.
 */
function count_recent_attempts(string $column, string $value): int
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts
        WHERE {$column} = ?
          AND attempted_at >= DATE_SUB(NOW(), INTERVAL " . LOGIN_WINDOW_SECS . " SECOND)
    ");
    $stmt->execute([$value]);
    return (int) $stmt->fetchColumn();
}

/**
 * Record one failed login attempt (both email + IP stored in one row).
 */
function record_failed_attempt(string $email, string $ip): void
{
    $pdo = db_connect();
    $pdo->prepare("
        INSERT INTO login_attempts (email, ip_address, attempted_at)
        VALUES (?, ?, NOW())
    ")->execute([$email, $ip]);
}

/**
 * Seconds until the oldest attempt in the window expires (i.e. unlock time).
 * Returns 0 if no attempts exist.
 */
function seconds_until_unlock(string $column, string $value): int
{
    $pdo  = db_connect();
    $stmt = $pdo->prepare("
        SELECT MIN(attempted_at) FROM login_attempts
        WHERE {$column} = ?
          AND attempted_at >= DATE_SUB(NOW(), INTERVAL " . LOGIN_WINDOW_SECS . " SECOND)
    ");
    $stmt->execute([$value]);
    $oldest = $stmt->fetchColumn();
    if (!$oldest) return 0;
    $unlockAt = strtotime($oldest) + LOGIN_WINDOW_SECS;
    return max(0, $unlockAt - time());
}

/**
 * Lazily prune rows older than the window to keep the table small.
 * Runs ~1% of requests (probabilistic — avoids a cron dependency).
 */
function maybe_prune_attempts(): void
{
    if (mt_rand(1, 100) !== 1) return;
    try {
        db_connect()->exec("
            DELETE FROM login_attempts
            WHERE attempted_at < DATE_SUB(NOW(), INTERVAL " . LOGIN_WINDOW_SECS . " SECOND)
        ");
    } catch (Throwable $e) {
        error_log('[LoginAttempts] Prune failed: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Auth helpers
// ─────────────────────────────────────────────────────────────────────────────
>>>>>>> 5353f4c (Final complete work)

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
    if (!$user) redirect('/login');
    return $user;
}

function require_auth_api(): array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) json_response(['success' => false, 'message' => 'Unauthenticated. Please log in.'], 401);
    $user = find_user_by_id((int) $id);
    if (!$user) json_response(['success' => false, 'message' => 'User not found.'], 401);
    return $user;
}

<<<<<<< HEAD
function redirect_forbidden(): void
{
    redirect('/403');
}

function require_role(string $role): array
{
    $user = require_auth();
    if (($user['role'] ?? '') !== $role) {
        redirect_forbidden();
    }
    return $user;
}

function require_role_api(string $role): array
{
    $user = require_auth_api();
    if (($user['role'] ?? '') !== $role) {
        json_response(['success' => false, 'message' => 'Forbidden.'], 403);
    }
    return $user;
}

function require_patient(): array
{
    return require_role('patient');
}

function require_patient_api(): array
{
    return require_role_api('patient');
}

function require_admin(): array
{
    return require_role('admin');
}

// ── GET /login ────────────────────────────────────────────────────────────────
=======
// ─────────────────────────────────────────────────────────────────────────────
// Login
// ─────────────────────────────────────────────────────────────────────────────
>>>>>>> 5353f4c (Final complete work)

function login_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
<<<<<<< HEAD
    if (!empty($_SESSION['user_id'])) {
        $user = auth_user();
        if (($user['role'] ?? '') === 'admin') {
            redirect('/admin');
        }
        if (($user['role'] ?? '') === 'doctor') {
            redirect('/doctor/dashboard');
        }
        redirect('/dashboard');
    }
=======
    if (!empty($_SESSION['user_id'])) redirect('/dashboard');
>>>>>>> 5353f4c (Final complete work)
    include BASE_PATH . '/app/views/auth/login.php';
    exit;
}

<<<<<<< HEAD
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

=======
>>>>>>> 5353f4c (Final complete work)
function login_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    maybe_prune_attempts();

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $ip       = get_client_ip();
    $errors   = [];

    // ── Rate-limit: IP (20 attempts / hour across all accounts) ──────────────
    if (count_recent_attempts('ip_address', $ip) >= LOGIN_MAX_PER_IP) {
        $wait = (int) ceil(seconds_until_unlock('ip_address', $ip) / 60);
        $errors['general'] = "Too many login attempts from your network. Please try again in {$wait} minute(s).";
        include BASE_PATH . '/app/views/auth/login.php';
        exit;
    }

    // ── Rate-limit: email (5 attempts / hour — account lockout) ──────────────
    if ($email !== '' && count_recent_attempts('email', $email) >= LOGIN_MAX_PER_EMAIL) {
        $wait = (int) ceil(seconds_until_unlock('email', $email) / 60);
        $errors['general'] = "Too many failed login attempts. Please try again in {$wait} minute(s).";
        include BASE_PATH . '/app/views/auth/login.php';
        exit;
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') $errors['password'] = 'Password is required.';

    if (empty($errors)) {
        $user = find_user_by_email($email);
        if (!$user || !password_verify($password, (string) $user['password'])) {
<<<<<<< HEAD
=======
            // Record the failed attempt for rate-limiting
            record_failed_attempt($email, $ip);
            audit_log(
                $user ? (int) $user['id'] : null,
                $user ? $user['name']     : null,
                $user ? $user['role']     : null,
                'LOGIN_FAILED', 'auth',
                'Failed login attempt for email: ' . $email . ' from IP: ' . $ip
            );
>>>>>>> 5353f4c (Final complete work)
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

<<<<<<< HEAD
    if (($user['role'] ?? '') === 'admin') {
        redirect('/admin');
    }
    if (($user['role'] ?? '') === 'doctor') {
        redirect('/doctor/dashboard');
    }
    redirect('/dashboard');
}

// ── GET /signup ───────────────────────────────────────────────────────────────
=======
    audit_log((int) $user['id'], $user['name'], $user['role'], 'LOGIN', 'auth', 'User logged in.');

    $dest = match ($user['role']) {
        'admin'     => '/admin/dashboard',
        'doctor'    => '/doctor/dashboard',
        'lab_admin' => '/lab-admin/dashboard',
        default     => '/dashboard',
    };
    redirect($dest);
}

// ─────────────────────────────────────────────────────────────────────────────
// Signup (Step 1 of 2) — collect details, send OTP
// ─────────────────────────────────────────────────────────────────────────────

require_once BASE_PATH . '/app/models/SignupOtpModel.php';
require_once BASE_PATH . '/app/core/Mailer.php';
>>>>>>> 5353f4c (Final complete work)

function signup_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) redirect('/dashboard');
    include BASE_PATH . '/app/views/auth/signup.php';
    exit;
}

<<<<<<< HEAD
// ── POST /signup ──────────────────────────────────────────────────────────────

=======
>>>>>>> 5353f4c (Final complete work)
function signup_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $firstName       = trim($_POST['first_name']       ?? '');
    $lastName        = trim($_POST['last_name']        ?? '');
    $name            = trim($firstName . ' ' . $lastName);
    $email           = trim($_POST['email']            ?? '');
    $password        =      $_POST['password']         ?? '';
    $confirmPassword =      $_POST['confirm_password'] ?? '';
    $phone           = trim($_POST['phone']            ?? '');
    $dob             = trim($_POST['dob']              ?? '');
    $role            = 'patient';
    $errors          = [];

    // ── Field validation ──────────────────────────────────────────────────────

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

    // Phone is now mandatory
    if ($phone === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[\+]?[\d\s\-\(\)]{7,15}$/', $phone)) {
        $errors['phone'] = 'Enter a valid phone number.';
    }

    if ($dob === '') {
        $errors['dob'] = 'Date of birth is required.';
    } else {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate) {
            $errors['dob'] = 'Please enter a valid date of birth.';
        } else {
            $age = (new DateTime())->diff($dobDate)->y;
            if ($age < 1 || $age > 120) $errors['dob'] = 'Please enter a valid date of birth.';
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
    } elseif (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.< >?\/\\\\`~|]/', $password)) {
        $errors['password'] = 'Password must include at least one special character.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $old = compact('firstName', 'lastName', 'email', 'phone', 'dob');
        $old = ['first_name' => $firstName, 'last_name' => $lastName,
                'email' => $email, 'phone' => $phone, 'dob' => $dob];
        include BASE_PATH . '/app/views/auth/signup.php';
        exit;
    }

    // ── Send OTP ──────────────────────────────────────────────────────────────

    ensure_signup_otps_table();

    $expiresAt = (new DateTimeImmutable('+15 minutes'))->format('Y-m-d H:i:s');
    $otp       = create_signup_otp($email, $expiresAt);

    $subject  = 'DocBook — Verify your email';
    $htmlBody = render_signup_otp_email($firstName, $otp, $expiresAt);
    $textBody = sprintf(
        "Hello %s,\n\nYour DocBook email verification code is: %s\nIt expires in 15 minutes.\n\nIf you did not sign up, please ignore this email.\n",
        $firstName, $otp
    );

    if (!send_smtp_mail($email, $subject, $htmlBody, $textBody)) {
        $errors['general'] = 'We could not send the verification email. Please try again.';
        $old = ['first_name' => $firstName, 'last_name' => $lastName,
                'email' => $email, 'phone' => $phone, 'dob' => $dob];
        include BASE_PATH . '/app/views/auth/signup.php';
        exit;
    }

    // ── Store pending registration in session (not DB) ────────────────────────
    session_regenerate_id(true);
    $_SESSION['signup_pending'] = [
        'name'            => $name,
        'email'           => $email,
        'password_hash'   => password_hash($password, PASSWORD_BCRYPT),
        'phone'           => $phone,
        'dob'             => $dob,
        'role'            => $role,
    ];

    redirect('/signup/verify-email');
}

// ─────────────────────────────────────────────────────────────────────────────
// Signup (Step 2 of 2) — OTP verification
// ─────────────────────────────────────────────────────────────────────────────

function signup_verify_email_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['signup_pending'])) redirect('/signup');
    $errors  = [];
    $message = !empty($_GET['resent']) ? 'A new verification code has been sent to your email.' : '';
    $email   = $_SESSION['signup_pending']['email'] ?? '';
    include BASE_PATH . '/app/views/auth/signup-verify.php';
    exit;
}

function signup_verify_email_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['signup_pending'])) redirect('/signup');

    $pending = $_SESSION['signup_pending'];
    $email   = $pending['email'];
    $otp     = trim($_POST['otp'] ?? '');
    $errors  = [];
    $message = '';

    ensure_signup_otps_table();
    $otpRecord = find_active_signup_otp($email);

    if (!$otpRecord) {
        $errors['otp'] = 'Your code has expired. Please go back and sign up again.';
        include BASE_PATH . '/app/views/auth/signup-verify.php';
        exit;
    }

    if (new DateTimeImmutable($otpRecord['expires_at']) < new DateTimeImmutable()) {
        $errors['otp'] = 'Your code has expired. Please go back and sign up again.';
        include BASE_PATH . '/app/views/auth/signup-verify.php';
        exit;
    }

    if ($otp === '') {
        $errors['otp'] = 'Verification code is required.';
    } elseif (!preg_match('/^\d{6}$/', $otp)) {
        $errors['otp'] = 'Enter the 6-digit code from your email.';
    } elseif (!password_verify($otp, $otpRecord['otp_hash'])) {
        $attempts = bump_signup_otp_attempts((int) $otpRecord['id']);
        if ($attempts >= 5) {
            delete_signup_otp($email);
            unset($_SESSION['signup_pending']);
            redirect('/signup?otp_failed=1');
        }
        $errors['otp'] = 'Incorrect code. Please try again.';
    }

    if (!empty($errors)) {
        include BASE_PATH . '/app/views/auth/signup-verify.php';
        exit;
    }

    // ── OTP correct — create the account ─────────────────────────────────────
    mark_signup_otp_verified((int) $otpRecord['id']);
    delete_signup_otp($email);

    create_user(
        $pending['name'],
        $pending['email'],
        $pending['password_hash'],
        $pending['role'],
        $pending['phone']
    );

    unset($_SESSION['signup_pending']);
    redirect('/login?registered=1');
}

<<<<<<< HEAD
// ── GET /logout ───────────────────────────────────────────────────────────────
=======
function signup_resend_otp_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['signup_pending'])) redirect('/signup');

    $pending    = $_SESSION['signup_pending'];
    $email      = $pending['email'];
    $firstName  = explode(' ', $pending['name'])[0];

    ensure_signup_otps_table();

    $expiresAt = (new DateTimeImmutable('+15 minutes'))->format('Y-m-d H:i:s');
    $otp       = create_signup_otp($email, $expiresAt);

    $subject  = 'DocBook — Verify your email';
    $htmlBody = render_signup_otp_email($firstName, $otp, $expiresAt);
    $textBody = sprintf(
        "Hello %s,\n\nYour new DocBook verification code is: %s\nIt expires in 15 minutes.\n",
        $firstName, $otp
    );

    send_smtp_mail($email, $subject, $htmlBody, $textBody);
    redirect('/signup/verify-email?resent=1');
}

// ─────────────────────────────────────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────────────────────────────────────
>>>>>>> 5353f4c (Final complete work)

function logout(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
<<<<<<< HEAD
=======

    $uid   = $_SESSION['user_id']   ?? null;
    $uname = $_SESSION['user_name'] ?? null;
    $urole = $_SESSION['user_role'] ?? null;
    if ($uid) audit_log((int) $uid, $uname, $urole, 'LOGOUT', 'auth', 'User logged out.');

>>>>>>> 5353f4c (Final complete work)
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect('/login');
}

// ─────────────────────────────────────────────────────────────────────────────
// Password Reset — Magic Link
// ─────────────────────────────────────────────────────────────────────────────

require_once BASE_PATH . '/app/models/PasswordResetModel.php';

// Step 1: Request form
function forgot_password_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $errors  = [];
    $message = '';
    include BASE_PATH . '/app/views/auth/forgot-password.php';
    exit;
}

// Step 1: Submit email → send magic link
function forgot_password_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    ensure_password_reset_table();

    $email  = trim($_POST['email'] ?? '');
    $errors = [];

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!empty($errors)) {
        $message = '';
        include BASE_PATH . '/app/views/auth/forgot-password.php';
        exit;
    }

    // Always show success message — never reveal whether the account exists.
    $user      = find_user_by_email($email);
    $message   = 'If an account with that email exists, a password reset link has been sent.';

    if ($user) {
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');
        $rawToken  = create_password_reset_token((int) $user['id'], $email, $expiresAt);

        $resetUrl  = rtrim(BASE_URL, '/') . '/reset-password?token=' . urlencode($rawToken);
        $subject   = 'DocBook — Password reset link';
        $htmlBody  = render_magic_link_reset_email($user['name'] ?? 'User', $resetUrl, $expiresAt);
        $textBody  = sprintf(
            "Hello %s,\n\nClick this link to reset your DocBook password:\n%s\n\nThe link expires in 1 hour.\n\nIf you did not request this, please ignore this email.\n",
            $user['name'] ?? 'User', $resetUrl
        );

        send_smtp_mail($email, $subject, $htmlBody, $textBody);
    }

    $errors = [];
    include BASE_PATH . '/app/views/auth/forgot-password.php';
    exit;
}

// Step 2: User clicks link — show new-password form
function reset_password_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    ensure_password_reset_table();

    $token = trim($_GET['token'] ?? '');
    if ($token === '') {
        redirect('/forgot-password?invalid=1');
    }

    $resetRequest = find_valid_reset_by_token($token);
    if (!$resetRequest) {
        redirect('/forgot-password?invalid=1');
    }

    $errors  = [];
    $message = '';
    include BASE_PATH . '/app/views/auth/reset-password.php';
    exit;
}

// Step 2: Submit new password
function reset_password_post(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    ensure_password_reset_table();

    $token           = trim($_POST['token']            ?? '');
    $newPassword     =      $_POST['password']         ?? '';
    $confirmPassword =      $_POST['confirm_password'] ?? '';
    $errors          = [];

    if ($token === '') redirect('/forgot-password?invalid=1');

    // Validate password strength first.
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
    } elseif (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.< >?\/\\\\`~|]/', $newPassword)) {
        $errors['password'] = 'Password must include at least one special character.';
    }

    if ($confirmPassword === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        // Re-validate token is still alive before showing the form again.
        $resetRequest = find_valid_reset_by_token($token);
        if (!$resetRequest) redirect('/forgot-password?invalid=1');
        $message = '';
        include BASE_PATH . '/app/views/auth/reset-password.php';
        exit;
    }

    // Atomically consume token + update password (race-condition safe).
    $success = consume_token_and_reset_password($token, password_hash($newPassword, PASSWORD_BCRYPT));
    if (!$success) {
        redirect('/forgot-password?invalid=1');
    }

    redirect('/login?reset=1');
}
