<?php
$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['redemption_status']) ? $_GET['redemption_status'] : '';
$year_filter = isset($_GET['redemption_year']) ? (int)$_GET['redemption_year'] : 0;

// Build where clause
$where = ["employee_id = $user_id"];
if (!empty($status_filter)) {
    $where[] = "status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if ($year_filter > 0) {
    $where[] = "year = $year_filter";
}
$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected,
                COALESCE(SUM(CASE WHEN status = 'APPROVED' THEN points_requested ELSE 0 END), 0) as total_points_approved
                FROM points_redemption_requests 
                WHERE employee_id = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get redemption requests
$requests_query = "SELECT * FROM points_redemption_requests 
                   WHERE $where_clause 
                   ORDER BY requested_at DESC";
$requests_result = mysqli_query($connection, $requests_query);

// Get available years for filter
$years_query = "SELECT DISTINCT year FROM points_redemption_requests 
                WHERE employee_id = $user_id 
                ORDER BY year DESC";
$years_result = mysqli_query($connection, $years_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Redemption History</h4>
        <a href="wallet.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Summary
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-small bg-primary text-white">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total_requests'] ?? 0; ?></h3>
                    <p class="stat-label">Total Requests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small bg-warning text-white">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['pending'] ?? 0; ?></h3>
                    <p class="stat-label">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small bg-success text-white">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['approved'] ?? 0; ?></h3>
                    <p class="stat-label">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small bg-info text-white">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-content">
                    <h3 class="stat-value">AED <?php echo number_format($stats['total_points_approved'] ?? 0, 2); ?></h3>
                    <p class="stat-label">Total Redeemed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="source" value="redemption_history">
                
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="redemption_status" class="form-select">
                        <option value="">All</option>
                        <option value="PENDING" <?php echo $status_filter == 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                        <option value="APPROVED" <?php echo $status_filter == 'APPROVED' ? 'selected' : ''; ?>>Approved</option>
                        <option value="REJECTED" <?php echo $status_filter == 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="redemption_year" class="form-select">
                        <option value="">All Years</option>
                        <?php while($year = mysqli_fetch_assoc($years_result)): ?>
                            <option value="<?php echo $year['year']; ?>" <?php echo $year_filter == $year['year'] ? 'selected' : ''; ?>>
                                <?php echo $year['year']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="wallet.php?source=redemption_history" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Redemption Requests Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-list-ul me-2"></i>Redemption Requests</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($requests_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request Date</th>
                                <th>Period</th>
                                <th>Points</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Review Date</th>
                                <th>Notes</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php while($req = mysqli_fetch_assoc($requests_result)):
                                $status_class = 'secondary';
                                $status_icon = 'question-circle';
                                if ($req['status'] == 'PENDING') {
                                    $status_class = 'warning';
                                    $status_icon = 'clock-history';
                                } elseif ($req['status'] == 'APPROVED') {
                                    $status_class = 'success';
                                    $status_icon = 'check-circle';
                                } elseif ($req['status'] == 'REJECTED') {
                                    $status_class = 'danger';
                                    $status_icon = 'x-circle';
                                }
                                
                                $month_name = date('F', mktime(0, 0, 0, $req['month'], 1));
                            ?>
                                 <tr>
                                    <td><?php echo date('M d, Y', strtotime($req['requested_at'])); ?></td>
                                    <td><?php echo $month_name . ' ' . $req['year']; ?></td>
                                    <td class="fw-bold"><?php echo number_format($req['points_requested']); ?> pts</td>
                                    <td class="text-success">AED <?php echo number_format($req['points_requested'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                            <?php echo $req['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $req['reviewed_at'] ? date('M d, Y', strtotime($req['reviewed_at'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($req['notes'] ?: '-'); ?></td>
                                 </tr>
                            <?php endwhile; ?>
                        </tbody>
                     </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-cash-stack display-1 text-muted"></i>
                    <h5 class="mt-3">No Redemption Requests</h5>
                    <p class="text-muted">You haven't submitted any redemption requests yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
}
.stat-card-small .stat-icon { width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.stat-card-small .stat-value { font-size: 1.2rem; font-weight: 600; margin-bottom: 2px; }
.stat-card-small .stat-label { font-size: 0.7rem; opacity: 0.9; margin: 0; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 40px 20px; }
</style>