<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password • DocBook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width: 480px; margin: 80px auto;">
    <h1 style="text-align:center; margin-bottom:30px;">Forgot Password</h1>
    <p style="text-align:center; color:var(--muted); margin-bottom:40px;">
        Enter your email and we'll send you a reset link.
    </p>

    <form id="forgotForm">
        <input type="email" id="email" class="input" placeholder="Email Address" required><br><br>
        <button type="submit" id="submitBtn" class="btn">Send Reset Link</button>
    </form>

    <p style="text-align:center; margin-top:30px;">
        <a href="login.php" style="color:var(--blue);">Back to Login</a>
    </p>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
        await apiCall('../api/auth/forgot-password.php', 'POST', { email });
        alert('Reset link has been sent to your email (if account exists).');
        window.location.href = 'login.php';
    } catch (err) {
        alert(err.error || 'Something went wrong');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Send Reset Link';
    }
});
</script>
</body>
</html>