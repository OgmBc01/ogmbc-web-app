<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// IMPORTANT: Uncomment this session check!
if (!isset($_SESSION['client_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$client_id = $_SESSION['client_id'];
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Engagements</li>
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
                    case 'view_details':
                        include "includes/view_engagement_details.php";
                        break;
                    case 'upload_file';
                        include "includes/upload_file.php";
                        break;
                    case 'submit_feedback';
                        include "includes/submit_feedback.php";
                        break;
                    default:
                        include "includes/view_engagements.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Engagement Details Modal -->
<div class="modal fade" id="engagementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title">Engagement Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="engagementDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="uploadFileBtn" class="btn btn-success">Upload File</a>
                <a href="#" id="feedbackBtn" class="btn btn-warning">Submit Feedback</a>
            </div>
        </div>
    </div>
</div>

<script>
function viewEngagement(id) {
    const modal = new bootstrap.Modal(document.getElementById('engagementModal'));
    document.getElementById('engagementDetails').innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>`;
    modal.show();
    
    fetch('includes/ajax/get_engagement_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('engagementDetails').innerHTML = data.html;
                document.getElementById('uploadFileBtn').href = 'engagements.php?source=upload_file&id=' + id;
                document.getElementById('feedbackBtn').href = 'engagements.php?source=submit_feedback&id=' + id;
            } else {
                document.getElementById('engagementDetails').innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        });
}
</script>

<?php include 'includes/client_footer.php'; ?>