How to Use in Other Pages
In any page that needs session/authentication, simply do:

<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Initialize session with security settings
initSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Check specific role (example for operations page)
if (!isOperations()) {
    header("Location: ../index.php");
    exit();
}

// Rest of your page code...
