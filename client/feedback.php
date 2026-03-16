<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// Set client_id from session (user_id for clients)
$client_id = $_SESSION['user_id'];
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Feedback & Reviews</li>
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
                    case 'submit';
                        include "includes/submit_feedback.php";
                        break;
                    default:
                        include "includes/view_feedback.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/client_footer.php'; ?>