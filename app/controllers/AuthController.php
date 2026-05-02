<?php

require_once BASE_PATH . '/app/models/User.php';

// Auth helpers

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

// GET /login

function login_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) {
        redirect('/dashboard');
    }
    include BASE_PATH . '/app/views/auth/login.php';
    exit;
}

// POST /login

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

    redirect('/dashboard');
}

// GET /signup

function signup_get(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) {
        redirect('/dashboard');
    }
    include BASE_PATH . '/app/views/auth/signup.php';
    exit;
}

// POST /signup

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

// GET /logout

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