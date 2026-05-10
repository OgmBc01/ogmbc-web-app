<?php
include 'includes/sales_header.php';
include 'includes/sales_nav.php';
include 'includes/sales_sidebar.php';

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$current_month = date('m');
$current_year = date('Y');

// Get employee details
$emp_query = "SELECT e.*, u.username, r.role_name 
              FROM employees e 
              LEFT JOIN users u ON e.employee_id = u.user_id 
              LEFT JOIN user_roles r ON u.role_id = r.role_id 
              WHERE e.employee_id = $user_id";
$emp_result = mysqli_query($connection, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);

// ============================================
// DASHBOARD STATISTICS QUERIES
// ============================================

// 1. Sales Targets Summary
$targets_query = "SELECT 
    COUNT(*) as total_targets,
    SUM(CASE WHEN status = 'VALIDATED' THEN 1 ELSE 0 END) as validated_targets,
    SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted_targets,
    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_targets,
    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_targets,
    COALESCE(SUM(points_awarded), 0) as total_points,
    COALESCE(SUM(CASE WHEN status = 'VALIDATED' THEN points_awarded ELSE 0 END), 0) as points_from_validated,
    ROUND(AVG(CASE WHEN status = 'VALIDATED' THEN attainment_percentage END), 1) as avg_attainment,
    SUM(CASE WHEN status = 'VALIDATED' AND attainment_percentage >= 100 THEN 1 ELSE 0 END) as targets_exceeded,
    SUM(CASE WHEN status = 'VALIDATED' AND attainment_percentage >= 75 AND attainment_percentage < 100 THEN 1 ELSE 0 END) as targets_good,
    SUM(CASE WHEN status = 'VALIDATED' AND attainment_percentage < 75 THEN 1 ELSE 0 END) as targets_needs_improvement
    FROM sales_targets 
    WHERE employee_id = $user_id";
$targets_result = mysqli_query($connection, $targets_query);
$target_stats = mysqli_fetch_assoc($targets_result);

// 2. Current Month Target
$current_target_query = "SELECT 
    target_value,
    actual_value,
    attainment_percentage,
    points_awarded,
    status
    FROM sales_targets 
    WHERE employee_id = $user_id 
    AND year = $current_year 
    AND month = $current_month
    LIMIT 1";
$current_target_result = mysqli_query($connection, $current_target_query);
$current_target = mysqli_fetch_assoc($current_target_result);

// 3. CDP Records Summary
$cdp_query = "SELECT 
    COUNT(*) as total_cdp,
    SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved_cdp,
    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_cdp,
    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_cdp,
    COALESCE(SUM(CASE WHEN status = 'APPROVED' THEN uplift_percentage ELSE 0 END), 0) as total_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id";
$cdp_result = mysqli_query($connection, $cdp_query);
$cdp_stats = mysqli_fetch_assoc($cdp_result);

// 4. Points Ledger Summary
$points_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END), 0) as total_earned,
    COALESCE(SUM(CASE WHEN points_type = 'EARNED' AND MONTH(created_at) = $current_month AND YEAR(created_at) = $current_year THEN points ELSE 0 END), 0) as month_earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted
    FROM points_ledger 
    WHERE employee_id = $user_id";
$points_result = mysqli_query($connection, $points_query);
$points_stats = mysqli_fetch_assoc($points_result);

$net_points = ($points_stats['total_earned'] ?? 0) - ($points_stats['total_deducted'] ?? 0);

// 5. Recent Activities (Last 10)
$recent_query = "(
    SELECT 
        'target' as type,
        CONCAT('Sales target for ', DATE_FORMAT(MAKEDATE(year, 1) + INTERVAL (month - 1) MONTH, '%M %Y'), ' - ', status) as description,
        updated_at as created_at,
        CONCAT('Target: AED ', FORMAT(target_value, 0), ' | Actual: AED ', IFNULL(FORMAT(actual_value, 0), 'N/A')) as details
    FROM sales_targets 
    WHERE employee_id = $user_id
)
UNION ALL
(
    SELECT 
        'cdp' as type,
        CONCAT('CDP Record: ', title, ' - ', status) as description,
        created_at,
        CONCAT('Type: ', cdp_type, ' | Uplift: ', uplift_percentage, '%') as details
    FROM cdp_records 
    WHERE employee_id = $user_id
)
UNION ALL
(
    SELECT 
        'point' as type,
        CONCAT(points, ' points ', points_type) as description,
        created_at,
        source_type as details
    FROM points_ledger 
    WHERE employee_id = $user_id
)
UNION ALL
(
    SELECT 
        'activity' as type,
        CONCAT('Work Activity on ', DATE_FORMAT(activity_date, '%M %d, %Y')) as description,
        created_at,
        CONCAT(hours_worked, ' hours at ', work_location) as details
    FROM employee_activities 
    WHERE employee_id = $user_id
)
ORDER BY created_at DESC
LIMIT 10";
$recent_result = mysqli_query($connection, $recent_query);

// 6. Monthly Performance Chart Data (Last 6 months)
$chart_query = "SELECT 
    DATE_FORMAT(MAKEDATE(year, 1) + INTERVAL (month - 1) MONTH, '%b %Y') as month_label,
    year,
    month,
    AVG(CASE WHEN status = 'VALIDATED' THEN attainment_percentage ELSE NULL END) as avg_attainment,
    SUM(points_awarded) as points_earned
    FROM sales_targets 
    WHERE employee_id = $user_id 
    AND (year > $current_year - 1 OR (year = $current_year AND month >= $current_month - 5))
    GROUP BY year, month
    ORDER BY year DESC, month DESC
    LIMIT 6";
$chart_result = mysqli_query($connection, $chart_query);

$chart_months = [];
$chart_attainment = [];
$chart_points = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    array_unshift($chart_months, $row['month_label']);
    array_unshift($chart_attainment, round($row['avg_attainment'] ?? 0));
    array_unshift($chart_points, $row['points_earned'] ?? 0);
}

// 7. This Month's Activities Summary
$activities_query = "SELECT 
    COUNT(*) as total_days,
    SUM(hours_worked) as total_hours,
    ROUND(AVG(hours_worked), 1) as avg_hours,
    COUNT(DISTINCT work_location) as locations_count,
    SUM(CASE WHEN work_location = 'OGMBC' THEN 1 ELSE 0 END) as office_days,
    SUM(CASE WHEN work_location = 'Client Place' THEN 1 ELSE 0 END) as client_days,
    SUM(CASE WHEN work_location = 'Work from Home' THEN 1 ELSE 0 END) as wfh_days
    FROM employee_activities 
    WHERE employee_id = $user_id 
    AND MONTH(activity_date) = $current_month 
    AND YEAR(activity_date) = $current_year";
$activities_result = mysqli_query($connection, $activities_query);
$activities_stats = mysqli_fetch_assoc($activities_result);

// 8. CDP Uplift Impact
$cdp_uplift_query = "SELECT 
    cdp_type,
    COUNT(*) as count,
    SUM(uplift_percentage) as total_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id AND status = 'APPROVED'
    GROUP BY cdp_type";
$cdp_uplift_result = mysqli_query($connection, $cdp_uplift_query);
$cdp_uplift_data = [];
while ($row = mysqli_fetch_assoc($cdp_uplift_result)) {
    $cdp_uplift_data[$row['cdp_type']] = [
        'count' => $row['count'],
        'total' => $row['total_uplift']
    ];
}

// 9. Performance Rating
$total_targets = $target_stats['total_targets'] ?? 0;
$validated_targets = $target_stats['validated_targets'] ?? 0;
$avg_attainment = $target_stats['avg_attainment'] ?? 0;

if ($validated_targets >= 6 && $avg_attainment >= 100) {
    $performance_rating = 'Outstanding';
    $rating_color = 'success';
    $rating_icon = 'star-fill';
} elseif ($validated_targets >= 4 && $avg_attainment >= 85) {
    $performance_rating = 'Excellent';
    $rating_color = 'primary';
    $rating_icon = 'trophy-fill';
} elseif ($validated_targets >= 2 && $avg_attainment >= 70) {
    $performance_rating = 'Good';
    $rating_color = 'info';
    $rating_icon = 'check-circle-fill';
} elseif ($validated_targets > 0) {
    $performance_rating = 'Needs Improvement';
    $rating_color = 'warning';
    $rating_icon = 'exclamation-triangle-fill';
} else {
    $performance_rating = 'No Data';
    $rating_color = 'secondary';
    $rating_icon = 'question-circle-fill';
}
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
                                Welcome back, <?php echo htmlspecialchars($employee['first_name'] ?? 'Sales Professional'); ?>! 🎯
                            </h2>
                            <p class="welcome-subtitle">
                                Here's your sales performance overview for <?php echo date('F Y'); ?>.
                                <?php if (($target_stats['pending_targets'] ?? 0) > 0): ?>
                                    <span class="overdue-warning">⚠️ You have <?php echo $target_stats['pending_targets']; ?> pending target<?php echo ($target_stats['pending_targets'] ?? 0) > 1 ? 's' : ''; ?> to submit</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="btn-group mb-2" role="group">
                                <button class="btn btn-light active" onclick="window.location.href='sales_dashboard.php'">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </button>
                                <button class="btn btn-outline-light" onclick="window.location.href='sales_performers.php'">
                                    <i class="bi bi-trophy-fill me-1"></i> Top Performers
                                </button>
                            </div>
                            <div class="current-date">
                                <i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Sales Targets Card -->
            <div class="col-xl-3 col-md-6">
                <a href="sales_targets.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-primary-soft">
                                <i class="bi bi-bullseye text-primary"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $target_stats['total_targets'] ?? 0; ?></h3>
                                <p class="stat-label">Sales Targets</p>
                                <div class="stat-progress">
                                    <span class="badge bg-success-soft text-success me-1">
                                        <i class="bi bi-check-circle me-1"></i><?php echo $target_stats['validated_targets'] ?? 0; ?> Validated
                                    </span>
                                    <span class="badge bg-warning-soft text-warning">
                                        <i class="bi bi-clock me-1"></i><?php echo ($target_stats['pending_targets'] ?? 0) + ($target_stats['submitted_targets'] ?? 0); ?> Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Points Card -->
            <div class="col-xl-3 col-md-6">
                <a href="wallet.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-success-soft">
                                <i class="bi bi-trophy-fill text-success"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo number_format($net_points); ?></h3>
                                <p class="stat-label">Net Points Earned</p>
                                <div class="stat-progress">
                                    <span class="badge bg-success-soft text-success">
                                        <i class="bi bi-calendar-check me-1"></i><?php echo number_format($points_stats['month_earned'] ?? 0); ?> This Month
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- CDP Records Card -->
            <div class="col-xl-3 col-md-6">
                <a href="cdp.php" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-card-body">
                            <div class="stat-icon bg-info-soft">
                                <i class="bi bi-mortarboard-fill text-info"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo $cdp_stats['total_cdp'] ?? 0; ?></h3>
                                <p class="stat-label">CDP Records</p>
                                <div class="stat-progress">
                                    <span class="badge bg-success-soft text-success me-1">
                                        <i class="bi bi-check-circle me-1"></i><?php echo $cdp_stats['approved_cdp'] ?? 0; ?> Approved
                                    </span>
                                    <span class="badge bg-info-soft text-info">
                                        <i class="bi bi-percent me-1"></i>+<?php echo $cdp_stats['total_uplift'] ?? 0; ?>% Uplift
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Avg Attainment Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-graph-up-arrow text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $avg_attainment ?: 0; ?>%</h3>
                            <p class="stat-label">Avg. Attainment</p>
                            <div class="stat-progress">
                                <span class="badge bg-primary-soft text-primary">
                                    <i class="bi bi-emoji-smile me-1"></i><?php echo $performance_rating; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-xl-8">
                <!-- Current Month Target Card -->
                <?php if ($current_target): ?>
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>
                            Current Month Target - <?php echo date('F Y'); ?>
                        </h5>
                        <span class="badge bg-<?php echo $current_target['status'] == 'VALIDATED' ? 'success' : ($current_target['status'] == 'SUBMITTED' ? 'info' : 'warning'); ?>">
                            <?php echo $current_target['status']; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="current-target-stats">
                                    <div class="stat-item">
                                        <label>Target Amount</label>
                                        <h4 class="text-primary">AED <?php echo number_format($current_target['target_value'], 2); ?></h4>
                                    </div>
                                    <div class="stat-item mt-2">
                                        <label>Actual Achievement</label>
                                        <h4 class="text-success">AED <?php echo number_format($current_target['actual_value'] ?? 0, 2); ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="attainment-circle">
                                    <div class="circular-progress" data-progress="<?php echo $current_target['attainment_percentage'] ?? 0; ?>">
                                        <div class="progress-value"><?php echo round($current_target['attainment_percentage'] ?? 0); ?>%</div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <?php if ($current_target['points_awarded']): ?>
                                            <span class="badge bg-success">🎯 <?php echo number_format($current_target['points_awarded']); ?> Points Earned</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($current_target['status'] == 'PENDING'): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Submit your actual achievement for this month to get validated and earn points!
                            <a href="sales_targets.php?source=submit_achievement&id=<?php echo $current_target['target_id']; ?>" class="btn btn-sm btn-primary ms-3">
                                Submit Now <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Performance Chart Card -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Performance Trend (Last 6 Months)
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary active" onclick="showChart('attainment')">Attainment</button>
                            <button class="btn btn-outline-primary" onclick="showChart('points')">Points</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Recent Activities Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
                            Recent Activities
                        </h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                            <div class="activity-feed">
                                <?php while($activity = mysqli_fetch_assoc($recent_result)): 
                                    $icon = $activity['type'] == 'target' ? 'bullseye' : ($activity['type'] == 'cdp' ? 'mortarboard' : ($activity['type'] == 'point' ? 'trophy' : 'calendar-check'));
                                    $color = $activity['type'] == 'target' ? 'primary' : ($activity['type'] == 'cdp' ? 'info' : ($activity['type'] == 'point' ? 'success' : 'warning'));
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

            <!-- Right Column -->
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
                            <a href="sales_targets.php?source=submit_achievement" class="quick-action-item">
                                <div class="quick-action-icon bg-success-soft">
                                    <i class="bi bi-cloud-upload text-success"></i>
                                </div>
                                <span>Submit Achievement</span>
                            </a>
                            <a href="cdp.php?source=add" class="quick-action-item">
                                <div class="quick-action-icon bg-info-soft">
                                    <i class="bi bi-mortarboard text-info"></i>
                                </div>
                                <span>Add CDP Record</span>
                            </a>
                            <a href="employee_activities.php?source=add" class="quick-action-item">
                                <div class="quick-action-icon bg-warning-soft">
                                    <i class="bi bi-calendar-plus text-warning"></i>
                                </div>
                                <span>Log Activity</span>
                            </a>
                            <a href="sales_targets.php" class="quick-action-item">
                                <div class="quick-action-icon bg-primary-soft">
                                    <i class="bi bi-bar-chart text-primary"></i>
                                </div>
                                <span>View All Targets</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CDP Uplift Breakdown Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>
                            CDP Uplift Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($cdp_uplift_data)): ?>
                            <div class="cdp-breakdown">
                                <?php
                                $uplift_types = [
                                    'CERTIFICATE' => ['label' => 'Certificates', 'color' => 'success'],
                                    'COURSE' => ['label' => 'Courses', 'color' => 'info'],
                                    'LOYALTY' => ['label' => 'Loyalty', 'color' => 'warning'],
                                    'BEHAVIOR' => ['label' => 'Behavior', 'color' => 'primary']
                                ];
                                foreach ($uplift_types as $type => $info):
                                    $data = $cdp_uplift_data[$type] ?? null;
                                ?>
                                <div class="uplift-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-<?php echo $info['color']; ?>"><?php echo $info['label']; ?></span>
                                        <span class="fw-bold text-<?php echo $info['color']; ?>">+<?php echo $data['total'] ?? 0; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-<?php echo $info['color']; ?>" style="width: <?php echo min(100, ($data['total'] ?? 0) * 2); ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo $data['count'] ?? 0; ?> record(s)</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Total CDP Uplift: <strong>+<?php echo $cdp_stats['total_uplift'] ?? 0; ?>%</strong>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-mortarboard display-4"></i>
                                <h6>No CDP Records Yet</h6>
                                <p class="text-muted">Add CDP records to boost your performance uplift.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- This Month's Activities Card -->
                <div class="dashboard-card mt-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-briefcase me-2 text-primary"></i>
                            This Month's Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo round($activities_stats['total_hours'] ?? 0, 1); ?></div>
                                <div class="stat-box-label">Total Hours</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $activities_stats['total_days'] ?? 0; ?></div>
                                <div class="stat-box-label">Active Days</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value"><?php echo $activities_stats['avg_hours'] ?? 0; ?></div>
                                <div class="stat-box-label">Avg Hours/Day</div>
                            </div>
                        </div>
                        
                        <?php if (($activities_stats['office_days'] ?? 0) > 0 || ($activities_stats['client_days'] ?? 0) > 0): ?>
                        <div class="location-breakdown mt-3">
                            <small class="text-muted d-block mb-2">Work Location Distribution:</small>
                            <?php if (($activities_stats['office_days'] ?? 0) > 0): ?>
                            <span class="badge bg-primary me-1">🏢 Office: <?php echo $activities_stats['office_days']; ?> days</span>
                            <?php endif; ?>
                            <?php if (($activities_stats['client_days'] ?? 0) > 0): ?>
                            <span class="badge bg-info me-1">🤝 Client: <?php echo $activities_stats['client_days']; ?> days</span>
                            <?php endif; ?>
                            <?php if (($activities_stats['wfh_days'] ?? 0) > 0): ?>
                            <span class="badge bg-warning">🏠 WFH: <?php echo $activities_stats['wfh_days']; ?> days</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Performance Tips Card -->
                <div class="dashboard-card mt-4 bg-gradient-primary">
                    <div class="card-body">
                        <h6 class="text-white mb-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            Performance Insights
                        </h6>
                        <ul class="text-white-50 small mb-0 ps-3">
                            <?php if ($target_stats['targets_exceeded'] > 0): ?>
                                <li>🎉 Excellent! You've exceeded <?php echo $target_stats['targets_exceeded']; ?> target(s). Keep pushing!</li>
                            <?php endif; ?>
                            <?php if (($cdp_stats['pending_cdp'] ?? 0) > 0): ?>
                                <li>📚 You have <?php echo $cdp_stats['pending_cdp']; ?> CDP record(s) pending approval.</li>
                            <?php endif; ?>
                            <?php if (($target_stats['pending_targets'] ?? 0) > 0): ?>
                                <li>🎯 Don't forget to submit your pending sales targets.</li>
                            <?php endif; ?>
                            <?php if (($cdp_stats['total_uplift'] ?? 0) < 10): ?>
                                <li>💡 Add more CDP records to increase your performance uplift.</li>
                            <?php endif; ?>
                            <?php if (($activities_stats['total_hours'] ?? 0) < 160 && date('d') > 20): ?>
                                <li>⏰ Consider logging more activities this month.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
.stat-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

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

.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }

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
    background: linear-gradient(135deg, #002147 0%, #003366 100%) !important;
    color: #fff;
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

/* Current Target */
.current-target-stats .stat-item label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
    display: block;
}

.current-target-stats .stat-item h4 {
    margin: 0;
}

.attainment-circle {
    text-align: center;
}

.circular-progress {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg, #e9ecef 0deg);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.circular-progress .progress-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
}

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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    text-align: center;
}

.stat-box {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 12px;
}

.stat-box-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0a2240;
}

.stat-box-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 5px;
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

/* Uplift Items */
.uplift-item .progress {
    background-color: #e9ecef;
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
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .stat-box {
        padding: 8px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentChart = null;

// Initialize circular progress indicators
document.querySelectorAll('.circular-progress').forEach(element => {
    const progress = element.getAttribute('data-progress') || 0;
    const angle = (progress / 100) * 360;
    element.style.background = `conic-gradient(#28a745 ${angle}deg, #e9ecef ${angle}deg)`;
});

// Initialize performance chart
function initChart(type = 'attainment') {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    if (currentChart) {
        currentChart.destroy();
    }
    
    const months = <?php echo json_encode($chart_months); ?>;
    const attainmentData = <?php echo json_encode($chart_attainment); ?>;
    const pointsData = <?php echo json_encode($chart_points); ?>;
    
    currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: type === 'attainment' ? 'Attainment Percentage (%)' : 'Points Earned',
                data: type === 'attainment' ? attainmentData : pointsData,
                borderColor: type === 'attainment' ? '#ffc107' : '#28a745',
                backgroundColor: type === 'attainment' ? 'rgba(255, 193, 7, 0.1)' : 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: type === 'attainment' ? '#ffc107' : '#28a745',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}${type === 'attainment' ? '%' : ' points'}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: type === 'attainment' ? 'Attainment (%)' : 'Points'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        }
    });
}

function showChart(type) {
    initChart(type);
    // Update button active state
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    initChart('attainment');
});
</script>

<?php include 'includes/sales_footer.php'; ?>