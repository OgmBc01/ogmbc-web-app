<?php
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["t.assigned_to = $user_id"];

if (!empty($status_filter)) {
    $where[] = "t.status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if (!empty($priority_filter)) {
    $where[] = "t.priority = '" . mysqli_real_escape_string($connection, $priority_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "t.client_id = $client_filter";
}
if (!empty($date_from)) {
    $where[] = "DATE(t.created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(t.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high
    FROM support_tickets t
    WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                 FROM clients c
                 JOIN support_tickets t ON c.client_id = t.client_id
                 WHERE t.assigned_to = $user_id
                 ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get tickets
$tickets_query = "SELECT 
    t.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    DATEDIFF(NOW(), t.created_at) as days_old
    FROM support_tickets t
    JOIN clients c ON t.client_id = c.client_id
    WHERE $where_clause
    ORDER BY 
        CASE t.priority
            WHEN 'urgent' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
        END,
        CASE t.status
            WHEN 'open' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
            WHEN 'closed' THEN 4
        END,
        t.created_at DESC";
$tickets_result = mysqli_query($connection, $tickets_query);
?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-ticket text-primary"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Total Tickets</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-envelope-open text-warning"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo ($stats['open'] + $stats['in_progress']) ?? 0; ?></h3>
                    <p class="stat-label mb-0">Active</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-card-body d-flex align-items-center">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
                <div class="stat-content ms-3">
                    <h3 class="stat-value mb-0"><?php echo $stats['urgent'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Urgent</p>
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
                    <h3 class="stat-value mb-0"><?php echo $stats['resolved'] ?? 0; ?></h3>
                    <p class="stat-label mb-0">Resolved</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Priority Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="priority-card urgent">
            <div class="d-flex align-items-center">
                <div class="priority-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Urgent Tickets</h6>
                    <span class="priority-count"><?php echo $stats['urgent'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="priority-card high">
            <div class="d-flex align-items-center">
                <div class="priority-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">High Priority</h6>
                    <span class="priority-count"><?php echo $stats['high'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="priority-card average">
            <div class="d-flex align-items-center">
                <div class="priority-icon">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-1">Avg Response</h6>
                    <span class="priority-count">24h</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-funnel me-2"></i>Filter Tickets
        </h5>
        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>
    <div class="collapse show" id="filtersCollapse">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" <?php echo $status_filter == 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
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

<!-- Tickets List -->
<div class="card shadow-sm">
    <div class="card-header dark-header">
        <h5 class="card-title">
            <i class="bi bi-list-ul me-2"></i>My Tickets
        </h5>
        <span class="badge bg-light text-dark"><?php echo $stats['total'] ?? 0; ?> tickets</span>
    </div>
    <div class="card-body p-0">
        <?php if ($tickets_result && mysqli_num_rows($tickets_result) > 0): ?>
            <div class="tickets-list">
                <?php while($ticket = mysqli_fetch_assoc($tickets_result)): 
                    $priority_class = 'secondary';
                    $priority_icon = 'record-circle';
                    switch($ticket['priority']) {
                        case 'urgent':
                            $priority_class = 'danger';
                            $priority_icon = 'exclamation-triangle-fill';
                            break;
                        case 'high':
                            $priority_class = 'warning';
                            $priority_icon = 'exclamation-circle-fill';
                            break;
                        case 'medium':
                            $priority_class = 'info';
                            $priority_icon = 'info-circle-fill';
                            break;
                        case 'low':
                            $priority_class = 'success';
                            $priority_icon = 'check-circle-fill';
                            break;
                    }
                    
                    $status_class = 'secondary';
                    $status_icon = 'record-circle';
                    switch($ticket['status']) {
                        case 'open':
                            $status_class = 'warning';
                            $status_icon = 'envelope-open';
                            break;
                        case 'in_progress':
                            $status_class = 'info';
                            $status_icon = 'arrow-repeat';
                            break;
                        case 'resolved':
                            $status_class = 'success';
                            $status_icon = 'check-circle';
                            break;
                        case 'closed':
                            $status_class = 'dark';
                            $status_icon = 'lock';
                            break;
                    }
                    
                    $days_old = $ticket['days_old'];
                    $old_class = $days_old > 7 ? 'danger' : ($days_old > 3 ? 'warning' : 'success');
                ?>
                <div class="ticket-item">
                    <div class="ticket-priority priority-<?php echo $ticket['priority']; ?>">
                        <i class="bi bi-<?php echo $priority_icon; ?>"></i>
                    </div>
                    <div class="ticket-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="ticket-title">
                                    <a href="tickets.php?source=view&id=<?php echo $ticket['ticket_id']; ?>" class="text-decoration-none">
                                        <strong><?php echo htmlspecialchars($ticket['subject']); ?></strong>
                                    </a>
                                    <span class="badge bg-<?php echo $priority_class; ?> ms-2">
                                        <i class="bi bi-<?php echo $priority_icon; ?> me-1"></i>
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                    <span class="badge bg-<?php echo $status_class; ?> ms-2">
                                        <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                        <?php echo str_replace('_', ' ', $ticket['status']); ?>
                                    </span>
                                </h6>
                                
                                <!-- Client Info -->
                                <p class="ticket-client mb-2">
                                    <i class="bi bi-building me-1 text-muted"></i>
                                    <strong><?php echo htmlspecialchars($ticket['company_name']); ?></strong>
                                    <span class="text-muted ms-2">(<?php echo htmlspecialchars($ticket['contact_name']); ?>)</span>
                                </p>
                                
                                <!-- Message Preview -->
                                <p class="ticket-preview mb-2">
                                    <?php echo htmlspecialchars(substr($ticket['message'], 0, 150)); ?>
                                    <?php if (strlen($ticket['message']) > 150): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </p>
                                
                                <!-- Meta Info -->
                                <div class="ticket-meta">
                                    <span class="badge bg-<?php echo $old_class; ?>-soft text-<?php echo $old_class; ?> me-2">
                                        <i class="bi bi-clock me-1"></i><?php echo $days_old; ?> days old
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                    </small>
                                    <small class="text-muted ms-2">
                                        <i class="bi bi-clock-history me-1"></i>Updated: <?php echo date('M d', strtotime($ticket['updated_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="ticket-actions">
                                <button class="btn btn-sm btn-outline-info" onclick="viewTicket(<?php echo $ticket['ticket_id']; ?>)" title="Quick View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="quickReply(<?php echo $ticket['ticket_id']; ?>)" title="Reply">
                                    <i class="bi bi-reply"></i>
                                </button>
                                <?php if ($ticket['status'] != 'closed'): ?>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="tickets.php?update_status=<?php echo $ticket['ticket_id']; ?>&status=in_progress">
                                            <i class="bi bi-play-circle me-2"></i>Start Progress
                                        </a></li>
                                        <li><a class="dropdown-item" href="tickets.php?update_status=<?php echo $ticket['ticket_id']; ?>&status=resolved">
                                            <i class="bi bi-check-circle me-2"></i>Mark Resolved
                                        </a></li>
                                        <li><a class="dropdown-item" href="tickets.php?update_status=<?php echo $ticket['ticket_id']; ?>&status=closed">
                                            <i class="bi bi-lock me-2"></i>Close Ticket
                                        </a></li>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-ticket display-1 text-muted"></i>
                <h5 class="mt-3">No Tickets Found</h5>
                <p class="text-muted">You don't have any assigned tickets matching your criteria.</p>
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
                        Ticket Management Tips
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>⚡ Respond to urgent tickets within 4 hours</li>
                        <li>📝 Always provide clear, detailed responses</li>
                        <li>✅ Update status regularly to keep clients informed</li>
                        <li>🔍 Check ticket history before responding</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-ticket display-4 text-white-50"></i>
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
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-danger { border-left-color: #dc3545; }
.stat-card-success { border-left-color: #38c172; }

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

/* Priority Cards */
.priority-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    height: 100%;
}
.priority-card.urgent { border-left: 6px solid #dc3545; }
.priority-card.high { border-left: 6px solid #fd7e14; }
.priority-card.average { border-left: 6px solid #20c997; }

.priority-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.priority-card.urgent .priority-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.priority-card.high .priority-icon {
    background: rgba(253, 126, 20, 0.1);
    color: #fd7e14;
}
.priority-card.average .priority-icon {
    background: rgba(32, 201, 151, 0.1);
    color: #20c997;
}
.priority-count {
    font-size: 1.5rem;
    font-weight: 700;
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

/* Tickets List */
.tickets-list {
    display: flex;
    flex-direction: column;
}
.ticket-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.ticket-item:hover {
    background: #f8f9fa;
}
.ticket-priority {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.priority-urgent { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
.priority-high { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
.priority-medium { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.priority-low { background: rgba(25, 135, 84, 0.1); color: #198754; }

.ticket-content {
    flex: 1;
}
.ticket-title {
    margin-bottom: 8px;
    font-size: 1rem;
}
.ticket-title a {
    color: #2c3e50;
}
.ticket-title a:hover {
    color: #f1bf70;
}
.ticket-client {
    font-size: 0.9rem;
    color: #495057;
}
.ticket-preview {
    font-size: 0.9rem;
    color: #6c757d;
}
.ticket-meta {
    margin-top: 8px;
}
.ticket-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

/* Badge backgrounds */
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }

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
    .ticket-item {
        flex-direction: column;
    }
    .ticket-actions {
        align-self: flex-end;
        flex-wrap: wrap;
    }
    .ticket-priority {
        align-self: center;
    }
}
</style>