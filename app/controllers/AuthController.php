<?php
require_once dirname(__DIR__) . '/models/UserModel.php';
require_once dirname(__DIR__) . '/core/ApiResponse.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::json(false, 405, 'Method not allowed');
        }

        $input = $this->getJsonInput();
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $confirmPassword = (string) ($input['confirmPassword'] ?? '');
        $role = (string) ($input['role'] ?? '');

        if ($name === '') {
            ApiResponse::json(false, 422, 'Name is required');
        }
        if ($email === '') {
            ApiResponse::json(false, 422, 'Email is required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::json(false, 422, 'Invalid email format');
        }
        if ($password === '') {
            ApiResponse::json(false, 422, 'Password is required');
        }
        if (strlen($password) < 6) {
            ApiResponse::json(false, 422, 'Password must be at least 6 characters');
        }
        if ($password !== $confirmPassword) {
            ApiResponse::json(false, 422, 'Passwords do not match');
        }
        if (!in_array($role, ['Patient', 'Doctor'], true)) {
            ApiResponse::json(false, 422, 'Role must be either Patient or Doctor');
        }
        if ($this->userModel->emailExists($email)) {
            ApiResponse::json(false, 422, 'Email already registered. Please log in or use a different email.');
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = $this->userModel->createUser($name, $email, $hashedPassword, $role);
        $token = $this->userModel->createToken((int) $user['id'], $email, $role);

        ApiResponse::json(true, 201, 'User registered successfully', [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::json(false, 405, 'Method not allowed');
        }

        $input = $this->getJsonInput();
        $identifier = trim((string) ($input['identifier'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $role = (string) ($input['role'] ?? '');

        if ($identifier === '') {
            ApiResponse::json(false, 422, 'Email or phone number is required');
        }
        if ($password === '') {
            ApiResponse::json(false, 422, 'Password is required');
        }
        if (!in_array($role, ['Patient', 'Doctor'], true)) {
            ApiResponse::json(false, 422, 'Role must be either Patient or Doctor');
        }

        // Current implementation supports email identifier.
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::json(false, 422, 'Invalid email or phone number format');
        }

        $user = $this->userModel->findByEmail($identifier);
        if ($user === null || !password_verify($password, (string) $user['password'])) {
            ApiResponse::json(false, 401, 'Incorrect email or password');
        }
        if (($user['role'] ?? '') !== $role) {
            ApiResponse::json(false, 401, 'Incorrect role. Please select the correct role and try again.');
        }

        $token = $this->userModel->createToken((int) $user['id'], (string) $user['email'], (string) $user['role']);

        ApiResponse::json(true, 200, 'Login successful', [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    }

    public function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::json(false, 405, 'Method not allowed');
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            ApiResponse::json(false, 401, 'No authorization token provided');
        }

        $tokenData = $this->userModel->verifyToken($matches[1]);
        if ($tokenData === null) {
            ApiResponse::json(false, 401, 'Invalid or expired token');
        }

        ApiResponse::json(true, 200, 'Token is valid', [
            'user' => [
                'userId' => $tokenData['userId'],
                'email' => $tokenData['email'],
                'role' => $tokenData['role'],
            ],
        ]);
    }

    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::json(false, 405, 'Method not allowed');
        }

        $input = $this->getJsonInput();
        $identifier = trim((string) ($input['identifier'] ?? ''));

        if ($identifier === '') {
            ApiResponse::json(false, 422, 'Email or phone number is required');
        }

        ApiResponse::json(true, 200, 'If an account exists with this email/phone, a password reset link has been sent.');
    }
}
