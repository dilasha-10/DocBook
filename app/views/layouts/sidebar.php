<?php 
$current = $_GET['page'] ?? 'dashboard';
?>

<aside class="sidebar">
    <div class="logo">
            Doc<span>Book</span>
     </div>
    <div class="menu-section">
        <div class="menu-title">Doctor Menu</div>

        <a href="index.php?page=dashboard"
           class="nav-item <?= $current == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <a href="index.php?page=schedule"
           class="nav-item <?= $current == 'schedule' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> My schedule
        </a>

        <a href="index.php?page=patients"
           class="nav-item <?= $current == 'patients' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Patients
        </a>

        <a href="index.php?page=availability"
           class="nav-item <?= $current == 'availability' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i> Availability
        </a>
    </div>

    <div class="menu-section">
        <div class="menu-title">Account</div>

        <a href="index.php?page=profile"
           class="nav-item <?= $current == 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> Profile
        </a>
    </div>

</aside>