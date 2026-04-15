<header class="top-header">
    <nav>
    <ul class="top-nav">
      <li><a href="index.php?page=dashboard" class="active">Dashboard</a></li>
      <li><a href="index.php?page=schedule">Schedule</a></li>
      <li><a href="index.php?page=patients">Patients</a></li>
      <li><a href="index.php?page=availability">Availability</a></li>
    </ul>
  </nav>
    <div class="user-profile" style="display:flex; align-items:center; gap:15px;">
       <button id="theme-toggle" class="btn-outline" style="border:none; background:transparent; font-size:18px; cursor:pointer;" title="Toggle Theme">
            <i class="fas fa-sun"></i>
        </button>
        <div class="user-badge">
            <div class="avatar" style="overflow: hidden; padding: 0;">
                <?php if (!empty($authPhoto)): ?>
                    <img src="<?= htmlspecialchars($authPhoto) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <?= htmlspecialchars($authInitials) ?>
                <?php endif; ?>
            </div>
            <span><?= htmlspecialchars($authName) ?></span>
        </div>
        <button class="btn-signout">Sign out</button>
    </div>
</header>