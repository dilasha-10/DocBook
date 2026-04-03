<?php
// In real project you would integrate Google OAuth library.
// For this sprint we simulate success and redirect to dashboard as patient.
require_once '../../includes/functions.php';

$fakeEmail = "user" . rand(100,999) . "@gmail.com";
$role = 'patient';

// Check or create user
$result = $conn->query("SELECT * FROM users WHERE email='$fakeEmail'");
if ($result->num_rows === 0) {
    $name = "Google User";
    $hashed = password_hash("google123", PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$fakeEmail', '$hashed', '$role')");
}

$token = generateToken();
jsonResponse(['token' => $token, 'role' => $role]); // In real flow this would redirect
?>