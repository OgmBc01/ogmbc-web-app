<?php
// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$read_filter = isset($_GET['read']) ? $_GET['read'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["user_id = $user_id"];

if (!empty($type_filter)) {
    $where[] = "type = '" . mysqli_real_escape_string($connection, $type_filter) . "'";
}
if ($read_filter === 'read') {
    $where[] = "is_read = 1";
} elseif ($read_filter === 'unread') {
    $where[] = "is_read = 0";
}
if (!empty($date_from)) {
    $where[] = "DATE(created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN type = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN type = 'warning' THEN 1 ELSE 0 END) as warning,
                SUM(CASE WHEN type = 'danger' THEN 1 ELSE 0 END) as danger,
                SUM(CASE WHEN type = 'info' THEN 1 ELSE 0 END) as info
                FROM user_notifications 
                WHERE user_id = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get notifications
$notifications_query = "SELECT * FROM user_notifications 
                        WHERE $where_clause
                        ORDER BY is_read ASC, created_at DESC";
$notifications_result = mysqli_query($connection, $notifications_query);
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card notifications-welcome">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="welcome-title">
                            <i class="bi bi-bell me-2"></i>Notifications
                        </h2>
                        <p class="welcome-subtitle">
                            Stay updated with your latest activities and alerts.
                            <?php if ($stats['unread'] > 0): ?>
                                <span class="unread-badge">🔔 <?php echo $stats['unread']; ?> unread</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="current-date">
                            <i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-primary text-white">
                <div class="stat-icon">
                    <i class="bi bi-bell"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-warning text-white">
                <div class="stat-icon">
                    <i class="bi bi-envelope-open"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['unread'] ?? 0; ?></h3>
                    <p class="stat-label">Unread</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-success text-white">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['success'] ?? 0; ?></h3>
                    <p class="stat-label">Success</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-info text-white">
                <div class="stat-icon">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['info'] ?? 0; ?></h3>
                    <p class="stat-label">Info</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-warning text-white">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['warning'] ?? 0; ?></h3>
                    <p class="stat-label">Warning</p>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card-small bg-danger text-white">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['danger'] ?? 0; ?></h3>
                    <p class="stat-label">Danger</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-funnel me-2"></i>Filter Notifications
            </h5>
            <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="info" <?php echo $type_filter == 'info' ? 'selected' : ''; ?>>Info</option>
                            <option value="success" <?php echo $type_filter == 'success' ? 'selected' : ''; ?>>Success</option>
                            <option value="warning" <?php echo $type_filter == 'warning' ? 'selected' : ''; ?>>Warning</option>
                            <option value="danger" <?php echo $type_filter == 'danger' ? 'selected' : ''; ?>>Danger</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="read" class="form-select">
                            <option value="">All</option>
                            <option value="unread" <?php echo $read_filter == 'unread' ? 'selected' : ''; ?>>Unread</option>
                            <option value="read" <?php echo $read_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="notifications.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <?php if (($stats['total'] ?? 0) > 0): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="text-muted">Showing <?php echo mysqli_num_rows($notifications_result); ?> of <?php echo $stats['total']; ?> notifications</span>
        </div>
        <div class="btn-group">
            <?php if ($stats['unread'] > 0): ?>
                <a href="notifications.php?mark_all_read=1" class="btn btn-sm btn-success" onclick="return confirm('Mark all notifications as read?')">
                    <i class="bi bi-check-all me-1"></i>Mark All Read
                </a>
            <?php endif; ?>
            <a href="notifications.php?delete_all=1" class="btn btn-sm btn-danger" onclick="return confirm('Delete all notifications? This cannot be undone.')">
                <i class="bi bi-trash me-1"></i>Delete All
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notifications List -->
    <?php if ($notifications_result && mysqli_num_rows($notifications_result) > 0): ?>
        <div class="notifications-list">
            <?php while($notif = mysqli_fetch_assoc($notifications_result)): 
                $icon = 'info-circle';
                $bg_class = 'info-soft';
                $text_class = 'info';
                
                switch($notif['type']) {
                    case 'success':
                        $icon = 'check-circle';
                        $bg_class = 'success-soft';
                        $text_class = 'success';
                        break;
                    case 'warning':
                        $icon = 'exclamation-triangle';
                        $bg_class = 'warning-soft';
                        $text_class = 'warning';
                        break;
                    case 'danger':
                        $icon = 'x-circle';
                        $bg_class = 'danger-soft';
                        $text_class = 'danger';
                        break;
                    default:
                        $icon = 'info-circle';
                        $bg_class = 'info-soft';
                        $text_class = 'info';
                }
            ?>
            <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" id="notification-<?php echo $notif['notif_id']; ?>">
                <div class="notification-icon bg-<?php echo $bg_class; ?>">
                    <i class="bi bi-<?php echo $icon; ?> text-<?php echo $text_class; ?>"></i>
                </div>
                <div class="notification-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="notification-title">
                                <?php echo htmlspecialchars($notif['title']); ?>
                                <?php if (!$notif['is_read']): ?>
                                    <span class="badge bg-warning ms-2">New</span>
                                <?php endif; ?>
                            </h6>
                            <p class="notification-message">
                                <?php echo htmlspecialchars(substr($notif['message'], 0, 150)) . (strlen($notif['message']) > 150 ? '...' : ''); ?>
                            </p>
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View Details
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="notification-meta text-end">
                            <small class="text-muted d-block">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                            </small>
                            <div class="btn-group btn-group-sm mt-2">
                                <?php if (!$notif['is_read']): ?>
                                    <a href="notifications.php?mark_read=<?php echo $notif['notif_id']; ?>" class="btn btn-outline-success" title="Mark as Read">
                                        <i class="bi bi-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="notifications.php?delete=<?php echo $notif['notif_id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this notification?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-bell display-1 text-muted"></i>
            <h5 class="mt-3">No Notifications Found</h5>
            <p class="text-muted">You're all caught up! No notifications match your criteria.</p>
            <?php if ($type_filter || $read_filter || $date_from || $date_to): ?>
                <a href="notifications.php" class="btn btn-primary mt-3">Clear Filters</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.notifications-welcome {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.unread-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.9rem;
    margin-left: 10px;
    display: inline-block;
}

.stat-card-small {
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-card-small .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.stat-card-small .stat-value {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 2px;
    line-height: 1.2;
}

.stat-card-small .stat-label {
    font-size: 0.7rem;
    opacity: 0.9;
    margin: 0;
}

.dark-header {
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    transition: all 0.3s ease;
    display: flex;
    gap: 15px;
}

.notification-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.notification-item.unread {
    border-left: 4px solid #f1bf70;
    background: #fff9f0;
}

.notification-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 1rem;
    margin-bottom: 5px;
}

.notification-message {
    color: #6c757d;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.notification-meta {
    min-width: 180px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

/* Responsive */
@media (max-width: 768px) {
    .notification-item {
        flex-direction: column;
    }
    
    .notification-meta {
        text-align: left !important;
        min-width: auto;
    }
    
    .notification-meta .btn-group {
        justify-content: flex-start;
    }
    
    .stat-card-small {
        padding: 10px;
    }
    
    .stat-card-small .stat-value {
        font-size: 1rem;
    }
}
</style>