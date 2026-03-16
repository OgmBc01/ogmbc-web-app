<?php
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$client_filter = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';
$assigned_filter = isset($_GET['assigned_to']) ? (int)$_GET['assigned_to'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = ["1=1"];

if (!empty($status_filter)) {
    $where[] = "t.status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
if (!empty($priority_filter)) {
    $where[] = "t.priority = '" . mysqli_real_escape_string($connection, $priority_filter) . "'";
}
if (!empty($client_filter)) {
    $where[] = "t.client_id = $client_filter";
}
if (!empty($assigned_filter)) {
    $where[] = "t.assigned_to = $assigned_filter";
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
                SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
                FROM support_tickets t";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get staff for filter
$staff_query = "SELECT u.user_id, u.first_name, u.last_name 
                FROM users u
                JOIN user_roles r ON u.role_id = r.role_id
                WHERE u.user_status = 'active'
                ORDER BY u.first_name";
$staff_result = mysqli_query($connection, $staff_query);

// Get tickets
$tickets_query = "SELECT 
    t.*,
    c.company_name,
    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
    (SELECT COUNT(*) FROM ticket_replies WHERE ticket_id = t.ticket_id) as reply_count,
    (SELECT MAX(created_at) FROM ticket_replies WHERE ticket_id = t.ticket_id) as last_reply
    FROM support_tickets t
    JOIN clients c ON t.client_id = c.client_id
    LEFT JOIN users u ON t.assigned_to = u.user_id
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

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="bi bi-ticket me-2"></i>Support Tickets
        </h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="stat-card-small bg-primary text-white">
                <div class="stat-icon">
                    <i class="bi bi-ticket"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-warning text-white">
                <div class="stat-icon">
                    <i class="bi bi-envelope-open"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['open'] ?? 0; ?></h3>
                    <p class="stat-label">Open</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-info text-white">
                <div class="stat-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></h3>
                    <p class="stat-label">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-success text-white">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['resolved'] ?? 0; ?></h3>
                    <p class="stat-label">Resolved</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-secondary text-white">
                <div class="stat-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['closed'] ?? 0; ?></h3>
                    <p class="stat-label">Closed</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small bg-danger text-white">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['urgent'] ?? 0; ?></h3>
                    <p class="stat-label">Urgent</p>
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
                            <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
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
                    <div class="col-md-3">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned / All</option>
                            <?php while($staff = mysqli_fetch_assoc($staff_result)): ?>
                                <option value="<?php echo $staff['user_id']; ?>" <?php echo $assigned_filter == $staff['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
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

    <!-- Tickets Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-list-ul me-2"></i>All Support Tickets
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Client</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Last Activity</th>
                            <th>Replies</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tickets_result && mysqli_num_rows($tickets_result) > 0): ?>
                            <?php while($ticket = mysqli_fetch_assoc($tickets_result)): 
                                $priority_class = 'secondary';
                                switch($ticket['priority']) {
                                    case 'urgent': $priority_class = 'danger'; break;
                                    case 'high': $priority_class = 'warning'; break;
                                    case 'medium': $priority_class = 'info'; break;
                                    case 'low': $priority_class = 'success'; break;
                                }
                                
                                $status_class = 'secondary';
                                switch($ticket['status']) {
                                    case 'open': $status_class = 'warning'; break;
                                    case 'in_progress': $status_class = 'info'; break;
                                    case 'resolved': $status_class = 'success'; break;
                                    case 'closed': $status_class = 'dark'; break;
                                }
                            ?>
                            <tr>
                                <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($ticket['company_name']); ?></td>
                                <td>
                                    <a href="support_tickets.php?source=view&id=<?php echo $ticket['ticket_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $priority_class; ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo str_replace('_', ' ', $ticket['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ticket['assigned_to_name']): ?>
                                        <?php echo htmlspecialchars($ticket['assigned_to_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted" data-bs-toggle="tooltip" title="<?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>">
                                        <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($ticket['last_reply']): ?>
                                        <small class="text-muted" data-bs-toggle="tooltip" title="<?php echo date('M d, Y H:i', strtotime($ticket['last_reply'])); ?>">
                                            <?php echo date('M d, Y', strtotime($ticket['last_reply'])); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?php echo $ticket['reply_count']; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="viewTicket(<?php echo $ticket['ticket_id']; ?>)" title="Quick View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="support_tickets.php?source=view&id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-outline-primary" title="Full View">
                                            <i class="bi bi-chat-dots"></i>
                                        </a>
                                        <?php if (!$ticket['assigned_to']): ?>
                                            <button class="btn btn-outline-warning" onclick="assignTicket(<?php echo $ticket['ticket_id']; ?>)" title="Assign">
                                                <i class="bi bi-person-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($can_manage_tickets): ?>
                                            <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $ticket['ticket_id']; ?>, '<?php echo htmlspecialchars($ticket['subject'], ENT_QUOTES); ?>')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="bi bi-ticket display-1 text-muted"></i>
                                        <h5 class="mt-3">No tickets found</h5>
                                        <p class="text-muted">No support tickets match your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    height: 100%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.stat-card-small .stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-card-small .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 2px;
    line-height: 1.2;
}

.stat-card-small .stat-label {
    font-size: 0.75rem;
    opacity: 0.9;
    margin: 0;
}

.dark-header {
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}
</style>