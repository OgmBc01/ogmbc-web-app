<?php
// Get filter parameters
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["cf.employee_id = $user_id"];

if ($rating_filter > 0) {
    $where[] = "cf.rating = $rating_filter";
}
if (!empty($status_filter)) {
    if ($status_filter === 'validated') {
        $where[] = "cf.is_validated = 1 AND cf.is_rejected = 0";
    } elseif ($status_filter === 'pending') {
        $where[] = "cf.is_validated = 0 AND cf.is_rejected = 0";
    } elseif ($status_filter === 'rejected') {
        $where[] = "cf.is_rejected = 1";
    }
}
if (!empty($client_filter)) {
    $where[] = "cf.client_id = $client_filter";
}
if (!empty($date_from)) {
    $where[] = "DATE(cf.created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(cf.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN cf.is_validated = 1 AND cf.is_rejected = 0 THEN 1 ELSE 0 END) as validated,
    SUM(CASE WHEN cf.is_validated = 0 AND cf.is_rejected = 0 THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN cf.is_rejected = 1 THEN 1 ELSE 0 END) as rejected,
    COALESCE(AVG(CASE WHEN cf.is_validated = 1 AND cf.is_rejected = 0 THEN cf.rating ELSE NULL END), 0) as avg_rating,
    SUM(CASE WHEN cf.rating >= 4 THEN 1 ELSE 0 END) as positive,
    SUM(cf.points_awarded) as total_points
    FROM client_feedback cf
    WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                 FROM clients c
                 JOIN client_feedback cf ON c.client_id = cf.client_id
                 WHERE cf.employee_id = $user_id
                 ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get feedback list
$feedback_query = "SELECT 
    cf.*,
    c.company_name,
    c.contact_name,
    e.title as engagement_title,
    CONCAT(u.first_name, ' ', u.last_name) as validated_by_name
    FROM client_feedback cf
    JOIN clients c ON cf.client_id = c.client_id
    LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
    LEFT JOIN users u ON cf.validated_by = u.user_id
    WHERE $where_clause
    ORDER BY cf.created_at DESC";
$feedback_result = mysqli_query($connection, $feedback_query);
?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-star text-primary"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Total Feedback</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['validated'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Validated</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-clock-history text-warning"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['pending'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Pending</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-trophy text-info"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['total_points'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Points Earned</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="rating-card">
            <div class="d-flex align-items-center">
                <div class="rating-circle bg-warning">
                    <span class="rating-value"><?php echo number_format($stats['avg_rating'], 1); ?></span>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Average Rating</h6>
                    <div class="stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= round($stats['avg_rating']) ? '-fill' : ''; ?> text-warning"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="rating-card">
            <div class="d-flex align-items-center">
                <div class="rating-circle bg-success">
                    <span class="rating-value"><?php echo $stats['positive'] ?? 0; ?></span>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Positive Feedback</h6>
                    <small class="text-success">Rating 4-5 stars</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="rating-card">
            <div class="d-flex align-items-center">
                <div class="rating-circle bg-primary">
                    <span class="rating-value"><?php echo round(($stats['positive'] / max($stats['total'], 1)) * 100); ?>%</span>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Satisfaction Rate</h6>
                    <small class="text-primary">Positive / Total</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-funnel me-2"></i>Filter Feedback
        </h5>
        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse show" id="filtersCollapse">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        <option value="">All Ratings</option>
                        <option value="5" <?php echo $rating_filter == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4" <?php echo $rating_filter == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4)</option>
                        <option value="3" <?php echo $rating_filter == 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3)</option>
                        <option value="2" <?php echo $rating_filter == 2 ? 'selected' : ''; ?>>⭐⭐ (2)</option>
                        <option value="1" <?php echo $rating_filter == 1 ? 'selected' : ''; ?>>⭐ (1)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="validated" <?php echo $status_filter == 'validated' ? 'selected' : ''; ?>>Validated</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
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
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Feedback List -->
<div class="card shadow-sm">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-list-ul me-2"></i>Client Feedback
        </h5>
        <span class="badge bg-light text-dark"><?php echo $stats['total'] ?? 0; ?> entries</span>
    </div>
    <div class="card-body p-0">
        <?php if ($feedback_result && mysqli_num_rows($feedback_result) > 0): ?>
            <div class="feedback-list">
                <?php while($feedback = mysqli_fetch_assoc($feedback_result)): 
                    $status_class = 'secondary';
                    $status_text = 'Unknown';
                    $status_icon = 'question-circle';
                    
                    if ($feedback['is_rejected']) {
                        $status_class = 'danger';
                        $status_text = 'Rejected';
                        $status_icon = 'x-circle';
                    } elseif ($feedback['is_validated']) {
                        $status_class = 'success';
                        $status_text = 'Validated';
                        $status_icon = 'check-circle';
                    } else {
                        $status_class = 'warning';
                        $status_text = 'Pending';
                        $status_icon = 'clock';
                    }
                ?>
                <div class="feedback-item">
                    <div class="feedback-icon">
                        <?php if ($feedback['rating'] >= 4): ?>
                            <i class="bi bi-emoji-smile text-success fs-2"></i>
                        <?php elseif ($feedback['rating'] == 3): ?>
                            <i class="bi bi-emoji-neutral text-warning fs-2"></i>
                        <?php else: ?>
                            <i class="bi bi-emoji-frown text-danger fs-2"></i>
                        <?php endif; ?>
                    </div>
                    <div class="feedback-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="feedback-title">
                                    <a href="feedback.php?source=view&id=<?php echo $feedback['feedback_id']; ?>" class="text-decoration-none">
                                        <strong><?php echo htmlspecialchars($feedback['company_name']); ?></strong>
                                    </a>
                                    <?php if (!empty($feedback['engagement_title'])): ?>
                                        <span class="badge bg-secondary ms-2">
                                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($feedback['engagement_title']); ?>
                                        </span>
                                    <?php endif; ?>
                                </h6>
                                
                                <!-- Star Rating Display -->
                                <div class="rating-display mb-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?> text-warning"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2 fw-bold"><?php echo $feedback['rating']; ?>/5</span>
                                </div>
                                
                                <!-- Feedback Preview -->
                                <p class="feedback-preview mb-1">
                                    <?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 150)); ?>
                                    <?php if (strlen($feedback['feedback_text']) > 150): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </p>
                                
                                <!-- Meta Info -->
                                <div class="feedback-meta">
                                    <span class="badge bg-<?php echo $status_class; ?> me-2">
                                        <i class="bi bi-<?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                    </span>
                                    <?php if ($feedback['points_awarded'] > 0): ?>
                                        <span class="badge bg-success me-2">
                                            <i class="bi bi-trophy me-1"></i>+<?php echo $feedback['points_awarded']; ?> pts
                                        </span>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                    </small>
                                    <?php if ($feedback['validated_by_name']): ?>
                                        <small class="text-muted ms-2">
                                            <i class="bi bi-person-check me-1"></i>by <?php echo htmlspecialchars($feedback['validated_by_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="feedback-actions">
                                <button class="btn btn-sm btn-outline-info" onclick="viewFeedback(<?php echo $feedback['feedback_id']; ?>)" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-star display-1 text-muted"></i>
                <h5 class="mt-3">No Feedback Found</h5>
                <p class="text-muted">No client feedback matches your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card gradient-bg">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Feedback Insights
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>⭐ Positive feedback (4-5 stars) earns you 50 points each</li>
                        <li>📊 Your satisfaction rate: <?php echo round(($stats['positive'] / max($stats['total'], 1)) * 100); ?>%</li>
                        <li>✅ Validated feedback contributes to your performance metrics</li>
                        <li>💬 Use feedback to improve client relationships</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-star display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Stat Cards - Matching clients.php */
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

/* Rating Cards */
.rating-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    height: 100%;
}
.rating-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
}
.rating-circle.bg-warning { background: linear-gradient(135deg, #f1bf70 0%, #e5b465 100%); }
.rating-circle.bg-success { background: linear-gradient(135deg, #38c172 0%, #2fa35c 100%); }
.rating-circle.bg-primary { background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%); }
.rating-value {
    font-size: 1.8rem;
    font-weight: 700;
}
.stars {
    font-size: 1rem;
}

/* Dark Header */
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

/* Feedback List */
.feedback-list {
    display: flex;
    flex-direction: column;
}
.feedback-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.feedback-item:hover {
    background: #f8f9fa;
}
.feedback-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.feedback-content {
    flex: 1;
}
.feedback-title {
    margin-bottom: 8px;
    font-size: 1rem;
}
.feedback-title a {
    color: #2c3e50;
}
.feedback-title a:hover {
    color: #f1bf70;
}
.feedback-preview {
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.5;
}
.feedback-meta {
    margin-top: 8px;
}
.feedback-actions {
    display: flex;
    gap: 5px;
}

/* Pro Tip Card - Gradient */
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.18);
    padding: 28px 24px;
    margin-bottom: 24px;
}
.text-white-50 {
    color: rgba(255, 255, 255, 0.7);
}
.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i {
    color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card-body { 
        padding: 16px 10px; 
    }
    .stat-icon { 
        width: 40px; 
        height: 40px; 
        font-size: 1.3rem; 
    }
    .stat-value { 
        font-size: 1.3rem; 
    }
    .feedback-item {
        flex-direction: column;
    }
    .feedback-actions {
        align-self: flex-end;
    }
    .rating-circle {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    .rating-value {
        font-size: 1.4rem;
    }
}
</style>