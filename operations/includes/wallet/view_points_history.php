<?php
// Ensure session is started and $connection is available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($connection)) {
    // Try to include DB connection if not already set
    $db_path1 = __DIR__ . '/../../../admin/includes/db.php';
    $db_path2 = __DIR__ . '/../../admin/includes/db.php';
    if (file_exists($db_path1)) {
        include_once $db_path1;
    } elseif (file_exists($db_path2)) {
        include_once $db_path2;
    }
}

// Get the logged-in user's user_id from session
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// IMPORTANT: points_ledger uses user_id directly, not employee_id
// So we use $user_id for querying points_ledger
$employee_id = $user_id; // Use user_id for points_ledger queries

// Get filter parameters
$type_filter = isset($_GET['type']) && $_GET['type'] !== '' ? mysqli_real_escape_string($connection, $_GET['type']) : '';
$source_filter = isset($_GET['source_filter']) && $_GET['source_filter'] !== '' ? mysqli_real_escape_string($connection, $_GET['source_filter']) : '';
$month_filter = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : 0;
$year_filter = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : 0;

// Build where clause
$where = ["employee_id = $employee_id"];

if (!empty($type_filter)) {
    $where[] = "points_type = '$type_filter'";
}
if (!empty($source_filter)) {
    $where[] = "source_type = '$source_filter'";
}
if ($month_filter > 0) {
    $where[] = "MONTH(created_at) = $month_filter";
}
if ($year_filter > 0) {
    $where[] = "YEAR(created_at) = $year_filter";
}

$where_clause = implode(' AND ', $where);

// Get total points for period (for summary card)
$total_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as total_earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted,
    COUNT(*) as transaction_count
    FROM points_ledger 
    WHERE $where_clause";

$total_result = mysqli_query($connection, $total_query);
$totals = mysqli_fetch_assoc($total_result);

$net = ($totals['total_earned'] ?? 0) - ($totals['total_deducted'] ?? 0);

// Get distinct years for filter
$years_query = "SELECT DISTINCT YEAR(created_at) as year FROM points_ledger WHERE employee_id = $employee_id ORDER BY year DESC";
$years_result = mysqli_query($connection, $years_query);

// Get distinct sources for filter
$sources_query = "SELECT DISTINCT source_type FROM points_ledger WHERE employee_id = $employee_id ORDER BY source_type";
$sources_result = mysqli_query($connection, $sources_query);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM points_ledger WHERE $where_clause";
$count_result = mysqli_query($connection, $count_query);
$total_records = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = $total_records > 0 ? ceil($total_records / $per_page) : 1;

// Get transactions
$transactions_query = "SELECT * FROM points_ledger 
                       WHERE $where_clause 
                       ORDER BY created_at DESC 
                       LIMIT $offset, $per_page";
$transactions_result = mysqli_query($connection, $transactions_query);
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-clock-history me-2"></i>Transaction History</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="wallet.php">Wallet</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </nav>
        </div>
        <a href="wallet.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Summary
        </a>
    </div>

    <!-- Period Summary Card -->
    <div class="summary-card mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="summary-item">
                    <span class="summary-label">Period Total</span>
                    <span class="summary-value <?php echo $net >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $net >= 0 ? '+' : ''; ?><?php echo number_format($net); ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-item">
                    <span class="summary-label">Earned</span>
                    <span class="summary-value text-success">+<?php echo number_format($totals['total_earned'] ?? 0); ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-item">
                    <span class="summary-label">Deducted</span>
                    <span class="summary-value text-danger">-<?php echo number_format($totals['total_deducted'] ?? 0); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card mb-4">
        <div class="filters-header" data-bs-toggle="collapse" data-bs-target="#historyFilters">
            <i class="bi bi-funnel me-2"></i>
            Filter Transactions
            <i class="bi bi-chevron-down ms-auto"></i>
        </div>
        <div class="collapse show" id="historyFilters">
            <div class="filters-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="history">
                    
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            <?php 
                            if ($years_result && mysqli_num_rows($years_result) > 0):
                                mysqli_data_seek($years_result, 0);
                                while($year = mysqli_fetch_assoc($years_result)): 
                            ?>
                                <option value="<?php echo $year['year']; ?>" <?php echo $year_filter == $year['year'] ? 'selected' : ''; ?>>
                                    <?php echo $year['year']; ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select">
                            <option value="">All Months</option>
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $month_filter == $m ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Source</label>
                        <select name="source_filter" class="form-select">
                            <option value="">All Sources</option>
                            <?php 
                            if ($sources_result && mysqli_num_rows($sources_result) > 0):
                                mysqli_data_seek($sources_result, 0);
                                while($src = mysqli_fetch_assoc($sources_result)): 
                            ?>
                                <option value="<?php echo $src['source_type']; ?>" <?php echo $source_filter == $src['source_type'] ? 'selected' : ''; ?>>
                                    <?php echo ucwords(str_replace('_', ' ', $src['source_type'])); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="EARNED" <?php echo $type_filter == 'EARNED' ? 'selected' : ''; ?>>Earned</option>
                            <option value="DEDUCTED" <?php echo $type_filter == 'DEDUCTED' ? 'selected' : ''; ?>>Deducted</option>
                            <option value="ADJUSTMENT" <?php echo $type_filter == 'ADJUSTMENT' ? 'selected' : ''; ?>>Adjustment</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-list-ul me-2"></i>Transactions
                <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?> total</span>
            </h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-light" onclick="exportCSV()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if ($transactions_result && mysqli_num_rows($transactions_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="transactions-table">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Points</th>
                                <th>Status</th>
                                <th>Redemption</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($trans = mysqli_fetch_assoc($transactions_result)): 
                                $source_badge = 'secondary';
                                $source_icon = 'tag';
                                $is_redeemable = false;
                                $source_type_lower = strtolower($trans['source_type']);
                                
                                switch($source_type_lower) {
                                    case 'engagement':
                                        $source_badge = 'primary';
                                        $source_icon = 'briefcase';
                                        $is_redeemable = true;
                                        break;
                                    case 'client_feedback':
                                    case 'feedback':
                                        $source_badge = 'warning';
                                        $source_icon = 'star';
                                        $is_redeemable = true;
                                        break;
                                    case 'sales_target':
                                        $source_badge = 'success';
                                        $source_icon = 'graph-up';
                                        $is_redeemable = false;
                                        break;
                                    case 'cdp':
                                        $source_badge = 'info';
                                        $source_icon = 'mortarboard';
                                        $is_redeemable = false;
                                        break;
                                    case 'redemption':
                                        $source_badge = 'success';
                                        $source_icon = 'cash-stack';
                                        $is_redeemable = false;
                                        break;
                                    case 'manual_adjustment':
                                        $source_badge = 'secondary';
                                        $source_icon = 'pencil';
                                        $is_redeemable = true;
                                        break;
                                    default:
                                        $source_badge = 'secondary';
                                        $source_icon = 'tag';
                                        $is_redeemable = false;
                                }
                                
                                $type_class = $trans['points_type'] == 'EARNED' ? 'success' : 
                                             ($trans['points_type'] == 'DEDUCTED' ? 'danger' : 'warning');
                                $sign = $trans['points_type'] == 'EARNED' ? '+' : 
                                       ($trans['points_type'] == 'DEDUCTED' ? '-' : '±');
                                
                                // Check if this transaction was part of a redemption
                                $redemption_status = '';
                                if ($source_type_lower == 'redemption' && $trans['points_type'] == 'DEDUCTED') {
                                    $redemption_status = '<span class="badge bg-success"><i class="bi bi-cash-stack me-1"></i>Redeemed</span>';
                                } elseif ($is_redeemable && $trans['points_type'] == 'EARNED') {
                                    $redemption_status = '<span class="badge bg-info-soft text-info">Eligible</span>';
                                } else {
                                    $redemption_status = '<span class="badge bg-secondary-soft text-secondary">Not eligible</span>';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($trans['created_at'])); ?>
                                            <br>
                                            <?php echo date('H:i', strtotime($trans['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $source_badge; ?>-soft text-<?php echo $source_badge; ?>">
                                            <i class="bi bi-<?php echo $source_icon; ?> me-1"></i>
                                            <?php echo ucwords(str_replace('_', ' ', $trans['source_type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($trans['description'] ?? '-'); ?>
                                        <?php if (!empty($trans['notes'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($trans['notes']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $type_class; ?>">
                                            <?php echo ucfirst(strtolower($trans['points_type'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-<?php echo $type_class; ?>">
                                            <?php echo $sign . abs($trans['points']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($trans['requires_approval'] && !$trans['approved_by']): ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock me-1"></i>Pending
                                            </span>
                                        <?php elseif ($trans['approved_by']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Approved
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-check me-1"></i>Auto
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $redemption_status; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state py-5">
                    <i class="bi bi-clock-history display-1 text-muted"></i>
                    <h5 class="mt-3">No Transactions Found</h5>
                    <p class="text-muted">No transactions match your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.summary-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.summary-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    height: 100%;
}

.summary-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: 600;
}

.filters-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.filters-header {
    background: #f8f9fa;
    padding: 15px 20px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.filters-body {
    padding: 20px;
    border-top: 1px solid #dee2e6;
}

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

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table td {
    vertical-align: middle;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    color: #dee2e6;
}

/* Badge background utilities */
.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-info-soft { background: rgba(13, 202, 240, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function exportCSV() {
    const urlParams = new URLSearchParams(window.location.search);
    const year = urlParams.get('year') || '';
    const month = urlParams.get('month') || '';
    const source = urlParams.get('source_filter') || '';
    const type = urlParams.get('type') || '';
    window.location.href = 'includes/ajax/export_transactions.php?year=' + year + '&month=' + month + '&source=' + source + '&type=' + type;
}
</script>