<?php
// Get the current user's user_id from session
$user_id = $_SESSION['user_id'] ?? 0;
$client_id = 0;
if ($user_id > 0) {
    // Fetch the actual client_id from the clients table
    $result_client = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = " . intval($user_id));
    if ($result_client && mysqli_num_rows($result_client) > 0) {
        $row = mysqli_fetch_assoc($result_client);
        $client_id = $row['client_id'];
    }
}

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
if ($client_id > 0 && mysqli_num_rows($tables_check) > 0) {
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
                                    <a href="#" class="text-decoration-none" onclick="viewTicketDetails(<?php echo $ticket['ticket_id']; ?>); return false;">
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
                                    <button class="btn btn-sm btn-info" onclick="viewTicketDetails(<?php echo $ticket['ticket_id']; ?>)" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
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

    <!-- Ticket Details Modal -->
    <div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                    <h5 class="modal-title"><i class="bi bi-ticket me-2"></i>Ticket Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ticketDetailsBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading ticket details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewTicketDetails(ticketId) {
        const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
        document.getElementById('ticketDetailsBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading ticket details...</p>
            </div>
        `;
        modal.show();
        fetch('includes/ajax/get_ticket_details.php?ticket_id=' + ticketId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.ticket) {
                    let html = `<div class="mb-3">
                        <h5><span class="badge bg-primary">#${data.ticket.ticket_id}</span> ${escapeHtml(data.ticket.subject)}</h5>
                        <div class="mb-2"><strong>Status:</strong> <span class="badge bg-info">${escapeHtml(data.ticket.status)}</span> &nbsp; <strong>Priority:</strong> <span class="badge bg-warning">${escapeHtml(data.ticket.priority)}</span></div>
                        <div class="mb-2"><strong>Created:</strong> ${escapeHtml(data.ticket.created_at)}</div>
                        <div class="mb-2"><strong>Message:</strong><br><div class="border rounded p-2 bg-light">${escapeHtml(data.ticket.message)}</div></div>
                    </div>`;
                    if (data.replies && data.replies.length > 0) {
                        html += `<h6 class="mt-4 mb-2"><i class="bi bi-chat-dots me-1"></i>Replies</h6>`;
                        html += `<div class="list-group mb-2">`;
                        data.replies.forEach(function(reply) {
                            html += `<div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold">${reply.is_staff == 1 ? 'Support' : 'You'}</span>
                                    <small class="text-muted">${escapeHtml(reply.created_at)}</small>
                                </div>
                                <div>${escapeHtml(reply.message)}</div>
                            </div>`;
                        });
                        html += `</div>`;
                    } else {
                        html += `<div class="alert alert-info">No replies yet.</div>`;
                    }
                    document.getElementById('ticketDetailsBody').innerHTML = html;
                } else {
                    document.getElementById('ticketDetailsBody').innerHTML = `<div class="alert alert-danger">${escapeHtml(data.message || 'Failed to load ticket details.')}</div>`;
                }
            })
            .catch(error => {
                document.getElementById('ticketDetailsBody').innerHTML = `<div class="alert alert-danger">Error loading ticket details. Please try again.</div>`;
            });
    }
    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, function(m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'})[m];
        });
    }
    </script>
</div>