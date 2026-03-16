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
        
        <!-- Welcome Card (matching clients.php) -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">
                            <i class="bi bi-bell me-2"></i>Notifications
                        </div>
                        <div class="welcome-subtitle">Stay updated with your latest activities and alerts.</div>
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
                <li class="breadcrumb-item active">Notifications</li>
            </ol>
        </nav>

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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="notificationModalLabel">
                    <i class="bi bi-bell me-2"></i>Notification Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading notification details...</p>
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
    const markReadBtn = document.getElementById('markReadFromModal');
    const deleteBtn = document.getElementById('deleteFromModal');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading notification details...</p>
        </div>
    `;
    
    markReadBtn.href = 'notifications.php?mark_read=' + id;
    deleteBtn.href = 'notifications.php?delete=' + id;
    
    modal.show();
    
    // For now, just show basic info
    setTimeout(() => {
        contentDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Detailed view coming soon.
            </div>
        `;
    }, 500);
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
        setTimeout(() => div.remove(), 3000);
    }
}
</script>

<!-- Dashboard Theme Styles (matching clients.php) -->
<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    width: 100%;
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
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    .welcome-card {
        padding: 18px;
    }
}
</style>

<?php include 'includes/operations_footer.php'; ?>