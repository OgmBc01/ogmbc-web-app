<?php
include 'includes/sales_header.php';
include 'includes/sales_nav.php';
include 'includes/sales_sidebar.php';

// Set user_id from session (operations employee)
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
?>

<div class="container-fluid">
    <h1 class="mt-4">Sales Dashboard</h1>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title">Today's Sales</h5>
                    <p class="card-text display-4">
                </div>
            </div>
        </div>
    </div>
</div>       

<?php include 'includes/sales_footer.php'; ?>