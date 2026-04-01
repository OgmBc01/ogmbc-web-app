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
   HANDLE SERVICE DELETION
=================================*/
if (isset($_GET['delete'])) {
    $service_id = (int)$_GET['delete'];
    
    // Check if service is used in any engagements
    $check_query = "SELECT COUNT(*) as engagement_count FROM engagements WHERE service_id = $service_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['engagement_count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete service that is used in engagements.";
    } else {
        // First delete related point rules
        $delete_rules = "DELETE FROM service_point_rules WHERE service_id = $service_id";
        mysqli_query($connection, $delete_rules);
        
        // Then delete the service
        $delete_query = "DELETE FROM service_types WHERE service_id = $service_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Service deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting service: " . mysqli_error($connection);
        }
    }
    
    header("Location: services.php");
    exit();
}

/* ===============================
   HANDLE RULE DELETION
=================================*/
if (isset($_GET['delete_rule'])) {
    $rule_id = (int)$_GET['delete_rule'];
    
    // Check if rule is used in any engagements
    $check_query = "SELECT COUNT(*) as engagement_count FROM engagements WHERE rule_version_id = $rule_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['engagement_count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete rule that is used in engagements.";
    } else {
        $delete_query = "DELETE FROM service_point_rules WHERE rule_id = $rule_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Point rule deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting rule: " . mysqli_error($connection);
        }
    }
    
    header("Location: services.php");
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
                <li class="breadcrumb-item active">Services Config</li>
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
                    case 'add_service';
                        include "includes/add_service.php";
                        break;
                    case 'edit_service';
                        include "includes/edit_service.php";
                        break;
                    case 'add_rule';
                        include "includes/add_service_rule.php";
                        break;
                    case 'edit_rule';
                        include "includes/edit_service_rule.php";
                        break;
                    default:
                        include "includes/view_all_services.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="serviceDetailsModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Service Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="serviceDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading service details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
                <a href="#" id="editServiceBtn" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i>Edit Service
                </a>
                <a href="#" id="addRuleBtn" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Add Rule
                </a>
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
                <p>Are you sure you want to delete <span id="deleteItemName"></span>?</p>
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
let deleteId = null;
let deleteType = null; // 'service' or 'rule'

// Show delete confirmation modal
function confirmDelete(id, name, type) {
    deleteId = id;
    deleteType = type;
    document.getElementById('deleteItemName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Handle delete confirmation with AJAX
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!deleteId || !deleteType) return;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    // Show loading state
    showAlert('Deleting...', 'info');
    
    let url = '';
    let fetchOptions = {};
    if (deleteType === 'service') {
        url = 'includes/ajax/delete_service.php';
        fetchOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'service_id=' + encodeURIComponent(deleteId)
        };
    } else {
        url = 'includes/ajax/delete_service_rule.php';
        fetchOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'rule_id=' + encodeURIComponent(deleteId)
        };
    }

    // Send AJAX request
    fetch(url, fetchOptions)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Remove the deleted row from table
            const row = document.getElementById(deleteType + '-row-' + deleteId);
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

// View service details
function viewService(id) {
    const modal = new bootstrap.Modal(document.getElementById('serviceDetailsModal'));
    const contentDiv = document.getElementById('serviceDetailsContent');
    const editBtn = document.getElementById('editServiceBtn');
    const addRuleBtn = document.getElementById('addRuleBtn');
    
    // Show loading state
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading service details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch service details
    fetch('includes/ajax/get_service_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
                editBtn.href = 'services.php?source=edit_service&id=' + id;
                addRuleBtn.href = 'services.php?source=add_rule&service_id=' + id;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading service details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading service details: ${error.message}
                </div>
            `;
        });
}

// Escape HTML helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include "includes/footer.php"; ?>