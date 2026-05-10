<?php
include 'includes/sales_header.php';
include 'includes/sales_nav.php';
include 'includes/sales_sidebar.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;

// Get employee ID from user_id
$employee_query = "SELECT employee_id, first_name, last_name, department_id FROM employees WHERE user_id = $user_id";
$employee_result = mysqli_query($connection, $employee_query);
$employee = mysqli_fetch_assoc($employee_result);

$employee_id = isset($employee['employee_id']) ? $employee['employee_id'] : null;
$employee_name = ($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '');

// If no employee_id, show warning and skip calculations
if (!$employee_id) {
    echo '<div class="alert alert-danger m-4">No employee record found for your user. Please contact HR/admin to ensure your user is linked to an employee profile.</div>';
    include 'includes/sales_footer.php';
    exit();
}

// ============================================
// 1. CALCULATE TOTAL POINTS EARNED (Excluding redemptions)
// ============================================

$points_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END), 0) as total_earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted,
    COALESCE(SUM(CASE WHEN points_type = 'ADJUSTMENT' THEN points ELSE 0 END), 0) as total_adjustment
    FROM points_ledger 
    WHERE employee_id = $employee_id AND YEAR(created_at) = $selected_year";
$points_result = mysqli_query($connection, $points_query);
$points_data = mysqli_fetch_assoc($points_result);
$total_earned_points = $points_data['total_earned'];
$total_deducted_points = $points_data['total_deducted'];
$total_adjustment_points = $points_data['total_adjustment'];
$net_points = $total_earned_points - $total_deducted_points + $total_adjustment_points;

// Subtract approved redemptions from net_points for annual calculation
$redemption_subtract_query = "SELECT COALESCE(SUM(points_requested),0) as redeemed_points FROM points_redemption_requests WHERE employee_id = $employee_id AND year = $selected_year AND status = 'APPROVED'";
$redemption_subtract_result = mysqli_query($connection, $redemption_subtract_query);
$redemption_subtract = mysqli_fetch_assoc($redemption_subtract_result);
$redeemed_points = $redemption_subtract['redeemed_points'] ?? 0;
$net_points -= $redeemed_points;

// ============================================
// 2. CALCULATE CDP UPLIFTS
// ============================================
$cdp_query = "SELECT 
    cdp_type,
    COUNT(*) as count,
    SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved_count,
    COALESCE(SUM(CASE WHEN status = 'APPROVED' THEN uplift_percentage ELSE 0 END), 0) as total_uplift
    FROM cdp_records 
    WHERE employee_id = $employee_id AND YEAR(created_at) = $selected_year
    GROUP BY cdp_type";
$cdp_result = mysqli_query($connection, $cdp_query);

$cdp_uplifts = [
    'CERTIFICATE' => ['percentage' => 18, 'count' => 0, 'approved_count' => 0, 'total' => 0],
    'COURSE' => ['percentage' => 7, 'count' => 0, 'approved_count' => 0, 'total' => 0],
    'LOYALTY' => ['percentage' => 3, 'count' => 0, 'approved_count' => 0, 'total' => 0],
    'BEHAVIOR' => ['percentage' => 2, 'count' => 0, 'approved_count' => 0, 'total' => 0]
];

$total_cdp_uplift = 0;
$approved_cdp_count = 0;
$total_cdp_count = 0;

while ($cdp = mysqli_fetch_assoc($cdp_result)) {
    $type = $cdp['cdp_type'];
    if (isset($cdp_uplifts[$type])) {
        $cdp_uplifts[$type]['count'] = $cdp['count'];
        $cdp_uplifts[$type]['approved_count'] = $cdp['approved_count'];
        $cdp_uplifts[$type]['total'] = $cdp['total_uplift'];
        $total_cdp_uplift += $cdp['total_uplift'];
        $approved_cdp_count += $cdp['approved_count'];
        $total_cdp_count += $cdp['count'];
    }
}

// ============================================
// 3. CALCULATE ANNUAL PERFORMANCE PERCENTAGE
// Rule: 12,000 points = 70% of annual performance
// ============================================
$max_points_for_70_percent = 12000;
$max_performance_percent = 100;

// Calculate performance percentage based on points
if ($net_points >= $max_points_for_70_percent) {
    // Points exceed 12,000 - calculate additional percentage
    $base_performance = 70;
    $excess_points = $net_points - $max_points_for_70_percent;
    // Each additional 171.43 points adds ~1% (since 30% remaining for 5143 points approx)
    $additional_percent = ($excess_points / 171.43);
    $performance_percent = min($base_performance + $additional_percent, $max_performance_percent);
} else {
    // Points below 12,000 - proportional calculation
    $performance_percent = ($net_points / $max_points_for_70_percent) * 70;
}

// Apply CDP uplift to performance percentage
$total_performance_with_uplift = $performance_percent + $total_cdp_uplift;
$total_performance_with_uplift = min($total_performance_with_uplift, $max_performance_percent);

// ============================================
// 4. DETERMINE SALARY INCREMENT BAND
// ============================================
$salary_increment = 0;
$increment_band = '';

if ($total_performance_with_uplift >= 86) {
    $salary_increment = 35;
    $increment_band = 'Excellent (86% - 100%)';
} elseif ($total_performance_with_uplift >= 75) {
    $salary_increment = 30;
    $increment_band = 'Very Good (75% - 85%)';
} elseif ($total_performance_with_uplift >= 65) {
    $salary_increment = 20;
    $increment_band = 'Good (65% - 74%)';
} elseif ($total_performance_with_uplift >= 50) {
    $salary_increment = 10;
    $increment_band = 'Satisfactory (50% - 64%)';
} else {
    $salary_increment = 0;
    $increment_band = 'Needs Improvement (Below 50%)';
}

// ============================================
// 5. GET MONTHLY BREAKDOWN FOR CHART
// ============================================
$monthly_points_query = "SELECT 
    MONTH(created_at) as month,
    COALESCE(SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END), 0) as earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as deducted,
    COALESCE(SUM(CASE WHEN points_type = 'ADJUSTMENT' THEN points ELSE 0 END), 0) as adjustment
    FROM points_ledger 
    WHERE employee_id = $employee_id AND YEAR(created_at) = $selected_year
    GROUP BY MONTH(created_at)
    ORDER BY month ASC";
$monthly_result = mysqli_query($connection, $monthly_points_query);

$monthly_data = [];
for ($i = 1; $i <= 12; $i++) {
    $monthly_data[$i] = ['earned' => 0, 'deducted' => 0, 'adjustment' => 0, 'net' => 0];
}
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $month = $row['month'];
    $monthly_data[$month]['earned'] = $row['earned'];
    $monthly_data[$month]['deducted'] = $row['deducted'];
    $monthly_data[$month]['adjustment'] = $row['adjustment'];
    $monthly_data[$month]['net'] = $row['earned'] - $row['deducted'] + $row['adjustment'];
}

// ============================================
// 6. GET REDEMPTIONS FOR THE YEAR
// ============================================
$redemptions_query = "SELECT 
    COUNT(*) as total_requests,
    SUM(points_requested) as total_points_requested,
    SUM(CASE WHEN status = 'APPROVED' THEN points_requested ELSE 0 END) as approved_points,
    SUM(CASE WHEN status = 'PENDING' THEN points_requested ELSE 0 END) as pending_points
    FROM points_redemption_requests 
    WHERE employee_id = $employee_id AND year = $selected_year";
$redemptions_result = mysqli_query($connection, $redemptions_query);
$redemptions = mysqli_fetch_assoc($redemptions_result);
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="cdp_annual.php">CDP & Annual</a></li>
                <li class="breadcrumb-item active">My Performance</li>
            </ol>
        </nav>

        <!-- Year Selector -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Select Year</label>
                                <select name="year" class="form-select">
                                    <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>View
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card" style="background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="welcome-title">
                                Annual Performance Report - <?php echo $selected_year; ?>
                            </h2>
                            <p class="welcome-subtitle">
                                <?php echo htmlspecialchars($employee_name); ?> • Employee ID: <?php echo $employee_id; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="current-date">
                                <i class="bi bi-calendar3 me-2"></i>Last Updated: <?php echo date('F j, Y'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Total Points Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-trophy-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($net_points); ?></h3>
                            <p class="stat-label">Net Points Earned</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success me-2">
                                    <i class="bi bi-plus-circle me-1"></i>+<?php echo number_format($total_earned_points); ?> Earned
                                </span>
                                <span class="badge bg-danger-soft text-danger">
                                    <i class="bi bi-dash-circle me-1"></i>-<?php echo number_format($total_deducted_points); ?> Deducted
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CDP Uplift Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-mortarboard-fill text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($total_cdp_uplift, 1); ?>%</h3>
                            <p class="stat-label">CDP Total Uplift</p>
                            <div class="stat-progress">
                                <span class="badge bg-info-soft text-info me-2">
                                    <i class="bi bi-certificate me-1"></i><?php echo $cdp_uplifts['CERTIFICATE']['approved_count']; ?> Certificates (+18%)
                                </span>
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-book me-1"></i><?php echo $cdp_uplifts['COURSE']['approved_count']; ?> Courses (+7%)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Percentage Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-graph-up-arrow text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($total_performance_with_uplift, 1); ?>%</h3>
                            <p class="stat-label">Annual Performance</p>
                            <div class="stat-progress">
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $total_performance_with_uplift; ?>%"></div>
                                </div>
                                <small class="text-muted">Target: 70% (12,000 points)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projected Increment Card -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-cash-stack text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">+<?php echo $salary_increment; ?>%</h3>
                            <p class="stat-label">Projected Salary Increment</p>
                            <div class="stat-progress">
                                <span class="badge bg-primary-soft text-primary">
                                    <i class="bi bi-bar-chart me-1"></i><?php echo $increment_band; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Column - Performance Breakdown -->
            <div class="col-xl-7">
                <!-- Monthly Points Chart -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-bar-chart-line me-2" style="color: #f1bf70;"></i>
                            Monthly Points Breakdown - <?php echo $selected_year; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" style="height: 300px; width: 100%;"></canvas>
                    </div>
                </div>

                <!-- CDP Details Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-mortarboard me-2" style="color: #f1bf70;"></i>
                            CDP Records & Uplifts
                        </h5>
                        <a href="cdp_annual.php?tab=cdp" class="btn btn-sm" style="background: #f1bf70; color: #0a2240;">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>CDP Type</th>
                                        <th class="text-center">Total Records</th>
                                        <th class="text-center">Approved</th>
                                        <th class="text-end">Uplift per Item</th>
                                        <th class="text-end">Total Uplift</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="bi bi-certificate me-2 text-success"></i>Certificate</td>
                                        <td class="text-center"><?php echo $cdp_uplifts['CERTIFICATE']['count']; ?></td>
                                        <td class="text-center"><?php echo $cdp_uplifts['CERTIFICATE']['approved_count']; ?></td>
                                        <td class="text-end">+18%</td>
                                        <td class="text-end fw-bold text-success">+<?php echo number_format($cdp_uplifts['CERTIFICATE']['total'], 1); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-book me-2 text-info"></i>Course</td>
                                        <td class="text-center"><?php echo $cdp_uplifts['COURSE']['count']; ?></td>
                                        <td class="text-center"><?php echo $cdp_uplifts['COURSE']['approved_count']; ?></td>
                                        <td class="text-end">+7%</td>
                                        <td class="text-end fw-bold text-success">+<?php echo number_format($cdp_uplifts['COURSE']['total'], 1); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-heart me-2 text-danger"></i>Loyalty</td>
                                        <td class="text-center"><?php echo $cdp_uplifts['LOYALTY']['count']; ?></td>
                                        <td class="text-center"><?php echo $cdp_uplifts['LOYALTY']['approved_count']; ?></td>
                                        <td class="text-end">+3%</td>
                                        <td class="text-end fw-bold text-success">+<?php echo number_format($cdp_uplifts['LOYALTY']['total'], 1); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-people me-2 text-warning"></i>Behavior</td>
                                        <td class="text-center"><?php echo $cdp_uplifts['BEHAVIOR']['count']; ?></td>
                                        <td class="text-center"><?php echo $cdp_uplifts['BEHAVIOR']['approved_count']; ?></td>
                                        <td class="text-end">+2%</td>
                                        <td class="text-end fw-bold text-success">+<?php echo number_format($cdp_uplifts['BEHAVIOR']['total'], 1); ?>%</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="fw-bold">Total CDP Uplift</td>
                                        <td class="text-end fw-bold text-success fs-5">+<?php echo number_format($total_cdp_uplift, 1); ?>%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Performance Summary -->
            <div class="col-xl-5">
                <!-- Performance Calculation Card -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-calculator me-2" style="color: #f1bf70;"></i>
                            Performance Calculation
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="calculation-steps">
                            <div class="step-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-trophy me-2 text-primary"></i>Base Points Earned</span>
                                    <span class="fw-bold"><?php echo number_format($net_points); ?> points</span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo min(($net_points / 12000) * 100, 100); ?>%"></div>
                                </div>
                                <small class="text-muted">Target: 12,000 points = 70% performance</small>
                            </div>
                            
                            <div class="step-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-arrow-right me-2 text-success"></i>Base Performance</span>
                                    <span class="fw-bold"><?php echo number_format($performance_percent, 1); ?>%</span>
                                </div>
                            </div>
                            
                            <div class="step-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-plus-circle me-2 text-success"></i>CDP Uplift</span>
                                    <span class="fw-bold text-success">+<?php echo number_format($total_cdp_uplift, 1); ?>%</span>
                                </div>
                            </div>
                            
                            <div class="step-item mb-3 pt-2 border-top">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold fs-5"><i class="bi bi-graph-up me-2 text-warning"></i>Total Annual Performance</span>
                                    <span class="fw-bold fs-4 text-warning"><?php echo number_format($total_performance_with_uplift, 1); ?>%</span>
                                </div>
                            </div>
                            
                            <div class="step-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-cash-stack me-2 text-info"></i>Projected Salary Increment</span>
                                    <span class="fw-bold fs-3 text-success">+<?php echo $salary_increment; ?>%</span>
                                </div>
                                <div class="progress mt-1" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $salary_increment; ?>%"></div>
                                </div>
                                <small class="text-muted">Based on <?php echo $increment_band; ?> performance band</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Breakdown Card -->
                <div class="dashboard-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-pie-chart me-2" style="color: #f1bf70;"></i>
                            Points Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #e8f5e9;">
                                    <i class="bi bi-plus-circle text-success fs-2"></i>
                                    <h4 class="mt-2 mb-0 text-success">+<?php echo number_format($total_earned_points); ?></h4>
                                    <small class="text-muted">Earned</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #ffebee;">
                                    <i class="bi bi-dash-circle text-danger fs-2"></i>
                                    <h4 class="mt-2 mb-0 text-danger">-<?php echo number_format($total_deducted_points); ?></h4>
                                    <small class="text-muted">Deducted</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #e3f2fd;">
                                    <i class="bi bi-arrow-repeat text-info fs-2"></i>
                                    <h4 class="mt-2 mb-0 text-info"><?php echo number_format($total_adjustment_points); ?></h4>
                                    <small class="text-muted">Adjustment</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($redemptions['total_requests'] > 0): ?>
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-cash-stack me-2"></i>Points Redeemed</span>
                                <span class="fw-bold text-danger">-<?php echo number_format($redemptions['approved_points']); ?></span>
                            </div>
                            <small class="text-muted"><?php echo $redemptions['total_requests']; ?> redemption requests (<?php echo $redemptions['pending_points']; ?> pending)</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Salary Increment Bands Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="bi bi-table me-2" style="color: #f1bf70;"></i>
                            Salary Increment Bands
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Performance Range</th>
                                        <th>Salary Increase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="<?php echo $total_performance_with_uplift >= 86 ? 'table-success' : ''; ?>">
                                        <td>86% - 100% (Excellent)</td>
                                        <td class="fw-bold text-success">+35%</td>
                                    </tr>
                                    <tr class="<?php echo ($total_performance_with_uplift >= 75 && $total_performance_with_uplift < 86) ? 'table-success' : ''; ?>">
                                        <td>75% - 85% (Very Good)</td>
                                        <td class="fw-bold text-success">+30%</td>
                                    </tr>
                                    <tr class="<?php echo ($total_performance_with_uplift >= 65 && $total_performance_with_uplift < 75) ? 'table-warning' : ''; ?>">
                                        <td>65% - 74% (Good)</td>
                                        <td class="fw-bold text-warning">+20%</td>
                                    </tr>
                                    <tr class="<?php echo ($total_performance_with_uplift >= 50 && $total_performance_with_uplift < 65) ? 'table-info' : ''; ?>">
                                        <td>50% - 64% (Satisfactory)</td>
                                        <td class="fw-bold text-info">+10%</td>
                                    </tr>
                                    <tr class="<?php echo $total_performance_with_uplift < 50 ? 'table-danger' : ''; ?>">
                                        <td>Below 50% (Needs Improvement)</td>
                                        <td class="fw-bold text-danger">0%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Styles */
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
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: #f1bf70;
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

.stat-icon.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.stat-icon.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.stat-icon.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.stat-icon.bg-info-soft { background: rgba(13, 202, 240, 0.1); }

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
    color: #0a2240;
}

.stat-label {
    color: #6c757d;
    margin-bottom: 5px;
    font-size: 0.85rem;
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
    height: 100%;
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
    color: #0a2240;
}

.card-body {
    padding: 20px;
}

.calculation-steps .step-item {
    padding: 5px 0;
}

/* Badge backgrounds */
.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-info-soft { background: rgba(13, 202, 240, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }

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
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Points Chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = <?php 
        $months = [];
        $earned_data = [];
        $net_data = [];
        for($i = 1; $i <= 12; $i++) {
            $months[] = "'" . date('M', mktime(0, 0, 0, $i, 1)) . "'";
            $earned_data[] = $monthly_data[$i]['earned'];
            $net_data[] = $monthly_data[$i]['net'];
        }
        echo '{ months: [' . implode(',', $months) . '], earned: [' . implode(',', $earned_data) . '], net: [' . implode(',', $net_data) . '] }';
    ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.months,
            datasets: [
                {
                    label: 'Points Earned',
                    data: monthlyData.earned,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Net Points',
                    data: monthlyData.net,
                    backgroundColor: 'rgba(241, 191, 112, 0.7)',
                    borderColor: '#f1bf70',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
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
                            return context.dataset.label + ': ' + context.raw.toLocaleString() + ' points';
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
});
</script>

<?php include 'includes/sales_footer.php'; ?>