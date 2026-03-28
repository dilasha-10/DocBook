const form = document.getElementById("login-form");
const passwordInput = document.getElementById("password");
const passwordToggle = document.getElementById("password-toggle");
const roleCards = Array.from(document.querySelectorAll(".role-card"));
const roleInput = document.getElementById("role");
const roleError = document.getElementById("role-error");
const submitBtn = document.getElementById("submit-btn");

function setRole(role) {
  roleInput.value = role;
  roleCards.forEach((card) => {
    const isActive = card.dataset.role === role;
    card.setAttribute("aria-checked", String(isActive));
  });
  roleError.textContent = "";
  submitBtn.disabled = false;
}

passwordToggle.addEventListener("click", () => {
  const isHidden = passwordInput.type === "password";
  passwordInput.type = isHidden ? "text" : "password";
  passwordToggle.textContent = isHidden ? "Hide" : "Show";
  passwordToggle.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
});

roleCards.forEach((card) => {
  card.addEventListener("click", () => setRole(card.dataset.role));
});

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  // Validate role selection
  if (!roleInput.value) {
    roleError.textContent = "Please select Patient or Doctor before signing in.";
    submitBtn.disabled = true;
    return;
  }

  // Validate form inputs
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // Disable submit button and show loading state
  submitBtn.disabled = true;
  const originalText = submitBtn.textContent;
  submitBtn.textContent = "Signing in...";

  try {
    const formData = new FormData(form);
    const payload = {
      identifier: formData.get("identifier"),
      password: formData.get("password"),
      role: formData.get("role")
    };

    const response = await fetch("api/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // Store token in localStorage
      localStorage.setItem("authToken", data.token);
      localStorage.setItem("userRole", data.user.role);
      localStorage.setItem("userName", data.user.name);

      // Show success message
      alert(`Welcome back, ${data.user.name}! Logging in as ${data.user.role}...`);

      // Redirect to dashboard (or next page)
      window.location.href = "dashboard.php";
    } else {
      // Show error message
      alert(data.message || "Login failed. Please try again.");
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  } catch (error) {
    console.error("Login error:", error);
    alert("An error occurred. Please check your connection and try again.");
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
});
