<?php
// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$direction_filter = isset($_GET['direction']) ? $_GET['direction'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["cc.user_id = $user_id"];

if (!empty($type_filter)) {
    $where[] = "cc.comm_type = '" . mysqli_real_escape_string($connection, $type_filter) . "'";
}
if (!empty($direction_filter)) {
    $where[] = "cc.direction = '" . mysqli_real_escape_string($connection, $direction_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "cc.client_id = $client_filter";
}
if (!empty($date_from)) {
    $where[] = "DATE(cc.created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(cc.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as outgoing,
    SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as incoming,
    SUM(CASE WHEN comm_type = 'email' THEN 1 ELSE 0 END) as emails,
    SUM(CASE WHEN comm_type = 'call' THEN 1 ELSE 0 END) as calls,
    SUM(CASE WHEN comm_type = 'whatsapp' THEN 1 ELSE 0 END) as whatsapp,
    SUM(CASE WHEN comm_type = 'meeting' THEN 1 ELSE 0 END) as meetings
    FROM client_communications cc
    WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                 FROM clients c
                 JOIN client_communications cc ON c.client_id = cc.client_id
                 WHERE cc.user_id = $user_id
                 ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get communications
$communications_query = "SELECT 
    cc.*,
    c.company_name,
    c.contact_name,
    e.title as engagement_title
    FROM client_communications cc
    JOIN clients c ON cc.client_id = c.client_id
    LEFT JOIN engagements e ON cc.engagement_id = e.engagement_id
    WHERE $where_clause
    ORDER BY cc.created_at DESC";
$communications_result = mysqli_query($connection, $communications_query);
?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-chat-dots text-primary"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Total Communications</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-arrow-right-circle text-success"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['outgoing'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Outgoing</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-arrow-left-circle text-info"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['incoming'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Incoming</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-whatsapp text-warning"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['whatsapp'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">WhatsApp</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-funnel me-2"></i>Filter Communications
        </h5>
        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse show" id="filtersCollapse">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="email" <?php echo $type_filter == 'email' ? 'selected' : ''; ?>>Email</option>
                        <option value="call" <?php echo $type_filter == 'call' ? 'selected' : ''; ?>>Call</option>
                        <option value="whatsapp" <?php echo $type_filter == 'whatsapp' ? 'selected' : ''; ?>>WhatsApp</option>
                        <option value="meeting" <?php echo $type_filter == 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                        <option value="note" <?php echo $type_filter == 'note' ? 'selected' : ''; ?>>Note</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">All</option>
                        <option value="outgoing" <?php echo $direction_filter == 'outgoing' ? 'selected' : ''; ?>>Outgoing</option>
                        <option value="incoming" <?php echo $direction_filter == 'incoming' ? 'selected' : ''; ?>>Incoming</option>
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

<!-- Communications List -->
<div class="card shadow-sm">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-list-ul me-2"></i>All Communications
        </h5>
        <button class="btn btn-sm btn-success" onclick="quickComm()">
            <i class="bi bi-plus-circle me-1"></i>New Communication
        </button>
    </div>
    <div class="card-body p-0">
        <?php if ($communications_result && mysqli_num_rows($communications_result) > 0): ?>
            <div class="communications-list">
                <?php while($comm = mysqli_fetch_assoc($communications_result)): 
                    $icon = 'chat-dots';
                    $color = 'primary';
                    $bgClass = 'bg-primary-soft';
                    
                    switch($comm['comm_type']) {
                        case 'email':
                            $icon = 'envelope';
                            $color = 'info';
                            $bgClass = 'bg-info-soft';
                            break;
                        case 'whatsapp':
                            $icon = 'whatsapp';
                            $color = 'success';
                            $bgClass = 'bg-success-soft';
                            break;
                        case 'call':
                            $icon = 'telephone';
                            $color = 'warning';
                            $bgClass = 'bg-warning-soft';
                            break;
                        case 'meeting':
                            $icon = 'people';
                            $color = 'secondary';
                            $bgClass = 'bg-secondary-soft';
                            break;
                        case 'note':
                            $icon = 'sticky';
                            $color = 'dark';
                            $bgClass = 'bg-dark-soft';
                            break;
                    }
                    
                    $direction_icon = $comm['direction'] == 'outgoing' ? 'arrow-right' : 'arrow-left';
                    $direction_color = $comm['direction'] == 'outgoing' ? 'success' : 'info';
                ?>
                <div class="communication-item">
                    <div class="communication-icon <?php echo $bgClass; ?>">
                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                    </div>
                    <div class="communication-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="communication-title">
                                    <a href="communications.php?source=view&id=<?php echo $comm['comm_id']; ?>" class="text-decoration-none">
                                        <strong><?php echo htmlspecialchars($comm['company_name']); ?></strong>
                                    </a>
                                    <span class="badge bg-<?php echo $direction_color; ?> ms-2">
                                        <i class="bi bi-<?php echo $direction_icon; ?> me-1"></i>
                                        <?php echo ucfirst($comm['direction']); ?>
                                    </span>
                                    <span class="badge bg-<?php echo $color; ?> ms-2">
                                        <?php echo ucfirst($comm['comm_type']); ?>
                                    </span>
                                </h6>
                                <?php if (!empty($comm['subject'])): ?>
                                    <p class="communication-subject mb-1">
                                        <strong>Subject:</strong> <?php echo htmlspecialchars($comm['subject']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($comm['message'])): ?>
                                    <p class="communication-message mb-1">
                                        <?php echo nl2br(htmlspecialchars(substr($comm['message'], 0, 150))); ?>
                                        <?php if (strlen($comm['message']) > 150): ?>
                                            <span class="text-muted">...</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <div class="communication-meta">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y H:i', strtotime($comm['created_at'])); ?>
                                    </small>
                                    <?php if (!empty($comm['engagement_title'])): ?>
                                        <small class="text-muted ms-3">
                                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($comm['engagement_title']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="communication-actions">
                                <button class="btn btn-sm btn-outline-info" onclick="viewCommunication(<?php echo $comm['comm_id']; ?>)" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="communications.php?delete=<?php echo $comm['comm_id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this communication?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-chat-dots display-1 text-muted"></i>
                <h5 class="mt-3">No Communications Found</h5>
                <p class="text-muted">Start logging your client interactions.</p>
                <button class="btn btn-primary mt-3" onclick="quickComm()">
                    <i class="bi bi-plus-circle me-2"></i>Log First Communication
                </button>
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
                        Communication Best Practices
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>📝 Always log client communications for future reference</li>
                        <li>⚡ Use WhatsApp for urgent matters, email for formal documentation</li>
                        <li>📞 Follow up calls with a summary email when appropriate</li>
                        <li>📅 Schedule regular check-ins with key clients</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-chat-dots display-4 text-white-50"></i>
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

/* Communications List */
.communications-list {
    display: flex;
    flex-direction: column;
}

.communication-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.communication-item:hover {
    background: #f8f9fa;
}

.communication-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }
.bg-dark-soft { background: rgba(52, 58, 64, 0.1); }

.communication-content {
    flex: 1;
}

.communication-title {
    margin-bottom: 8px;
    font-size: 1rem;
}
.communication-title a {
    color: #2c3e50;
}
.communication-title a:hover {
    color: #f1bf70;
}

.communication-subject {
    font-size: 0.95rem;
    color: #495057;
}

.communication-message {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
}

.communication-meta {
    margin-top: 8px;
}

.communication-actions {
    display: flex;
    gap: 5px;
}

/* Pro Tip Card - Gradient */
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
    .communication-item {
        flex-direction: column;
    }
    .communication-actions {
        align-self: flex-end;
    }
}
</style>