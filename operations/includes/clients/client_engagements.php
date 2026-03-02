<?php
// Check if client ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify client access
$check_query = "SELECT c.* 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                GROUP BY c.client_id";
$check_result = mysqli_query($connection, $check_query);
$client = mysqli_fetch_assoc($check_result);

if (!$client) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

// Get all engagements for this client
$engagements_query = "SELECT 
    e.*,
    s.service_name,
    s.service_category,
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN service_types s ON e.service_id = s.service_id
    WHERE e.client_id = $client_id AND e.assigned_to = $user_id
    ORDER BY 
        CASE e.status
            WHEN 'IN_PROGRESS' THEN 1
            WHEN 'AWAITING_REVIEW' THEN 2
            WHEN 'ASSIGNED' THEN 3
            WHEN 'SUBMITTED' THEN 4
            WHEN 'CLOSED' THEN 5
            ELSE 6
        END,
        COALESCE(e.approved_deadline, e.original_deadline) ASC";
$engagements_result = mysqli_query($connection, $engagements_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN COALESCE(approved_deadline, original_deadline) < CURDATE() AND status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as overdue
    FROM engagements
    WHERE client_id = $client_id AND assigned_to = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="client-header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-2">Engagements - <?php echo htmlspecialchars($client['company_name']); ?></h4>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-briefcase me-2"></i><?php echo $stats['total'] ?? 0; ?> total engagements
                            <span class="mx-3">|</span>
                            <i class="bi bi-play-circle me-1 text-success"></i><?php echo $stats['active'] ?? 0; ?> active
                            <span class="mx-3">|</span>
                            <i class="bi bi-check-circle me-1 text-info"></i><?php echo $stats['completed'] ?? 0; ?> completed
                            <?php if (($stats['overdue'] ?? 0) > 0): ?>
                                <span class="mx-3">|</span>
                                <i class="bi bi-exclamation-triangle me-1 text-danger"></i><?php echo $stats['overdue']; ?> overdue
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <a href="clients.php?source=view&id=<?php echo $client_id; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Client
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagements List -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-list-ul me-2"></i>All Engagements
            </h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-light" onclick="filterEngagements('all')">All</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterEngagements('active')">Active</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterEngagements('completed')">Completed</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterEngagements('overdue')">Overdue</button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($engagements_result && mysqli_num_rows($engagements_result) > 0): ?>
                <div class="engagements-list" id="engagementsList">
                    <?php while($eng = mysqli_fetch_assoc($engagements_result)): 
                        $status_class = 'secondary';
                        $status_icon = 'bell';
                        
                        switch($eng['status']) {
                            case 'ASSIGNED':
                                $status_class = 'secondary';
                                $status_icon = 'bell';
                                break;
                            case 'IN_PROGRESS':
                                $status_class = 'primary';
                                $status_icon = 'play-circle';
                                break;
                            case 'AWAITING_REVIEW':
                                $status_class = 'warning';
                                $status_icon = 'clock-history';
                                break;
                            case 'SUBMITTED':
                                $status_class = 'success';
                                $status_icon = 'check-circle';
                                break;
                            case 'CLOSED':
                                $status_class = 'dark';
                                $status_icon = 'check2-all';
                                break;
                        }
                        
                        $is_overdue = ($eng['days_remaining'] < 0 && $eng['status'] != 'CLOSED' && $eng['status'] != 'SUBMITTED');
                        $status_category = $eng['status'] == 'CLOSED' ? 'completed' : ($is_overdue ? 'overdue' : 'active');
                    ?>
                    <div class="engagement-list-item" data-status="<?php echo $status_category; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <div class="status-indicator bg-<?php echo $status_class; ?>"></div>
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="engagements.php?source=view&id=<?php echo $eng['engagement_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($eng['title']); ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($eng['service_name']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo str_replace('_', ' ', $eng['status']); ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <?php if ($is_overdue): ?>
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        <?php echo abs($eng['days_remaining']); ?> days overdue
                                    </span>
                                <?php elseif ($eng['status'] != 'CLOSED'): ?>
                                    <span class="text-<?php echo $eng['days_remaining'] <= 3 ? 'warning' : 'success'; ?>">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo $eng['days_remaining']; ?> days left
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Completed
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="engagements.php?source=view&id=<?php echo $eng['engagement_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-briefcase display-1 text-muted"></i>
                    <h5 class="mt-3">No Engagements Found</h5>
                    <p class="text-muted">This client doesn't have any engagements assigned to you.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card engagements-tip">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Engagement Management Tips
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>📋 Prioritize engagements with approaching deadlines</li>
                        <li>⚡ Update status regularly to keep client informed</li>
                        <li>📎 Upload evidence as soon as tasks are completed</li>
                        <li>💬 Communicate delays early - request deadline extensions proactively</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-briefcase display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.client-header-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 25px;
}

.engagement-list-item {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.engagement-list-item:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transform: translateX(5px);
}

.status-indicator {
    width: 4px;
    height: 40px;
    border-radius: 4px;
    margin-right: 15px;
}

.pro-tip-card.engagements-tip {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .client-header-card .d-flex {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .engagement-list-item .row {
        gap: 10px;
    }
    
    .engagement-list-item .text-end {
        text-align: left !important;
    }
}
</style>

<script>
function filterEngagements(type) {
    const items = document.querySelectorAll('.engagement-list-item');
    items.forEach(item => {
        if (type === 'all' || item.dataset.status === type) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>