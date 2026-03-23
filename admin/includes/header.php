<?php

// Start session and enforce admin-area authorization before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/database.php';
include 'functions.php';
// Enforce inactivity timeout (30 minutes) for logged-in admin users
enforce_session_timeout();

// Initialize session with security settings
initSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../index.php");
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
    <link rel="stylesheet" href="resources/style.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Password Authentication Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Authentication Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
?>
                    <div class="mb-3">
                        <label for="user_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="user_password" name="user_password" required>
                    </div>
                    <div id="authError" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAuth">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankAccountsLink = document.querySelector('a[href="bank_accounts.php"]');
    if(bankAccountsLink) {
        bankAccountsLink.addEventListener('click', function(e) {
            e.preventDefault();
            const authModal = new bootstrap.Modal(document.getElementById('authModal'));
            authModal.show();
        });
    }

    document.getElementById('submitAuth').addEventListener('click', function() {
        const password = document.getElementById('user_password').value;
        const errorDiv = document.getElementById('authError');
        
        if(!password) {
            errorDiv.textContent = 'Please enter your password';
            errorDiv.classList.remove('d-none');
            return;
        }

        // AJAX request to verify password
        fetch('verify_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_password=' + encodeURIComponent(password)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'bank_accounts.php';
            } else {
                errorDiv.textContent = data.message || 'Invalid password';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            errorDiv.textContent = 'Authentication failed. Please try again.';
            errorDiv.classList.remove('d-none');
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>