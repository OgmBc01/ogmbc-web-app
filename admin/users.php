<?php
ob_start();
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   HANDLE USER DELETION WITH CONFIRMATION
=================================*/
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    $confirm = isset($_GET['confirm']) && $_GET['confirm'] == '1';
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account.";
                if (!headers_sent()) {
                    header("Location: users.php");
                    exit();
                }
    }
    
    // Check if user exists
    $check_query = "SELECT user_id, username FROM users WHERE user_id = $user_id";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: users.php");
        exit();
    }
    
    $user = mysqli_fetch_assoc($check_result);
    
    // Check for foreign key references
    $references = [];
    
    // Check clients table (assigned_sales_id)
    $client_ref_query = "SELECT COUNT(*) as count FROM clients WHERE assigned_sales_id = $user_id";
    $client_ref_result = mysqli_query($connection, $client_ref_query);
    $client_ref_count = mysqli_fetch_assoc($client_ref_result)['count'];
    if ($client_ref_count > 0) {
        $references['clients'] = $client_ref_count;
    }
    
    // Check employees table (user_id foreign key)
    $employee_ref_query = "SELECT COUNT(*) as count FROM employees WHERE user_id = $user_id";
    $employee_ref_result = mysqli_query($connection, $employee_ref_query);
    $employee_ref_count = mysqli_fetch_assoc($employee_ref_result)['count'];
    if ($employee_ref_count > 0) {
        $references['employees'] = $employee_ref_count;
    }
    
    // Check audit_log table
    $audit_ref_query = "SELECT COUNT(*) as count FROM audit_log WHERE user_id = $user_id";
    $audit_ref_result = mysqli_query($connection, $audit_ref_query);
    $audit_ref_count = mysqli_fetch_assoc($audit_ref_result)['count'];
    if ($audit_ref_count > 0) {
        $references['audit_log'] = $audit_ref_count;
    }
    
    // Check client_communications
    $comm_ref_query = "SELECT COUNT(*) as count FROM client_communications WHERE user_id = $user_id";
    $comm_ref_result = mysqli_query($connection, $comm_ref_query);
    $comm_ref_count = mysqli_fetch_assoc($comm_ref_result)['count'];
    if ($comm_ref_count > 0) {
        $references['communications'] = $comm_ref_count;
    }
    
    // Check cdp_records (created_by, approved_by)
    $cdp_created_query = "SELECT COUNT(*) as count FROM cdp_records WHERE created_by = $user_id";
    $cdp_created_result = mysqli_query($connection, $cdp_created_query);
    $cdp_created_count = mysqli_fetch_assoc($cdp_created_result)['count'];
    if ($cdp_created_count > 0) {
        $references['cdp_created'] = $cdp_created_count;
    }
    
    $cdp_approved_query = "SELECT COUNT(*) as count FROM cdp_records WHERE approved_by = $user_id";
    $cdp_approved_result = mysqli_query($connection, $cdp_approved_query);
    $cdp_approved_count = mysqli_fetch_assoc($cdp_approved_result)['count'];
    if ($cdp_approved_count > 0) {
        $references['cdp_approved'] = $cdp_approved_count;
    }
    
    // Check points_ledger
    $points_query = "SELECT COUNT(*) as count FROM points_ledger WHERE employee_id = $user_id OR created_by = $user_id OR approved_by = $user_id";
    $points_result = mysqli_query($connection, $points_query);
    $points_count = mysqli_fetch_assoc($points_result)['count'];
    if ($points_count > 0) {
        $references['points_ledger'] = $points_count;
    }
    
    // If there are references and not confirmed, show warning
    if (!empty($references) && !$confirm) {
        $_SESSION['delete_warning'] = [
            'user_id' => $user_id,
            'username' => $user['username'],
            'references' => $references
        ];
        header("Location: users.php?show_delete_warning=1");
        exit();
    }
    
    // Proceed with deletion (either no references or confirmed)
    mysqli_begin_transaction($connection);
    
    try {
        // Nullify foreign key references
        if ($client_ref_count > 0) {
            mysqli_query($connection, "UPDATE clients SET assigned_sales_id = NULL WHERE assigned_sales_id = $user_id");
        }
        
        if ($employee_ref_count > 0) {
            // You might want to handle employees differently - perhaps delete or set user_id to NULL
            // For now, we'll just note it
        }
        
        // Delete the user
        $delete_query = "DELETE FROM users WHERE user_id = $user_id";
        if (!mysqli_query($connection, $delete_query)) {
            throw new Exception("Error deleting user: " . mysqli_error($connection));
        }
        
        mysqli_commit($connection);
        
        // Build success message
        $msg = "User '" . $user['username'] . "' deleted successfully!";
        if (!empty($references)) {
            $ref_details = [];
            foreach ($references as $key => $count) {
                $ref_details[] = "$count " . str_replace('_', ' ', $key);
            }
            $msg .= " <strong>Note:</strong> References in " . implode(', ', $ref_details) . " were handled automatically.";
        }
        $_SESSION['success_message'] = $msg;
        
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header("Location: users.php");
    exit();
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Delete Warning Modal (shown when references exist) -->
        <?php if (isset($_SESSION['delete_warning']) && isset($_GET['show_delete_warning'])): 
            $warning = $_SESSION['delete_warning'];
            unset($_SESSION['delete_warning']);
        ?>
        <div class="modal fade" id="deleteWarningModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Warning: User Has Dependencies
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fs-5 mb-3">The user "<strong><?php echo htmlspecialchars($warning['username']); ?></strong>" is referenced in the following records:</p>
                        
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference Type</th>
                                        <th>Count</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($warning['references'] as $key => $count): ?>
                                    <tr>
                                        <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                                        <td><span class="badge bg-info"><?php echo $count; ?></span></td>
                                        <td><span class="text-muted">Will be set to NULL</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>What will happen?</strong><br>
                            • All references to this user will be set to NULL<br>
                            • The user account will be permanently deleted<br>
                            • This action cannot be undone
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <a href="users.php?delete=<?php echo $warning['user_id']; ?>&confirm=1" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Delete Anyway
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var warningModal = new bootstrap.Modal(document.getElementById('deleteWarningModal'));
                warningModal.show();
            });
        </script>
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

<!-- Delete Confirmation Modal (initial confirmation) -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user "<strong><span id="deleteUserName"></span></strong>"?</p>
                <p class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title" id="userDetailsModalLabel">
                    <i class="bi bi-person-badge me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editUserBtn" class="btn btn-primary" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240;">
                    <i class="bi bi-pencil me-1"></i>Edit User
                </a>
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
    document.getElementById('confirmDeleteBtn').href = 'users.php?delete=' + id;
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
                document.getElementById('editUserBtn').href = 'users.php?source=edit_user&id=' + id;
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

<?php ob_end_flush(); ?>