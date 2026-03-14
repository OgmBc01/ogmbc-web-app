<?php
// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

// ============================================
// COMPREHENSIVE OPERATIONS ANALYTICS QUERIES
// ============================================

// 1. OVERALL STATISTICS
$overall_stats_query = "SELECT 
    COUNT(DISTINCT c.client_id) as total_clients,
    COUNT(DISTINCT e.engagement_id) as total_engagements,
    COUNT(DISTINCT cf.feedback_id) as total_feedback,
    COALESCE(SUM(e.points_awarded), 0) as total_points_awarded
    FROM clients c
    LEFT JOIN engagements e ON c.client_id = e.client_id
    LEFT JOIN client_feedback cf ON c.client_id = cf.client_id AND cf.is_validated = 1";
$overall_result = mysqli_query($connection, $overall_stats_query);
$overall = mysqli_fetch_assoc($overall_result);

// 2. ENGAGEMENT STATUS DISTRIBUTION
$status_query = "SELECT 
    status,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM engagements), 1) as percentage
    FROM engagements 
    GROUP BY status
    ORDER BY FIELD(status, 'ASSIGNED', 'IN_PROGRESS', 'AWAITING_REVIEW', 'SUBMITTED', 'CLOSED', 'REJECTED')";
$status_result = mysqli_query($connection, $status_query);

// 3. INDUSTRY DISTRIBUTION
$industry_query = "SELECT 
    COALESCE(c.industry, 'Not Specified') as industry,
    COUNT(DISTINCT c.client_id) as client_count,
    COUNT(DISTINCT e.engagement_id) as engagement_count
    FROM clients c
    LEFT JOIN engagements e ON c.client_id = e.client_id
    GROUP BY c.industry
    ORDER BY client_count DESC
    LIMIT 10";
$industry_result = mysqli_query($connection, $industry_query);

// 4. SERVICE PERFORMANCE
$service_query = "SELECT 
    s.service_name,
    COUNT(DISTINCT e.engagement_id) as engagement_count,
    COUNT(DISTINCT c.client_id) as client_count,
    COALESCE(AVG(e.points_awarded), 0) as avg_points,
    COALESCE(SUM(e.points_awarded), 0) as total_points,
    COALESCE(AVG(c.service_total_fee), 0) as avg_fee,
    COALESCE(SUM(c.service_total_fee), 0) as potential_revenue
    FROM service_types s
    LEFT JOIN engagements e ON s.service_id = e.service_id
    LEFT JOIN clients c ON s.service_id = c.service_id
    GROUP BY s.service_id, s.service_name
    ORDER BY engagement_count DESC";
$service_result = mysqli_query($connection, $service_query);

// 5. MONTHLY TRENDS (Last 12 months)
$monthly_query = "SELECT 
    DATE_FORMAT(e.created_at, '%Y-%m') as month,
    DATE_FORMAT(e.created_at, '%b %Y') as month_label,
    COUNT(*) as new_engagements,
    SUM(CASE WHEN e.status = 'CLOSED' THEN 1 ELSE 0 END) as completed,
    COALESCE(SUM(e.points_awarded), 0) as points_awarded,
    COUNT(DISTINCT e.client_id) as unique_clients
    FROM engagements e
    WHERE e.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(e.created_at, '%Y-%m'), DATE_FORMAT(e.created_at, '%b %Y')
    ORDER BY month ASC";
$monthly_result = mysqli_query($connection, $monthly_query);

// 6. DEADLINE PERFORMANCE
$deadline_query = "SELECT 
    COUNT(*) as total_completed,
    SUM(CASE WHEN completion_date <= COALESCE(approved_deadline, original_deadline) THEN 1 ELSE 0 END) as on_time,
    SUM(CASE WHEN completion_date > COALESCE(approved_deadline, original_deadline) THEN 1 ELSE 0 END) as `delayed`,
    AVG(CASE WHEN completion_date IS NOT NULL 
        THEN DATEDIFF(completion_date, COALESCE(approved_deadline, original_deadline)) 
        ELSE NULL END) as avg_delay_days
    FROM engagements 
    WHERE status = 'CLOSED'";
$deadline_result = mysqli_query($connection, $deadline_query);
$deadline_stats = mysqli_fetch_assoc($deadline_result);

// 7. CLIENT FEEDBACK ANALYSIS
$feedback_analysis_query = "SELECT 
    COUNT(*) as total_feedback,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as neutral,
    SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative,
    COUNT(DISTINCT client_id) as clients_with_feedback
    FROM client_feedback 
    WHERE is_validated = 1";
$feedback_result = mysqli_query($connection, $feedback_analysis_query);
$feedback_stats = mysqli_fetch_assoc($feedback_result);

// 8. TOP PERFORMING EMPLOYEES
$top_employees_query = "SELECT 
    CONCAT(u.first_name, ' ', u.last_name) as employee_name,
    COUNT(DISTINCT e.engagement_id) as engagements_handled,
    COUNT(DISTINCT CASE WHEN e.status = 'CLOSED' THEN e.engagement_id END) as completed,
    COALESCE(SUM(e.points_awarded), 0) as points_earned,
    AVG(cf.rating) as avg_feedback_rating
    FROM users u
    LEFT JOIN engagements e ON u.user_id = e.assigned_to
    LEFT JOIN client_feedback cf ON u.user_id = cf.employee_id AND cf.is_validated = 1
    WHERE u.user_status = 'active'
    GROUP BY u.user_id
    HAVING engagements_handled > 0
    ORDER BY points_earned DESC
    LIMIT 5";
$top_employees_result = mysqli_query($connection, $top_employees_query);

// 9. REVENUE BY PAYMENT TERM
$revenue_by_term_query = "SELECT 
    payment_term,
    COUNT(*) as client_count,
    COALESCE(SUM(service_total_fee), 0) as total_revenue,
    AVG(service_total_fee) as avg_revenue
    FROM clients
    WHERE service_total_fee > 0
    GROUP BY payment_term";
$revenue_term_result = mysqli_query($connection, $revenue_by_term_query);

// 10. LEAD SOURCE ANALYSIS
$lead_source_query = "SELECT 
    lead_source,
    COUNT(*) as client_count,
    COUNT(DISTINCT e.engagement_id) as engagement_count,
    COALESCE(AVG(e.points_awarded), 0) as avg_points
    FROM clients c
    LEFT JOIN engagements e ON c.client_id = e.client_id
    GROUP BY lead_source";
$lead_source_result = mysqli_query($connection, $lead_source_query);

// 11. PENDING APPROVALS
$pending_approvals_query = "SELECT 
    COUNT(DISTINCT CASE WHEN e.status = 'AWAITING_REVIEW' THEN e.engagement_id END) as pending_engagements,
    COUNT(DISTINCT CASE WHEN dcr.status = 'PENDING' THEN dcr.request_id END) as pending_deadline_requests,
    COUNT(DISTINCT CASE WHEN cf.is_validated = 0 THEN cf.feedback_id END) as pending_feedback
    FROM engagements e
    CROSS JOIN deadline_change_requests dcr ON 1=1
    CROSS JOIN client_feedback cf ON 1=1
    WHERE 1=1";
$pending_result = mysqli_query($connection, $pending_approvals_query);
$pending = mysqli_fetch_assoc($pending_result);
?>

<!-- Include Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="operations-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="operations-title">
                            <i class="bi bi-graph-up me-2"></i>Operations Analytics Dashboard
                        </h2>
                        <p class="operations-subtitle">
                            Comprehensive overview of operational performance, client engagement, and service delivery metrics.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="date-range-badge">
                            <i class="bi bi-calendar3 me-2"></i>Last Updated: <?php echo date('M d, Y H:i'); ?>
                        </div>
                        <a href="engagements.php" class="btn btn-outline-light mt-2">
                            <i class="bi bi-arrow-left me-2"></i>Back to Engagements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='clients.php'" style="cursor: pointer;">
                <div class="kpi-icon bg-primary-soft">
                    <i class="bi bi-building text-primary"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo number_format($overall['total_clients']); ?></h3>
                    <p class="kpi-label">Total Clients</p>
                    <span class="kpi-trend">Click to view</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='engagements.php'" style="cursor: pointer;">
                <div class="kpi-icon bg-success-soft">
                    <i class="bi bi-briefcase text-success"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo number_format($overall['total_engagements']); ?></h3>
                    <p class="kpi-label">Total Engagements</p>
                    <span class="kpi-trend">Click to view</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='engagements.php?status=pending'" style="cursor: pointer;">
                <div class="kpi-icon bg-warning-soft">
                    <i class="bi bi-clock-history text-warning"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo $pending['pending_engagements'] ?? 0; ?></h3>
                    <p class="kpi-label">Pending Reviews</p>
                    <span class="kpi-trend">Awaiting approval</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='reports.php?type=points'" style="cursor: pointer;">
                <div class="kpi-icon bg-info-soft">
                    <i class="bi bi-trophy text-info"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo number_format($overall['total_points_awarded']); ?></h3>
                    <p class="kpi-label">Points Awarded</p>
                    <span class="kpi-trend">Total earned</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='feedback.php'" style="cursor: pointer;">
                <div class="kpi-icon bg-danger-soft">
                    <i class="bi bi-star text-danger"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo number_format($overall['total_feedback']); ?></h3>
                    <p class="kpi-label">Client Feedback</p>
                    <span class="kpi-trend"><?php echo number_format($feedback_stats['avg_rating'] ?? 0, 1); ?>/5 avg</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="kpi-card" onclick="window.location.href='reports.php?type=deadlines'" style="cursor: pointer;">
                <div class="kpi-icon bg-secondary-soft">
                    <i class="bi bi-calendar-check text-secondary"></i>
                </div>
                <div class="kpi-content">
                    <h3 class="kpi-value"><?php echo $deadline_stats['on_time'] ?? 0; ?>/<?php echo $deadline_stats['total_completed'] ?? 0; ?></h3>
                    <p class="kpi-label">On-Time Delivery</p>
                    <span class="kpi-trend">
                        <?php 
                        $on_time_pct = ($deadline_stats['total_completed'] > 0) 
                            ? round(($deadline_stats['on_time'] / $deadline_stats['total_completed']) * 100) 
                            : 0;
                        echo $on_time_pct; ?>% success rate
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Engagement Status Distribution -->
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>
                        Engagement Status Distribution
                    </h5>
                    <span class="badge bg-info">Live</span>
                </div>
                <div class="card-body">
                    <div style="width:100%;display:flex;justify-content:center;align-items:center;">
                        <canvas id="statusChart" width="220" height="220" style="max-width:100%;max-height:220px;display:block;"></canvas>
                    </div>
                    <div class="chart-legend">
                        <?php 
                        $status_colors = [
                            'ASSIGNED' => '#6c757d',
                            'IN_PROGRESS' => '#0d6efd',
                            'AWAITING_REVIEW' => '#ffc107',
                            'SUBMITTED' => '#198754',
                            'CLOSED' => '#0dcaf0',
                            'REJECTED' => '#dc3545'
                        ];
                        mysqli_data_seek($status_result, 0);
                        while($status = mysqli_fetch_assoc($status_result)): 
                        ?>
                        <div class="legend-item">
                            <span class="color-dot" style="background: <?php echo $status_colors[$status['status']] ?? '#6c757d'; ?>;"></span>
                            <span class="legend-label"><?php echo $status['status']; ?>:</span>
                            <span class="legend-value"><?php echo $status['count']; ?> (<?php echo $status['percentage']; ?>%)</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Industry Distribution -->
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-bar-chart me-2 text-success"></i>
                        Top Industries by Clients
                    </h5>
                    <a href="clients.php?filter=industry" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body">
                    <canvas id="industryChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Service Performance -->
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-trophy me-2 text-warning"></i>
                        Service Performance
                    </h5>
                    <span class="badge bg-warning">By Points</span>
                </div>
                <div class="card-body">
                    <canvas id="serviceChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Monthly Trends Line Chart -->
        <div class="col-xl-8">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Monthly Engagement Trends (Last 12 Months)
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" onclick="toggleChartData('engagements')">Engagements</button>
                        <button class="btn btn-outline-primary" onclick="toggleChartData('points')">Points</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-xl-4">
            <div class="chart-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-speedometer2 me-2 text-danger"></i>
                        Key Performance Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Deadline Performance Gauge -->
                    <div class="metric-block mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>On-Time Delivery</span>
                            <span class="fw-bold text-success"><?php echo $on_time_pct; ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $on_time_pct; ?>%"></div>
                        </div>
                    </div>

                    <!-- Client Satisfaction -->
                    <div class="metric-block mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Client Satisfaction</span>
                            <span class="fw-bold text-info">
                                <?php echo number_format($feedback_stats['avg_rating'] ?? 0, 1); ?>/5.0
                            </span>
                        </div>
                        <div class="stars-rating mb-2">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo ($i <= round($feedback_stats['avg_rating'] ?? 0)) ? '-fill' : ''; ?> text-warning"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="feedback-breakdown small">
                            <span class="text-success"><?php echo $feedback_stats['positive'] ?? 0; ?> Positive</span> •
                            <span class="text-warning"><?php echo $feedback_stats['neutral'] ?? 0; ?> Neutral</span> •
                            <span class="text-danger"><?php echo $feedback_stats['negative'] ?? 0; ?> Negative</span>
                        </div>
                    </div>

                    <!-- Pending Items -->
                    <div class="metric-block">
                        <h6 class="mb-3">Pending Approvals</h6>
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="pending-item" onclick="window.location.href='engagements.php?status=awaiting_review'">
                                    <span class="pending-count"><?php echo $pending['pending_engagements'] ?? 0; ?></span>
                                    <span class="pending-label">Engagements</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="pending-item" onclick="window.location.href='deadline_requests.php?status=pending'">
                                    <span class="pending-count"><?php echo $pending['pending_deadline_requests'] ?? 0; ?></span>
                                    <span class="pending-label">Deadline Changes</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="pending-item" onclick="window.location.href='feedback.php?filter=pending'">
                                    <span class="pending-count"><?php echo $pending['pending_feedback'] ?? 0; ?></span>
                                    <span class="pending-label">Feedback</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4">
        <!-- Top Employees Table -->
        <div class="col-xl-6">
            <div class="data-table-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-trophy me-2 text-warning"></i>
                        Top Performing Employees
                    </h5>
                    <a href="employees.php?sort=performance" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-center">Engagements</th>
                                    <th class="text-center">Completed</th>
                                    <th class="text-end">Points Earned</th>
                                    <th class="text-end">Avg Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_employees_result && mysqli_num_rows($top_employees_result) > 0): ?>
                                    <?php while($emp = mysqli_fetch_assoc($top_employees_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emp['employee_name']); ?></td>
                                        <td class="text-center"><?php echo $emp['engagements_handled']; ?></td>
                                        <td class="text-center"><?php echo $emp['completed']; ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo number_format($emp['points_earned']); ?></td>
                                        <td class="text-end">
                                            <?php if ($emp['avg_feedback_rating']): ?>
                                                <span class="badge bg-info"><?php echo number_format($emp['avg_feedback_rating'], 1); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Payment Term -->
        <div class="col-xl-6">
            <div class="data-table-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-cash-stack me-2 text-success"></i>
                        Revenue Analysis by Payment Term
                    </h5>
                    <a href="reports.php?type=revenue" class="btn btn-sm btn-outline-success">Full Report</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Term</th>
                                    <th class="text-center">Clients</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-end">Avg per Client</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($revenue_term_result && mysqli_num_rows($revenue_term_result) > 0): ?>
                                    <?php 
                                    $total_revenue_all = 0;
                                    mysqli_data_seek($revenue_term_result, 0);
                                    while($rev = mysqli_fetch_assoc($revenue_term_result)): 
                                        $total_revenue_all += $rev['total_revenue'];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $rev['payment_term']; ?></strong></td>
                                        <td class="text-center"><?php echo $rev['client_count']; ?></td>
                                        <td class="text-end fw-bold"><?php echo number_format($rev['total_revenue'], 2); ?></td>
                                        <td class="text-end"><?php echo number_format($rev['avg_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <tr class="table-info">
                                        <td colspan="2"><strong>Total Revenue</strong></td>
                                        <td class="text-end fw-bold"><?php echo number_format($total_revenue_all, 2); ?></td>
                                        <td></td>
                                    </tr>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4">No revenue data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Analysis -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="data-table-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-megaphone me-2 text-info"></i>
                        Lead Source Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($lead_source_result && mysqli_num_rows($lead_source_result) > 0): ?>
                            <?php while($lead = mysqli_fetch_assoc($lead_source_result)): ?>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="lead-source-card">
                                    <div class="lead-source-header">
                                        <span class="lead-source-icon">
                                            <?php 
                                            $icons = [
                                                'website' => 'globe',
                                                'referral' => 'people',
                                                'digital_marketing' => 'megaphone',
                                                'event' => 'calendar-event'
                                            ];
                                            $icon = $icons[$lead['lead_source']] ?? 'question-circle';
                                            ?>
                                            <i class="bi bi-<?php echo $icon; ?>"></i>
                                        </span>
                                        <span class="lead-source-name"><?php echo ucfirst(str_replace('_', ' ', $lead['lead_source'])); ?></span>
                                    </div>
                                    <div class="lead-source-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">Clients</span>
                                            <span class="stat-value"><?php echo $lead['client_count']; ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Engagements</span>
                                            <span class="stat-value"><?php echo $lead['engagement_count']; ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Avg Points</span>
                                            <span class="stat-value"><?php echo number_format($lead['avg_points'], 1); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart (Pie)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php 
                mysqli_data_seek($status_result, 0);
                $labels = [];
                $counts = [];
                while($s = mysqli_fetch_assoc($status_result)) {
                    $labels[] = "'" . $s['status'] . "'";
                    $counts[] = $s['count'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                data: [<?php echo implode(',', $counts); ?>],
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

    // Industry Chart (Bar)
    const industryCtx = document.getElementById('industryChart').getContext('2d');
    new Chart(industryCtx, {
        type: 'bar',
        data: {
            labels: [<?php 
                mysqli_data_seek($industry_result, 0);
                $labels = [];
                $client_counts = [];
                while($ind = mysqli_fetch_assoc($industry_result)) {
                    $labels[] = "'" . addslashes($ind['industry']) . "'";
                    $client_counts[] = $ind['client_count'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Number of Clients',
                data: [<?php echo implode(',', $client_counts); ?>],
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: '#28a745',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Service Chart (Horizontal Bar)
    const serviceCtx = document.getElementById('serviceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'bar',
        data: {
            labels: [<?php 
                mysqli_data_seek($service_result, 0);
                $labels = [];
                $points = [];
                while($serv = mysqli_fetch_assoc($service_result)) {
                    $labels[] = "'" . addslashes(substr($serv['service_name'], 0, 15)) . "'";
                    $points[] = $serv['total_points'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Total Points',
                data: [<?php echo implode(',', $points); ?>],
                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                borderColor: '#ffc107',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Monthly Trends Line Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [<?php 
                mysqli_data_seek($monthly_result, 0);
                $labels = [];
                $new = [];
                $completed = [];
                $points = [];
                while($m = mysqli_fetch_assoc($monthly_result)) {
                    $labels[] = "'" . $m['month_label'] . "'";
                    $new[] = $m['new_engagements'];
                    $completed[] = $m['completed'];
                    $points[] = $m['points_awarded'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [
                {
                    label: 'New Engagements',
                    data: [<?php echo implode(',', $new); ?>],
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Completed',
                    data: [<?php echo implode(',', $completed); ?>],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false }
        }
    });

    // Make chart accessible for toggle function
    window.monthlyChart = monthlyChart;
    window.monthlyData = {
        labels: [<?php echo implode(',', $labels); ?>],
        new: [<?php echo implode(',', $new); ?>],
        completed: [<?php echo implode(',', $completed); ?>],
        points: [<?php echo implode(',', $points); ?>]
    };
});

// Toggle monthly chart data
function toggleChartData(type) {
    const btns = document.querySelectorAll('.btn-group .btn');
    btns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    if (type === 'points') {
        window.monthlyChart.data.datasets = [
            {
                label: 'Points Awarded',
                data: window.monthlyData.points,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }
        ];
    } else {
        window.monthlyChart.data.datasets = [
            {
                label: 'New Engagements',
                data: window.monthlyData.new,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            },
            {
                label: 'Completed',
                data: window.monthlyData.completed,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }
        ];
    }
    window.monthlyChart.update();
}
</script>

<style>
/* Operations Dashboard Styles */
.operations-header {
    background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(10, 34, 64, 0.3);
}

.operations-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.operations-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.date-range-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
    display: inline-block;
}

/* KPI Cards */
.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid rgba(0,0,0,0.05);
    cursor: pointer;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: transparent;
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-info-soft { background: rgba(13, 202, 240, 0.1); }
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }

.kpi-content {
    flex: 1;
}

.kpi-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
}

.kpi-label {
    color: #6c757d;
    margin-bottom: 2px;
    font-size: 0.85rem;
}

.kpi-trend {
    font-size: 0.75rem;
    color: #f1bf70;
    font-weight: 500;
}

/* Chart Cards */

.chart-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    /* Remove fixed height to allow content to size naturally */
}

.chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 10px 16px 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 0;
}

.chart-card .card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.chart-card .card-body {
    padding: 12px 16px 12px 16px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Chart Legend */
.chart-card .card-body > *:last-child {
    margin-bottom: 0 !important;
}
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 15px;
    margin-top: 0 !important;
    max-height: 80px;
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

/* Data Table Cards */
.data-table-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
}

.data-table-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.data-table-card .card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

/* Pending Items */
.pending-item {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pending-item:hover {
    background: #e9ecef;
    transform: scale(1.05);
}

.pending-count {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #0a2240;
    line-height: 1.2;
}

.pending-label {
    font-size: 0.7rem;
    color: #6c757d;
}

/* Lead Source Cards */
.lead-source-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.lead-source-card:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.lead-source-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.lead-source-icon {
    width: 35px;
    height: 35px;
    background: #0a2240;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.lead-source-name {
    font-weight: 600;
    text-transform: capitalize;
}

.lead-source-stats {
    display: flex;
    justify-content: space-between;
}

.stat-item {
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 2px;
}

.stat-value {
    font-weight: 600;
    color: #0a2240;
}

/* Responsive */
@media (max-width: 768px) {
    .operations-title {
        font-size: 1.5rem;
    }
    
    .kpi-card {
        padding: 15px;
    }
    
    .kpi-value {
        font-size: 1.5rem;
    }
    
    .kpi-icon {
        width: 45px;
        height: 45px;
        font-size: 1.5rem;
    }
}
</style>