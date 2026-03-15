<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// Check if user has permission (CEO_GM, HR_ADMIN, ADMIN_STAFF can validate)
$can_validate = ($user_role == 'ceo_gm' || $user_role == 'hr_admin' || $user_role == 'admin_staff');

/* ===============================
   HANDLE FEEDBACK VALIDATION
=================================*/
if (isset($_GET['validate']) && $can_validate) {
    $feedback_id = (int)$_GET['validate'];
    
    // Get feedback details
    $feedback_query = "SELECT * FROM client_feedback WHERE feedback_id = $feedback_id";
    $feedback_result = mysqli_query($connection, $feedback_query);
    $feedback = mysqli_fetch_assoc($feedback_result);
    
    if ($feedback) {
        // Update feedback as validated
        $update_query = "UPDATE client_feedback SET 
                        is_validated = 1, 
                        validated_by = {$_SESSION['user_id']}, 
                        validated_at = NOW() 
                        WHERE feedback_id = $feedback_id";
        
        if (mysqli_query($connection, $update_query)) {
            // Add points to ledger
            $ledger_query = "INSERT INTO points_ledger 
                            (employee_id, source_type, source_id, points, points_type, description, created_by)
                            VALUES 
                            ({$feedback['employee_id']}, 'CLIENT_FEEDBACK', $feedback_id, 50, 'EARNED',
                             'Positive client feedback validated', {$_SESSION['user_id']})";
            mysqli_query($connection, $ledger_query);
            
            $_SESSION['success_message'] = "Feedback validated and 50 points awarded!";
        } else {
            $_SESSION['error_message'] = "Error validating feedback: " . mysqli_error($connection);
        }
    }
    
    header("Location: client_feedback.php");
    exit();
}

/* ===============================
   HANDLE FEEDBACK DELETION
=================================*/
if (isset($_GET['delete']) && $can_validate) {
    $feedback_id = (int)$_GET['delete'];
    
    // Check if feedback has points awarded
    $check_query = "SELECT COUNT(*) as count FROM points_ledger WHERE source_type = 'CLIENT_FEEDBACK' AND source_id = $feedback_id";
    $check_result = mysqli_query($connection, $check_query);
    $check = mysqli_fetch_assoc($check_result);
    
    if ($check['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete feedback that already has points awarded.";
    } else {
        $delete_query = "DELETE FROM client_feedback WHERE feedback_id = $feedback_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Feedback deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting feedback: " . mysqli_error($connection);
        }
    }
    
    header("Location: client_feedback.php");
    exit();
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Client Feedback</li>
            </ol>
        </nav>

        <!-- Alert Messages Container for AJAX -->
        <div id="alertBox"></div>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all';
                }

                switch($source) {
                    case 'add_feedback';
                        include "includes/add_client_feedback.php";
                        break;
                    case 'edit_feedback';
                        include "includes/edit_client_feedback.php";
                        break;
                    default:
                        include "includes/view_all_client_feedback.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Details Modal -->
<div class="modal fade" id="feedbackDetailsModal" tabindex="-1" aria-labelledby="feedbackDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Changed to modal-xl for more space -->
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title" id="feedbackDetailsModalLabel">
                    <i class="bi bi-chat-quote me-2"></i>Feedback Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="feedbackDetailsContent" style="max-height: 70vh; overflow-y: auto;">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border" style="color: #f1bf70;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading feedback details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this feedback?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// View feedback details
function viewFeedback(id) {
    console.log('Viewing feedback ID:', id); // Debug log
    
    const modalElement = document.getElementById('feedbackDetailsModal');
    if (!modalElement) {
        console.error('Modal element not found!');
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    const contentDiv = document.getElementById('feedbackDetailsContent');
    
    // Show loading state
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border" style="color: #f1bf70;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading feedback details...</p>
        </div>
    `;
    
    modal.show();
    
    // Use absolute path to ensure correct URL
    fetch('includes/ajax/get_feedback_details.php?id=' + id, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug log
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text(); // Get as text first for debugging
    })
    .then(text => {
        console.log('Raw response:', text.substring(0, 200)); // Debug first 200 chars
        
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load feedback details'}</div>`;
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response was not valid JSON:', text);
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: Server returned invalid JSON. Check console for details.</div>`;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    });
}

// Show delete confirmation
function confirmDelete(id) {
    document.getElementById('confirmDeleteBtn').href = 'client_feedback.php?delete=' + id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
        const container = document.querySelector('.container-fluid');
        const div = document.createElement('div');
        div.id = 'alertBox';
        div.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        container.prepend(div);
    } else {
        alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    }
}
</script>

<?php include "includes/footer.php"; ?>