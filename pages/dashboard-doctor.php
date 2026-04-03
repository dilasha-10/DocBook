<?php
require_once '../includes/functions.php';

$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($type === 'categories') {
    $result = $conn->query("SELECT name, slug FROM categories");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    jsonResponse($data);
    exit;
}

// Fetch doctors
$sql = "SELECT id, name, specialty, rating, fee, avatar FROM doctors WHERE 1=1";
$params = [];
$types = '';

if ($category !== 'all') {
    $sql .= " AND category_slug = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$doctors = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse($doctors);
?>