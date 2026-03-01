<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    // Use JavaScript redirect instead of header()
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

// Get current user's role
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// Check if user has permission to view audit log (CEO, Admin, HR only)
$can_view_audit = ($user_role == 'ceo_gm' || $user_role == 'admin_staff' || $user_role == 'hr_admin');

// Role check disabled temporarily
// if (!$can_view_audit) {
//     $_SESSION['error_message'] = "You don't have permission to view audit logs.";
//     echo "<script>window.location.href = 'index.php';</script>";
//     exit();
// }

/* ===============================
   HANDLE LOG EXPORT - This is a file download, not a redirect
=================================*/
if (isset($_GET['export']) && $can_view_audit) {
    // Get filter parameters for export
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
    $user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
    $table_filter = isset($_GET['table_name']) ? $_GET['table_name'] : '';
    $action_filter = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Clear any previous output
    ob_clean();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Date', 'User', 'Action', 'Table', 'Record ID', 'Description', 'IP Address']);
    
    // Build query
    $where = ["DATE(created_at) BETWEEN '$date_from' AND '$date_to'"];
    if (!empty($user_filter)) {
        $where[] = "user_id = $user_filter";
    }
    if (!empty($table_filter)) {
        $where[] = "table_name = '$table_filter'";
    }
    if (!empty($action_filter)) {
        $where[] = "action = '$action_filter'";
    }
    
    $where_clause = implode(' AND ', $where);
    
    $query = "SELECT created_at, username, action, table_name, record_id, description, ip_address 
              FROM audit_log 
              WHERE $where_clause 
              ORDER BY created_at DESC";
    
    $result = mysqli_query($connection, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['created_at'],
            $row['username'],
            $row['action'],
            $row['table_name'],
            $row['record_id'],
            $row['description'],
            $row['ip_address']
        ]);
    }
    
    fclose($output);
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
                <li class="breadcrumb-item active">Audit Log</li>
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
                    $source = 'view_log';
                }

                switch($source) {
                    case 'view_log';
                    default:
                        include "includes/view_audit_log.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="logDetailsModalLabel">Audit Log Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading log details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View log details
function viewLog(id) {
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    const contentDiv = document.getElementById('logDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading log details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_log_details.php?id=' + id)
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