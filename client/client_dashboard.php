<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// Get client_ids for this user (a user can have multiple clients)
$user_id = (int)$_SESSION['user_id'];
$client_ids = [];

// Get all client_ids associated with this user
$client_query = "SELECT client_id FROM clients WHERE user_id = $user_id";
$client_result = mysqli_query($connection, $client_query);
while ($client_row = mysqli_fetch_assoc($client_result)) {
    $client_ids[] = (int)$client_row['client_id'];
}

// If no clients found, try to find engagements directly by user_id (legacy)
if (empty($client_ids)) {
    // Fallback: check if there are engagements with assigned_to = user_id
    $check_engagements = "SELECT COUNT(*) as count FROM engagements WHERE assigned_to = $user_id LIMIT 1";
    $check_result = mysqli_query($connection, $check_engagements);
    $check = mysqli_fetch_assoc($check_result);
    
    if ($check['count'] > 0) {
        // Use a special flag to query by assigned_to instead
        $query_by_assigned = true;
    } else {
        // Last resort: use user_id as client_id
        $client_ids = [$user_id];
        $query_by_assigned = false;
    }
} else {
    $query_by_assigned = false;
}

// Convert client_ids array to comma-separated string for IN clause
$client_ids_str = !empty($client_ids) ? implode(',', $client_ids) : '0';

$today = date('Y-m-d');

// Debug: Log the client IDs (remove in production)
// error_log("User ID: $user_id, Client IDs: " . print_r($client_ids, true));

// ============================================
// DASHBOARD STATISTICS QUERIES
// ============================================

// 1. Active Engagements Count - Modified to handle multiple clients
if ($query_by_assigned) {
    // Query by assigned_to instead of client_id
    $active_engagements_query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review,
        SUM(CASE WHEN status = 'ASSIGNED' THEN 1 ELSE 0 END) as assigned
        FROM engagements 
    WHERE assigned_to = $user_id AND status != 'CLOSED'";
} else {
    // Query by client_id(s)
    $active_engagements_query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review,
        SUM(CASE WHEN status = 'ASSIGNED' THEN 1 ELSE 0 END) as assigned
        FROM engagements 
    WHERE client_id IN ($client_ids_str) AND status != 'CLOSED'";
}
$active_result = mysqli_query($connection, $active_engagements_query);
if (!$active_result) {
    error_log("MySQL Error: " . mysqli_error($connection));
    $active_stats = ['total' => 0, 'in_progress' => 0, 'awaiting_review' => 0, 'assigned' => 0];
} else {
    $active_stats = mysqli_fetch_assoc($active_result);
    if (!$active_stats) {
        $active_stats = ['total' => 0, 'in_progress' => 0, 'awaiting_review' => 0, 'assigned' => 0];
    }
}

// 2. Files Count - Modified to handle multiple clients
if ($query_by_assigned) {
    // For files, we need to get files from engagements assigned to this user
    $files_query = "SELECT COUNT(DISTINCT cf.file_id) as total 
                    FROM client_files cf
                    JOIN engagements e ON cf.client_id = e.client_id
                    WHERE e.assigned_to = $user_id";
} else {
    $files_query = "SELECT COUNT(*) as total FROM client_files WHERE client_id IN ($client_ids_str)";
}
$files_result = mysqli_query($connection, $files_query);
$files_count = ($files_result && ($row = mysqli_fetch_assoc($files_result))) ? ($row['total'] ?? 0) : 0;

// 3. Unread Notifications - Modified to handle multiple clients
if ($query_by_assigned) {
    $notifications_query = "SELECT COUNT(DISTINCT cn.notification_id) as total 
                            FROM client_notifications cn
                            JOIN engagements e ON cn.client_id = e.client_id
                            WHERE e.assigned_to = $user_id AND cn.is_read = 0";
} else {
    $notifications_query = "SELECT COUNT(*) as total FROM client_notifications 
                            WHERE client_id IN ($client_ids_str) AND is_read = 0";
}
$notifications_result = mysqli_query($connection, $notifications_query);
$unread_notifications = ($notifications_result && ($row = mysqli_fetch_assoc($notifications_result))) ? ($row['total'] ?? 0) : 0;

// 4. Recent Activity - Modified to handle multiple clients
if ($query_by_assigned) {
    $activity_query = "SELECT 
        'engagement' as type,
        e.title as description,
        e.updated_at as created_at,
        e.status as details,
        e.engagement_id
        FROM engagements e
        WHERE e.assigned_to = $user_id
        UNION ALL
        SELECT 
        'file' as type,
        CONCAT('File uploaded: ', cf.file_name) as description,
        cf.uploaded_at as created_at,
        cf.uploaded_by as details,
        NULL as engagement_id
        FROM client_files cf
        JOIN engagements e ON cf.client_id = e.client_id
        WHERE e.assigned_to = $user_id
        UNION ALL
        SELECT 
        'comment' as type,
        LEFT(c.comment, 50) as description,
        c.created_at,
        CONCAT('Comment on engagement #', c.engagement_id) as details,
        c.engagement_id
        FROM task_comments c
        JOIN engagements e ON c.engagement_id = e.engagement_id
        WHERE e.assigned_to = $user_id
        ORDER BY created_at DESC
        LIMIT 10";
} else {
    $activity_query = "SELECT 
        'engagement' as type,
        e.title as description,
        e.updated_at as created_at,
        e.status as details,
        e.engagement_id
        FROM engagements e
        WHERE e.client_id IN ($client_ids_str)
        UNION ALL
        SELECT 
        'file' as type,
        CONCAT('File uploaded: ', file_name) as description,
        uploaded_at as created_at,
        uploaded_by as details,
        NULL as engagement_id
        FROM client_files 
        WHERE client_id IN ($client_ids_str)
        UNION ALL
        SELECT 
        'comment' as type,
        LEFT(c.comment, 50) as description,
        c.created_at,
        CONCAT('Comment on engagement #', c.engagement_id) as details,
        c.engagement_id
        FROM task_comments c
        JOIN engagements e ON c.engagement_id = e.engagement_id
        WHERE e.client_id IN ($client_ids_str)
        ORDER BY created_at DESC
        LIMIT 10";
}
$activity_result = mysqli_query($connection, $activity_query);

// 5. Engagement Status Distribution - Modified to handle multiple clients
if ($query_by_assigned) {
    $status_query = "SELECT 
        status,
        COUNT(*) as count
        FROM engagements 
        WHERE assigned_to = $user_id
        GROUP BY status";
} else {
    $status_query = "SELECT 
        status,
        COUNT(*) as count
        FROM engagements 
        WHERE client_id IN ($client_ids_str)
        GROUP BY status";
}
$status_result = mysqli_query($connection, $status_query);

// 6. Monthly Engagement Trends - Modified to handle multiple clients
if ($query_by_assigned) {
    $monthly_query = "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
        FROM engagements 
        WHERE assigned_to = $user_id 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC";
} else {
    $monthly_query = "SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
        FROM engagements 
        WHERE client_id IN ($client_ids_str) 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC";
}
$monthly_result = mysqli_query($connection, $monthly_query);

// 7. Upcoming Deadlines - Modified to handle multiple clients
if ($query_by_assigned) {
    $upcoming_query = "SELECT 
        e.engagement_id,
        e.title,
        e.status,
        COALESCE(e.approved_deadline, e.original_deadline) as deadline,
        DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
        FROM engagements e
        WHERE e.assigned_to = $user_id AND e.status NOT IN ('CLOSED', 'SUBMITTED')
        HAVING days_remaining >= 0 OR days_remaining IS NULL
        ORDER BY deadline ASC
        LIMIT 5";
} else {
    $upcoming_query = "SELECT 
        e.engagement_id,
        e.title,
        e.status,
        COALESCE(e.approved_deadline, e.original_deadline) as deadline,
        DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
        FROM engagements e
        WHERE e.client_id IN ($client_ids_str) AND e.status NOT IN ('CLOSED', 'SUBMITTED')
        HAVING days_remaining >= 0 OR days_remaining IS NULL
        ORDER BY deadline ASC
        LIMIT 5";
}
$upcoming_result = mysqli_query($connection, $upcoming_query);

// 8. Team Members - Modified to handle multiple clients
if ($query_by_assigned) {
    $team_query = "SELECT DISTINCT 
        u.user_id, 
        u.first_name, 
        u.last_name, 
        u.user_email, 
        u.user_image,
        r.role_name,
        COUNT(DISTINCT e.engagement_id) as engagement_count
        FROM engagements e
        JOIN users u ON e.assigned_to = u.user_id
        LEFT JOIN user_roles r ON u.role_id = r.role_id
        WHERE e.assigned_to = u.user_id AND e.status != 'CLOSED'
        GROUP BY u.user_id
        LIMIT 4";
} else {
    $team_query = "SELECT DISTINCT 
        u.user_id, 
        u.first_name, 
        u.last_name, 
        u.user_email, 
        u.user_image,
        r.role_name,
        COUNT(DISTINCT e.engagement_id) as engagement_count
        FROM engagements e
        JOIN users u ON e.assigned_to = u.user_id
        LEFT JOIN user_roles r ON u.role_id = r.role_id
        WHERE e.client_id IN ($client_ids_str) AND e.status != 'CLOSED'
        GROUP BY u.user_id
        LIMIT 4";
}
$team_result = mysqli_query($connection, $team_query);

// 9. Recent Files - Modified to handle multiple clients
if ($query_by_assigned) {
    $recent_files_query = "SELECT DISTINCT cf.* 
                           FROM client_files cf
                           JOIN engagements e ON cf.client_id = e.client_id
                           WHERE e.assigned_to = $user_id
                           ORDER BY cf.uploaded_at DESC 
                           LIMIT 5";
} else {
    $recent_files_query = "SELECT * FROM client_files 
                           WHERE client_id IN ($client_ids_str)
                           ORDER BY uploaded_at DESC 
                           LIMIT 5";
}
$recent_files_result = mysqli_query($connection, $recent_files_query);

// 10. Client Info - Get all associated clients for display
if ($query_by_assigned) {
    $client_info_query = "SELECT DISTINCT 
        c.client_id,
        c.company_name,
        c.contact_name,
        c.contact_email,
        c.contact_mobile,
        c.created_at
        FROM clients c
        JOIN engagements e ON c.client_id = e.client_id
        WHERE e.assigned_to = $user_id
        ORDER BY c.company_name ASC, c.client_id ASC";
} else {
    $client_info_query = "SELECT * FROM clients WHERE client_id IN ($client_ids_str) ORDER BY company_name ASC, client_id ASC";
}
$client_info_result = mysqli_query($connection, $client_info_query);
$client_profiles = [];
if ($client_info_result) {
    while ($client_row = mysqli_fetch_assoc($client_info_result)) {
        $client_profiles[] = $client_row;
    }
}

$client_info = $client_profiles[0] ?? null;
$company_names = [];
foreach ($client_profiles as $client_profile) {
    if (!empty($client_profile['company_name'])) {
        $company_names[] = $client_profile['company_name'];
    }
}
$company_names = array_values(array_unique($company_names));
$company_count = count($company_names);

// If still no client info, create default
if (!$client_info) {
    $client_info = [
        'company_name' => 'Your Company',
        'contact_name' => $_SESSION['client_name'] ?? 'Client',
        'contact_email' => $_SESSION['user_email'] ?? '',
        'contact_mobile' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    $company_names = [$client_info['company_name']];
    $company_count = 1;
}

// Get current year for display
$current_year = date('Y');

// Calculate total active engagements correctly
$total_active_engagements = (int)($active_stats['total'] ?? 0);

// Debug info (remove in production)
// Uncomment to debug:
/*
echo "<!-- Debug Info: 
User ID: $user_id
Client IDs: " . print_r($client_ids, true) . "
Query by assigned: " . ($query_by_assigned ? 'Yes' : 'No') . "
Active Stats: " . print_r($active_stats, true) . "
-->";
*/
?>

<!-- Rest of your HTML remains the same from here -->
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card" style="background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="welcome-title">
                                Welcome back, <?php echo htmlspecialchars($client_info['contact_name'] ?? $_SESSION['client_name'] ?? 'Client'); ?>! 👋
                            </h2>
                            <p class="welcome-subtitle">
                                Member since <?php echo date('F Y', strtotime($client_info['created_at'] ?? 'now')); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach ($company_names as $company_name): ?>
                                <span class="badge rounded-pill border border-light-subtle text-white px-3 py-2">
                                    <?php echo htmlspecialchars($company_name); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
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

        <!-- Statistics Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Active Engagements Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" onclick="window.location.href='engagements.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-briefcase-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $total_active_engagements; ?></h3>
                            <p class="stat-label">Active Engagements</p>
                            <div class="stat-progress">
                                <span class="badge bg-info-soft text-info me-2">
                                    <i class="bi bi-play-circle me-1"></i><?php echo $active_stats['in_progress']; ?> In Progress
                                </span>
                                <span class="badge bg-secondary-soft text-secondary me-2">
                                    <i class="bi bi-person-lines-fill me-1"></i><?php echo $active_stats['assigned']; ?> Assigned
                                </span>
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-clock me-1"></i><?php echo $active_stats['awaiting_review']; ?> Review
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" onclick="window.location.href='files.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-file-earmark-text-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $files_count; ?></h3>
                            <p class="stat-label">Files Shared</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success">
                                    <i class="bi bi-arrow-up-short me-1"></i><?php echo $recent_files_result ? mysqli_num_rows($recent_files_result) : 0; ?> Recent
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-bell-fill text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $unread_notifications; ?></h3>
                            <p class="stat-label">Notifications</p>
                            <div class="stat-progress">
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-envelope me-1"></i><?php echo $unread_notifications > 0 ? $unread_notifications . ' Unread' : 'All Clear'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" onclick="window.location.href='feedback.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-star-fill text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $current_year; ?></h3>
                            <p class="stat-label">Current Year</p>
                            <div class="stat-progress">
                                <span class="badge bg-info-soft text-info">
                                    <i class="bi bi-chat me-1"></i>Share Feedback
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Engagement Status Distribution -->
            <div class="col-xl-5">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2" style="color: #f1bf70;"></i>
                            Engagement Status
                        </h5>
                        <span class="badge" style="background: #f1bf70; color: #0a2240;">Live</span>
                    </div>
                    <div class="card-body">
                        <?php if ($status_result && mysqli_num_rows($status_result) > 0): ?>
                        <div style="display: flex; justify-content: center; align-items: center; min-height: 260px; overflow: visible;">
                            <canvas id="statusChart" style="height: 220px; width: 220px; max-width: 100%; max-height: 220px; display: block; z-index: 2;"></canvas>
                        </div>
                        <div class="chart-legend mt-3">
                            <?php 
                            $status_colors = [
                                'ASSIGNED' => '#6c757d',
                                'IN_PROGRESS' => '#0d6efd',
                                'AWAITING_REVIEW' => '#ffc107',
                                'SUBMITTED' => '#198754',
                                'CLOSED' => '#0dcaf0',
                                'REJECTED' => '#dc3545'
                            ];
                            $total_engagements = 0;
                            $status_data = [];
                            if ($status_result && mysqli_num_rows($status_result) > 0) {
                                mysqli_data_seek($status_result, 0);
                                while($row = mysqli_fetch_assoc($status_result)) {
                                    $total_engagements += $row['count'];
                                    $status_data[] = $row;
                                }
                            }
                            foreach($status_data as $status): 
                                $percentage = $total_engagements > 0 ? round(($status['count'] / $total_engagements) * 100) : 0;
                            ?>
                            <div class="legend-item">
                                <span class="color-dot" style="background: <?php echo $status_colors[$status['status']] ?? '#6c757d'; ?>;"></span>
                                <span class="legend-label"><?php echo $status['status']; ?>:</span>
                                <span class="legend-value"><?php echo $status['count']; ?> (<?php echo $percentage; ?>%)</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state py-4">
                            <i class="bi bi-pie-chart display-4"></i>
                            <p class="text-muted mt-2">No engagement data available.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends Line Chart -->
            <div class="col-xl-7">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2" style="color: #f1bf70;"></i>
                            Engagement Trends (Last 6 Months)
                        </h5>
                        <span class="badge" style="background: #f1bf70; color: #0a2240;">Monthly</span>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" style="height: 250px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Column - Upcoming Deadlines & Activity -->
            <div class="col-xl-8">
                <!-- Upcoming Deadlines Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-check me-2" style="color: #f1bf70;"></i>
                            Upcoming Deadlines
                        </h5>
                        <a href="engagements.php" class="btn btn-sm" style="background: #f1bf70; color: #0a2240;">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($upcoming_result && mysqli_num_rows($upcoming_result) > 0): ?>
                            <div class="task-timeline">
                                <?php while($task = mysqli_fetch_assoc($upcoming_result)): 
                                    $days_class = 'success';
                                    if ($task['days_remaining'] !== null) {
                                        $days_class = $task['days_remaining'] <= 2 ? 'danger' : ($task['days_remaining'] <= 5 ? 'warning' : 'success');
                                    }
                                    $status_class = $task['status'] == 'IN_PROGRESS' ? 'primary' : ($task['status'] == 'AWAITING_REVIEW' ? 'warning' : 'secondary');
                                ?>
                                <div class="task-item">
                                    <div class="task-indicator bg-<?php echo $days_class; ?>"></div>
                                    <div class="task-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="task-title">
                                                    <a href="engagements.php?source=view_details&id=<?php echo $task['engagement_id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($task['title']); ?>
                                                    </a>
                                                </h6>
                                                <p class="task-client">
                                                    <i class="bi bi-tag me-1"></i>
                                                    <a href="engagements.php?source=view_details&id=<?php echo $task['engagement_id']; ?>" class="text-decoration-underline">
                                                        ENG-<?php echo date('dmy', strtotime($task['deadline'] ?? $today)); ?>-<?php echo $task['engagement_id']; ?>
                                                    </a>
                                                </p>
                                            </div>
                                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo $task['status']; ?></span>
                                        </div>
                                        <div class="task-meta">
                                            <?php if ($task['days_remaining'] !== null): ?>
                                            <span class="deadline-badge bg-<?php echo $days_class; ?>-soft text-<?php echo $days_class; ?>">
                                                <i class="bi bi-clock me-1"></i>
                                                <?php 
                                                if ($task['days_remaining'] == 0) echo 'Due today';
                                                elseif ($task['days_remaining'] == 1) echo 'Due tomorrow';
                                                else echo $task['days_remaining'] . ' days left';
                                                ?>
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($task['deadline'])); ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-muted">No deadline set</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-calendar-check display-4"></i>
                                <h6>No upcoming deadlines</h6>
                                <p class="text-muted">You're all caught up! Great job!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2" style="color: #f1bf70;"></i>
                            Recent Activity
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivity()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($activity_result && mysqli_num_rows($activity_result) > 0): ?>
                            <div class="activity-feed">
                                <?php while($activity = mysqli_fetch_assoc($activity_result)): 
                                    $icon = $activity['type'] == 'engagement' ? 'briefcase' : ($activity['type'] == 'file' ? 'file-earmark' : 'chat');
                                    $color = $activity['type'] == 'engagement' ? 'primary' : ($activity['type'] == 'file' ? 'success' : 'info');
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon bg-<?php echo $color; ?>-soft">
                                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <small class="activity-details text-muted">
                                                    <?php echo htmlspecialchars($activity['details']); ?>
                                                    <?php if ($activity['engagement_id']): ?>
                                                        • <a href="view_engagement.php?id=<?php echo $activity['engagement_id']; ?>">View</a>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <small class="activity-time text-muted">
                                                <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-activity display-4"></i>
                                <h6>No recent activity</h6>
                                <p class="text-muted">Your recent actions will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Team & Files -->
            <div class="col-xl-4">
                <!-- Your Team Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-people me-2" style="color: #f1bf70;"></i>
                            Your Expert Team
                        </h5>
                        <span class="badge" style="background: #f1bf70; color: #0a2240;"><?php echo $team_result ? mysqli_num_rows($team_result) : 0; ?> Members</span>
                    </div>
                    <div class="card-body">
                        <?php if ($team_result && mysqli_num_rows($team_result) > 0): ?>
                            <div class="team-list">
                                <?php while($staff = mysqli_fetch_assoc($team_result)): 
                                    $avatar_url = !empty($staff['user_image']) 
                                        ? '../uploads/profiles/' . $staff['user_image']
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($staff['first_name'] . ' ' . $staff['last_name']) . '&background=f1bf70&color=0a2240&size=40';
                                ?>
                                <div class="team-member mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $avatar_url; ?>" 
                                             alt="<?php echo htmlspecialchars($staff['first_name']); ?>"
                                             class="rounded-circle me-3" width="48" height="48"
                                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($staff['first_name'] . '+' . $staff['last_name']); ?>&background=f1bf70&color=0a2240&size=48'">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h6>
                                            <p class="small text-muted mb-1"><?php echo ucfirst($staff['role_name'] ?? 'Staff'); ?></p>
                                            <div class="d-flex gap-2">
                                                <a href="https://wa.me/?text=Hi%20<?php echo urlencode($staff['first_name']); ?>%2C%20I%20have%20a%20question" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                                <a href="mailto:<?php echo $staff['user_email']; ?>" class="btn btn-sm btn-outline-info" title="Email">
                                                    <i class="bi bi-envelope"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <span class="badge bg-secondary"><?php echo $staff['engagement_count']; ?> engagements</span>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-people display-4"></i>
                                <p class="text-muted mt-2">No team members assigned yet.</p>
                            </div>
                        <?php endif; ?>
                        <a href="engagements.php" class="btn btn-outline-primary w-100 mt-2">
                            View All Engagements
                        </a>
                    </div>
                </div>

                <!-- Recent Files Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-file-earmark me-2" style="color: #f1bf70;"></i>
                            Recent Files
                        </h5>
                        <a href="files.php" class="btn btn-sm" style="background: #f1bf70; color: #0a2240;">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_files_result && mysqli_num_rows($recent_files_result) > 0): ?>
                            <div class="files-list">
                                <?php while($file = mysqli_fetch_assoc($recent_files_result)): 
                                    $file_ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                    $file_icon = 'file-earmark';
                                    if (in_array($file_ext, ['jpg','jpeg','png','gif'])) $file_icon = 'file-earmark-image';
                                    elseif ($file_ext == 'pdf') $file_icon = 'file-earmark-pdf';
                                    elseif (in_array($file_ext, ['doc','docx'])) $file_icon = 'file-earmark-word';
                                    elseif (in_array($file_ext, ['xls','xlsx'])) $file_icon = 'file-earmark-excel';
                                ?>
                                <div class="file-item d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background: #f8f9fa;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-<?php echo $file_icon; ?> me-3" style="color: #f1bf70; font-size: 1.5rem;"></i>
                                        <div>
                                            <span class="d-block small fw-bold"><?php echo htmlspecialchars(substr($file['file_name'], 0, 25)) . (strlen($file['file_name']) > 25 ? '...' : ''); ?></span>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?></small>
                                        </div>
                                    </div>
                                    <a href="includes/download_file.php?id=<?php echo $file['file_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <i class="bi bi-file-earmark display-4"></i>
                                <p class="text-muted mt-2">No files shared yet.</p>
                            </div>
                        <?php endif; ?>
                        <a href="files.php?source=upload" class="btn btn-outline-success w-100 mt-2">
                            <i class="bi bi-cloud-upload me-2"></i>Upload New File
                        </a>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge me-2" style="color: #f1bf70;"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="engagements.php" class="quick-action-item">
                                <div class="quick-action-icon bg-primary-soft">
                                    <i class="bi bi-briefcase text-primary"></i>
                                </div>
                                <span>View Engagements</span>
                            </a>
                            <a href="files.php?source=upload" class="quick-action-item">
                                <div class="quick-action-icon bg-success-soft">
                                    <i class="bi bi-cloud-upload text-success"></i>
                                </div>
                                <span>Upload Files</span>
                            </a>
                            <a href="feedback.php?source=add" class="quick-action-item">
                                <div class="quick-action-icon bg-warning-soft">
                                    <i class="bi bi-star text-warning"></i>
                                </div>
                                <span>Leave Feedback</span>
                            </a>
                            <a href="support.php" class="quick-action-item">
                                <div class="quick-action-icon bg-info-soft">
                                    <i class="bi bi-question-circle text-info"></i>
                                </div>
                                <span>Get Support</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Client Info Card -->
                <div class="dashboard-card mt-4 bg-gradient-primary" style="background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);">
                    <div class="card-body">
                        <h6 class="text-white mb-3">
                            <i class="bi bi-building me-2"></i>
                            Account Overview
                        </h6>
                        <div class="text-white-50 small mb-2">
                            <div class="mb-2">
                                <strong>Companies:</strong>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <?php foreach ($company_names as $company_name): ?>
                                    <span class="badge rounded-pill border border-light-subtle text-white px-3 py-2">
                                        <?php echo htmlspecialchars($company_name); ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <p class="mb-1"><strong>Contact:</strong> <?php echo htmlspecialchars($client_info['contact_name'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($client_info['contact_email'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($client_info['contact_mobile'] ?? 'N/A'); ?></p>
                        </div>
                        <a href="profile.php" class="btn btn-sm btn-light mt-2 w-100">
                            Update Profile <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        const statusLabels = [<?php 
            $labels = [];
            $counts = [];
            if ($status_result && mysqli_num_rows($status_result) > 0) {
                mysqli_data_seek($status_result, 0);
                while($s = mysqli_fetch_assoc($status_result)) {
                    $labels[] = "'" . addslashes($s['status']) . "'";
                    $counts[] = $s['count'];
                }
            } else {
                $labels[] = "'No Data'";
                $counts[] = 1;
            }
            echo implode(',', $labels);
        ?>];
        
        const statusCounts = [<?php echo implode(',', $counts); ?>];
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: [
                        '#6c757d', '#0d6efd', '#ffc107', '#198754', '#0dcaf0', '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '65%'
            }
        });
    }

    // Monthly Trends Line Chart
    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx) {
        const months = [<?php 
            $month_labels = [];
            $month_counts = [];
            if ($monthly_result && mysqli_num_rows($monthly_result) > 0) {
                mysqli_data_seek($monthly_result, 0);
                while($m = mysqli_fetch_assoc($monthly_result)) {
                    $month_labels[] = "'" . $m['month'] . "'";
                    $month_counts[] = $m['count'];
                }
            } else {
                $month_labels = ["'Jan'", "'Feb'", "'Mar'", "'Apr'", "'May'", "'Jun'"];
                $month_counts = [0,0,0,0,0,0];
            }
            echo implode(',', $month_labels);
        ?>];
        
        const monthlyCounts = [<?php echo implode(',', $month_counts); ?>];
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'New Engagements',
                    data: monthlyCounts,
                    borderColor: '#f1bf70',
                    backgroundColor: 'rgba(241, 191, 112, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#0a2240',
                    pointBorderColor: '#f1bf70',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    }
});

// Function to refresh activity feed
function refreshActivity() {
    location.reload();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
/* Client Dashboard Styles - Matching Operations Dashboard Theme */
:root {
    --dark-blue: #0a2240;
    --gold: #f1bf70;
}

/* Welcome Card */
.welcome-card {
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(10, 34, 64, 0.3);
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
    display: inline-block;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
    min-width: 0;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: var(--gold);
}

.stat-card-body {
    display: flex;
    align-items: center;
    gap: 20px;
    width: 100%;
    min-width: 0;
    flex-wrap: wrap;
    overflow: hidden;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-icon.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.stat-icon.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.stat-icon.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.stat-icon.bg-info-soft { background: rgba(13, 202, 240, 0.1); }

.stat-content {
    flex: 1 1 0%;
    min-width: 0;
    overflow: hidden;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
    color: var(--dark-blue);
    word-break: break-word;
    overflow-wrap: break-word;
}

.stat-label {
    color: #6c757d;
    margin-bottom: 5px;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.stat-progress {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    min-width: 0;
    overflow: hidden;
}

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    min-height: 320px;
    display: flex;
    flex-direction: column;
    padding-bottom: 24px;
}

.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-blue);
}

.card-body {
    padding: 20px;
    flex: 1;
    overflow-y: auto;
}

/* Task Timeline */
.task-timeline {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.task-item {
    display: flex;
    gap: 15px;
    padding: 10px;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.task-item:hover {
    background: #f8f9fa;
}

.task-indicator {
    width: 4px;
    height: auto;
    border-radius: 4px;
}

.task-indicator.bg-danger { background: #dc3545; }
.task-indicator.bg-warning { background: #ffc107; }
.task-indicator.bg-success { background: #28a745; }

.task-content {
    flex: 1;
}

.task-title {
    margin-bottom: 5px;
    font-size: 1rem;
}

.task-title a {
    color: var(--dark-blue);
    font-weight: 500;
    text-decoration: none;
}

.task-title a:hover {
    color: var(--gold);
}

.task-client {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 8px;
}

.task-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 0.85rem;
    flex-wrap: wrap;
}

.deadline-badge {
    padding: 4px 10px;
    border-radius: 50px;
    font-weight: 500;
}

.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }

/* Activity Feed */
.activity-feed {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 10px;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    margin-bottom: 3px;
    font-weight: 500;
}

.activity-details {
    font-size: 0.8rem;
}

.activity-time {
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Team List */
.team-member {
    padding: 10px;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.team-member:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.quick-action-item {
    text-decoration: none;
    color: var(--dark-blue);
    text-align: center;
    padding: 15px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.quick-action-item:hover {
    background: #f8f9fa;
    transform: translateY(-3px);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 10px;
}

.quick-action-item span {
    font-size: 0.85rem;
    font-weight: 500;
}

/* Chart Legend */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 15px;
    max-height: 120px;
    overflow-y: auto;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
}

.color-dot {
    width: 10px;
    height: 10px;
    border-radius: 10px;
}

.legend-label {
    color: #6c757d;
}

.legend-value {
    font-weight: 600;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 30px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 15px;
}

.empty-state h6 {
    margin-bottom: 5px;
}

/* Gradient Card */
.bg-gradient-primary {
    background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);
    border: none;
}

.text-white-50 {
    color: rgba(255, 255, 255, 0.7);
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    
    .stat-card-body {
        flex-direction: column;
        text-align: center;
    }
    
    .stat-progress {
        justify-content: center;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .task-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<?php include 'includes/client_footer.php'; ?>