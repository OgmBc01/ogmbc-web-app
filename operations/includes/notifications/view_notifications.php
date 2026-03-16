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

<!-- Statistics Cards - Matching clients.php style -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-bell text-primary"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Total</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-envelope-open text-warning"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['unread'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Unread</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['success'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Success</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['danger'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Urgent</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Type Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="type-card info">
            <div class="d-flex align-items-center">
                <div class="type-icon bg-info-soft">
                    <i class="bi bi-info-circle text-info"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Info</h6>
                    <span class="type-count"><?php echo $stats['info'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="type-card success">
            <div class="d-flex align-items-center">
                <div class="type-icon bg-success-soft">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Success</h6>
                    <span class="type-count"><?php echo $stats['success'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="type-card warning">
            <div class="d-flex align-items-center">
                <div class="type-icon bg-warning-soft">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Warning</h6>
                    <span class="type-count"><?php echo $stats['warning'] ?? 0; ?></span>
                </div>
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
                            <?php echo htmlspecialchars(substr($notif['message'], 0, 200)) . (strlen($notif['message']) > 200 ? '...' : ''); ?>
                        </p>
                        <div class="notification-meta">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                            </small>
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn btn-sm btn-link p-0 ms-3">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="notification-actions">
                        <div class="btn-group btn-group-sm">
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

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card gradient-bg">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Notification Tips
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>🔔 Unread notifications are highlighted with a yellow badge</li>
                        <li>✅ Mark notifications as read to keep your list organized</li>
                        <li>📅 Use date filters to find notifications from specific periods</li>
                        <li>⚡ Urgent notifications appear in red - prioritize them!</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-bell display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Stat Cards - Matching clients.php */
.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border-left: 6px solid #e0e0e0;
    padding: 0;
    margin-bottom: 0;
    transition: box-shadow 0.2s;
    height: 100%;
}
.stat-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-success { border-left-color: #38c172; }
.stat-card-danger { border-left-color: #dc3545; }

.stat-card-body {
    padding: 24px 20px;
    display: flex;
    align-items: center;
}
.stat-icon {
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: #f5f6fa;
    border-radius: 50%;
    flex-shrink: 0;
}
.stat-value {
    font-size: 2.1rem;
    font-weight: 700;
    color: #222;
    line-height: 1.2;
}
.stat-label {
    font-size: 1rem;
    color: #888;
    margin-top: 2px;
}

/* Type Cards */
.type-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    height: 100%;
}
.type-card.info { border-left: 6px solid #17a2b8; }
.type-card.success { border-left: 6px solid #38c172; }
.type-card.warning { border-left: 6px solid #ffc107; }

.type-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-success-soft { background: rgba(56, 193, 114, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }

.type-count {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

/* Dark Header */
.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
}
.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

/* Notifications List */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    display: flex;
    gap: 15px;
}

.notification-item:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.notification-item.unread {
    border-left: 6px solid #f1bf70;
    background: #fff9f0;
}

.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
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
    font-size: 1.1rem;
    margin-bottom: 8px;
}

.notification-message {
    color: #6c757d;
    margin-bottom: 8px;
    font-size: 0.95rem;
    line-height: 1.5;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

/* Pro Tip Card - Gradient */
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.18);
    padding: 28px 24px;
    margin-bottom: 24px;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.7);
}
.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.empty-state i {
    color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card-body { 
        padding: 16px 10px; 
    }
    .stat-icon { 
        width: 40px; 
        height: 40px; 
        font-size: 1.3rem; 
    }
    .stat-value { 
        font-size: 1.3rem; 
    }
    .notification-item {
        flex-direction: column;
    }
    .notification-actions {
        align-self: flex-end;
    }
    .notification-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    .type-card {
        padding: 15px;
    }
    .type-count {
        font-size: 1.2rem;
    }
}
</style>