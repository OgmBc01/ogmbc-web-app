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

/* ===============================
   HANDLE MARK AS READ
=================================*/
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    
    $update_query = "UPDATE user_notifications SET is_read = 1 WHERE notif_id = $notif_id AND user_id = $user_id";
    mysqli_query($connection, $update_query);
    
    // Redirect back to notifications using JavaScript to avoid header issues
    echo "<script>window.location.href = 'notifications.php" . (isset($_GET['source']) ? '?source=' . $_GET['source'] : '') . "';</script>";
    exit();
}

/* ===============================
   HANDLE MARK ALL AS READ
=================================*/
if (isset($_GET['mark_all_read'])) {
    $update_query = "UPDATE user_notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0";
    mysqli_query($connection, $update_query);
    
    $_SESSION['success_message'] = "All notifications marked as read.";
    echo "<script>window.location.href = 'notifications.php';</script>";
    exit();
}

/* ===============================
   HANDLE DELETE NOTIFICATION
=================================*/
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notif_id = (int)$_GET['delete'];
    
    $delete_query = "DELETE FROM user_notifications WHERE notif_id = $notif_id AND user_id = $user_id";
    
    if (mysqli_query($connection, $delete_query)) {
        $_SESSION['success_message'] = "Notification deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting notification.";
    }
    
    echo "<script>window.location.href = 'notifications.php';</script>";
    exit();
}

/* ===============================
   HANDLE DELETE ALL
=================================*/
if (isset($_GET['delete_all'])) {
    $delete_query = "DELETE FROM user_notifications WHERE user_id = $user_id";
    
    if (mysqli_query($connection, $delete_query)) {
        $_SESSION['success_message'] = "All notifications deleted.";
    } else {
        $_SESSION['error_message'] = "Error deleting notifications.";
    }
    
    echo "<script>window.location.href = 'notifications.php';</script>";
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
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Notifications</li>
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
                    case 'settings':
                        include "includes/notifications/notification_settings.php";
                        break;
                    case 'view':
                        include "includes/notifications/view_notification_details.php";
                        break;
                    default:
                        include "includes/notifications/view_notifications.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="notificationModalHeader">
                <h5 class="modal-title" id="notificationModalLabel">
                    <i class="bi bi-bell me-2"></i>Notification Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationDetails">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading notification details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="markReadFromModal" class="btn btn-success">Mark as Read</a>
                <a href="#" id="deleteFromModal" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// View notification details
function viewNotification(id, type) {
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    const contentDiv = document.getElementById('notificationDetails');
    const modalHeader = document.getElementById('notificationModalHeader');
    const markReadBtn = document.getElementById('markReadFromModal');
    const deleteBtn = document.getElementById('deleteFromModal');
    
    // Set header color based on type
    modalHeader.className = 'modal-header';
    if (type === 'success') modalHeader.classList.add('bg-success', 'text-white');
    else if (type === 'warning') modalHeader.classList.add('bg-warning', 'text-dark');
    else if (type === 'danger') modalHeader.classList.add('bg-danger', 'text-white');
    else modalHeader.classList.add('bg-info', 'text-white');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading notification details...</p>
        </div>
    `;
    
    markReadBtn.href = 'notifications.php?mark_read=' + id;
    deleteBtn.href = 'notifications.php?delete=' + id;
    
    modal.show();
    
    // For now, just show basic info since we don't have an AJAX endpoint yet
    // We'll create the AJAX endpoint later if needed
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
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
</script>

<?php include 'includes/operations_footer.php'; ?>