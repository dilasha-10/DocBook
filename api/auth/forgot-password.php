<?php
require_once '../../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (!$email) jsonResponse(['error' => 'Email required'], 422);

// In real app, check if email exists. For demo we always say success (security best practice)
$token = generateToken(64);
$expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$conn->query("DELETE FROM password_resets WHERE email='$email'");
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires);
$stmt->execute();

// In production: send real email with link like reset-password.php?token=xxx
// For now we just return success
jsonResponse(['message' => 'If an account exists, a reset link has been sent.']);
?>