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

// Placeholder: You can add annual CDP summary, stats, and actions for employees here.
?>
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-check me-2"></i>Annual CDP Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>This page will show your annual CDP records, uplifts, and performance summary. (Employee view)</p>
                        <!-- TODO: Add employee-specific annual CDP logic here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/operations_footer.php'; ?>
