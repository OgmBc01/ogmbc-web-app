<?php
include 'includes/operations_header.php';
include 'includes/operations_nav.php';
include 'includes/operations_sidebar.php';

// Set user_id from session (operations employee)
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// ============================================
// DASHBOARD STATISTICS QUERIES
// ============================================

// 1. Active Engagements Count
$active_engagements_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review
    FROM engagements 
    WHERE assigned_to = $user_id AND status NOT IN ('CLOSED', 'SUBMITTED')";
$active_result = mysqli_query($connection, $active_engagements_query);
$active_stats = mysqli_fetch_assoc($active_result);

// 2. Overdue Tasks
$overdue_query = "SELECT COUNT(*) as overdue
    FROM engagements 
    WHERE assigned_to = $user_id 
    AND status NOT IN ('CLOSED', 'SUBMITTED')
    AND COALESCE(approved_deadline, original_deadline) < CURDATE()";
$overdue_result = mysqli_query($connection, $overdue_query);
$overdue = mysqli_fetch_assoc($overdue_result);

// 3. Points Summary
$points_query = "SELECT 
    COALESCE(SUM(points), 0) as total_points,
    COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN points ELSE 0 END), 0) as month_points
    FROM points_ledger 
    WHERE employee_id = $user_id AND points_type = 'EARNED'";
$points_result = mysqli_query($connection, $points_query);
$points = mysqli_fetch_assoc($points_result);

// 4. CDP Records
$cdp_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending
    FROM cdp_records 
    WHERE employee_id = $user_id";
$cdp_result = mysqli_query($connection, $cdp_query);
$cdp_stats = mysqli_fetch_assoc($cdp_result);

// 5. Upcoming Tasks (Next 5)
$upcoming_query = "SELECT 
    e.engagement_id,
    e.title,
    e.status,
    COALESCE(e.approved_deadline, e.original_deadline) as deadline,
    c.company_name,
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN clients c ON e.client_id = c.client_id
    WHERE e.assigned_to = $user_id AND e.status NOT IN ('CLOSED', 'SUBMITTED')
    HAVING days_remaining >= 0
    ORDER BY deadline ASC
    LIMIT 5";
$upcoming_result = mysqli_query($connection, $upcoming_query);

// 6. Recent Activity
$activity_query = "SELECT 
    'engagement' as type,
    e.title as description,
    e.updated_at as created_at,
    CONCAT('Status: ', e.status) as details
    FROM engagements e
    WHERE e.assigned_to = $user_id
    UNION ALL
    SELECT 
    'point' as type,
    CONCAT(points, ' points earned') as description,
    created_at,
    source_type as details
    FROM points_ledger 
    WHERE employee_id = $user_id
    UNION ALL
    SELECT 
    'cdp' as type,
    CONCAT('CDP: ', title) as description,
    created_at,
    status as details
    FROM cdp_records 
    WHERE employee_id = $user_id
    ORDER BY created_at DESC
    LIMIT 7";
$activity_result = mysqli_query($connection, $activity_query);

// 7. Performance This Month
$performance_query = "SELECT 
    COUNT(CASE WHEN status = 'CLOSED' AND MONTH(updated_at) = MONTH(CURDATE()) THEN 1 END) as completed_this_month,
    COUNT(CASE WHEN MONTH(updated_at) = MONTH(CURDATE()) THEN 1 END) as total_this_month
    FROM engagements 
    WHERE assigned_to = $user_id";
$performance_result = mysqli_query($connection, $performance_query);
$performance = mysqli_fetch_assoc($performance_result);
$completion_rate = $performance['total_this_month'] > 0 
    ? round(($performance['completed_this_month'] / $performance['total_this_month']) * 100) 
    : 0;

// 8. Client Feedback Summary
$feedback_query = "SELECT 
    COUNT(*) as total_feedback,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive
    FROM client_feedback 
    WHERE employee_id = $user_id AND is_validated = 1";
$feedback_result = mysqli_query($connection, $feedback_query);
$feedback = mysqli_fetch_assoc($feedback_result);
$satisfaction_rate = $feedback['total_feedback'] > 0 
    ? round(($feedback['positive'] / $feedback['total_feedback']) * 100) 
    : 0;
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="welcome-title">
                                Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']); ?>! 👋
                            </h2>
                            <p class="welcome-subtitle">
                                Here's what's happening with your tasks today.
                                <?php if ($overdue['overdue'] > 0): ?>
                                    <span class="overdue-warning">⚠️ You have <?php echo $overdue['overdue']; ?> overdue task<?php echo $overdue['overdue'] > 1 ? 's' : ''; ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="page-navigation-group mb-2">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="page_navigation" id="nav_dashboard" autocomplete="off" checked onclick="window.location.href='operations_dashboard.php'">
                                    <label class="btn btn-light active" for="nav_dashboard">
                                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                    </label>
                                    <input type="radio" class="btn-check" name="page_navigation" id="nav_performers" autocomplete="off" onclick="window.location.href='operations_perfromers.php'">
                                    <label class="btn btn-outline-light" for="nav_performers">
                                        <i class="bi bi-trophy-fill me-1"></i> Top Performers
                                    </label>
                                </div>
                            </div>
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
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-briefcase-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $active_stats['total'] ?? 0; ?></h3>
                            <p class="stat-label">Active Engagements</p>
                            <div class="stat-progress">
                                <span class="badge bg-info-soft text-info me-2">
                                    <i class="bi bi-play-circle me-1"></i><?php echo $active_stats['in_progress'] ?? 0; ?> In Progress
                                </span>
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-clock me-1"></i><?php echo $active_stats['awaiting_review'] ?? 0; ?> Review
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Points Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-trophy-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($points['total_points']); ?></h3>
                            <p class="stat-label">Total Points Earned</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success">
                                    <i class="bi bi-calendar-check me-1"></i><?php echo number_format($points['month_points']); ?> This Month
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CDP Records Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-mortarboard-fill text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $cdp_stats['total'] ?? 0; ?></h3>
                            <p class="stat-label">CDP Records</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success me-2">
                                    <i class="bi bi-check-circle me-1"></i><?php echo $cdp_stats['approved'] ?? 0; ?> Approved
                                </span>
                                <?php if (($cdp_stats['pending'] ?? 0) > 0): ?>
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-hourglass me-1"></i><?php echo $cdp_stats['pending']; ?> Pending
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-graph-up-arrow text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $completion_rate; ?>%</h3>
                            <p class="stat-label">Monthly Completion</p>
                            <div class="stat-progress">
                                <span class="badge bg-primary-soft text-primary me-2">
                                    <i class="bi bi-emoji-smile me-1"></i><?php echo $satisfaction_rate; ?>% Satisfaction
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Column - Tasks & Deadlines -->
            <div class="col-xl-8">
                <!-- Upcoming Tasks Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>
                            Upcoming Deadlines
                        </h5>
                        <a href="engagements.php" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($upcoming_result && mysqli_num_rows($upcoming_result) > 0): ?>
                            <div class="task-timeline">
                                <?php while($task = mysqli_fetch_assoc($upcoming_result)): 
                                    $days_class = $task['days_remaining'] <= 2 ? 'danger' : ($task['days_remaining'] <= 5 ? 'warning' : 'success');
                                    $status_class = $task['status'] == 'IN_PROGRESS' ? 'primary' : ($task['status'] == 'AWAITING_REVIEW' ? 'warning' : 'secondary');
                                ?>
                                <div class="task-item">
                                    <div class="task-indicator bg-<?php echo $days_class; ?>"></div>
                                    <div class="task-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="task-title">
                                                    <a href="engagements.php?source=view&id=<?php echo $task['engagement_id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($task['title']); ?>
                                                    </a>
                                                </h6>
                                                <p class="task-client">
                                                    <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($task['company_name']); ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo $task['status']; ?></span>
                                        </div>
                                        <div class="task-meta">
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
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-calendar-check display-4"></i>
                                <h6>No upcoming tasks</h6>
                                <p class="text-muted">You're all caught up! Great job!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
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
                                    $icon = $activity['type'] == 'engagement' ? 'briefcase' : ($activity['type'] == 'point' ? 'trophy' : 'mortarboard');
                                    $color = $activity['type'] == 'engagement' ? 'primary' : ($activity['type'] == 'point' ? 'success' : 'info');
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon bg-<?php echo $color; ?>-soft">
                                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <small class="activity-details text-muted"><?php echo htmlspecialchars($activity['details']); ?></small>
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

            <!-- Right Column - Quick Actions & Stats -->
            <div class="col-xl-4">
                <!-- Quick Actions Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge me-2 text-primary"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="engagements.php?source=upload" class="quick-action-item">
                                <div class="quick-action-icon bg-success-soft">
                                    <i class="bi bi-cloud-upload text-success"></i>
                                </div>
                                <span>Upload Evidence</span>
                            </a>
                            <a href="engagements.php?source=request_deadline" class="quick-action-item">
                                <div class="quick-action-icon bg-warning-soft">
                                    <i class="bi bi-calendar-plus text-warning"></i>
                                </div>
                                <span>Request Deadline</span>
                            </a>
                            <a href="cdp.php?source=add" class="quick-action-item">
                                <div class="quick-action-icon bg-info-soft">
                                    <i class="bi bi-mortarboard text-info"></i>
                                </div>
                                <span>Add CDP Record</span>
                            </a>
                            <a href="communications.php?source=new" class="quick-action-item">
                                <div class="quick-action-icon bg-primary-soft">
                                    <i class="bi bi-chat-dots text-primary"></i>
                                </div>
                                <span>Log Communication</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Performance Overview Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>
                            Performance Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="performance-stats">
                            <div class="performance-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Completion Rate</span>
                                    <span class="fw-bold"><?php echo $completion_rate; ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $completion_rate; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="performance-item mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Client Satisfaction</span>
                                    <span class="fw-bold"><?php echo $satisfaction_rate; ?>%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo $satisfaction_rate; ?>%"></div>
                                </div>
                            </div>

                            <div class="performance-metrics mt-4">
                                <div class="metric">
                                    <span class="metric-label">Completed This Month</span>
                                    <span class="metric-value"><?php echo $performance['completed_this_month'] ?? 0; ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">Points This Month</span>
                                    <span class="metric-value"><?php echo number_format($points['month_points']); ?></span>
                                </div>
                                <div class="metric">
                                    <span class="metric-label">CDP Approved</span>
                                    <span class="metric-value"><?php echo $cdp_stats['approved'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips & Insights Card -->
                <div class="dashboard-card mt-4 bg-gradient-primary">
                    <div class="card-body">
                        <h6 class="text-white mb-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            Pro Tip
                        </h6>
                        <p class="text-white-50 small mb-2">
                            <?php if ($overdue['overdue'] > 0): ?>
                                You have overdue tasks. Consider requesting deadline extensions or prioritizing them.
                            <?php elseif ($points['month_points'] > 1000): ?>
                                Great job! You've earned over 1000 points this month. Keep up the excellent work!
                            <?php elseif ($cdp_stats['pending'] > 0): ?>
                                You have CDP records pending approval. Check their status in the CDP section.
                            <?php else: ?>
                                Stay organized by uploading evidence as soon as tasks are completed.
                            <?php endif; ?>
                        </p>
                        <a href="performance.php" class="btn btn-sm btn-light mt-2">
                            View Full Performance <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this CSS to your stylesheet or in a style tag -->
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

.overdue-warning {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.9rem;
    margin-left: 15px;
    display: inline-block;
}

.current-date {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-card-body {
    display: flex;
    align-items: center;
    gap: 20px;
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

.stat-icon.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.stat-icon.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.stat-icon.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.stat-icon.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1.2;
}

.stat-label {
    color: #6c757d;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.stat-progress {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
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
}

.card-body {
    padding: 20px;
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
    color: #2c3e50;
    font-weight: 500;
}

.task-title a:hover {
    color: #f1bf70;
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

.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }

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

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.quick-action-item {
    text-decoration: none;
    color: #2c3e50;
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

/* Performance Metrics */
.performance-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    text-align: center;
}

.metric-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.metric-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
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
    
    .performance-metrics {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
// Function to refresh activity feed (can be enhanced with AJAX)
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

<?php include 'includes/operations_footer.php'; ?>