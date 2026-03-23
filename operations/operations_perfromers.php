<?php

include __DIR__ . '/includes/operations_header.php';
include __DIR__ . '/includes/operations_nav.php';
include __DIR__ . '/includes/operations_sidebar.php';

// Initialize session with security settings
if (!function_exists('initSession')) {
    include_once __DIR__ . '/includes/operations_functions.php';
}
initSession();

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Get current period from URL or default to weekly
$period = isset($_GET['period']) ? $_GET['period'] : 'weekly';

// Calculate date ranges based on selected period
$today = date('Y-m-d');
$current_year = date('Y');
$current_month = date('m');

switch ($period) {
    case 'weekly':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        $period_label = 'This Week';
        break;
    case 'monthly':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $period_label = 'This Month';
        break;
    case 'quarterly':
        $current_quarter = ceil(date('n') / 3);
        $start_date = date('Y-m-d', strtotime(date('Y') . '-' . (($current_quarter - 1) * 3 + 1) . '-01'));
        $end_date = date('Y-m-d', strtotime(date('Y') . '-' . ($current_quarter * 3) . '-' . date('t', mktime(0, 0, 0, $current_quarter * 3, 1, date('Y')))));
        $period_label = 'This Quarter';
        break;
    default:
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        $period_label = 'This Week';
}

// ============================================
// TOP PERFORMERS QUERY (WEEKLY/MONTHLY/QUARTERLY)
// ============================================
$top_performers_query = "
    SELECT 
        u.user_id,
        CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
        u.user_email,
        r.role_name,
        COALESCE(SUM(pl.points), 0) AS total_points,
        COUNT(CASE WHEN pl.points_type = 'EARNED' THEN 1 END) AS earned_count,
        COUNT(CASE WHEN pl.points_type = 'DEDUCTED' THEN 1 END) AS deducted_count,
        COUNT(DISTINCT DATE(pl.created_at)) AS active_days
    FROM users u
    LEFT JOIN points_ledger pl ON u.user_id = pl.employee_id 
        AND pl.created_at BETWEEN '$start_date' AND '$end_date' 
        AND pl.points_type != 'ADJUSTMENT'
    LEFT JOIN user_roles r ON u.role_id = r.role_id
    WHERE u.user_status = 'active' 
        AND u.role_id != 1  -- Exclude admins from performers list
    GROUP BY u.user_id
    HAVING total_points > 0 OR earned_count > 0
    ORDER BY total_points DESC
    LIMIT 3
";

$top_performers_result = mysqli_query($connection, $top_performers_query);

// Fetch top performers data
$top_performers = [];
if ($top_performers_result && mysqli_num_rows($top_performers_result) > 0) {
    while ($row = mysqli_fetch_assoc($top_performers_result)) {
        $top_performers[] = $row;
    }
}

// If less than 3 performers, add placeholder
while (count($top_performers) < 3) {
    $top_performers[] = [
        'user_id' => null,
        'employee_name' => 'No Data',
        'user_email' => '',
        'role_name' => '',
        'total_points' => 0,
        'earned_count' => 0,
        'deducted_count' => 0,
        'active_days' => 0
    ];
}

// Get additional statistics for the selected period
$period_stats_query = "
    SELECT 
        COUNT(DISTINCT employee_id) AS active_employees,
        COALESCE(SUM(points), 0) AS total_points_awarded,
        AVG(points) AS avg_points_per_transaction,
        COUNT(*) AS total_transactions
    FROM points_ledger
    WHERE created_at BETWEEN '$start_date' AND '$end_date'
        AND points_type != 'ADJUSTMENT'
";

$period_stats_result = mysqli_query($connection, $period_stats_query);
$period_stats = mysqli_fetch_assoc($period_stats_result);

// Get top performer's recent achievements
$top_performer_id = isset($top_performers[0]['user_id']) ? $top_performers[0]['user_id'] : null;
$recent_achievements = [];
if ($top_performer_id) {
    $recent_achievements_query = "
        SELECT 
            pl.points,
            pl.points_type,
            pl.description,
            pl.created_at,
            CONCAT(u.first_name, ' ', u.last_name) AS awarded_by_name
        FROM points_ledger pl
        LEFT JOIN users u ON pl.awarded_by = u.user_id
        WHERE pl.employee_id = $top_performer_id
            AND pl.created_at BETWEEN '$start_date' AND '$end_date'
        ORDER BY pl.created_at DESC
        LIMIT 5
    ";
    $recent_achievements_result = mysqli_query($connection, $recent_achievements_query);
    if ($recent_achievements_result) {
        while ($row = mysqli_fetch_assoc($recent_achievements_result)) {
            $recent_achievements[] = $row;
        }
    }
}

// Get historical trends for the selected period (last 6 periods)
$historical_data = [];
if ($period == 'weekly') {
    // Last 6 weeks
    for ($i = 5; $i >= 0; $i--) {
        $week_start = date('Y-m-d', strtotime("-$i weeks monday"));
        $week_end = date('Y-m-d', strtotime("-$i weeks sunday"));
        $week_label = 'Week ' . date('W', strtotime($week_start));
        
        $week_query = "
            SELECT COALESCE(SUM(points), 0) AS total_points
            FROM points_ledger
            WHERE created_at BETWEEN '$week_start' AND '$week_end'
                AND points_type = 'EARNED'
        ";
        $week_result = mysqli_query($connection, $week_query);
        $week_data = mysqli_fetch_assoc($week_result);
        $historical_data[] = [
            'label' => $week_label,
            'points' => (int)$week_data['total_points']
        ];
    }
} elseif ($period == 'monthly') {
    // Last 6 months
    for ($i = 5; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t', strtotime("-$i months"));
        $month_label = date('M Y', strtotime($month_start));
        
        $month_query = "
            SELECT COALESCE(SUM(points), 0) AS total_points
            FROM points_ledger
            WHERE created_at BETWEEN '$month_start' AND '$month_end'
                AND points_type = 'EARNED'
        ";
        $month_result = mysqli_query($connection, $month_query);
        $month_data = mysqli_fetch_assoc($month_result);
        $historical_data[] = [
            'label' => $month_label,
            'points' => (int)$month_data['total_points']
        ];
    }
} else {
    // Last 6 quarters
    for ($i = 5; $i >= 0; $i--) {
        $quarter_num = ceil(date('n') / 3) - $i;
        $year = date('Y');
        if ($quarter_num <= 0) {
            $quarter_num += 4;
            $year--;
        }
        $quarter_start = date('Y-m-d', strtotime($year . '-' . (($quarter_num - 1) * 3 + 1) . '-01'));
        $quarter_end = date('Y-m-t', strtotime($year . '-' . ($quarter_num * 3) . '-01'));
        $quarter_label = 'Q' . $quarter_num . ' ' . $year;
        
        $quarter_query = "
            SELECT COALESCE(SUM(points), 0) AS total_points
            FROM points_ledger
            WHERE created_at BETWEEN '$quarter_start' AND '$quarter_end'
                AND points_type = 'EARNED'
        ";
        $quarter_result = mysqli_query($connection, $quarter_query);
        $quarter_data = mysqli_fetch_assoc($quarter_result);
        $historical_data[] = [
            'label' => $quarter_label,
            'points' => (int)$quarter_data['total_points']
        ];
    }
}

// Get employee ranking distribution
$ranking_distribution_query = "
    SELECT 
        CASE 
            WHEN total_points >= 100 THEN 'Platinum (100+)'
            WHEN total_points >= 50 THEN 'Gold (50-99)'
            WHEN total_points >= 25 THEN 'Silver (25-49)'
            WHEN total_points >= 10 THEN 'Bronze (10-24)'
            ELSE 'Rising Star (0-9)'
        END AS tier,
        COUNT(*) AS count
    FROM (
        SELECT COALESCE(SUM(points), 0) AS total_points
        FROM users u
        LEFT JOIN points_ledger pl ON u.user_id = pl.employee_id 
            AND pl.created_at BETWEEN '$start_date' AND '$end_date'
            AND pl.points_type = 'EARNED'
        WHERE u.user_status = 'active' AND u.role_id != 1
        GROUP BY u.user_id
    ) AS employee_points
    GROUP BY tier
    ORDER BY 
        CASE 
            WHEN tier = 'Platinum (100+)' THEN 1
            WHEN tier = 'Gold (50-99)' THEN 2
            WHEN tier = 'Silver (25-49)' THEN 3
            WHEN tier = 'Bronze (10-24)' THEN 4
            ELSE 5
        END
";

$ranking_result = mysqli_query($connection, $ranking_distribution_query);
$ranking_tiers = [];
$ranking_counts = [];
if ($ranking_result) {
    while ($row = mysqli_fetch_assoc($ranking_result)) {
        $ranking_tiers[] = $row['tier'];
        $ranking_counts[] = $row['count'];
    }
}

// Get period comparison data (previous period)
$previous_period_stats = [];
switch ($period) {
    case 'weekly':
        $prev_start = date('Y-m-d', strtotime('monday last week'));
        $prev_end = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'monthly':
        $prev_start = date('Y-m-01', strtotime('first day of last month'));
        $prev_end = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'quarterly':
        $prev_quarter = ceil(date('n') / 3) - 1;
        $prev_year = date('Y');
        if ($prev_quarter <= 0) {
            $prev_quarter = 4;
            $prev_year--;
        }
        $prev_start = date('Y-m-d', strtotime($prev_year . '-' . (($prev_quarter - 1) * 3 + 1) . '-01'));
        $prev_end = date('Y-m-t', strtotime($prev_year . '-' . ($prev_quarter * 3) . '-01'));
        break;
}

if (isset($prev_start) && isset($prev_end)) {
    $prev_stats_query = "
        SELECT 
            COALESCE(SUM(points), 0) AS total_points,
            COUNT(DISTINCT employee_id) AS active_employees
        FROM points_ledger
        WHERE created_at BETWEEN '$prev_start' AND '$prev_end'
            AND points_type = 'EARNED'
    ";
    $prev_stats_result = mysqli_query($connection, $prev_stats_query);
    $previous_period_stats = mysqli_fetch_assoc($prev_stats_result);
}

// Calculate percentage changes
$points_change = 0;
if ($previous_period_stats['total_points'] > 0) {
    $points_change = round((($period_stats['total_points_awarded'] - $previous_period_stats['total_points']) / $previous_period_stats['total_points']) * 100);
}

$employees_change = 0;
if ($previous_period_stats['active_employees'] > 0) {
    $employees_change = round((($period_stats['active_employees'] - $previous_period_stats['active_employees']) / $previous_period_stats['active_employees']) * 100);
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent" style="background: linear-gradient(135deg, #f8fafc 0%, #f6f7fb 100%); min-height: 100vh;">
    <div class="container-fluid py-3">
        
        <!-- Header with Navigation Radios -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="welcome-title">
                                🏆 Top Performers
                            </h2>
                            <p class="welcome-subtitle">
                                Recognizing excellence and celebrating achievements
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="page-navigation-group">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="page_navigation" id="nav_dashboard" autocomplete="off" 
                                           onclick="window.location.href='operations_dashboard.php'">
                                    <label class="btn btn-outline-light" for="nav_dashboard">
                                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="page_navigation" id="nav_performers" autocomplete="off" checked onclick="window.location.href='operations_perfromers.php'">
                                    <label class="btn btn-light active" for="nav_performers">
                                        <i class="bi bi-trophy-fill me-1"></i> Top Performers
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Selector Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="period-selector">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="?period=weekly" class="period-card <?php echo $period == 'weekly' ? 'active' : ''; ?>">
                                <div class="period-card-content">
                                    <i class="bi bi-calendar-week-fill period-icon"></i>
                                    <h4>Weekly</h4>
                                    <p class="period-date"><?php echo date('M d', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)); ?></p>
                                    <?php if ($period == 'weekly'): ?>
                                        <span class="active-badge">Current View</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="?period=monthly" class="period-card <?php echo $period == 'monthly' ? 'active' : ''; ?>">
                                <div class="period-card-content">
                                    <i class="bi bi-calendar-month-fill period-icon"></i>
                                    <h4>Monthly</h4>
                                    <p class="period-date"><?php echo date('F Y', strtotime($start_date)); ?></p>
                                    <?php if ($period == 'monthly'): ?>
                                        <span class="active-badge">Current View</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="?period=quarterly" class="period-card <?php echo $period == 'quarterly' ? 'active' : ''; ?>">
                                <div class="period-card-content">
                                    <i class="bi bi-calendar-range-fill period-icon"></i>
                                    <h4>Quarterly</h4>
                                    <p class="period-date"><?php echo date('M d', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date)); ?></p>
                                    <?php if ($period == 'quarterly'): ?>
                                        <span class="active-badge">Current View</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 3 Performers Display -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="dashboard-card shadow-lg p-4" style="border-radius: 20px;">
                    <div class="card-header py-3 px-4">
                        <h5 class="card-title">
                            <i class="bi bi-trophy-fill me-2 text-warning"></i>
                            Top 3 Performers - <?php echo $period_label; ?>
                        </h5>
                        <span class="badge bg-primary">🏅 Based on Points Earned</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 performers-row">
                            <!-- 2nd Place -->
                            <div class="col-md-4 order-md-1">
                                <div class="performer-card performer-silver">
                                    <div class="performer-rank rank-2">
                                        <i class="bi bi-trophy-fill"></i>
                                        <span>2</span>
                                    </div>
                                    <div class="performer-avatar bg-secondary-soft">
                                        <i class="bi bi-person-circle fs-1 text-secondary"></i>
                                    </div>
                                    <h4 class="performer-name"><?php echo htmlspecialchars($top_performers[1]['employee_name']); ?></h4>
                                    <p class="performer-role"><?php echo htmlspecialchars($top_performers[1]['role_name'] ?? 'Employee'); ?></p>
                                    <div class="performer-points">
                                        <span class="points-number"><?php echo number_format($top_performers[1]['total_points']); ?></span>
                                        <span class="points-label">Points</span>
                                    </div>
                                    <div class="performer-stats">
                                        <div class="stat-badge">
                                            <i class="bi bi-plus-circle-fill text-success"></i>
                                            <?php echo $top_performers[1]['earned_count']; ?> Earned
                                        </div>
                                        <div class="stat-badge">
                                            <i class="bi bi-dash-circle-fill text-danger"></i>
                                            <?php echo $top_performers[1]['deducted_count']; ?> Deducted
                                        </div>
                                    </div>
                                    <?php if ($top_performers[1]['user_id']): ?>
                                        <a href="profile.php?user_id=<?php echo $top_performers[1]['user_id']; ?>" class="btn btn-outline-secondary btn-sm mt-3">
                                            View Profile <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- 1st Place (Champion) -->
                            <div class="col-md-4 order-md-2">
                                <div class="performer-card performer-gold champion">
                                    <div class="performer-rank rank-1">
                                        <i class="bi bi-trophy-fill"></i>
                                        <span>1</span>
                                    </div>
                                    <div class="performer-avatar bg-warning-soft">
                                        <i class="bi bi-star-fill fs-1 text-warning"></i>
                                    </div>
                                    <h4 class="performer-name"><?php echo htmlspecialchars($top_performers[0]['employee_name']); ?></h4>
                                    <p class="performer-role"><?php echo htmlspecialchars($top_performers[0]['role_name'] ?? 'Employee'); ?></p>
                                    <div class="performer-points champion-points">
                                        <span class="points-number"><?php echo number_format($top_performers[0]['total_points']); ?></span>
                                        <span class="points-label">Points</span>
                                    </div>
                                    <div class="performer-stats">
                                        <div class="stat-badge">
                                            <i class="bi bi-plus-circle-fill text-success"></i>
                                            <?php echo $top_performers[0]['earned_count']; ?> Earned
                                        </div>
                                        <div class="stat-badge">
                                            <i class="bi bi-dash-circle-fill text-danger"></i>
                                            <?php echo $top_performers[0]['deducted_count']; ?> Deducted
                                        </div>
                                        <div class="stat-badge">
                                            <i class="bi bi-calendar-check-fill text-info"></i>
                                            <?php echo $top_performers[0]['active_days']; ?> Active Days
                                        </div>
                                    </div>
                                    <?php if ($top_performers[0]['user_id']): ?>
                                        <a href="profile.php?user_id=<?php echo $top_performers[0]['user_id']; ?>" class="btn btn-warning btn-sm mt-3">
                                            <i class="bi bi-crown"></i> View Champion
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- 3rd Place -->
                            <div class="col-md-4 order-md-3">
                                <div class="performer-card performer-bronze">
                                    <div class="performer-rank rank-3">
                                        <i class="bi bi-trophy-fill"></i>
                                        <span>3</span>
                                    </div>
                                    <div class="performer-avatar bg-secondary-soft">
                                        <i class="bi bi-person-circle fs-1 text-secondary"></i>
                                    </div>
                                    <h4 class="performer-name"><?php echo htmlspecialchars($top_performers[2]['employee_name']); ?></h4>
                                    <p class="performer-role"><?php echo htmlspecialchars($top_performers[2]['role_name'] ?? 'Employee'); ?></p>
                                    <div class="performer-points">
                                        <span class="points-number"><?php echo number_format($top_performers[2]['total_points']); ?></span>
                                        <span class="points-label">Points</span>
                                    </div>
                                    <div class="performer-stats">
                                        <div class="stat-badge">
                                            <i class="bi bi-plus-circle-fill text-success"></i>
                                            <?php echo $top_performers[2]['earned_count']; ?> Earned
                                        </div>
                                        <div class="stat-badge">
                                            <i class="bi bi-dash-circle-fill text-danger"></i>
                                            <?php echo $top_performers[2]['deducted_count']; ?> Deducted
                                        </div>
                                    </div>
                                    <?php if ($top_performers[2]['user_id']): ?>
                                        <a href="profile.php?user_id=<?php echo $top_performers[2]['user_id']; ?>" class="btn btn-outline-secondary btn-sm mt-3">
                                            View Profile <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- Period Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card stat-blue">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-people-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($period_stats['active_employees'] ?? 0); ?></h3>
                            <p class="stat-label">Active Employees</p>
                            <div class="stat-progress">
                                <?php if ($employees_change > 0): ?>
                                    <small class="text-success">↑ <?php echo $employees_change; ?>% vs previous</small>
                                <?php elseif ($employees_change < 0): ?>
                                    <small class="text-danger">↓ <?php echo abs($employees_change); ?>% vs previous</small>
                                <?php else: ?>
                                    <small class="text-muted">No change vs previous</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-green">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-trophy-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($period_stats['total_points_awarded'] ?? 0); ?></h3>
                            <p class="stat-label">Points Awarded</p>
                            <div class="stat-progress">
                                <?php if ($points_change > 0): ?>
                                    <small class="text-success">↑ <?php echo $points_change; ?>% vs previous</small>
                                <?php elseif ($points_change < 0): ?>
                                    <small class="text-danger">↓ <?php echo abs($points_change); ?>% vs previous</small>
                                <?php else: ?>
                                    <small class="text-muted">No change vs previous</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-teal">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-bar-chart-fill text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($period_stats['total_transactions'] ?? 0); ?></h3>
                            <p class="stat-label">Transactions</p>
                            <div class="stat-progress">
                                <small class="text-muted">Avg <?php echo round($period_stats['avg_points_per_transaction'] ?? 0); ?> pts/transaction</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Additional Info Row -->
        <div class="row g-4 mb-4">
            <!-- Historical Trends Chart -->
            <div class="col-xl-6">
                <div class="dashboard-card">
                    <div class="card-header py-3 px-4">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Points Trend - <?php echo ucfirst($period); ?> Comparison
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Employee Ranking Distribution -->
            <div class="col-xl-6">
                <div class="dashboard-card">
                    <div class="card-header py-3 px-4">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>
                            Employee Ranking Distribution
                        </h5>
                        <span class="badge bg-secondary"><?php echo $period_label; ?></span>
                    </div>
                    <div class="card-body">
                        <canvas id="rankingChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Achievements and Leaderboard -->
        <div class="row g-4">
            <!-- Recent Achievements of Top Performer -->
            <div class="col-xl-6">
                <div class="dashboard-card h-100">
                    <div class="card-header py-3 px-4">
                        <h5 class="card-title">
                            <i class="bi bi-star-fill me-2 text-warning"></i>
                            Recent Achievements - Top Performer
                        </h5>
                        <span class="badge bg-warning">🏆 Champion's Feed</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($recent_achievements) > 0): ?>
                            <div class="achievements-feed">
                                <?php foreach ($recent_achievements as $achievement): ?>
                                    <div class="achievement-item">
                                        <div class="achievement-icon <?php echo $achievement['points_type'] == 'EARNED' ? 'bg-success-soft' : 'bg-danger-soft'; ?>">
                                            <i class="bi bi-<?php echo $achievement['points_type'] == 'EARNED' ? 'plus-circle' : 'dash-circle'; ?>-fill"></i>
                                        </div>
                                        <div class="achievement-content">
                                            <p class="achievement-text">
                                                <?php echo htmlspecialchars($achievement['description'] ?? ($achievement['points_type'] == 'EARNED' ? 'Points earned' : 'Points deducted')); ?>
                                            </p>
                                            <div class="achievement-meta">
                                                <span class="achievement-points <?php echo $achievement['points_type'] == 'EARNED' ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $achievement['points_type'] == 'EARNED' ? '+' : '-'; ?><?php echo $achievement['points']; ?> pts
                                                </span>
                                                <?php if ($achievement['awarded_by_name']): ?>
                                                    <span class="achievement-awarder">
                                                        <i class="bi bi-person"></i> by <?php echo htmlspecialchars($achievement['awarded_by_name']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="achievement-time">
                                                    <i class="bi bi-clock"></i> <?php echo date('M d, H:i', strtotime($achievement['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-emoji-smile fs-1 text-muted"></i>
                                <p class="text-muted mt-3">No achievements recorded for the top performer this period.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Preview -->
            <div class="col-xl-6">
                <div class="dashboard-card h-100">
                    <div class="card-header py-3 px-4">
                        <h5 class="card-title">
                            <i class="bi bi-list-ul me-2 text-primary"></i>
                            How Rankings Work
                        </h5>
                        <a href="points_ledger.php" class="btn btn-sm btn-outline-primary">View Full Ledger</a>
                    </div>
                    <div class="card-body">
                        <div class="ranking-info">
                            <div class="ranking-tier platinum">
                                <div class="tier-icon">
                                    <i class="bi bi-gem"></i>
                                </div>
                                <div class="tier-info">
                                    <h6>Platinum (100+ points)</h6>
                                    <p>Top performers who consistently exceed expectations</p>
                                </div>
                            </div>
                            <div class="ranking-tier gold">
                                <div class="tier-icon">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div class="tier-info">
                                    <h6>Gold (50-99 points)</h6>
                                    <p>Outstanding contributors with exceptional performance</p>
                                </div>
                            </div>
                            <div class="ranking-tier silver">
                                <div class="tier-icon">
                                    <i class="bi bi-star"></i>
                                </div>
                                <div class="tier-info">
                                    <h6>Silver (25-49 points)</h6>
                                    <p>Consistent performers who deliver quality work</p>
                                </div>
                            </div>
                            <div class="ranking-tier bronze">
                                <div class="tier-icon">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="tier-info">
                                    <h6>Bronze (10-24 points)</h6>
                                    <p>Emerging talents showing great potential</p>
                                </div>
                            </div>
                            <div class="ranking-tier rising">
                                <div class="tier-icon">
                                    <i class="bi bi-flower1"></i>
                                </div>
                                <div class="tier-info">
                                    <h6>Rising Star (0-9 points)</h6>
                                    <p>Newcomers starting their journey to excellence</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Points are earned through completed engagements, client feedback, and exceptional contributions.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>

    <!-- Static Points System Insights Tip Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="pro-tip-card" style="background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%); border-radius: 16px; padding: 20px; color: white;">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h6 class="text-white mb-2">
                            <i class="bi bi-info-circle me-2"></i>
                            Points System Insights
                        </h6>
                        <p class="text-white-50 small mb-md-0">
                            💪 Great job! Your dedication and achievements are making a real difference. Keep pushing forward and inspiring your team!
                        </p>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <i class="bi bi-graph-up display-4 text-white-50"></i>
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
    // Historical Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($historical_data, 'label')); ?>,
            datasets: [{
                label: 'Total Points Earned',
                data: <?php echo json_encode(array_column($historical_data, 'points')); ?>,
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(255, 193, 7, 1)',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toLocaleString() + ' points';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Points'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: '<?php echo ucfirst($period); ?> Period'
                    }
                }
            }
        }
    });

    // Ranking Distribution Chart
    const rankingCtx = document.getElementById('rankingChart').getContext('2d');
    new Chart(rankingCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($ranking_tiers); ?>,
            datasets: [{
                data: <?php echo json_encode($ranking_counts); ?>,
                backgroundColor: [
                    'rgba(111, 66, 193, 0.8)',   // Platinum
                    'rgba(255, 193, 7, 0.8)',    // Gold
                    'rgba(108, 117, 125, 0.8)',  // Silver
                    'rgba(205, 127, 50, 0.8)',   // Bronze
                    'rgba(23, 162, 184, 0.8)'    // Rising Star
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} employees (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
/* Period Selector Styles */
.period-selector {
    margin-bottom: 10px;
}

.period-card {
    display: block;
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
}

.period-card:hover {
    transform: translateY(-5px);
    text-decoration: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.period-card.active {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
}

.period-card-content {
    position: relative;
    z-index: 1;
}

.period-icon {
    font-size: 2.5rem;
    color: #ffc107;
    margin-bottom: 10px;
    display: inline-block;
}

.period-card h4 {
    font-size: 1.2rem;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
}

.period-date {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0;
}

.active-badge {
    position: absolute;
    top: 10px;
    right: -30px;
    background: #ffc107;
    color: #2c3e50;
    font-size: 0.7rem;
    padding: 3px 30px;
    transform: rotate(45deg);
    font-weight: 600;
}

/* Performer Cards */
.performers-row {
    margin-top: 20px;
}

.performer-card {
    background: white;
    border-radius: 24px;
    padding: 30px 20px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    height: 100%;
}

.performer-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.performer-gold {
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    border: 2px solid #ffc107;
}

.performer-silver {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 2px solid #adb5bd;
}

.performer-bronze {
    background: linear-gradient(135deg, #fff4e6 0%, #ffffff 100%);
    border: 2px solid #cd7f32;
}

.champion {
    transform: scale(1.05);
    margin: -15px 0;
}

@media (max-width: 768px) {
    .champion {
        transform: scale(1);
        margin: 0;
    }
}

.performer-rank {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
}

.rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffb347);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
}

.rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #a0a0a0);
    box-shadow: 0 5px 15px rgba(192, 192, 192, 0.4);
}

.rank-3 {
    background: linear-gradient(135deg, #cd7f32, #b87333);
    box-shadow: 0 5px 15px rgba(205, 127, 50, 0.4);
}

.performer-rank i {
    font-size: 1.2rem;
    margin-right: 3px;
}

.performer-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px auto 15px;
}

.performer-name {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #2c3e50;
}

.performer-role {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.performer-points {
    background: #f8f9fa;
    border-radius: 50px;
    padding: 10px 20px;
    display: inline-block;
    margin: 10px 0;
}

.champion-points {
    background: #ffc107;
    color: #2c3e50;
}

.points-number {
    font-size: 1.5rem;
    font-weight: 800;
    display: block;
}

.points-label {
    font-size: 0.75rem;
    opacity: 0.8;
}

.performer-stats {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin: 15px 0;
}

.stat-badge {
    font-size: 0.75rem;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Achievements Feed */
.achievements-feed {
    max-height: 400px;
    overflow-y: auto;
}

.achievement-item {
    display: flex;
    gap: 15px;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}

.achievement-item:hover {
    background: #f8f9fa;
}

.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.achievement-content {
    flex: 1;
}

.achievement-text {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 500;
}

.achievement-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 0.7rem;
    color: #6c757d;
}

.achievement-points {
    font-weight: 600;
}

.achievement-awarder i,
.achievement-time i {
    margin-right: 2px;
}

/* Ranking Info */
.ranking-info {
    margin-bottom: 20px;
}

.ranking-tier {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 10px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.ranking-tier:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.tier-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.ranking-tier.platinum .tier-icon {
    background: rgba(111, 66, 193, 0.1);
    color: #6f42c1;
}

.ranking-tier.gold .tier-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.ranking-tier.silver .tier-icon {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.ranking-tier.bronze .tier-icon {
    background: rgba(205, 127, 50, 0.1);
    color: #cd7f32;
}

.ranking-tier.rising .tier-icon {
    background: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.tier-info h6 {
    margin-bottom: 2px;
    font-size: 0.9rem;
    font-weight: 600;
}

.tier-info p {
    margin-bottom: 0;
    font-size: 0.75rem;
    color: #6c757d;
}

/* Page Navigation Group */
.page-navigation-group {
    display: inline-block;
}

.btn-group .btn-check:checked + .btn-light {
    background: #0a2240;
    color: #fff;
    font-weight: 600;
    border-color: #0a2240;
    box-shadow: 0 2px 8px rgba(10,34,64,0.10);
    transition: background 0.2s, color 0.2s;
}

.btn-group .btn-outline-light {
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

.btn-group .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .performer-card {
        margin-bottom: 20px;
    }
    
    .period-card {
        margin-bottom: 15px;
    }
    
    .stat-value {
        font-size: 1.2rem;
    }
    
    .welcome-title {
        font-size: 1.4rem;
    }
}
</style>

<?php include __DIR__ . '/includes/operations_footer.php'; ?>