<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Forgot Password</title>
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

      <main class="forgot-shell">
        <section class="forgot-card" aria-labelledby="forgot-title">
          <h1 id="forgot-title">Forgot password?</h1>
          <p>
            Enter your email address or cell phone number. We will send you a reset link.
          </p>

          <form id="forgot-form" action="api/forgot-password.php" method="post" novalidate>
            <div class="field-group">
              <label for="identifier">Email / Cell</label>
              <input
                id="identifier"
                name="identifier"
                type="text"
                inputmode="email"
                placeholder="Email address or cell phone number"
                required
              />
            </div>

            <div class="forgot-actions">
              <button type="button" class="secondary-btn" onclick="window.location.href='index.php'">
                Back to sign in
              </button>
              <button type="submit" class="primary-btn">Send reset link</button>
            </div>

            <p id="reset-notice" class="notice" aria-live="polite"></p>
          </form>
        </section>
      </main>
    </div>

    <script src="assets/js/auth-pages.js"></script>
  </body>
</html>
