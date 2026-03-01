<?php
// Initialize variables with default values
$result = null;
$counts = [
    'total' => 0,
    'open_count' => 0,
    'in_progress_count' => 0,
    'resolved_count' => 0,
    'closed_count' => 0
];

// Check if support_tickets table exists
$tables_check = mysqli_query($connection, "SHOW TABLES LIKE 'support_tickets'");
if (mysqli_num_rows($tables_check) > 0) {
    // Get all tickets for this client
    $query = "SELECT t.*, 
              COUNT(r.reply_id) as reply_count,
              MAX(r.created_at) as last_reply
              FROM support_tickets t
              LEFT JOIN ticket_replies r ON t.ticket_id = r.ticket_id
              WHERE t.client_id = " . intval($client_id) . "
              GROUP BY t.ticket_id
              ORDER BY 
                CASE t.status
                    WHEN 'open' THEN 1
                    WHEN 'in_progress' THEN 2
                    WHEN 'resolved' THEN 3
                    WHEN 'closed' THEN 4
                    ELSE 5
                END,
                t.created_at DESC";
    
    $result = mysqli_query($connection, $query);
    if (!$result) {
        error_log("Support tickets query failed: " . mysqli_error($connection));
        $result = null;
    }

    // Get ticket counts by status
    $count_query = "SELECT 
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count,
                    COUNT(*) as total
                    FROM support_tickets 
                    WHERE client_id = " . intval($client_id);
    $count_result = mysqli_query($connection, $count_query);
    if ($count_result) {
        $counts = mysqli_fetch_assoc($count_result);
    }
}

// Ensure all count values are set
$counts['total'] = $counts['total'] ?? 0;
$counts['open_count'] = $counts['open_count'] ?? 0;
$counts['in_progress_count'] = $counts['in_progress_count'] ?? 0;
$counts['resolved_count'] = $counts['resolved_count'] ?? 0;
$counts['closed_count'] = $counts['closed_count'] ?? 0;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Support Tickets</h1>
        <a href="support.php?source=new" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Ticket
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2><?php echo $counts['total']; ?></h2>
                    <div>Total Tickets</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h2><?php echo $counts['open_count']; ?></h2>
                    <div>Open</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2><?php echo $counts['in_progress_count']; ?></h2>
                    <div>In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2><?php echo $counts['resolved_count']; ?></h2>
                    <div>Resolved</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-ticket me-2"></i>My Support Tickets</h5>
        </div>
        <div class="card-body">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created</th>
                                <th>Last Activity</th>
                                <th>Replies</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ticket = mysqli_fetch_assoc($result)): 
                                $status_class = 'secondary';
                                if ($ticket['status'] == 'open') $status_class = 'warning';
                                if ($ticket['status'] == 'in_progress') $status_class = 'info';
                                if ($ticket['status'] == 'resolved') $status_class = 'success';
                                if ($ticket['status'] == 'closed') $status_class = 'dark';
                                
                                $priority_class = 'secondary';
                                if ($ticket['priority'] == 'high') $priority_class = 'danger';
                                if ($ticket['priority'] == 'urgent') $priority_class = 'danger';
                                if ($ticket['priority'] == 'medium') $priority_class = 'warning';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $ticket['ticket_id']; ?></strong></td>
                                <td>
                                    <a href="support.php?source=view&id=<?php echo $ticket['ticket_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($ticket['status']); ?></span></td>
                                <td><span class="badge bg-<?php echo $priority_class; ?>"><?php echo ucfirst($ticket['priority']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    if ($ticket['last_reply']) {
                                        echo date('M d, Y', strtotime($ticket['last_reply']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="text-center"><?php echo $ticket['reply_count']; ?></td>
                                <td>
                                    <a href="support.php?source=view&id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($ticket['status'] != 'closed'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openReplyModal(<?php echo $ticket['ticket_id']; ?>)" title="Reply">
                                        <i class="bi bi-reply"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-ticket display-1 text-muted"></i>
                    <h4 class="mt-3">No Support Tickets Yet</h4>
                    <p class="text-muted">Need help? Create a support ticket and we'll get back to you.</p>
                    <a href="support.php?source=new" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Create First Ticket
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>