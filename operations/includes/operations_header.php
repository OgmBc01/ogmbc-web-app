<?php
// Start session and enforce operations-area authorization before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/database.php';
include 'operations_functions.php';

// Enforce inactivity timeout (30 minutes) for logged-in users
enforce_session_timeout();

// Initialize session with security settings
initSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Check specific role (operations access)
if (!isOperations()) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operations Dashboard</title>
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