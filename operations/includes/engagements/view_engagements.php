<?php
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["assigned_to = $user_id"];

if (!empty($status_filter)) {
    $where[] = "status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "client_id = $client_filter";
}
if (!empty($date_from)) {
    $where[] = "start_date >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "COALESCE(approved_deadline, original_deadline) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'ASSIGNED' THEN 1 ELSE 0 END) as assigned,
    SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review,
    SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
    SUM(CASE WHEN COALESCE(approved_deadline, original_deadline) < CURDATE() AND status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as overdue
    FROM engagements 
    WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter dropdown
$clients_query = "SELECT DISTINCT c.client_id, c.company_name
                  FROM engagements e
                  JOIN clients c ON e.client_id = c.client_id
                  WHERE e.assigned_to = $user_id
                  ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get engagements
$engagements_query = "SELECT 
    e.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    s.service_name,
    s.service_category,
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN clients c ON e.client_id = c.client_id
    JOIN service_types s ON e.service_id = s.service_id
    WHERE $where_clause
    ORDER BY 
        CASE 
            WHEN status IN ('ASSIGNED', 'IN_PROGRESS') AND COALESCE(approved_deadline, original_deadline) < CURDATE() THEN 1
            WHEN status IN ('ASSIGNED', 'IN_PROGRESS') THEN 2
            WHEN status = 'AWAITING_REVIEW' THEN 3
            WHEN status = 'SUBMITTED' THEN 4
            WHEN status = 'CLOSED' THEN 5
            ELSE 6
        END,
        COALESCE(approved_deadline, original_deadline) ASC";
$engagements_result = mysqli_query($connection, $engagements_query);
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-briefcase text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label">Total</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-play-circle text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></h3>
                    <p class="stat-label">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-clock-history text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['awaiting_review'] ?? 0; ?></h3>
                    <p class="stat-label">Awaiting Review</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['submitted'] ?? 0; ?></h3>
                    <p class="stat-label">Submitted</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-secondary-soft">
                    <i class="bi bi-check2-all text-secondary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['assigned'] ?? 0; ?></h3>
                    <p class="stat-label">Assigned</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card-small">
                <div class="stat-icon bg-danger-soft">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['overdue'] ?? 0; ?></h3>
                    <p class="stat-label">Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card mb-4">
        <div class="filters-header" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
            <i class="bi bi-funnel me-2"></i>
            Filter Engagements
            <i class="bi bi-chevron-down ms-auto"></i>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="filters-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="ASSIGNED" <?php echo $status_filter == 'ASSIGNED' ? 'selected' : ''; ?>>Assigned</option>
                            <option value="IN_PROGRESS" <?php echo $status_filter == 'IN_PROGRESS' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="AWAITING_REVIEW" <?php echo $status_filter == 'AWAITING_REVIEW' ? 'selected' : ''; ?>>Awaiting Review</option>
                            <option value="SUBMITTED" <?php echo $status_filter == 'SUBMITTED' ? 'selected' : ''; ?>>Submitted</option>
                            <option value="CLOSED" <?php echo $status_filter == 'CLOSED' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select">
                            <option value="">All Clients</option>
                            <?php while($client = mysqli_fetch_assoc($clients_result)): ?>
                                <option value="<?php echo $client['client_id']; ?>" <?php echo $client_filter == $client['client_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($client['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Engagements Grid/List -->
    <div class="row g-4">
        <?php if ($engagements_result && mysqli_num_rows($engagements_result) > 0): ?>
            <?php while($engagement = mysqli_fetch_assoc($engagements_result)): 
                $status_class = '';
                $status_icon = '';
                switch($engagement['status']) {
                    case 'ASSIGNED':
                        $status_class = 'secondary';
                        $status_icon = 'bi-bell';
                        break;
                    case 'IN_PROGRESS':
                        $status_class = 'primary';
                        $status_icon = 'bi-play-circle';
                        break;
                    case 'AWAITING_REVIEW':
                        $status_class = 'warning';
                        $status_icon = 'bi-clock-history';
                        break;
                    case 'SUBMITTED':
                        $status_class = 'success';
                        $status_icon = 'bi-check-circle';
                        break;
                    case 'CLOSED':
                        $status_class = 'dark';
                        $status_icon = 'bi-check2-all';
                        break;
                    default:
                        $status_class = 'secondary';
                        $status_icon = 'bi-question-circle';
                }
                
                $deadline_class = '';
                $deadline_text = '';
                if ($engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED') {
                    if ($engagement['days_remaining'] < 0) {
                        $deadline_class = 'danger';
                        $deadline_text = abs($engagement['days_remaining']) . ' days overdue';
                    } elseif ($engagement['days_remaining'] == 0) {
                        $deadline_class = 'warning';
                        $deadline_text = 'Due today';
                    } elseif ($engagement['days_remaining'] <= 3) {
                        $deadline_class = 'warning';
                        $deadline_text = $engagement['days_remaining'] . ' days left';
                    } else {
                        $deadline_class = 'success';
                        $deadline_text = $engagement['days_remaining'] . ' days left';
                    }
                }
            ?>
            <div class="col-xl-4 col-lg-6">
                <div class="engagement-card">
                    <div class="engagement-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-<?php echo $status_class; ?>">
                                <i class="bi <?php echo $status_icon; ?> me-1"></i>
                                <?php echo str_replace('_', ' ', $engagement['status']); ?>
                            </span>
                            <span class="engagement-id">#<?php echo $engagement['engagement_id']; ?></span>
                        </div>
                        <h5 class="engagement-title mt-2">
                            <a href="engagements.php?source=view&id=<?php echo $engagement['engagement_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($engagement['title']); ?>
                            </a>
                        </h5>
                    </div>
                    <div class="engagement-body">
                        <div class="client-info mb-3">
                            <i class="bi bi-building me-2 text-muted"></i>
                            <strong><?php echo htmlspecialchars($engagement['company_name']); ?></strong>
                        </div>
                        <div class="service-info mb-3">
                            <i class="bi bi-tag me-2 text-muted"></i>
                            <?php echo htmlspecialchars($engagement['service_name']); ?>
                        </div>
                        <div class="deadline-info mb-3">
                            <i class="bi bi-calendar me-2 text-muted"></i>
                            <span class="deadline-badge text-<?php echo $deadline_class; ?>">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo $deadline_text ?: 'No deadline'; ?>
                            </span>
                        </div>
                        
                        <!-- Progress Bar (simplified) -->
                        <div class="progress mb-3" style="height: 6px;">
                            <?php
                            $progress = 0;
                            if ($engagement['status'] == 'CLOSED') $progress = 100;
                            elseif ($engagement['status'] == 'SUBMITTED') $progress = 90;
                            elseif ($engagement['status'] == 'AWAITING_REVIEW') $progress = 75;
                            elseif ($engagement['status'] == 'IN_PROGRESS') $progress = 50;
                            elseif ($engagement['status'] == 'ASSIGNED') $progress = 25;
                            ?>
                            <div class="progress-bar bg-<?php echo $status_class; ?>" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        
                        <!-- Contact Person -->
                        <div class="contact-info small text-muted">
                            <i class="bi bi-person me-1"></i>
                            <?php echo htmlspecialchars($engagement['contact_name'] ?: 'No contact'); ?>
                        </div>
                    </div>
                    <div class="engagement-footer">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewEngagement(<?php echo $engagement['engagement_id']; ?>)">
                            <i class="bi bi-eye me-1"></i>Details
                        </button>
                        <?php if ($engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED'): ?>
                        <a href="engagements.php?source=update_status&id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-arrow-repeat me-1"></i>Update
                        </a>
                        <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-cloud-upload me-1"></i>Upload
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-briefcase display-1 text-muted"></i>
                    <h4 class="mt-3">No Engagements Found</h4>
                    <p class="text-muted">You don't have any engagements matching your criteria.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card engagements-tip ">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Pro Tip
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        <?php if (($stats['overdue'] ?? 0) > 0): ?>
                            ⚠️ You have <?php echo $stats['overdue']; ?> overdue tasks. 
                            <a href="engagements.php?source=request_deadline" class="text-white text-decoration-underline">Request deadline extensions</a> 
                            or prioritize them in your task list.
                        <?php elseif (($stats['awaiting_review'] ?? 0) > 0): ?>
                            🔍 You have <?php echo $stats['awaiting_review']; ?> tasks awaiting review. 
                            Make sure all evidence is uploaded before submitting.
                        <?php else: ?>
                            🚀 Upload evidence immediately after completing tasks to keep your records up to date and earn points faster!
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <i class="bi bi-lightbulb display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Engagements Welcome */
.engagements-welcome {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

/* Statistics Cards - Small */
.stat-card-small {
    background: white;
    border-radius: 16px;
    padding: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card-small:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-card-small .stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.stat-card-small .stat-content {
    flex: 1;
}

.stat-card-small .stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
}

.stat-card-small .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin: 0;
}

/* Filters Card */
.filters-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
}

.filters-header {
    background: #f8f9fa;
    padding: 15px 20px;
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.filters-body {
    padding: 20px;
    border-top: 1px solid #dee2e6;
}

/* Engagement Cards */
.engagement-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.engagement-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.engagement-header {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.engagement-id {
    font-size: 0.8rem;
    color: #6c757d;
    font-family: monospace;
}

.engagement-title {
    font-size: 1.1rem;
    margin-top: 10px;
    margin-bottom: 0;
}

.engagement-title a {
    color: #2c3e50;
}

.engagement-title a:hover {
    color: #f1bf70;
}

.engagement-body {
    padding: 20px;
    flex: 1;
}

.client-info, .service-info, .deadline-info {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.deadline-badge {
    font-weight: 500;
}

.engagement-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #f0f0f0;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Pro Tip Card */
.pro-tip-card.engagements-tip {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card-small {
        padding: 12px;
    }
    
    .engagement-footer {
        flex-direction: column;
    }
    
    .engagement-footer .btn {
        width: 100%;
    }
}
</style>