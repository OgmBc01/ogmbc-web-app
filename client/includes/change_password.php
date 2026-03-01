<?php
ob_start();

// Check if user is logged in
// if (!isset($_SESSION['client_id'])) {
//     ob_end_clean();
//     echo "<script>window.location.href = '../login.php';</script>";
//     exit();
// }

$client_id = $_SESSION['client_id'];

// Initialize variables
$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_type = "danger";
    } elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $message_type = "danger";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = "danger";
    } else {
        // Fetch current password from database
        $query = "SELECT client_password FROM clients WHERE client_id = " . intval($client_id);
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $client = mysqli_fetch_assoc($result);
            
            // Verify current password
            if (password_verify($current_password, $client['client_password'])) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $update_query = "UPDATE clients SET client_password = '$hashed_password' WHERE client_id = " . intval($client_id);
                
                if (mysqli_query($connection, $update_query)) {
                    // Log activity if table exists
                    $log_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_activity_log'");
                    if ($log_check && mysqli_num_rows($log_check) > 0) {
                        $log_query = "INSERT INTO client_activity_log 
                                     (client_id, activity_type, description, ip_address)
                                     VALUES 
                                     (" . intval($client_id) . ", 'password_change', 'Changed account password', '{$_SERVER['REMOTE_ADDR']}')";
                        mysqli_query($connection, $log_query);
                    }
                    
                    $showSuccessModal = true;
                } else {
                    $message = "Error changing password. Please try again.";
                    $message_type = "danger";
                }
            } else {
                $message = "Current password is incorrect.";
                $message_type = "danger";
            }
        } else {
            $message = "Client not found.";
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                    <a href="profile.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="passwordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Enter your current password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter new password" minlength="6" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" required>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="change_password" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i>Update Password
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Password Tips:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Use at least 6 characters</li>
                            <li>Mix letters and numbers for stronger security</li>
                            <li>Avoid using common words or personal information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Password Updated!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Your password has been changed successfully!</h5>
                <p class="text-muted">You can now use your new password to log in.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="profile.php" class="btn btn-success px-4">Return to Profile</a>
                <a href="dashboard.php" class="btn btn-outline-success px-4">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<script>
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('New passwords do not match!');
    }
});
</script>