<?php
include "includes/sales_header.php";
include "includes/sales_nav.php";
include "includes/sales_sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// Get employee name for display
$emp_query = "SELECT CONCAT(first_name, ' ', last_name) as employee_name FROM employees WHERE employee_id = $employee_id";
$emp_result = mysqli_query($connection, $emp_query);
$employee_name = mysqli_fetch_assoc($emp_result)['employee_name'] ?? 'Employee';

// Fetch all sales targets for the logged-in employee
$targets_query = "SELECT 
                    st.*,
                    TIMESTAMPDIFF(DAY, st.created_at, st.validated_at) as validation_days,
                    CONCAT(u.first_name, ' ', u.last_name) as validator_name
                  FROM sales_targets st
                  LEFT JOIN users u ON st.validated_by = u.user_id
                  WHERE st.employee_id = $employee_id
                  ORDER BY st.year DESC, st.month DESC";
$targets_result = mysqli_query($connection, $targets_query);

// Get summary statistics
$summary_query = "SELECT 
                    COUNT(*) as total_targets,
                    SUM(CASE WHEN status = 'VALIDATED' THEN 1 ELSE 0 END) as validated_count,
                    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted_count,
                    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_count,
                    COALESCE(SUM(points_awarded), 0) as total_points_earned,
                    ROUND(AVG(CASE WHEN status = 'VALIDATED' THEN attainment_percentage END), 1) as avg_attainment,
                    SUM(CASE WHEN status = 'VALIDATED' AND attainment_percentage >= 100 THEN 1 ELSE 0 END) as targets_exceeded
                  FROM sales_targets
                  WHERE employee_id = $employee_id";
$summary_result = mysqli_query($connection, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="sales-header">
                    <h4><i class="bi bi-graph-up me-2"></i>Sales Targets Dashboard</h4>
                    <p class="text-muted mb-0">Track your monthly sales targets and performance</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-bullseye text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $summary['total_targets'] ?? 0; ?></h3>
                            <p class="stat-label">Total Targets</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-trophy text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $summary['validated_count'] ?? 0; ?></h3>
                            <p class="stat-label">Validated Targets</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-hourglass-split text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo ($summary['pending_count'] ?? 0) + ($summary['submitted_count'] ?? 0); ?></h3>
                            <p class="stat-label">Pending/Submitted</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-star text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($summary['total_points_earned'] ?? 0); ?></h3>
                            <p class="stat-label">Points Earned</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Performance Highlights</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Average Attainment</small>
                                <h5 class="mb-0 <?php echo ($summary['avg_attainment'] ?? 0) >= 100 ? 'text-success' : 'text-primary'; ?>">
                                    <?php echo $summary['avg_attainment'] ?? 0; ?>%
                                </h5>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Targets Exceeded</small>
                                <h5 class="mb-0 text-success"><?php echo $summary['targets_exceeded'] ?? 0; ?></h5>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo min(100, ($summary['validated_count'] ?? 0) / max(1, ($summary['total_targets'] ?? 1)) * 100); ?>%"></div>
                            </div>
                            <small class="text-muted">Validation Rate: <?php echo round(($summary['validated_count'] ?? 0) / max(1, ($summary['total_targets'] ?? 1)) * 100); ?>%</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Target Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="status-badge bg-success-soft">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <h6 class="mb-0 mt-1"><?php echo $summary['validated_count'] ?? 0; ?></h6>
                                    <small>Validated</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="status-badge bg-warning-soft">
                                    <i class="bi bi-clock-fill text-warning"></i>
                                    <h6 class="mb-0 mt-1"><?php echo $summary['pending_count'] ?? 0; ?></h6>
                                    <small>Pending</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="status-badge bg-info-soft">
                                    <i class="bi bi-send-fill text-info"></i>
                                    <h6 class="mb-0 mt-1"><?php echo $summary['submitted_count'] ?? 0; ?></h6>
                                    <small>Submitted</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="status-badge bg-danger-soft">
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                    <h6 class="mb-0 mt-1"><?php echo $summary['rejected_count'] ?? 0; ?></h6>
                                    <small>Rejected</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Targets Table -->
        <div class="card shadow-sm">
            <div class="card-header dark-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2"></i>My Sales Targets
                </h5>
            </div>
            <div class="card-body">
                <?php if ($targets_result && mysqli_num_rows($targets_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover sales-table">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Target (AED)</th>
                                    <th>Actual (AED)</th>
                                    <th>Attainment</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                    <th>Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($target = mysqli_fetch_assoc($targets_result)): 
                                    $period_name = date('F Y', mktime(0, 0, 0, $target['month'], 1, $target['year']));
                                    $attainment_class = 'secondary';
                                    $attainment_icon = 'minus-circle';
                                    
                                    if ($target['attainment_percentage'] >= 100) {
                                        $attainment_class = 'success';
                                        $attainment_icon = 'check-circle-fill';
                                    } elseif ($target['attainment_percentage'] >= 75) {
                                        $attainment_class = 'info';
                                        $attainment_icon = 'check-circle';
                                    } elseif ($target['attainment_percentage'] >= 50) {
                                        $attainment_class = 'warning';
                                        $attainment_icon = 'exclamation-triangle-fill';
                                    } elseif ($target['attainment_percentage'] > 0) {
                                        $attainment_class = 'danger';
                                        $attainment_icon = 'x-circle-fill';
                                    }
                                    
                                    $status_class = 'secondary';
                                    $status_icon = '';
                                    switch($target['status']) {
                                        case 'VALIDATED':
                                            $status_class = 'success';
                                            $status_icon = 'check-circle-fill';
                                            break;
                                        case 'SUBMITTED':
                                            $status_class = 'info';
                                            $status_icon = 'send-fill';
                                            break;
                                        case 'PENDING':
                                            $status_class = 'warning';
                                            $status_icon = 'clock-fill';
                                            break;
                                        case 'REJECTED':
                                            $status_class = 'danger';
                                            $status_icon = 'x-circle-fill';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $period_name; ?></strong>
                                    </td>
                                    <td>AED <?php echo number_format($target['target_value'], 2); ?></td>
                                    <td>
                                        <?php if ($target['actual_value']): ?>
                                            AED <?php echo number_format($target['actual_value'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($target['attainment_percentage']): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-<?php echo $attainment_icon; ?> text-<?php echo $attainment_class; ?> me-2"></i>
                                                <span class="fw-bold text-<?php echo $attainment_class; ?>">
                                                    <?php echo number_format($target['attainment_percentage'], 1); ?>%
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($target['points_awarded']): ?>
                                            <span class="badge bg-primary"><?php echo number_format($target['points_awarded']); ?> pts</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                            <?php echo $target['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($target['status'] == 'VALIDATED' && $target['validated_at']): ?>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                <?php echo date('M d, Y', strtotime($target['validated_at'])); ?>
                                            </small>
                                            <?php if ($target['validator_name']): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-person-check me-1"></i>
                                                    <?php echo $target['validator_name']; ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php elseif ($target['status'] == 'PENDING'): ?>
                                            <span class="text-muted">Awaiting submission</span>
                                        <?php elseif ($target['status'] == 'SUBMITTED'): ?>
                                            <span class="text-info">Awaiting validation</span>
                                        <?php elseif ($target['status'] == 'REJECTED' && $target['validation_notes']): ?>
                                            <span class="text-danger" title="<?php echo htmlspecialchars($target['validation_notes']); ?>">
                                                <i class="bi bi-chat-dots"></i> View notes
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewTarget(<?php echo $target['target_id']; ?>)">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-graph-up display-1 text-muted"></i>
                        <h5 class="mt-3">No Sales Targets Found</h5>
                        <p class="text-muted">Your sales targets will appear here once assigned by management.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Points Guide -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="info-card">
                    <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Points Calculation Guide</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="guide-item">
                                <span class="badge bg-success">≥100%</span>
                                <p class="mb-0 mt-1">1,000 points</p>
                                <small>Target exceeded</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="guide-item">
                                <span class="badge bg-info">75% - 99%</span>
                                <p class="mb-0 mt-1">750 points</p>
                                <small>Good performance</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="guide-item">
                                <span class="badge bg-warning">50% - 74%</span>
                                <p class="mb-0 mt-1">500 points</p>
                                <small>Satisfactory</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="guide-item">
                                <span class="badge bg-danger">&lt;50%</span>
                                <p class="mb-0 mt-1">250 points</p>
                                <small>Needs improvement</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Target Details Modal -->
<div class="modal fade" id="targetDetailsModal" tabindex="-1" aria-labelledby="targetDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0a2240 0%, #1a3a5a 100%); color: #fff;">
                <h5 class="modal-title" id="targetDetailsModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Target Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="targetDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading target details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.sales-header {
    background: linear-gradient(135deg, #002147 0%, #003366 100%);
    border-radius: 16px;
    padding: 25px;
    color: white;
}

.sales-header .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-card-body {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.stat-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.85rem;
}

.bg-primary-soft {
    background: rgba(13, 110, 253, 0.1);
}

.bg-success-soft {
    background: rgba(25, 135, 84, 0.1);
}

.bg-warning-soft {
    background: rgba(255, 193, 7, 0.1);
}

.bg-info-soft {
    background: rgba(13, 202, 240, 0.1);
}

.bg-danger-soft {
    background: rgba(220, 53, 69, 0.1);
}

.dark-header {
    background: linear-gradient(135deg, #0a2342 0%, #193a5e 100%);
    color: white;
    border-bottom: none;
}

.sales-table {
    font-size: 0.9rem;
}

.sales-table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.status-badge {
    text-align: center;
    padding: 10px;
    border-radius: 12px;
    background: #f8f9fa;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 20px;
}

.guide-item {
    text-align: center;
    padding: 10px;
    background: white;
    border-radius: 12px;
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 1.2rem;
    }
    
    .sales-table {
        font-size: 0.8rem;
    }
}
</style>

<script>
// View target details
function viewTarget(id) {
    const modal = new bootstrap.Modal(document.getElementById('targetDetailsModal'));
    const contentDiv = document.getElementById('targetDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading target details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch target details via AJAX
    fetch('includes/ajax/get_sales_target_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error loading target details: ${error.message}</div>`;
        });
}
</script>

<?php include "includes/sales_footer.php"; ?>