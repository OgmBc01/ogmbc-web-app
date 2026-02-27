<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   HANDLE USER DELETION
=================================*/
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account.";
    } else {
        // First check if user exists
        $check_query = "SELECT user_id, username FROM users WHERE user_id = $user_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $user = mysqli_fetch_assoc($check_result);
            
            // Delete the user
            $delete_query = "DELETE FROM users WHERE user_id = $user_id";
            if (mysqli_query($connection, $delete_query)) {
                $_SESSION['success_message'] = "User '" . $user['username'] . "' deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Error deleting user: " . mysqli_error($connection);
            }
        } else {
            $_SESSION['error_message'] = "User not found.";
        }
    }
    
    // Redirect back to users page
    header("Location: users.php");
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
                <li class="breadcrumb-item active">Users</li>
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
                    case 'add_user';
                        include "includes/add_user.php";
                        break;
                    case 'edit_user';
                        include "includes/edit_user.php";
                        break;
                    default:
                        include "includes/view_all_users.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
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
                <p>Are you sure you want to delete the user "<span id="deleteUserName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteUserId = null;

// Show delete confirmation modal
function confirmDelete(id, name) {
    deleteUserId = id;
    document.getElementById('deleteUserName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Handle delete confirmation with AJAX
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!deleteUserId) return;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    // Show loading state
    showAlert('Deleting user...', 'info');
    
    // Send AJAX request
    fetch('includes/delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + deleteUserId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Remove the deleted user row from table
            const row = document.querySelector(`button[onclick="confirmDelete(${deleteUserId}, '")]`).closest('tr');
            if (row) {
                row.remove();
            }
            // Refresh page after 2 seconds to update counts
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error: ' + error.message, 'danger');
    });
});

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
        // Create alert box if it doesn't exist
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

// View user details
function viewUser(id) {
    const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    const contentDiv = document.getElementById('userDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading user details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/get_user_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const roleBadge = user.role_name ? 
                    `<span class="badge bg-info">${user.role_name}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const typeBadge = user.type_name ? 
                    `<span class="badge bg-success">${user.type_name}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const statusBadge = user.user_status == 'active' ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-warning">Inactive</span>';
                
                contentDiv.innerHTML = `
                    <div class="text-center mb-3">
                        <img src="../images/${user.user_image || 'default.jpg'}" class="rounded-circle" width="100" height="100" alt="User Image">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User ID:</strong> ${user.user_id}</p>
                            <p><strong>Username:</strong> ${user.username}</p>
                            <p><strong>Full Name:</strong> ${user.first_name} ${user.last_name}</p>
                            <p><strong>Email:</strong> ${user.user_email}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Role:</strong> ${roleBadge}</p>
                            <p><strong>Type:</strong> ${typeBadge}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                `;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

</script>

<?php include "includes/footer.php"; ?>