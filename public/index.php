<?php 
require_once __DIR__ . '/../config/config.php';

// GET PAGE (default = dashboard)
$page = $_GET['page'] ?? 'dashboard';

// ALLOWED PAGES (security)
$allowed_pages = ['dashboard', 'schedule', 'patients', 'availability', 'profile'];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Fetch doctor info for the header
global $pdo;
$doctorId = 1; // Simulated auth
$stmt = $pdo->prepare("SELECT d.photo, u.name FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
$stmt->execute([$doctorId]);
$authDoctor = $stmt->fetch();

$authName = $authDoctor ? "Dr. " . $authDoctor['name'] : "Doctor";
$authPhoto = $authDoctor && $authDoctor['photo'] ? $authDoctor['photo'] : null;
$authInitials = '';
if ($authDoctor) {
    preg_match_all('/\b\w/', $authDoctor['name'], $matches);
    $authInitials = strtoupper(implode('', array_slice($matches[0], 0, 2)));
} else {
    $authInitials = "DR";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook - Doctor Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/drstyle.css">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar -->
<?php include __DIR__ . '/../app/views/layouts/sidebar.php'; ?>

<!-- Main Content -->
<main class="main-content">

    <!-- Header -->
    <?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

    <!-- Page Content -->
    <div class="content-area">

        <?php
        switch($page) {

            case 'availability':
                include __DIR__ . '/../app/views/page/availability.php';
                break;

            case 'patients':
                include __DIR__ . '/../app/views/page/patient.php';
                break;

            case 'schedule':
                include __DIR__ . '/../app/views/page/schedule.php';
                break;

            case 'profile':
                include __DIR__ . '/../app/views/page/drprofile.php';
                break;

            case 'dashboard':
            default:
                include __DIR__ . '/../app/views/page/drdashboard.php';
                break;
        }
        ?>

    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>

</main>

<!-- ✅ Load Patient Panel ONLY for Dashboard -->
<?php 
if ($page === 'dashboard') {
    include __DIR__ . '/../app/views/page/patient-panel.php';
}
?>

<script src="assets/drscript.js"></script>

</body>
</html>