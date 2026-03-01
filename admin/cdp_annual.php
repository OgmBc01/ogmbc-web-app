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

// Check permissions
$is_hr_admin = ($user_role == 'hr_admin' || $user_role == 'ceo_gm' || $user_role == 'admin_staff');
$is_employee = !$is_hr_admin;

/* ===============================
   HANDLE CDP APPROVAL/REJECTION
=================================*/
if (isset($_GET['approve_cdp']) && $is_hr_admin) {
    $cdp_id = (int)$_GET['approve_cdp'];
    
    $update_query = "UPDATE cdp_records SET 
                    status = 'APPROVED',
                    approved_by = {$_SESSION['user_id']},
                    approved_at = NOW()
                    WHERE cdp_id = $cdp_id";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "CDP record approved successfully!";
    } else {
        $_SESSION['error_message'] = "Error approving CDP record: " . mysqli_error($connection);
    }
    
    header("Location: cdp_annual.php");
    exit();
}

if (isset($_GET['reject_cdp']) && $is_hr_admin) {
    $cdp_id = (int)$_GET['reject_cdp'];
    
    $update_query = "UPDATE cdp_records SET 
                    status = 'REJECTED',
                    approved_by = {$_SESSION['user_id']},
                    approved_at = NOW()
                    WHERE cdp_id = $cdp_id";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "CDP record rejected.";
    } else {
        $_SESSION['error_message'] = "Error rejecting CDP record: " . mysqli_error($connection);
    }
    
    header("Location: cdp_annual.php");
    exit();
}

/* ===============================
   HANDLE ANNUAL PERFORMANCE APPROVAL
=================================*/
if (isset($_GET['approve_performance']) && $is_hr_admin) {
    $performance_id = (int)$_GET['approve_performance'];
    
    $update_query = "UPDATE annual_performance SET 
                    status = 'APPROVED',
                    approved_by = {$_SESSION['user_id']},
                    approved_at = NOW()
                    WHERE performance_id = $performance_id";
    
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "Annual performance approved successfully!";
    } else {
        $_SESSION['error_message'] = "Error approving annual performance: " . mysqli_error($connection);
    }
    
    header("Location: cdp_annual.php");
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
                <li class="breadcrumb-item active">CDP & Annual Performance</li>
            </ol>
        </nav>

        <!-- Alert Messages Container for AJAX -->
        <div id="alertBox"></div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="cdpTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'cdp') ? 'active' : ''; ?>" 
                        id="cdp-tab" data-bs-toggle="tab" data-bs-target="#cdp" type="button" role="tab">
                    <i class="bi bi-mortarboard me-2"></i>CDP Records
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'annual') ? 'active' : ''; ?>" 
                        id="annual-tab" data-bs-toggle="tab" data-bs-target="#annual" type="button" role="tab">
                    <i class="bi bi-graph-up me-2"></i>Annual Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'bands') ? 'active' : ''; ?>" 
                        id="bands-tab" data-bs-toggle="tab" data-bs-target="#bands" type="button" role="tab">
                    <i class="bi bi-bar-chart me-2"></i>Salary Bands
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="cdpTabsContent">
            <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'cdp') ? 'show active' : ''; ?>" id="cdp" role="tabpanel">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_cdp';
                }

                switch($source) {
                    case 'add_cdp';
                        include "includes/add_cdp_record.php";
                        break;
                    case 'edit_cdp';
                        include "includes/edit_cdp_record.php";
                        break;
                    default:
                        include "includes/view_cdp_records.php";
                        break;
                }
                ?>
            </div>
            
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'annual') ? 'show active' : ''; ?>" id="annual" role="tabpanel">
                <?php
                $annual_source = isset($_GET['annual_source']) ? $_GET['annual_source'] : 'view_annual';
                
                switch($annual_source) {
                    case 'calculate';
                        include "includes/calculate_annual_performance.php";
                        break;
                    default:
                        include "includes/view_annual_performance.php";
                        break;
                }
                ?>
            </div>
            
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'bands') ? 'show active' : ''; ?>" id="bands" role="tabpanel">
                <?php include "includes/view_salary_bands.php"; ?>
            </div>
        </div>
    </div>
</div>

<!-- CDP Details Modal -->
<div class="modal fade" id="cdpDetailsModal" tabindex="-1" aria-labelledby="cdpDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="cdpDetailsModalLabel">CDP Record Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cdpDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading CDP details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Performance Details Modal -->
<div class="modal fade" id="performanceModal" tabindex="-1" aria-labelledby="performanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="performanceModalLabel">Performance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="performanceContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading performance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View CDP details
function viewCDP(id) {
    const modal = new bootstrap.Modal(document.getElementById('cdpDetailsModal'));
    const contentDiv = document.getElementById('cdpDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading CDP details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_cdp_details.php?id=' + id)
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

// View Performance details
function viewPerformance(id) {
    const modal = new bootstrap.Modal(document.getElementById('performanceModal'));
    const contentDiv = document.getElementById('performanceContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading performance details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_performance_details.php?id=' + id)
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