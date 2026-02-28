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

/* ===============================
   HANDLE MANUAL ADJUSTMENT APPROVAL
=================================*/
if (isset($_GET['approve_adjustment']) && ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF')) {
    $ledger_id = (int)$_GET['approve_adjustment'];
    
    $update_query = "UPDATE points_ledger SET approved_by = {$_SESSION['user_id']}, approved_at = NOW() WHERE ledger_id = $ledger_id";
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "Manual adjustment approved successfully!";
    } else {
        $_SESSION['error_message'] = "Error approving adjustment: " . mysqli_error($connection);
    }
    
    header("Location: points_ledger.php");
    exit();
}

/* ===============================
   HANDLE MONTHLY CLOSE
=================================*/
if (isset($_GET['close_month']) && ($user_role == 'CEO_GM' || $user_role == 'ADMIN_STAFF')) {
    $year = (int)$_GET['year'];
    $month = (int)$_GET['month'];
    
    // Get all employees with points in this month
    $employees_query = "SELECT DISTINCT employee_id FROM points_ledger 
                        WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month";
    $employees_result = mysqli_query($connection, $employees_query);
    
    while ($emp = mysqli_fetch_assoc($employees_result)) {
        $employee_id = $emp['employee_id'];
        
        // Calculate total points for the month
        $total_query = "SELECT SUM(points) as total FROM points_ledger 
                        WHERE employee_id = $employee_id 
                        AND YEAR(created_at) = $year 
                        AND MONTH(created_at) = $month
                        AND points_type = 'EARNED'";
        $total_result = mysqli_query($connection, $total_query);
        $total = mysqli_fetch_assoc($total_result)['total'] ?? 0;
        
        // Calculate cashable points (max(0, total - 1000))
        $cashable = max(0, $total - 1000);
        
        // Check if summary exists
        $check_query = "SELECT summary_id FROM monthly_point_summary 
                        WHERE employee_id = $employee_id AND year = $year AND month = $month";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing
            $update_summary = "UPDATE monthly_point_summary 
                              SET total_points = $total, cashable_points = $cashable, 
                                  is_closed = 1, closed_by = {$_SESSION['user_id']}, closed_at = NOW()
                              WHERE employee_id = $employee_id AND year = $year AND month = $month";
            mysqli_query($connection, $update_summary);
        } else {
            // Insert new
            $insert_summary = "INSERT INTO monthly_point_summary 
                              (employee_id, year, month, total_points, cashable_points, is_closed, closed_by, closed_at)
                              VALUES ($employee_id, $year, $month, $total, $cashable, 1, {$_SESSION['user_id']}, NOW())";
            mysqli_query($connection, $insert_summary);
        }
    }
    
    $_SESSION['success_message'] = "Month $month/$year closed successfully!";
    header("Location: points_ledger.php");
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
                <li class="breadcrumb-item active">Points Ledger</li>
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
                    $source = 'view_ledger';
                }

                switch($source) {
                    case 'view_ledger';
                        include "includes/view_points_ledger.php";
                        break;
                    case 'monthly_summary';
                        include "includes/monthly_point_summary.php";
                        break;
                    case 'quarterly_payout';
                        include "includes/quarterly_payout.php";
                        break;
                    case 'employee_wallet';
                        include "includes/employee_wallet.php";
                        break;
                    case 'manual_adjustment';
                        include "includes/manual_point_adjustment.php";
                        break;
                    default:
                        include "includes/view_points_ledger.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transaction details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View transaction details
function viewTransaction(id) {
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    const contentDiv = document.getElementById('transactionDetails');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading transaction details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_transaction_details.php?id=' + id)
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