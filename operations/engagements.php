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
        <!-- Welcome Card (optional, for consistency) -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3"></nav>
                    <div>
                        <div class="welcome-title mb-1">Engagements</div>
                        <div class="welcome-subtitle">Manage and track all your assigned engagements here.</div>
                    </div>
                    <div class="current-date mt-3 mt-md-0">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Engagements</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="bi bi-briefcase me-2"></i>Engagements</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($_GET['source'])) {
                            $source = $_GET['source'];
                        } else {
                            $source = 'view_all';
                        }

                        switch($source) {
                            case 'view':
                                include "includes/engagements/view_engagement_details.php";
                                break;
                            case 'update_status':
                                include "includes/engagements/update_engagement_status.php";
                                break;
                            case 'upload_evidence':
                                include "includes/engagements/upload_evidence.php";
                                break;
                            case 'request_deadline':
                                include "includes/engagements/request_deadline_change.php";
                                break;
                            default:
                                include "includes/engagements/view_engagements.php";
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Engagement Details Modal -->
<div class="modal fade" id="engagementDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content dashboard-card">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle me-2"></i>Engagement Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="engagementDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading engagement details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="updateStatusBtn" class="btn btn-warning">Update Status</a>
                <a href="#" id="uploadEvidenceBtn" class="btn btn-success">Upload Evidence</a>
            </div>
        </div>
    </div>
</div>


<!-- Success Modal Template (will be shown after actions) -->
<div class="modal fade" id="actionSuccessModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content dashboard-card">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3" id="successMessage">Action completed successfully!</h5>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
</div>

<script>
// View engagement details
function viewEngagement(id) {
    const modal = new bootstrap.Modal(document.getElementById('engagementDetailsModal'));
    const contentDiv = document.getElementById('engagementDetailsContent');
    const updateBtn = document.getElementById('updateStatusBtn');
    const uploadBtn = document.getElementById('uploadEvidenceBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading engagement details...</p>
        </div>
    `;
    
    updateBtn.href = 'engagements.php?source=update_status&id=' + id;
    uploadBtn.href = 'engagements.php?source=upload_evidence&id=' + id;
    
    modal.show();
    
    fetch('includes/ajax/get_engagement_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading engagement details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading engagement details: ${error.message}
                </div>
            `;
        });
}

// Show success modal
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('actionSuccessModal'));
    modal.show();
}
</script>

<!-- Dashboard Theme Styles (from operations_dashboard.php) -->
<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}
.welcome-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}
.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0;
}
.current-date {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
}
.dashboard-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}
.card-body {
    padding: 20px;
}
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    .card-header {
        padding: 15px;
    }
    .card-body {
        padding: 15px;
    }
    .welcome-card {
        padding: 18px;
    }
}
</style>

<?php include 'includes/operations_footer.php'; ?>