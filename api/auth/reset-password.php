<?php
require_once '../../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$password = $data['password'] ?? '';

if (strlen($password) < 8) jsonResponse(['error' => 'Password must be at least 8 characters'], 422);

$result = $conn->query("SELECT email FROM password_resets WHERE token='$token' AND expires_at > NOW()");
if ($result->num_rows === 0) jsonResponse(['error' => 'Invalid or expired token'], 400);

$row = $result->fetch_assoc();
$email = $row['email'];

$hashed = password_hash($password, PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password='$hashed' WHERE email='$email'");
$conn->query("DELETE FROM password_resets WHERE token='$token'");

jsonResponse(['message' => 'Password updated successfully']);
?>