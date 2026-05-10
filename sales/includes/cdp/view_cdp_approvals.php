<?php
// Start session at the beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure $user_id is set from session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Only proceed if user is logged in
if ($user_id > 0) {
    // Get all CDP records with approval status
    $query = "SELECT * FROM cdp_records 
              WHERE employee_id = $user_id 
              ORDER BY 
                CASE status
                    WHEN 'PENDING' THEN 1
                    WHEN 'APPROVED' THEN 2
                    WHEN 'REJECTED' THEN 3
                END,
                updated_at DESC";
    $result = mysqli_query($connection, $query);

    // Get approval statistics
    $stats_query = "SELECT 
                    ROUND(AVG(CASE WHEN status = 'APPROVED' AND approved_at IS NOT NULL AND created_at IS NOT NULL 
                         THEN DATEDIFF(approved_at, created_at) END), 1) as avg_approval_days,
                    MIN(CASE WHEN status = 'APPROVED' THEN created_at END) as fastest_approval,
                    MAX(CASE WHEN status = 'APPROVED' THEN created_at END) as slowest_approval
                    FROM cdp_records 
                    WHERE employee_id = $user_id";
    $stats_result = mysqli_query($connection, $stats_query);
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $result = false;
    $stats = ['avg_approval_days' => null, 'fastest_approval' => null, 'slowest_approval' => null];
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="approvals-header">
                <h4><i class="bi bi-clock-history me-2"></i>Approval Status</h4>
                <p class="text-muted mb-0">Track the status of your CDP submissions</p>
            </div>
        </div>
    </div>

    <!-- Approval Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-info-soft">
                        <i class="bi bi-clock text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo isset($stats['avg_approval_days']) && $stats['avg_approval_days'] ? round($stats['avg_approval_days']) . ' days' : '—'; ?></h3>
                        <p class="stat-label">Avg. Approval Time</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-lightning-charge text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo isset($stats['fastest_approval']) && $stats['fastest_approval'] ? date('M d', strtotime($stats['fastest_approval'])) : '—'; ?></h3>
                        <p class="stat-label">Fastest Approval</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-hourglass text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo isset($stats['slowest_approval']) && $stats['slowest_approval'] ? date('M d', strtotime($stats['slowest_approval'])) : '—'; ?></h3>
                        <p class="stat-label">Slowest Approval</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Timeline -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-clock-history me-2"></i>Approval Timeline
            </h5>
        </div>
        <div class="card-body">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="approval-timeline">
                    <?php while($record = mysqli_fetch_assoc($result)): 
                        $status_color = 'warning';
                        $status_icon = 'clock';
                        $status_text = 'Pending';
                        
                        if ($record['status'] == 'APPROVED') {
                            $status_color = 'success';
                            $status_icon = 'check-circle';
                            $status_text = 'Approved';
                            
                            // Calculate approval time in days
                            $approval_time = null;
                            if (!empty($record['approved_at']) && !empty($record['created_at'])) {
                                $approval_time = round((strtotime($record['approved_at']) - strtotime($record['created_at'])) / 86400, 1);
                            }
                        } elseif ($record['status'] == 'REJECTED') {
                            $status_color = 'danger';
                            $status_icon = 'x-circle';
                            $status_text = 'Rejected';
                            $approval_time = null;
                        }
                    ?>
                    <div class="approval-item">
                        <div class="approval-icon bg-<?php echo $status_color; ?>-soft">
                            <i class="bi bi-<?php echo $status_icon; ?> text-<?php echo $status_color; ?>"></i>
                        </div>
                        <div class="approval-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($record['title']); ?></h6>
                                    <small class="text-muted"><?php echo ucfirst(strtolower($record['cdp_type'])); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $status_color; ?>"><?php echo $status_text; ?></span>
                            </div>
                            <div class="approval-meta mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>Submitted: <?php echo date('M d, Y', strtotime($record['created_at'])); ?>
                                </small>
                                <?php if ($record['status'] == 'APPROVED' && !empty($record['approved_at'])): ?>
                                    <small class="text-muted ms-3">
                                        <i class="bi bi-check-circle me-1"></i>Approved: <?php echo date('M d, Y', strtotime($record['approved_at'])); ?>
                                        <?php if (isset($approval_time) && $approval_time !== null): ?>
                                            (<span class="badge bg-success ms-1"><?php echo $approval_time; ?> days</span>)
                                        <?php endif; ?>
                                    </small>
                                <?php elseif ($record['status'] == 'REJECTED' && !empty($record['approved_at'])): ?>
                                    <small class="text-muted ms-3">
                                        <i class="bi bi-x-circle me-1"></i>Rejected: <?php echo date('M d, Y', strtotime($record['approved_at'])); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php if ($record['status'] == 'REJECTED' && !empty($record['approval_notes'])): ?>
                                <div class="rejection-notes mt-2">
                                    <i class="bi bi-chat me-1"></i>
                                    <small class="text-danger"><?php echo htmlspecialchars($record['approval_notes']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-clock-history display-1 text-muted"></i>
                    <h5 class="mt-3">No Approval Records</h5>
                    <p class="text-muted">Your submitted CDP records will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pro Tip Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="pro-tip-card">
                <h6 class="text-white mb-2">
                    <i class="bi bi-lightbulb me-2"></i>
                    Approval Tips
                </h6>
                <p class="text-white-50 small">
                    ✅ Ensure all documents are clear and legible<br>
                    ✅ Submit during business hours for faster processing<br>
                    ✅ Follow up if pending for more than 5 working days<br>
                    ✅ Check rejection notes carefully before resubmitting
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.approvals-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 25px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
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
    font-size: 1.2rem;
    font-weight: 700;
}

.stat-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.approval-timeline {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.approval-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.approval-item:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.approval-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.approval-content {
    flex: 1;
}

.approval-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.rejection-notes {
    background: rgba(220, 53, 69, 0.1);
    border-radius: 8px;
    padding: 8px 12px;
}

.pro-tip-card {
    background: linear-gradient(135deg, #0a2342 0%, #193a5e 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.dark-header {
    background: #0a2342;
    color: white;
    border-bottom: none;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

@media (max-width: 768px) {
    .approval-item {
        flex-direction: column;
    }
    
    .approval-meta {
        flex-direction: column;
        gap: 5px;
        align-items: flex-start;
    }
    
    .ms-3 {
        margin-left: 0 !important;
    }
}
</style>