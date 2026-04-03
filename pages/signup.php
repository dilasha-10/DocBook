<?php require_once '../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up • DocBook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .signup-container {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border-radius: 20px;
            padding: 40px 28px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        .title {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 36px;
        }
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input {
            width: 100%;
            padding: 15px 18px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-size: 16px;
        }
        .input:focus {
            border-color: var(--blue);
            outline: none;
        }
        .toggle-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--blue);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            padding: 6px 10px;
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 24px 0;
            font-size: 14px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            accent-color: var(--blue);
        }
        .checkbox-group a {
            color: var(--blue);
            text-decoration: none;
            cursor: pointer;
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 16px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4a9eff, #2b7dd1);
            color: white;
        }
        .links {
            text-align: center;
            font-size: 14px;
            color: var(--muted);
        }
        .links a { color: var(--blue); text-decoration: none; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: var(--surface);
            width: 90%;
            max-width: 460px;
            border-radius: 16px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 28px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }
        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--muted);
            cursor: pointer;
        }
        .modal-body {
            line-height: 1.7;
            color: var(--muted);
            font-size: 15px;
        }
        .error {
            color: var(--red);
            font-size: 13px;
            margin-top: 4px;
            display: none;
        }
    </style>
</head>
<body>

    <div class="signup-container">
        <div class="title">Create Account</div>

        <form id="signupForm">
            <div class="input-group">
                <input type="text" id="name" class="input" placeholder="Full Name" required>
                <div id="name-error" class="error"></div>
            </div>

            <div class="input-group">
                <input type="email" id="email" class="input" placeholder="Email Address" required>
                <div id="email-error" class="error"></div>
            </div>

            <div class="input-group">
                <input type="password" id="password" class="input" placeholder="Password (min 8 characters)" required>
                <button type="button" id="togglePassword" class="toggle-btn">SHOW</button>
                <div id="password-error" class="error"></div>
            </div>

            <div class="input-group">
                <input type="password" id="confirm_password" class="input" placeholder="Confirm Password" required>
                <button type="button" id="toggleConfirm" class="toggle-btn">SHOW</button>
                <div id="confirm-error" class="error"></div>
            </div>

            <!-- Terms Checkbox -->
            <div class="checkbox-group">
                <input type="checkbox" id="terms">
                <label for="terms">
                    I agree to the 
                    <a onclick="showModal('terms')">Terms & Conditions</a> 
                    and 
                    <a onclick="showModal('privacy')">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Sign Up</button>
        </form>

        <div class="links">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div id="modal-title" class="modal-title"></div>
                <button class="close-btn" onclick="closeModal()">✕</button>
            </div>
            <div id="modal-body" class="modal-body"></div>
        </div>
    </div>

<script src="../assets/js/main.js"></script>
<script>
// Password toggles
function setupToggle(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    btn.addEventListener('click', () => {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = 'HIDE';
        } else {
            input.type = 'password';
            btn.textContent = 'SHOW';
        }
    });
}
setupToggle('password', 'togglePassword');
setupToggle('confirm_password', 'toggleConfirm');

// Modal Functions
function showModal(type) {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    if (type === 'terms') {
        title.textContent = "Terms & Conditions";
        body.innerHTML = `
            <p>Welcome to DocBook Hospital Services. By creating an account, you agree to the following:</p>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>You must provide accurate and complete information during registration.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                <li>DocBook reserves the right to suspend or terminate accounts that violate hospital policies.</li>
                <li>All appointments and medical consultations are subject to availability and doctor discretion.</li>
                <li>Payments for services are non-refundable unless specified otherwise.</li>
            </ul>
            <p>Last updated: April 2026</p>
        `;
    } else if (type === 'privacy') {
        title.textContent = "Privacy Policy";
        body.innerHTML = `
            <p>DocBook values your privacy. This policy explains how we collect, use, and protect your information:</p>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>We collect your name, email, phone, and role to provide better healthcare services.</li>
                <li>Your medical data is stored securely and shared only with authorized doctors and staff.</li>
                <li>We do not sell your personal information to third parties.</li>
                <li>You can request deletion of your account and data at any time.</li>
                <li>Cookies are used to improve your experience on the platform.</li>
            </ul>
            <p>For questions, contact: support@docbook.com</p>
        `;
    }
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

// Validation (same as before)
let isNameValid = false, isEmailValid = false, isPasswordValid = false, isConfirmValid = false;

function validateName() {
    const name = document.getElementById('name').value.trim();
    const error = document.getElementById('name-error');
    if (name.length < 3) {
        error.textContent = "Name must be at least 3 characters";
        error.style.display = 'block';
        isNameValid = false;
    } else {
        error.style.display = 'none';
        isNameValid = true;
    }
    checkForm();
}

function validateEmail() {
    const email = document.getElementById('email').value.trim();
    const error = document.getElementById('email-error');
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(email)) {
        error.textContent = "Please enter a valid email";
        error.style.display = 'block';
        isEmailValid = false;
    } else {
        error.style.display = 'none';
        isEmailValid = true;
    }
    checkForm();
}

function validatePassword() {
    const pass = document.getElementById('password').value;
    const error = document.getElementById('password-error');
    if (pass.length < 8) {
        error.textContent = "Password must be at least 8 characters";
        error.style.display = 'block';
        isPasswordValid = false;
    } else {
        error.style.display = 'none';
        isPasswordValid = true;
    }
    validateConfirm();
    checkForm();
}

function validateConfirm() {
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const error = document.getElementById('confirm-error');
    if (confirm && pass !== confirm) {
        error.textContent = "Passwords do not match";
        error.style.display = 'block';
        isConfirmValid = false;
    } else if (confirm) {
        error.style.display = 'none';
        isConfirmValid = true;
    }
    checkForm();
}

function checkForm() {
    const termsChecked = document.getElementById('terms').checked;
    document.getElementById('submitBtn').disabled = !(isNameValid && isEmailValid && isPasswordValid && isConfirmValid && termsChecked);
}

// Event Listeners
document.getElementById('name').addEventListener('blur', validateName);
document.getElementById('email').addEventListener('blur', validateEmail);
document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('confirm_password').addEventListener('input', validateConfirm);
document.getElementById('terms').addEventListener('change', checkForm);

// Form Submit
document.getElementById('signupForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    try {
        const data = await apiCall('../api/auth/register.php', 'POST', {
            name, email, password,
            confirm_password: document.getElementById('confirm_password').value,
            role: 'patient'
        });

        localStorage.setItem('token', data.token);
        localStorage.setItem('role', data.role);
        alert('Account created successfully!');
        window.location.href = 'dashboard-patient.php';
    } catch (err) {
        alert(err.error || 'Registration failed');
    }
});
</script>
</body>
</html>