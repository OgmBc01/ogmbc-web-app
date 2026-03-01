<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// if (!isset($_SESSION['client_id'])) {
//     echo "<script>window.location.href = '../login.php';</script>";
//     exit();
// }

$client_id = $_SESSION['client_id'];
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
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
                    case 'edit';
                        include "includes/edit_profile.php";
                        break;
                    case 'password';
                        include "includes/change_password.php";
                        break;
                    case 'activity';
                        include "includes/view_activity.php";
                        break;
                    default:
                        include "includes/view_profile.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/client_footer.php'; ?>