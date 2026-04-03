<?php
require_once '../../includes/functions.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? '';

if (!$email || !$password || !$role) jsonResponse(['error' => 'All fields required'], 422);

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) jsonResponse(['error' => 'Invalid credentials'], 401);

$user = $result->fetch_assoc();
if (!password_verify($password, $user['password']) || $user['role'] !== $role) {
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

$token = generateToken();
$_SESSION['token'] = $token;
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

jsonResponse([
    'token' => $token,
    'role' => $user['role'],
    'message' => 'Login successful'
]);
?>