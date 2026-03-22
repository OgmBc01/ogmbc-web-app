<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Initialize session with security settings
initSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Check if user is admin
if (!isAdmin()) {
    header("Location: ../index.php");
    exit();
}

$today = date('Y-m-d');
$first_day_month = date('Y-m-01');
$last_day_month = date('Y-m-t');
$seven_days_ago = date('Y-m-d', strtotime('-7 days'));

// ============================================
// KEY METRICS - STAT CARDS
// ============================================

// 1. User Statistics
$user_stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN user_status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN created_at >= '$seven_days_ago' THEN 1 ELSE 0 END) as new_users
    FROM users";
$user_stats_result = mysqli_query($connection, $user_stats_query);
$user_stats = mysqli_fetch_assoc($user_stats_result);

// 2. Client Statistics
$client_stats_query = "SELECT 
    COUNT(*) as total_clients,
    SUM(CASE WHEN created_at >= '$seven_days_ago' THEN 1 ELSE 0 END) as new_clients
    FROM clients";
$client_stats_result = mysqli_query($connection, $client_stats_query);
$client_stats = mysqli_fetch_assoc($client_stats_result);

// 3. Engagement Statistics
$engagement_stats_query = "SELECT 
    COUNT(*) as total_engagements,
    SUM(CASE WHEN status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as open_engagements,
    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed_engagements,
    SUM(CASE WHEN status NOT IN ('CLOSED', 'SUBMITTED') 
              AND COALESCE(approved_deadline, original_deadline) < CURDATE() 
              THEN 1 ELSE 0 END) as overdue_engagements
    FROM engagements";
$engagement_stats_result = mysqli_query($connection, $engagement_stats_query);
$engagement_stats = mysqli_fetch_assoc($engagement_stats_result);

// 4. Engagement Completion Rate
$completion_query = "SELECT 
    COUNT(*) as total_this_month,
    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed_this_month
    FROM engagements 
    WHERE created_at BETWEEN '$first_day_month' AND '$last_day_month'";
$completion_result = mysqli_query($connection, $completion_query);
$completion = mysqli_fetch_assoc($completion_result);
$completion_rate = $completion['total_this_month'] > 0 
    ? round(($completion['closed_this_month'] / $completion['total_this_month']) * 100) 
    : 0;

// 5. Feedback & Support Statistics
$feedback_stats_query = "SELECT 
    (SELECT COUNT(*) FROM client_feedback WHERE is_validated = 1) as total_feedback,
    (SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')) as unresolved_tickets,
    (SELECT COUNT(*) FROM support_tickets WHERE priority = 'urgent' AND status != 'closed') as urgent_tickets
    FROM dual";
$feedback_stats_result = mysqli_query($connection, $feedback_stats_query);
$feedback_stats = mysqli_fetch_assoc($feedback_stats_result);

// 6. Content Statistics
$content_stats_query = "SELECT 
    COUNT(*) as total_posts,
    SUM(CASE WHEN created_at >= '$seven_days_ago' THEN 1 ELSE 0 END) as recent_posts
    FROM posts";
$content_stats_result = mysqli_query($connection, $content_stats_query);
$content_stats = mysqli_fetch_assoc($content_stats_result);

// 7. Points & Performance
$points_stats_query = "SELECT 
    COALESCE(SUM(points), 0) as total_points_earned,
    COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN points ELSE 0 END), 0) as month_points
    FROM points_ledger 
    WHERE points_type = 'EARNED'";
$points_stats_result = mysqli_query($connection, $points_stats_query);
$points_stats = mysqli_fetch_assoc($points_stats_result);

// ============================================
// CHARTS DATA - ENGAGEMENT TRENDS
// ============================================

// Engagements created per month (last 6 months)
$monthly_engagements_query = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as created,
    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as completed
    FROM engagements
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC";
$monthly_engagements_result = mysqli_query($connection, $monthly_engagements_query);

$months = [];
$created_data = [];
$completed_data = [];
while ($row = mysqli_fetch_assoc($monthly_engagements_result)) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $created_data[] = $row['created'];
    $completed_data[] = $row['completed'];
}

// Engagement status distribution
$status_distribution_query = "SELECT 
    status,
    COUNT(*) as count
    FROM engagements
    GROUP BY status";
$status_result = mysqli_query($connection, $status_distribution_query);
$status_labels = [];
$status_counts = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_labels[] = str_replace('_', ' ', $row['status']);
    $status_counts[] = $row['count'];
}

// Service type breakdown
$service_breakdown_query = "SELECT 
    s.service_category,
    COUNT(*) as count
    FROM engagements e
    JOIN service_types s ON e.service_id = s.service_id
    GROUP BY s.service_category
    ORDER BY count DESC";
$service_result = mysqli_query($connection, $service_breakdown_query);
$service_labels = [];
$service_counts = [];
while ($row = mysqli_fetch_assoc($service_result)) {
    $service_labels[] = ucfirst($row['service_category']);
    $service_counts[] = $row['count'];
}

// User role distribution
$role_distribution_query = "SELECT 
    r.role_name,
    COUNT(u.user_id) as count
    FROM users u
    LEFT JOIN user_roles r ON u.role_id = r.role_id
    GROUP BY r.role_name
    ORDER BY count DESC";
$role_result = mysqli_query($connection, $role_distribution_query);
$role_labels = [];
$role_counts = [];
while ($row = mysqli_fetch_assoc($role_result)) {
    $role_labels[] = ucfirst($row['role_name'] ?? 'Unassigned');
    $role_counts[] = $row['count'];
}

// ============================================
// RECENT ACTIVITY
// ============================================
$recent_activity_query = "SELECT 
    'engagement' as type,
    CONCAT('Engagement ', CHAR(34), e.title, CHAR(34), ' ', e.status) COLLATE utf8mb4_general_ci as description,
    e.updated_at as created_at,
    CONCAT('Client ID: ', e.client_id) COLLATE utf8mb4_general_ci as details,
    CONCAT(u.first_name, ' ', u.last_name) COLLATE utf8mb4_general_ci as user_name
    FROM engagements e
    LEFT JOIN users u ON e.assigned_to = u.user_id
    UNION ALL
    SELECT 
    'user' as type,
    CONCAT('User ', u.username, ' logged in') COLLATE utf8mb4_general_ci as description,
    u.created_at,
    'New user registration' COLLATE utf8mb4_general_ci as details,
    NULL as user_name
    FROM users u
    UNION ALL
    SELECT 
    'feedback' as type,
    CONCAT('Feedback received from client') COLLATE utf8mb4_general_ci as description,
    cf.created_at,
    CONCAT('Rating: ', cf.rating, '/5') COLLATE utf8mb4_general_ci as details,
    NULL as user_name
    FROM client_feedback cf
    UNION ALL
    SELECT 
    'ticket' as type,
    CONCAT('Support ticket created: ', st.subject) COLLATE utf8mb4_general_ci as description,
    st.created_at,
    CONCAT('Priority: ', st.priority) COLLATE utf8mb4_general_ci as details,
    NULL as user_name
    FROM support_tickets st
    ORDER BY created_at DESC
    LIMIT 10";
$recent_activity_result = mysqli_query($connection, $recent_activity_query);

// ============================================
// TOP PERFORMERS
// ============================================
$top_performers_query = "SELECT 
    u.user_id,
    CONCAT(u.first_name, ' ', u.last_name) as employee_name,
    r.role_name,
    COUNT(e.engagement_id) as total_engagements,
    SUM(CASE WHEN e.status = 'CLOSED' THEN 1 ELSE 0 END) as completed_engagements,
    COALESCE((SELECT SUM(points) FROM points_ledger WHERE employee_id = u.user_id AND points_type = 'EARNED'), 0) as total_points
    FROM users u
    LEFT JOIN engagements e ON u.user_id = e.assigned_to
    LEFT JOIN user_roles r ON u.role_id = r.role_id
    WHERE u.user_status = 'active'
    GROUP BY u.user_id
    HAVING total_engagements > 0
    ORDER BY total_points DESC
    LIMIT 5";
$top_performers_result = mysqli_query($connection, $top_performers_query);

// ============================================
// PENDING ACTIONS
// ============================================
$pending_actions_query = "SELECT 
    'engagement' as type,
    CONCAT('Engagement awaiting review: ', e.title) as description,
    e.engagement_id as item_id,
    'engagements.php?source=view_engagement&id=' as link
    FROM engagements e
    WHERE e.status = 'AWAITING_REVIEW'
    UNION ALL
    SELECT 
    'ticket' as type,
    CONCAT('Support ticket needs attention: ', st.subject) as description,
    st.ticket_id as item_id,
    'support_tickets.php?source=view&id=' as link
    FROM support_tickets st
    WHERE st.status IN ('open', 'in_progress') AND st.priority IN ('high', 'urgent')
    UNION ALL
    SELECT 
    'overdue' as type,
    CONCAT('Overdue engagement: ', e.title) as description,
    e.engagement_id as item_id,
    'engagements.php?source=view_engagement&id=' as link
    FROM engagements e
    WHERE e.status NOT IN ('CLOSED', 'SUBMITTED')
    AND COALESCE(e.approved_deadline, e.original_deadline) < CURDATE()
    LIMIT 5";
$pending_actions_result = mysqli_query($connection, $pending_actions_query);

// ============================================
// SALES TARGET PROGRESS
// ============================================
$sales_progress_query = "SELECT 
    CONCAT(u.first_name, ' ', u.last_name) as sales_name,
    st.target_value,
    st.actual_value,
    st.attainment_percentage,
    st.month,
    st.year
    FROM sales_targets st
    JOIN users u ON st.employee_id = u.user_id
    WHERE st.month = MONTH(CURDATE()) AND st.year = YEAR(CURDATE())
    ORDER BY st.attainment_percentage DESC
    LIMIT 3";
$sales_progress_result = mysqli_query($connection, $sales_progress_query);
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Header with Date -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="welcome-title">
                                Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?>! 👋
                            </h2>
                            <p class="welcome-subtitle">
                                Here's your business overview for today.
                                <?php if ($engagement_stats['overdue_engagements'] > 0): ?>
                                    <span class="overdue-warning">⚠️ <?php echo $engagement_stats['overdue_engagements']; ?> overdue engagements</span>
                                <?php endif; ?>
                                <?php if ($feedback_stats['urgent_tickets'] > 0): ?>
                                    <span class="urgent-warning">🔴 <?php echo $feedback_stats['urgent_tickets']; ?> urgent tickets</span>
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

        <!-- Key Metrics Row 1 - Core Stats -->
        <div class="row g-4 mb-4">
            <!-- Total Users Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card" onclick="window.location.href='users.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-people-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($user_stats['total_users']); ?></h3>
                            <p class="stat-label">Total Users</p>
                            <div class="stat-progress">
                                <small class="text-success">+<?php echo $user_stats['new_users']; ?> new</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-person-check-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($user_stats['active_users']); ?></h3>
                            <p class="stat-label">Active Users</p>
                            <div class="stat-progress">
                                <small class="text-muted"><?php echo round(($user_stats['active_users'] / max($user_stats['total_users'], 1)) * 100); ?>% active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Clients Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card" onclick="window.location.href='clients.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-building text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($client_stats['total_clients']); ?></h3>
                            <p class="stat-label">Total Clients</p>
                            <div class="stat-progress">
                                <small class="text-success">+<?php echo $client_stats['new_clients']; ?> new</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Engagements Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card" onclick="window.location.href='engagements.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-briefcase-fill text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($engagement_stats['total_engagements']); ?></h3>
                            <p class="stat-label">Engagements</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success me-1"><?php echo $engagement_stats['closed_engagements']; ?> closed</span>
                                <span class="badge bg-warning-soft text-warning"><?php echo $engagement_stats['open_engagements']; ?> open</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Engagements Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card <?php echo $engagement_stats['overdue_engagements'] > 0 ? 'border-danger' : ''; ?>">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-danger-soft">
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $engagement_stats['overdue_engagements']; ?></h3>
                            <p class="stat-label">Overdue</p>
                            <div class="stat-progress">
                                <small class="text-danger">Need attention</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completion Rate Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-secondary-soft">
                            <i class="bi bi-pie-chart-fill text-secondary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $completion_rate; ?>%</h3>
                            <p class="stat-label">Completion Rate</p>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $completion_rate; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Row 2 - Secondary Stats -->
        <div class="row g-4 mb-4">
            <!-- Total Points Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-purple-soft">
                            <i class="bi bi-trophy-fill text-purple"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($points_stats['total_points_earned']); ?></h3>
                            <p class="stat-label">Points Earned</p>
                            <div class="stat-progress">
                                <small class="text-success">+<?php echo number_format($points_stats['month_points']); ?> this month</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Feedback Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card" onclick="window.location.href='client_feedback.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-star-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($feedback_stats['total_feedback']); ?></h3>
                            <p class="stat-label">Feedback</p>
                            <div class="stat-progress">
                                <small class="text-muted">Client reviews</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Tickets Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card <?php echo $feedback_stats['unresolved_tickets'] > 0 ? 'border-warning' : ''; ?>" onclick="window.location.href='support_tickets.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-ticket-fill text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $feedback_stats['unresolved_tickets']; ?></h3>
                            <p class="stat-label">Open Tickets</p>
                            <?php if ($feedback_stats['urgent_tickets'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $feedback_stats['urgent_tickets']; ?> urgent</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Posts Card -->
            <div class="col-xl-2 col-md-4 col-6">
                <div class="stat-card" onclick="window.location.href='posts.php'" style="cursor: pointer;">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-file-post-fill text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($content_stats['total_posts']); ?></h3>
                            <p class="stat-label">Total Posts</p>
                            <div class="stat-progress">
                                <small class="text-success">+<?php echo $content_stats['recent_posts']; ?> recent</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats - Placeholder for future -->
            <div class="col-xl-4 col-md-8">
                <div class="stat-card bg-light">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-secondary-soft">
                            <i class="bi bi-graph-up text-secondary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo date('Y'); ?></h3>
                            <p class="stat-label">Year to Date</p>
                            <div class="d-flex gap-2">
                                <small><?php echo $engagement_stats['closed_engagements']; ?> engagements closed</small>
                                <small>•</small>
                                <small><?php echo $client_stats['new_clients']; ?> new clients</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Monthly Engagement Trends Chart -->
            <div class="col-xl-8">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Engagement Trends (Last 6 Months)
                        </h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary active">Monthly</button>
                            <button class="btn btn-sm btn-outline-secondary">Quarterly</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="engagementChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Engagement Status Distribution -->
            <div class="col-xl-4">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>
                            Engagement Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Service Type Breakdown -->
            <div class="col-xl-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-tags me-2 text-primary"></i>
                            Service Type Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="serviceChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Role Distribution -->
            <div class="col-xl-6">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-people me-2 text-primary"></i>
                            User Role Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="roleChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-4">
            <!-- Recent Activity -->
            <div class="col-xl-4">
                <div class="dashboard-card h-100">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
                            Recent Activity
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivity()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-feed">
                            <?php if ($recent_activity_result && mysqli_num_rows($recent_activity_result) > 0): ?>
                                <?php while($activity = mysqli_fetch_assoc($recent_activity_result)): 
                                    $icon = $activity['type'] == 'engagement' ? 'briefcase' : 
                                            ($activity['type'] == 'user' ? 'person' : 
                                            ($activity['type'] == 'feedback' ? 'star' : 'ticket'));
                                    $color = $activity['type'] == 'engagement' ? 'primary' : 
                                            ($activity['type'] == 'user' ? 'success' : 
                                            ($activity['type'] == 'feedback' ? 'warning' : 'info'));
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon bg-<?php echo $color; ?>-soft">
                                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <small class="activity-details text-muted"><?php echo htmlspecialchars($activity['details'] ?? ''); ?></small>
                                        <small class="activity-time text-muted d-block">
                                            <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers -->
            <div class="col-xl-4">
                <div class="dashboard-card h-100">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-trophy me-2 text-primary"></i>
                            Top Performers
                        </h5>
                        <a href="employees.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($top_performers_result && mysqli_num_rows($top_performers_result) > 0): ?>
                            <?php while($performer = mysqli_fetch_assoc($top_performers_result)): ?>
                                <div class="performer-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($performer['employee_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($performer['role_name'] ?? 'Employee'); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary"><?php echo $performer['completed_engagements']; ?> completed</span>
                                            <br>
                                            <small class="text-success"><?php echo number_format($performer['total_points']); ?> pts</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <?php 
                                        $completion_pct = $performer['total_engagements'] > 0 
                                            ? round(($performer['completed_engagements'] / $performer['total_engagements']) * 100) 
                                            : 0;
                                        ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $completion_pct; ?>%"></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No performer data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pending Actions -->
            <div class="col-xl-4">
                <div class="dashboard-card h-100">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-exclamation-triangle me-2 text-primary"></i>
                            Pending Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($pending_actions_result && mysqli_num_rows($pending_actions_result) > 0): ?>
                            <?php while($action = mysqli_fetch_assoc($pending_actions_result)): 
                                $badge_color = $action['type'] == 'engagement' ? 'warning' : 
                                              ($action['type'] == 'ticket' ? 'danger' : 'secondary');
                            ?>
                                <div class="pending-item mb-3 p-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-<?php echo $badge_color; ?> mb-2">
                                                <?php echo ucfirst($action['type']); ?>
                                            </span>
                                            <p class="mb-1"><?php echo htmlspecialchars($action['description']); ?></p>
                                        </div>
                                        <a href="<?php echo $action['link'] . $action['item_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                <p class="text-muted mt-2">No pending actions</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Target Progress (if any) -->
        <?php if ($sales_progress_result && mysqli_num_rows($sales_progress_result) > 0): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-bar-chart me-2 text-primary"></i>
                            Monthly Sales Target Progress
                        </h5>
                        <a href="sales_targets.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while($sales = mysqli_fetch_assoc($sales_progress_result)): ?>
                                <div class="col-md-4">
                                    <div class="target-card p-3">
                                        <h6><?php echo htmlspecialchars($sales['sales_name']); ?></h6>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Progress</small>
                                            <small><?php echo number_format($sales['attainment_percentage'] ?? 0, 1); ?>%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <?php 
                                            $pct = $sales['attainment_percentage'] ?? 0;
                                            $bar_class = $pct >= 100 ? 'bg-success' : ($pct >= 75 ? 'bg-info' : ($pct >= 50 ? 'bg-warning' : 'bg-danger'));
                                            ?>
                                            <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo min($pct, 100); ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <small class="text-muted">Target: AED <?php echo number_format($sales['target_value']); ?></small>
                                            <?php if ($sales['actual_value']): ?>
                                                <small class="text-success">AED <?php echo number_format($sales['actual_value']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge me-2 text-primary"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2 col-6">
                                <a href="clients.php?source=add_client" class="quick-action-item">
                                    <div class="quick-action-icon bg-primary-soft">
                                        <i class="bi bi-person-plus text-primary"></i>
                                    </div>
                                    <span>Add Client</span>
                                </a>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="engagements.php?source=add_engagement" class="quick-action-item">
                                    <div class="quick-action-icon bg-success-soft">
                                        <i class="bi bi-briefcase text-success"></i>
                                    </div>
                                    <span>New Engagement</span>
                                </a>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="users.php?source=add_user" class="quick-action-item">
                                    <div class="quick-action-icon bg-info-soft">
                                        <i class="bi bi-people text-info"></i>
                                    </div>
                                    <span>Add User</span>
                                </a>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="posts.php?source=add_post" class="quick-action-item">
                                    <div class="quick-action-icon bg-warning-soft">
                                        <i class="bi bi-file-post text-warning"></i>
                                    </div>
                                    <span>New Post</span>
                                </a>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="services.php?source=add_service" class="quick-action-item">
                                    <div class="quick-action-icon bg-secondary-soft">
                                        <i class="bi bi-gear text-secondary"></i>
                                    </div>
                                    <span>Add Service</span>
                                </a>
                            </div>
                            <div class="col-md-2 col-6">
                                <a href="support_tickets.php" class="quick-action-item">
                                    <div class="quick-action-icon bg-danger-soft">
                                        <i class="bi bi-ticket text-danger"></i>
                                    </div>
                                    <span>View Tickets</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pro Tip Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="pro-tip-card">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h6 class="text-white mb-2">
                                <i class="bi bi-lightbulb me-2"></i>
                                Admin Insights
                            </h6>
                            <p class="text-white-50 small mb-md-0">
                                <?php 
                                if ($engagement_stats['overdue_engagements'] > 5) {
                                    echo "⚠️ You have {$engagement_stats['overdue_engagements']} overdue engagements. Consider reviewing workload distribution.";
                                } elseif ($feedback_stats['urgent_tickets'] > 0) {
                                    echo "🔴 {$feedback_stats['urgent_tickets']} urgent tickets need immediate attention.";
                                } elseif ($user_stats['new_users'] > 5) {
                                    echo "📈 {$user_stats['new_users']} new users joined recently. Welcome them to the platform!";
                                } else {
                                    echo "📊 Your dashboard is up to date. All systems operating normally.";
                                }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <i class="bi bi-lightbulb display-4 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Engagement Trends Chart
    const ctx1 = document.getElementById('engagementChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [
                {
                    label: 'Created',
                    data: <?php echo json_encode($created_data); ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: <?php echo json_encode($completed_data); ?>,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($status_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($status_counts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Service Type Chart
    const ctx3 = document.getElementById('serviceChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($service_labels); ?>,
            datasets: [{
                label: 'Number of Engagements',
                data: <?php echo json_encode($service_counts); ?>,
                backgroundColor: 'rgba(241, 191, 112, 0.7)',
                borderColor: 'rgba(241, 191, 112, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Role Distribution Chart
    const ctx4 = document.getElementById('roleChart').getContext('2d');
    new Chart(ctx4, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($role_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($role_counts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});

function refreshActivity() {
    location.reload();
}
</script>

<style>
/* Modern Dashboard Styles */
.welcome-card {
    background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);
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

.overdue-warning, .urgent-warning {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.9rem;
    margin-left: 10px;
    display: inline-block;
}

.urgent-warning {
    background: rgba(220, 53, 69, 0.3);
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
    padding: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-card.border-danger {
    border-left: 4px solid #dc3545;
}

.stat-card.border-warning {
    border-left: 4px solid #ffc107;
}

.stat-card-body {

    align-items: center;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }
.bg-purple-soft { background: rgba(111, 66, 193, 0.1); }

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.7rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
}

.stat-label {
    color: #6c757d;
    margin-bottom: 5px;
    font-size: 1.2rem;
    font-weight: 500;
}

.stat-progress {
    font-size: 0.95rem;
}

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    height: 100%;
}

.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.card-body {
    padding: 20px;
}

/* Activity Feed */
.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    margin-bottom: 2px;
    font-size: 0.9rem;
}

.activity-details {
    font-size: 0.75rem;
}

.activity-time {
    font-size: 0.7rem;
    margin-top: 2px;
}

/* Performer Items */
.performer-item {
    padding: 10px;
    border-radius: 10px;
    background: #f8f9fa;
}

/* Quick Actions */
.quick-action-item {
    text-decoration: none;
    color: #2c3e50;
    text-align: center;
    padding: 10px;
    border-radius: 12px;
    transition: all 0.3s ease;
    display: block;
}

.quick-action-item:hover {
    background: #f8f9fa;
    transform: translateY(-3px);
    text-decoration: none;
    color: #2c3e50;
}

.quick-action-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin: 0 auto 8px;
}

.quick-action-item span {
    font-size: 0.8rem;
    font-weight: 500;
    display: block;
}

/* Target Cards */
.target-card {
    background: #f8f9fa;
    border-radius: 12px;
    height: 100%;
}

/* Pro Tip Card */
.pro-tip-card {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    
    .stat-card-body {
        flex-direction: row;
        align-items: center;
    }
    
    .stat-value {
        font-size: 1.2rem;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .quick-action-item span {
        font-size: 0.7rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>