<?php
// Start session and enforce admin-area authorization before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to site root if user is not authenticated or not in allowed admin roles
$allowed_roles = ['admin', 'super_admin', 'moderator'];

include '../includes/database.php';
include 'operations_functions.php';
// Enforce inactivity timeout (30 minutes) for logged-in admin users
enforce_session_timeout();

// Enforce session and role-based redirects
$allowed_roles = ['admin', 'super_admin', 'moderator'];
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=session');
    exit();
}
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: ../index.php?error=permission');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/operations_styles.css?v=<?php echo time(); ?>">
</head>
<body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>