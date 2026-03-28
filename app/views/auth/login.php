<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DocBook - Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/styles.css" />
  </head>
  <body>
    <div class="frame">
      <header class="topbar">
        <p class="brand">Doc<span>Book</span></p>
        <div class="topbar-cta">
          <p>Don't have an account?</p>
          <a href="sign-up.php" class="sign-up-btn">Sign up</a>
        </div>
      </header>

      <main class="auth-shell">
        <section class="auth-card" aria-labelledby="login-title">
          <h1 id="login-title"><span aria-hidden="true">&#8249;</span> Log in to your account</h1>

          <form id="login-form" action="api/login.php" method="post" novalidate>
            <div class="field-group">
              <label for="identifier">Email</label>
              <input
                id="identifier"
                name="identifier"
                type="text"
                inputmode="email"
                autocomplete="username"
                placeholder="Email address"
                required
              />
            </div>

            <div class="field-group">
              <label for="password">Password</label>
              <div class="password-wrap">
                <input
                  id="password"
                  name="password"
                  type="password"
                  autocomplete="current-password"
                  placeholder="Enter your password"
                  required
                />
                <button type="button" id="password-toggle" class="password-toggle" aria-controls="password" aria-label="Show password">
                  Show
                </button>
              </div>
            </div>

            <div class="role-block" aria-labelledby="role-label">
              <p id="role-label" class="role-label">Select role before sign in</p>
              <div class="role-grid" role="radiogroup" aria-label="Role selector">
                <button type="button" class="role-card" data-role="Patient" role="radio" aria-checked="false">Patient</button>
                <button type="button" class="role-card" data-role="Doctor" role="radio" aria-checked="false">Doctor</button>
              </div>
              <input type="hidden" id="role" name="role" required />
              <p id="role-error" class="role-error" aria-live="polite"></p>
            </div>

            <button id="submit-btn" class="submit-btn" type="submit" disabled>Log in</button>
          </form>

          <div class="divider" role="presentation"><span>OR</span></div>

          <button type="button" class="social-btn" aria-label="Log in with Google">
            <span class="social-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21.8055 10.0415H12.25V13.9582H17.7449C17.5081 15.2082 16.7969 16.2665 15.7291 16.9748L19.0016 19.5248C20.9073 17.7665 22 15.1748 22 12.0832C22 11.3748 21.9351 10.6915 21.8055 10.0415Z" fill="#4285F4"/>
                <path d="M12.25 22C14.99 22 17.2875 21.0917 19.0016 19.5249L15.7291 16.9749C14.8208 17.5833 13.6599 17.95 12.25 17.95C9.60736 17.95 7.36645 16.1667 6.56909 13.7667L3.18677 16.375C4.89181 19.7583 8.35545 22 12.25 22Z" fill="#34A853"/>
                <path d="M6.56909 13.7666C6.36545 13.1583 6.25 12.5083 6.25 11.8333C6.25 11.1583 6.36545 10.5083 6.56909 9.89998L3.18677 7.29165C2.48409 8.69165 2.08325 10.2583 2.08325 11.8333C2.08325 13.4083 2.48409 14.975 3.18677 16.375L6.56909 13.7666Z" fill="#FBBC05"/>
                <path d="M12.25 5.71668C13.7895 5.71668 15.1671 6.25001 16.2508 7.29168L19.0765 4.46668C17.2792 2.79168 14.99 1.66668 12.25 1.66668C8.35545 1.66668 4.89181 3.90834 3.18677 7.29168L6.56909 9.90001C7.36645 7.50001 9.60736 5.71668 12.25 5.71668Z" fill="#EA4335"/>
              </svg>
            </span>
            Log in with Google
          </button>

          <button type="button" class="social-btn" aria-label="Log in with Facebook">
            <span class="social-icon fb" aria-hidden="true">f</span>
            Log in with Facebook
          </button>

          <nav class="auth-links" aria-label="Authentication links">
            <a href="forgot-password.php">Forgot password?</a>
            <p>
              New to DocBook?
              <a href="sign-up.php">Sign up</a>
            </p>
          </nav>
        </section>
      </main>
    </div>

    <script src="assets/js/script.js"></script>
  </body>
</html>
