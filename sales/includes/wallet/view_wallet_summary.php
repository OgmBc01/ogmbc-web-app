<?php
// Get wallet statistics
$stats_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as total_points,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted,
    COALESCE(SUM(CASE WHEN points_type = 'EARNED' THEN points ELSE 0 END), 0) as earned_points,
    COUNT(*) as total_transactions
    FROM points_ledger 
    WHERE employee_id = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$net_points = $stats['total_points'] - $stats['total_deducted'];

// Get current month stats
$month_stats_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as month_points,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as month_deducted
    FROM points_ledger 
    WHERE employee_id = $user_id 
    AND MONTH(created_at) = $current_month 
    AND YEAR(created_at) = $current_year";
$month_stats_result = mysqli_query($connection, $month_stats_query);
$month_stats = mysqli_fetch_assoc($month_stats_result);

$month_net = $month_stats['month_points'] - $month_stats['month_deducted'];
$cashable_points = max(0, $month_net - 1000); // From your business rules


// Get eligible points for redemption (Engagement + Client Feedback + Manual Adjustment)
$eligible_query = "SELECT 
    COALESCE(SUM(CASE WHEN source_type IN ('ENGAGEMENT', 'CLIENT_FEEDBACK', 'MANUAL_ADJUSTMENT') AND points_type = 'EARNED' THEN points ELSE 0 END), 0) as eligible_points,
    COALESCE(SUM(CASE WHEN source_type = 'REDEMPTION' AND points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as redeemed_points
    FROM points_ledger 
    WHERE employee_id = $user_id 
    AND MONTH(created_at) = $current_month 
    AND YEAR(created_at) = $current_year";
$eligible_result = mysqli_query($connection, $eligible_query);
$eligible_data = mysqli_fetch_assoc($eligible_result);

$total_eligible = $eligible_data['eligible_points'];
$redeemed = $eligible_data['redeemed_points'];
$net_eligible = max(0, $total_eligible - 1000);
$available_for_redemption = max(0, $net_eligible - $redeemed);

// Check if there's a pending request

// Check if there's any redemption request for the month (any status)
$any_request_query = "SELECT request_id, status FROM points_redemption_requests 
                  WHERE employee_id = $user_id 
                  AND month = $current_month 
                  AND year = $current_year ";
$any_request_result = mysqli_query($connection, $any_request_query);
$has_any_request = mysqli_num_rows($any_request_result) > 0;
$has_pending = false;
if ($has_any_request) {
    while ($row = mysqli_fetch_assoc($any_request_result)) {
        if ($row['status'] === 'PENDING') {
            $has_pending = true;
            break;
        }
    }
}

// Get source breakdown
$source_query = "SELECT 
    source_type,
    COUNT(*) as count,
    SUM(points) as total_points
    FROM points_ledger 
    WHERE employee_id = $user_id AND points_type = 'EARNED'
    GROUP BY source_type
    ORDER BY total_points DESC";
$source_result = mysqli_query($connection, $source_query);

// Get recent transactions
$recent_query = "SELECT * FROM points_ledger 
                 WHERE employee_id = $user_id 
                 ORDER BY created_at DESC 
                 LIMIT 10";
$recent_result = mysqli_query($connection, $recent_query);

// Calculate cashable projection
$projected_cashable = max(0, $month_net - 1000);
$projected_aed = $projected_cashable; // 1 AED per point
?>

<!-- Statistics Cards Row - Matching clients.php style -->
<div class="row g-4 mb-4">
    <!-- Lifetime Points -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-trophy-fill text-primary"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo number_format($net_points); ?></h3>
                    <p class="stat-label mb-0">Lifetime Points</p>
                    <small class="text-muted"><?php echo $stats['total_transactions']; ?> transactions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Month Points -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-calendar-check-fill text-success"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo number_format($month_net); ?></h3>
                    <p class="stat-label mb-0">This Month</p>
                    <small class="text-<?php echo $month_net > 1000 ? 'success' : 'warning'; ?>">
                        <?php echo $month_net; ?> points earned
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashable Points -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack text-warning"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo number_format($cashable_points); ?></h3>
                    <p class="stat-label mb-0">Cashable Points</p>
                    <small class="text-success">≈ AED <?php echo number_format($cashable_points); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Redeem Action Card -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-card-info" style="border-left-color: #28a745;">
            <div class="stat-card-body d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center flex-grow-1 min-w-0">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-cash-coin text-success"></i>
                    </div>
                    <div class="stat-content ms-3 min-w-0">
                        <h3 class="stat-value mb-0">AED <?php echo number_format($available_for_redemption); ?></h3>
                        <p class="stat-label mb-0">Available to Redeem</p>
                        <small class="text-muted">From Engagements, Feedback & Manual Adjustments</small>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-3 mt-md-0 ms-md-3">
                <?php if ($available_for_redemption > 0 && !$has_any_request): ?>
                    <button class="btn btn-success btn-sm" style="white-space:nowrap;" onclick="showRedeemModal()">
                        <i class="bi bi-cash-stack me-1"></i>Redeem
                    </button>
                <?php elseif ($has_pending): ?>
                    <span class="badge bg-warning p-2">
                        <i class="bi bi-clock me-1"></i>Pending
                    </span>
                <?php elseif ($has_any_request): ?>
                    <span class="badge bg-danger p-2">
                        <i class="bi bi-x-circle me-1"></i>Request Exists
                    </span>
                <?php elseif ($available_for_redemption <= 0 && $month_net > 0): ?>
                    <span class="badge bg-secondary p-2">
                        <i class="bi bi-lock me-1"></i>Not Eligible
                    </span>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar for Monthly Target -->
<div class="row mb-4">
    <div class="col-12">
        <div class="progress-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                    <i class="bi bi-bullseye me-2 text-primary"></i>
                    Monthly Target Progress
                </h6>
                <span class="text-<?php echo $month_net >= 1000 ? 'success' : 'warning'; ?> fw-bold">
                    <?php echo $month_net; ?> / 1000 points
                </span>
            </div>
            <div class="progress" style="height: 10px;">
                <?php $progress = min(100, ($month_net / 1000) * 100); ?>
                <div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : ($progress >= 75 ? 'info' : ($progress >= 50 ? 'warning' : 'danger')); ?>" 
                     style="width: <?php echo $progress; ?>%">
                    <?php if ($progress >= 20): ?>
                        <?php echo round($progress); ?>%
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Cashable points = max(0, Monthly Total - 1,000)
                </small>
                <?php if ($available_for_redemption > 0): ?>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle me-1"></i>
                        <?php echo number_format($available_for_redemption); ?> points available to redeem
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">
    <!-- Left Column - Source Breakdown -->
    <div class="col-xl-5">
        <div class="card shadow-sm">
            <div class="card-header dark-header">
                <h5 class="card-title">
                    <i class="bi bi-pie-chart me-2"></i>Points by Source
                </h5>
                <span class="badge bg-light text-dark"><?php echo mysqli_num_rows($source_result); ?> sources</span>
            </div>
            <div class="card-body">
                <?php if ($source_result && mysqli_num_rows($source_result) > 0): ?>
                    <div class="source-list">
                        <?php while($source = mysqli_fetch_assoc($source_result)): 
                            $percentage = $stats['earned_points'] > 0 
                                ? round(($source['total_points'] / $stats['earned_points']) * 100, 1) 
                                : 0;
                            
                            $source_icon = 'briefcase';
                            $source_color = 'primary';
                            $is_redeemable = false;
                            switch(strtolower($source['source_type'])) {
                                case 'engagement':
                                    $source_icon = 'briefcase';
                                    $source_color = 'primary';
                                    $is_redeemable = true;
                                    break;
                                case 'client_feedback':
                                    $source_icon = 'star';
                                    $source_color = 'warning';
                                    $is_redeemable = true;
                                    break;
                                case 'sales_target':
                                    $source_icon = 'graph-up';
                                    $source_color = 'success';
                                    $is_redeemable = false;
                                    break;
                                case 'cdp':
                                    $source_icon = 'mortarboard';
                                    $source_color = 'info';
                                    $is_redeemable = false;
                                    break;
                                case 'manual_adjustment':
                                    $source_icon = 'pencil-square';
                                    $source_color = 'secondary';
                                    $is_redeemable = true;
                                    break;
                                default:
                                    $source_icon = 'tag';
                                    $source_color = 'dark';
                                    $is_redeemable = false;
                            }
                        ?>
                        <div class="source-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?php echo $source_color; ?>-soft text-<?php echo $source_color; ?> me-2 p-2">
                                        <i class="bi bi-<?php echo $source_icon; ?>"></i>
                                    </span>
                                    <strong><?php echo ucwords(str_replace('_', ' ', $source['source_type'])); ?></strong>
                                    <small class="text-muted ms-2">(<?php echo $source['count']; ?> entries)</small>
                                    <?php if ($is_redeemable || strtolower($source['source_type']) === 'client_feedback'): ?>
                                        <span class="badge bg-success-soft text-success ms-2">
                                            <i class="bi bi-cash-stack me-1"></i>Redeemable
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">+<?php echo number_format($source['total_points']); ?></span>
                                    <small class="text-muted ms-2"><?php echo $percentage; ?>%</small>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-<?php echo $source_color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-pie-chart display-4"></i>
                        <h6>No points data</h6>
                        <p class="text-muted">Points from different sources will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Recent Transactions -->
    <div class="col-xl-7">
        <div class="card shadow-sm">
            <div class="card-header dark-header">
                <h5 class="card-title">
                    <i class="bi bi-clock-history me-2"></i>Recent Transactions
                </h5>
                <a href="wallet.php?source=history" class="btn btn-sm btn-outline-light">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
                    <div class="transaction-list">
                        <?php while($trans = mysqli_fetch_assoc($recent_result)): 
                            $type_class = $trans['points_type'] == 'EARNED' ? 'success' : 'danger';
                            $sign = $trans['points_type'] == 'EARNED' ? '+' : '-';
                            $source_icon = 'briefcase';
                            
                            switch(strtolower($trans['source_type'])) {
                                case 'engagement':
                                    $source_icon = 'briefcase';
                                    break;
                                case 'client_feedback':
                                    $source_icon = 'star';
                                    break;
                                case 'sales_target':
                                    $source_icon = 'graph-up';
                                    break;
                                case 'cdp':
                                    $source_icon = 'mortarboard';
                                    break;
                                case 'redemption':
                                    $source_icon = 'cash-stack';
                                    break;
                                default:
                                    $source_icon = 'tag';
                            }
                        ?>
                        <div class="transaction-item">
                            <div class="transaction-icon bg-<?php echo $source_icon == 'briefcase' ? 'primary' : ($source_icon == 'star' ? 'warning' : ($source_icon == 'mortarboard' ? 'info' : ($source_icon == 'cash-stack' ? 'success' : 'secondary'))); ?>-soft">
                                <i class="bi bi-<?php echo $source_icon; ?> text-<?php echo $source_icon == 'briefcase' ? 'primary' : ($source_icon == 'star' ? 'warning' : ($source_icon == 'mortarboard' ? 'info' : ($source_icon == 'cash-stack' ? 'success' : 'secondary'))); ?>"></i>
                            </div>
                            <div class="transaction-details">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="transaction-title">
                                            <?php echo ucwords(str_replace('_', ' ', $trans['source_type'])); ?>
                                            <?php if (!empty($trans['description'])): ?>
                                                <small class="text-muted">- <?php echo htmlspecialchars($trans['description']); ?></small>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo date('M d, Y H:i', strtotime($trans['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $type_class; ?> fs-6">
                                            <?php echo $sign . abs($trans['points']); ?>
                                        </span>
                                        <?php if ($trans['requires_approval'] && !$trans['approved_by']): ?>
                                            <small class="text-warning d-block mt-1">
                                                <i class="bi bi-clock"></i> Pending approval
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state py-5">
                        <i class="bi bi-clock-history display-4"></i>
                        <h6>No transactions yet</h6>
                        <p class="text-muted">Your point transactions will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Cashable Points Info Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card gradient-bg">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-info-circle me-2"></i>
                        How Redemption Works
                    </h6>
                    <p class="text-white-50 mb-md-0">
                        <strong>Only points from Engagements and Client Feedback are redeemable!</strong><br>
                        You need at least 1,000 points in a month. The excess (points above 1,000) can be redeemed.<br>
                        Example: 1,500 total points → 500 redeemable → AED 500 upon approval.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="example-box bg-white-soft p-3 rounded">
                        <small class="text-white-50">Your Status:</small>
                        <div class="text-white fw-bold">
                            <?php if ($available_for_redemption > 0): ?>
                                <?php echo number_format($available_for_redemption); ?> points available
                                <i class="bi bi-check-circle ms-1"></i>
                            <?php elseif ($month_net >= 1000 && $available_for_redemption == 0 && $redeemed > 0): ?>
                                You've already redeemed <?php echo number_format($redeemed); ?> points
                            <?php elseif ($month_net < 1000): ?>
                                Need <?php echo number_format(1000 - $month_net); ?> more points to be eligible
                            <?php else: ?>
                                No redeemable points from Engagements & Feedback
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Stat Cards - Matching clients.php style */
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
.stat-card-success { border-left-color: #38c172; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-warning { border-left-color: #ffc107; }

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

/* Dark Header - Matching clients.php */
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

/* Progress Card */
.progress-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
}

/* Source Items */
.source-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}
.source-item:hover {
    background: #e9ecef;
}

/* Transaction List */
.transaction-list {
    max-height: 400px;
    overflow-y: auto;
}
.transaction-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.transaction-item:hover {
    background: #f8f9fa;
}
.transaction-icon {
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
.bg-success-soft { background: rgba(56, 193, 114, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }

.transaction-details {
    flex: 1;
}
.transaction-title {
    margin-bottom: 2px;
    font-size: 0.95rem;
}

/* Pro Tip Card - Matching clients.php gradient */
.gradient-bg {
    background: linear-gradient(90deg, #0a2240 0%, #003366 100%) !important;
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.18);
    padding: 28px 24px;
    margin-bottom: 24px;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.7);
}
.bg-white-soft {
    background: rgba(255, 255, 255, 0.15);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}
.empty-state i {
    color: #dee2e6;
    margin-bottom: 10px;
}

/* Badge backgrounds */
.badge.bg-primary-soft {
    background: rgba(102, 126, 234, 0.15);
}
.badge.bg-success-soft {
    background: rgba(56, 193, 114, 0.15);
}
.badge.bg-warning-soft {
    background: rgba(255, 193, 7, 0.15);
}
.badge.bg-info-soft {
    background: rgba(23, 162, 184, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card-body { 
        padding: 16px 10px; 
        flex-direction: row;
        text-align: left;
    }
    .stat-icon { 
        width: 40px; 
        height: 40px; 
        font-size: 1.3rem; 
        margin-right: 10px;
    }
    .stat-value { 
        font-size: 1.3rem; 
    }
    .stat-label { 
        font-size: 0.9rem; 
    }
    .transaction-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .transaction-icon {
        margin-bottom: 5px;
    }
}
</style>