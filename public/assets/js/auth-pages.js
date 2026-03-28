// Forgot password form handling
const forgotForm = document.getElementById("forgot-form");
const resetNotice = document.getElementById("reset-notice");

if (forgotForm && resetNotice) {
  forgotForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    
    const submitBtn = forgotForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    try {
      const data = new FormData(forgotForm);
      const identifier = String(data.get("identifier") || "").trim();

      if (!identifier) {
        throw new Error("Please enter your email or phone number.");
      }

      const response = await fetch('api/forgot-password.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ identifier })
      });

      const responseData = await response.json();

      if (response.ok) {
        resetNotice.textContent = "If an account exists with this email/phone, a password reset link has been sent.";
        resetNotice.style.color = 'var(--primary)';
        // Clear form
        forgotForm.reset();
      } else {
        throw new Error(responseData.message || 'Failed to send reset link');
      }
    } catch (error) {
      console.error('Forgot password error:', error);
      resetNotice.textContent = error.message || 'An error occurred. Please try again.';
      resetNotice.style.color = 'var(--danger)';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}

// Sign-up form handling
const signupForm = document.querySelector('form[action="api/register.php"]') || 
                   (document.querySelector('form') && 
                    document.querySelector('form').getAttribute('action') === 'api/register.php' ? 
                    document.querySelector('form') : null);

if (signupForm) {
  const nameInput = document.getElementById('full-name');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('new-password');
  const confirmPasswordInput = document.getElementById('confirm-password');

  signupForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    // Get submit button
    const submitBtn = signupForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating account...';

    try {
      const payload = {
        name: nameInput.value.trim(),
        email: emailInput.value.trim(),
        password: passwordInput.value,
        confirmPassword: confirmPasswordInput.value,
        role: 'Patient' // Default role for sign-up
      };

      // Validate required fields
      if (!payload.name) {
        throw new Error('Name is required');
      }
      if (!payload.email) {
        throw new Error('Email is required');
      }
      if (!payload.password) {
        throw new Error('Password is required');
      }
      if (payload.password !== payload.confirmPassword) {
        throw new Error('Passwords do not match');
      }

      const response = await fetch('api/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const responseData = await response.json();

      if (response.ok && responseData.success) {
        // Store token in localStorage
        localStorage.setItem('authToken', responseData.token);
        localStorage.setItem('userRole', responseData.user.role);
        localStorage.setItem('userName', responseData.user.name);

        // Show success and redirect
        alert(`Welcome, ${responseData.user.name}! Your account has been created.`);
        window.location.href = 'dashboard.php';
      } else {
        throw new Error(responseData.message || 'Sign-up failed');
      }
    } catch (error) {
      console.error('Sign-up error:', error);
      alert(error.message || 'An error occurred. Please try again.');
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
}
