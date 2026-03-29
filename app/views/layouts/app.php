<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook <?php echo isset($title) ? htmlspecialchars($title) : 'Doctor Appointment Booking'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <?php if (isset($extra_styles)) echo $extra_styles; ?>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="/categories" class="nav-logo">Doc<span>Book</span></a>
        <ul class="nav-links">
            <li><a href="/dashboard"  class="<?php echo request_is('/dashboard')  ? 'active' : ''; ?>">My Appointments</a></li>
            <li><a href="/categories" class="<?php echo request_is('/categories') ? 'active' : ''; ?>">Find Doctors</a></li>
            <li><a href="/about"      class="<?php echo request_is('/about')      ? 'active' : ''; ?>">About</a></li>
            <li><a href="/contact"    class="<?php echo request_is('/contact')    ? 'active' : ''; ?>">Contact</a></li>
        </ul>
        <div class="nav-actions">
            <?php if (isset($user)): ?>
                <div class="user-chip">
                    <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                    <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
                </div>
                <a href="/logout" class="btn-signout">Sign out</a>
            <?php else: ?>
                <a href="/login"  class="btn-signin">Sign in</a>
                <a href="/signup" class="btn-signup">Sign up</a>
            <?php endif; ?>
        </div>
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Mobile menu drawer -->
<div class="mobile-menu" id="mobileMenu">
    <ul class="mobile-nav-links">
        <li><a href="/dashboard"  class="<?php echo request_is('/dashboard')  ? 'active' : ''; ?>"><i class="fa fa-th-large"></i> My Appointments</a></li>
        <li><a href="/categories" class="<?php echo request_is('/categories') ? 'active' : ''; ?>"><i class="fa fa-stethoscope"></i> Find Doctors</a></li>
        <li><a href="/about"      class="<?php echo request_is('/about')      ? 'active' : ''; ?>"><i class="fa fa-circle-info"></i> About</a></li>
        <li><a href="/contact"    class="<?php echo request_is('/contact')    ? 'active' : ''; ?>"><i class="fa fa-envelope"></i> Contact</a></li>
        <?php if (isset($user)): ?>
        <li><a href="/profile"    class="<?php echo request_is('/profile')    ? 'active' : ''; ?>"><i class="fa fa-user"></i> Profile & Settings</a></li>
        <?php endif; ?>
    </ul>
    <div class="mobile-nav-actions">
        <?php if (isset($user)): ?>
            <div class="user-chip" style="justify-content:center;">
                <div class="avatar-circle"><?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?></div>
                <span><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
            </div>
            <a href="/logout" class="btn-signout" style="text-align:center;">Sign out</a>
        <?php else: ?>
            <a href="/login"  class="btn-signin" style="text-align:center;">Sign in</a>
            <a href="/signup" class="btn-signup" style="text-align:center;">Sign up</a>
        <?php endif; ?>
    </div>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<main class="main-wrap">
    <?php echo $content; ?>
</main>

<footer class="footer">
    <div class="footer-inner">
        <span class="footer-logo">Doc<span>Book</span></span>
        <div class="footer-links">
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
        <span class="footer-copy">&copy; 2026 DocBook</span>
    </div>
</footer>

<script src="<?= BASE_URL ?>/js/main.js"></script>
<script>
(function(){
    var btn = document.getElementById('hamburgerBtn');
    var menu = document.getElementById('mobileMenu');
    var overlay = document.getElementById('mobileOverlay');
    function close() {
        btn.classList.remove('open');
        menu.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    btn.addEventListener('click', function(){
        var isOpen = menu.classList.contains('open');
        if (isOpen) { close(); }
        else {
            btn.classList.add('open');
            menu.classList.add('open');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    });
    overlay.addEventListener('click', close);
})();
</script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>

</body>
</html>