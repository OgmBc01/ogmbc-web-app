<?php
// Check if notification ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'notifications.php';</script>";
    exit();
}

$notif_id = (int)$_GET['id'];

// Get notification details
$query = "SELECT * FROM user_notifications WHERE notif_id = $notif_id AND user_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'notifications.php';</script>";
    exit();
}

$notification = mysqli_fetch_assoc($result);

// Mark as read when viewed
if (!$notification['is_read']) {
    $update_query = "UPDATE user_notifications SET is_read = 1 WHERE notif_id = $notif_id";
    mysqli_query($connection, $update_query);
}

// Set styles based on type
$bg_class = 'info';
$icon = 'info-circle';
switch($notification['type']) {
    case 'success':
        $bg_class = 'success';
        $icon = 'check-circle';
        break;
    case 'warning':
        $bg_class = 'warning';
        $icon = 'exclamation-triangle';
        break;
    case 'danger':
        $bg_class = 'danger';
        $icon = 'x-circle';
        break;
    default:
        $bg_class = 'info';
        $icon = 'info-circle';
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="bi bi-bell me-2"></i>Notification Details
                </h4>
                <a href="notifications.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Notifications
                </a>
            </div>

            <!-- Notification Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-<?php echo $bg_class; ?> text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-<?php echo $icon; ?> fs-3 me-3"></i>
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h5>
                            <small>
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('F d, Y \a\t h:i A', strtotime($notification['created_at'])); ?>
                                <?php if ($notification['is_read']): ?>
                                    <span class="ms-3"><i class="bi bi-check-circle"></i> Read</span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="notification-message p-4 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                    </div>

                    <?php if (!empty($notification['link'])): ?>
                    <div class="mt-4">
                        <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="btn btn-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Go to Related Page
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-tag me-1"></i>Type: <?php echo ucfirst($notification['type']); ?>
                        </small>
                        <div>
                            <a href="notifications.php?delete=<?php echo $notif_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notification?')">
                                <i class="bi bi-trash me-1"></i>Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-header.bg-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
.card-header.bg-warning { background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); }
.card-header.bg-danger { background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%); }
.card-header.bg-info { background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); }

.notification-message {
    font-size: 1rem;
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>