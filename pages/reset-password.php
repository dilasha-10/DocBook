<?php 
require_once '../includes/functions.php'; 
$token = $_GET['token'] ?? '';
if (!$token) {
    die('<h2 style="text-align:center;color:red;">Invalid or expired reset link.</h2>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password • DocBook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:480px; margin:80px auto;">
    <h1 style="text-align:center;">Reset Password</h1>

    <form id="resetForm">
        <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">

        <div style="position:relative;">
            <input type="password" id="password" class="input" placeholder="New Password" required>
            <span id="password-icon" onclick="togglePassword('password')" style="position:absolute;right:16px;top:14px;cursor:pointer;">👁️</span>
        </div><br>

        <div style="position:relative;">
            <input type="password" id="confirm_password" class="input" placeholder="Confirm New Password" required>
            <span id="confirm-icon" onclick="togglePassword('confirm_password')" style="position:absolute;right:16px;top:14px;cursor:pointer;">👁️</span>
        </div><br>

        <button type="submit" class="btn">Reset Password</button>
    </form>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.getElementById('resetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const token = document.getElementById('token').value;
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (password !== confirm) {
        alert("Passwords do not match");
        return;
    }

    try {
        await apiCall('../api/auth/reset-password.php', 'POST', { token, password });
        alert('Password reset successful! Please login.');
        window.location.href = 'login.php';
    } catch (err) {
        alert(err.error || 'Reset failed');
    }
});
</script>
</body>
</html>