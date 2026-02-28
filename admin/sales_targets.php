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

// Check if user has permission (Sales staff can view their own, others can manage)
$is_sales_manager = ($user_role == 'CEO_GM' || $user_role == 'HR_ADMIN' || $user_role == 'ADMIN_STAFF');

/* ===============================
   HANDLE TARGET DELETION
=================================*/
if (isset($_GET['delete']) && $is_sales_manager) {
    $target_id = (int)$_GET['delete'];
    
    // Check if target has points awarded
    $check_query = "SELECT COUNT(*) as count FROM points_ledger WHERE source_type = 'SALES_TARGET' AND source_id = $target_id";
    $check_result = mysqli_query($connection, $check_query);
    $check = mysqli_fetch_assoc($check_result);
    
    if ($check['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete target that already has points awarded.";
    } else {
        $delete_query = "DELETE FROM sales_targets WHERE target_id = $target_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Sales target deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting target: " . mysqli_error($connection);
        }
    }
    
    header("Location: sales_targets.php");
    exit();
}

/* ===============================
   HANDLE TARGET VALIDATION
=================================*/
if (isset($_GET['validate']) && $is_sales_manager) {
    $target_id = (int)$_GET['validate'];
    
    // Get target details
    $target_query = "SELECT * FROM sales_targets WHERE target_id = $target_id";
    $target_result = mysqli_query($connection, $target_query);
    $target = mysqli_fetch_assoc($target_result);
    
    if ($target) {
        // Calculate attainment percentage
        $attainment_percentage = ($target['actual_value'] / $target['target_value']) * 100;
        
        // Determine points based on attainment bands
        $points = 0;
        if ($attainment_percentage >= 100) {
            $points = 1000;
        } elseif ($attainment_percentage >= 75) {
            $points = 750;
        } elseif ($attainment_percentage >= 50) {
            $points = 500;
        } else {
            $points = 250;
        }
        
        // Update target
        $update_query = "UPDATE sales_targets SET 
                        attainment_percentage = $attainment_percentage,
                        points_awarded = $points,
                        status = 'VALIDATED',
                        validated_by = {$_SESSION['user_id']},
                        validated_at = NOW()
                        WHERE target_id = $target_id";
        
        if (mysqli_query($connection, $update_query)) {
            // Add points to ledger
            $ledger_query = "INSERT INTO points_ledger 
                            (employee_id, source_type, source_id, points, points_type, description, created_by)
                            VALUES 
                            ({$target['employee_id']}, 'SALES_TARGET', $target_id, $points, 'EARNED',
                             'Sales target achievement for " . date('F Y', mktime(0,0,0,$target['month'],1,$target['year'])) . "',
                             {$_SESSION['user_id']})";
            mysqli_query($connection, $ledger_query);
            
            $_SESSION['success_message'] = "Target validated and points awarded!";
        } else {
            $_SESSION['error_message'] = "Error validating target: " . mysqli_error($connection);
        }
    }
    
    header("Location: sales_targets.php");
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
                <li class="breadcrumb-item active">Sales Targets</li>
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
                    case 'set_target';
                        include "includes/set_sales_target.php";
                        break;
                    case 'edit_target';
                        include "includes/edit_sales_target.php";
                        break;
                    case 'submit_achievement';
                        include "includes/submit_sales_achievement.php";
                        break;
                    default:
                        include "includes/view_all_sales_targets.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Target Details Modal -->
<div class="modal fade" id="targetDetailsModal" tabindex="-1" aria-labelledby="targetDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="targetDetailsModalLabel">Target Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="targetDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading target details...</p>
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
                <p>Are you sure you want to delete this sales target?</p>
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
// View target details
function viewTarget(id) {
    const modal = new bootstrap.Modal(document.getElementById('targetDetailsModal'));
    const contentDiv = document.getElementById('targetDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading target details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_target_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

// Show delete confirmation
function confirmDelete(id) {
    document.getElementById('confirmDeleteBtn').href = 'sales_targets.php?delete=' + id;
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