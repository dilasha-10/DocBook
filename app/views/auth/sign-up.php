<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Sign Up</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/auth-pages.css" />
  </head>
  <body>
    <div class="frame">
      <header class="topbar">
        <p class="brand">Doc<span>Book</span></p>
        <a class="topbar-link" href="index.php">Sign in</a>
      </header>

      <main class="layout">
        <section class="hero-panel" aria-label="Sign up highlights">
          <h1>Start booking appointments today</h1>
          <p>Join thousands of patients managing their health appointments online.</p>
          <ul class="benefits">
            <li>Browse doctors by specialization</li>
            <li>Check real-time availability</li>
            <li>Book and manage appointments</li>
            <li>Receive instant confirmation</li>
          </ul>
        </section>

        <section class="form-panel" aria-labelledby="create-account-title">
          <div class="form-card">
            <h2 id="create-account-title">Create your account</h2>
            <p class="form-intro">Sign up to start booking appointments</p>

            <form action="api/register.php" method="post" novalidate>
              <div class="field-group">
                <label for="full-name">Full name</label>
                <input id="full-name" name="fullName" type="text" placeholder="Enter your name" required />
              </div>

              <div class="field-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" placeholder="Enter your email address" required />
              </div>

              <div class="field-group">
                <label for="new-password">Password</label>
                <input id="new-password" name="password" type="password" placeholder="Enter password" required />
              </div>

              <div class="field-group">
                <label for="confirm-password">Confirm password</label>
                <input id="confirm-password" name="confirmPassword" type="password" placeholder="Confirm password" required />
              </div>

              <button class="submit-btn" type="submit">Sign up</button>
            </form>

            <p class="form-links">
              Already have an account? <a href="index.php">Sign in</a>
            </p>
          </div>
        </section>
      </main>
    </div>

    <script src="assets/js/auth-pages.js"></script>
  </body>
</html>
