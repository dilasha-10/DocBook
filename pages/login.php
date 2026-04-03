<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • DocBook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0f0f11;
            color: #ffffff;
            font-family: var(--font);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
            background: #1c1c1e;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .logo {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;           
        }
        .logo span.book { color: #4a9eff; }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;           
        }
        .input {
            width: 100%;
            padding: 15px 18px;
            background: #2c2c2e;
            border: 1px solid #3a3a3c;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            margin-bottom: 16px;           
        }
        .toggle-container {
            position: relative;
            margin-bottom: 16px;
        }
        .toggle-btn {
            position: absolute;
            right: 18px;
            top: 35%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #4a9eff;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: #4a9eff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            margin: 8px 0 24px;            
        }
        .forgot {
            text-align: center;
            color: #4a9eff;
            font-size: 15px;
            margin-bottom: 24px;
        }
        .or {
            text-align: center;
            color: #8e8e93;
            margin: 20px 0 24px;
            font-size: 14px;
            position: relative;
        }
        .or::before,
        .or::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 38%;
            height: 1px;
            background: #3a3a3c;
        }
        .or::before { left: 0; }
        .or::after { right: 0; }
        
        .social-btn {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 12px;
            background: #2c2c2e;
            border: 1px solid #3a3a3c;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        .social-btn img {
            width: 24px;
            height: 24px;
        }
        .bottom {
            text-align: center;
            margin-top: 20px;
            color: #8e8e93;
            font-size: 15px;
        }
        .bottom a { color: #4a9eff; }
    </style>
</head>
<body>

    <div class="login-card">
        
        <div class="logo">Doc<span class="book">Book</span></div>

        <div class="title">Log in to your account</div>

        <form id="loginForm">
            <input type="text" id="email" class="input" placeholder="Email address or phone number" required>

            <div class="toggle-container">
                <input type="password" id="password" class="input" placeholder="Password" required>
                <button type="button" id="toggleBtn" class="toggle-btn">Show</button>
            </div>

            <button type="submit" class="login-btn">Log in</button>
        </form>

        <div class="forgot">
            <a href="forgot-password.php">Forgot password?</a>
        </div>

        <div class="or"><span>OR</span></div>

        <button onclick="googleLogin()" class="social-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/2048px-Google_%22G%22_logo.svg.png" alt="Google">
            Log in with Google
        </button>

        <button onclick="facebookLogin()" class="social-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Facebook_f_logo_%282019%29.svg/2048px-Facebook_f_logo_%282019%29.svg.png" alt="Facebook">
            Log in with Facebook
        </button>

        <div class="bottom">
            New to DocBook? <a href="signup.php">Sign up</a>
        </div>
    </div>

<script src="../assets/js/main.js"></script>
<script>
// Password Show / Hide
document.getElementById('toggleBtn').addEventListener('click', function() {
    const pass = document.getElementById('password');
    if (pass.type === 'password') {
        pass.type = 'text';
        this.textContent = 'Hide';
    } else {
        pass.type = 'password';
        this.textContent = 'Show';
    }
});

// Form Submit
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    try {
        const data = await apiCall('../api/auth/login.php', 'POST', {
            email: email,
            password: password,
            role: 'patient'
        });

        localStorage.setItem('token', data.token);
        localStorage.setItem('role', data.role);

        window.location.href = data.role === 'patient' ? 'dashboard-patient.php' : 'dashboard-doctor.php';
    } catch (err) {
        alert(err.error || "Login failed");
    }
});

function googleLogin() {
    window.location.href = '../api/auth/google.php';
}

function facebookLogin() {
    alert("Facebook Login - Coming soon!");
}
</script>
</body>
</html>