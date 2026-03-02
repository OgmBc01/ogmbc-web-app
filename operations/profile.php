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
                <li class="breadcrumb-item active">My Profile</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view';
                }

                switch($source) {
                    case 'edit':
                        include "includes/profile/edit_profile.php";
                        break;
                    case 'password':
                        include "includes/profile/change_password.php";
                        break;
                    case 'activity':
                        include "includes/profile/view_activity.php";
                        break;
                    case 'settings':
                        include "includes/profile/settings.php";
                        break;
                    default:
                        include "includes/profile/view_profile.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/operations_footer.php'; ?>