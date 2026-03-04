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
   HANDLE ENGAGEMENT DELETION
=================================*/
if (isset($_GET['delete'])) {
    $engagement_id = (int)$_GET['delete'];
    
    // Check if engagement has evidence or ledger entries
    $check_evidence = "SELECT COUNT(*) as count FROM evidence WHERE engagement_id = $engagement_id";
    $evidence_result = mysqli_query($connection, $check_evidence);
    $evidence_row = mysqli_fetch_assoc($evidence_result);
    
    $check_ledger = "SELECT COUNT(*) as count FROM points_ledger WHERE source_type = 'ENGAGEMENT' AND source_id = $engagement_id";
    $ledger_result = mysqli_query($connection, $check_ledger);
    $ledger_row = mysqli_fetch_assoc($ledger_result);
    
    if ($evidence_row['count'] > 0 || $ledger_row['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete engagement with evidence or points awarded.";
    } else {
        // Delete deadline change requests first
        $delete_requests = "DELETE FROM deadline_change_requests WHERE engagement_id = $engagement_id";
        mysqli_query($connection, $delete_requests);
        
        // Delete status history
        $delete_history = "DELETE FROM engagement_status_history WHERE engagement_id = $engagement_id";
        mysqli_query($connection, $delete_history);
        
        // Delete engagement
        $delete_query = "DELETE FROM engagements WHERE engagement_id = $engagement_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Engagement deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting engagement: " . mysqli_error($connection);
        }
    }
    
    header("Location: engagements.php");
    exit();
}

/* ===============================
   HANDLE DEADLINE CHANGE APPROVAL/REJECTION
=================================*/
if (isset($_GET['approve_request'])) {
    $request_id = (int)$_GET['approve_request'];
    
    // Get request details
    $request_query = "SELECT * FROM deadline_change_requests WHERE request_id = $request_id";
    $request_result = mysqli_query($connection, $request_query);
    $request = mysqli_fetch_assoc($request_result);
    
    if ($request) {
        // Update engagement with approved deadline
        $update_engagement = "UPDATE engagements SET approved_deadline = '{$request['requested_date']}' WHERE engagement_id = {$request['engagement_id']}";
        mysqli_query($connection, $update_engagement);
        
        // Update request status
        $update_request = "UPDATE deadline_change_requests SET status = 'APPROVED', reviewed_by = {$_SESSION['user_id']}, reviewed_at = NOW() WHERE request_id = $request_id";
        mysqli_query($connection, $update_request);
        
        $_SESSION['success_message'] = "Deadline change approved successfully!";
    }
    header("Location: engagements.php");
    exit();
}

if (isset($_GET['reject_request'])) {
    $request_id = (int)$_GET['reject_request'];
    
    $update_request = "UPDATE deadline_change_requests SET status = 'REJECTED', reviewed_by = {$_SESSION['user_id']}, reviewed_at = NOW() WHERE request_id = $request_id";
    mysqli_query($connection, $update_request);
    
    $_SESSION['success_message'] = "Deadline change rejected.";
    header("Location: engagements.php");
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
                <li class="breadcrumb-item active">Engagements</li>
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
                    case 'add_engagement';
                        include "includes/add_engagement.php";
                        break;
                    case 'edit_engagement';
                        include "includes/edit_engagement.php";
                        break;
                    case 'view_engagement';
                        include "includes/view_engagement.php";
                        break;
                    case 'upload_evidence';
                        include "includes/upload_evidence.php";
                        break;
                    case 'request_deadline_change';
                        include "includes/request_deadline_change.php";
                        break;
                    default:
                        include "includes/view_all_engagements.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Engagement Details Modal -->
<div class="modal fade" id="engagementDetailsModal" tabindex="-1" aria-labelledby="engagementDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white border-bottom border-secondary" style="border-top-left-radius: .5rem; border-top-right-radius: .5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h5 class="modal-title" id="engagementDetailsModalLabel">
                    <i class="bi bi-briefcase me-2"></i>Engagement Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="engagementDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading engagement details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editEngagementBtn" class="btn btn-warning">Edit</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: .5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.12);">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete engagement: <strong><span id="deleteEngagementTitle"></span></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. Evidence and points will be affected.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

// Show delete confirmation modal
function confirmDelete(id, title) {
    deleteId = id;
    document.getElementById('deleteEngagementTitle').textContent = title;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Handle delete confirmation with AJAX
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!deleteId) return;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    // Show loading state
    showAlert('Deleting engagement...', 'info');
    
    // Send AJAX request
    fetch('includes/ajax/delete_engagement.php?id=' + deleteId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Remove the deleted row from table
            const row = document.getElementById('engagement-row-' + deleteId);
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

// View engagement details
function viewEngagement(id) {
    const modal = new bootstrap.Modal(document.getElementById('engagementDetailsModal'));
    const contentDiv = document.getElementById('engagementDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading engagement details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_engagement_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
                document.getElementById('editEngagementBtn').href = 'engagements.php?source=edit_engagement&id=' + id;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

// Load employees based on department selection
function loadEmployeesByDepartment(departmentId, targetSelectId, selectedEmployeeId = null) {
    if (!departmentId) {
        document.getElementById(targetSelectId).innerHTML = '<option value="">Select Department First</option>';
        return;
    }
    
    fetch('includes/ajax/get_employees_by_department.php?dept_id=' + departmentId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Employee</option>';
            if (data.success && data.employees.length > 0) {
                data.employees.forEach(emp => {
                    const selected = (selectedEmployeeId && emp.user_id == selectedEmployeeId) ? 'selected' : '';
                    options += `<option value="${emp.user_id}" ${selected}>${emp.first_name} ${emp.last_name}</option>`;
                });
            } else {
                options = '<option value="">No employees in this department</option>';
            }
            document.getElementById(targetSelectId).innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
}
</script>

<?php include "includes/footer.php"; ?>