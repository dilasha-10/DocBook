<?php
require_once '../../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirm = $data['confirm_password'] ?? '';
$role = $data['role'] ?? '';

if (!$name || !$email || !$password || $password !== $confirm || !$role) {
    jsonResponse(['error' => 'Validation failed'], 422);
}

if (strlen($password) < 8) jsonResponse(['error' => 'Password must be 8+ characters'], 422);

// Check duplicate email
$check = $conn->query("SELECT id FROM users WHERE email='$email'");
if ($check->num_rows > 0) jsonResponse(['error' => 'Email already exists'], 422);

$hashed = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $hashed, $role);

if ($stmt->execute()) {
    $token = generateToken();
    jsonResponse(['token' => $token, 'role' => $role, 'message' => 'Account created']);
} else {
    jsonResponse(['error' => 'Registration failed'], 500);
}
?>