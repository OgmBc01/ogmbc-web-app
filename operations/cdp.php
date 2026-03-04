<?php
include 'includes/operations_header.php';
include 'includes/operations_nav.php';
include 'includes/operations_sidebar.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Career Development</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all';
                }

                switch($source) {
                    case 'view':
                        include "includes/cdp/view_cdp_details.php";
                        break;
                    case 'add':
                        include "includes/cdp/add_cdp_record.php";
                        break;
                    case 'edit':
                        include "includes/cdp/edit_cdp_record.php";
                        break;
                    case 'approvals':
                        include "includes/cdp/view_cdp_approvals.php";
                        break;
                    case 'uplift':
                        include "includes/cdp/view_uplift_summary.php";
                        break;
                    default:
                        include "includes/cdp/view_cdp_records.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- CDP Details Modal -->
<div class="modal fade" id="cdpDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-mortarboard me-2"></i>CDP Record Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cdpDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading record details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editCdpBtn" class="btn btn-warning">Edit Record</a>
            </div>
        </div>
    </div>
</div>

<script>
// View CDP details
function viewCDP(id) {
    const modal = new bootstrap.Modal(document.getElementById('cdpDetailsModal'));
    const contentDiv = document.getElementById('cdpDetailsContent');
    const editBtn = document.getElementById('editCdpBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading record details...</p>
        </div>
    `;
    
    editBtn.href = 'cdp.php?source=edit&id=' + id;
    
    modal.show();
    
    fetch('includes/ajax/get_cdp_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading record details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading record details: ${error.message}
                </div>
            `;
        });
}

// Delete confirmation
function confirmDelete(id, title) {
    if (confirm('Are you sure you want to delete this CDP record? This action cannot be undone.')) {
        window.location.href = 'includes/ajax/delete_cdp_record.php?id=' + id;
    }
}
</script>

<?php include 'includes/operations_footer.php'; ?>