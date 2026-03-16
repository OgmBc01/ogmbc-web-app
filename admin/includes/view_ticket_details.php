<?php
// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'support_tickets.php';</script>";
    exit();
}

$ticket_id = (int)$_GET['id'];

// Get ticket details
$ticket_query = "SELECT t.*, 
                c.company_name, c.contact_name, c.contact_email,
                CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                u.user_email as assigned_email
                FROM support_tickets t
                JOIN clients c ON t.client_id = c.client_id
                LEFT JOIN users u ON t.assigned_to = u.user_id
                WHERE t.ticket_id = $ticket_id";
$ticket_result = mysqli_query($connection, $ticket_query);

if (!$ticket_result || mysqli_num_rows($ticket_result) == 0) {
    echo "<script>window.location.href = 'support_tickets.php';</script>";
    exit();
}

$ticket = mysqli_fetch_assoc($ticket_result);

// Get replies
$replies_query = "SELECT r.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  u.user_role
                  FROM ticket_replies r
                  JOIN users u ON r.user_id = u.user_id
                  WHERE r.ticket_id = $ticket_id
                  ORDER BY r.created_at ASC";
$replies_result = mysqli_query($connection, $replies_query);

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $message = mysqli_real_escape_string($connection, trim($_POST['message']));
    
    if (!empty($message)) {
        $insert_query = "INSERT INTO ticket_replies 
                        (ticket_id, user_id, message, is_staff, created_at)
                        VALUES ($ticket_id, {$_SESSION['user_id']}, '$message', 1, NOW())";
        
        if (mysqli_query($connection, $insert_query)) {
            // Update ticket status if it was resolved or closed
            if ($ticket['status'] == 'resolved' || $ticket['status'] == 'closed') {
                $update_status = "UPDATE support_tickets SET status = 'in_progress', updated_at = NOW() WHERE ticket_id = $ticket_id";
                mysqli_query($connection, $update_status);
            }
            
            $_SESSION['success_message'] = "Reply added successfully!";
            echo "<script>window.location.href = 'support_tickets.php?source=view&id=$ticket_id';</script>";
            exit();
        } else {
            $error_message = "Error adding reply: " . mysqli_error($connection);
        }
    }
}

// Priority and status badge classes
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

<div class="container-fluid">
    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2">Ticket #<?php echo $ticket_id; ?>: <?php echo htmlspecialchars($ticket['subject']); ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="support_tickets.php">Support Tickets</a></li>
                    <li class="breadcrumb-item active">Ticket #<?php echo $ticket_id; ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="support_tickets.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <?php if ($can_manage_tickets): ?>
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-pencil me-1"></i>Update Status
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="support_tickets.php?update_status=<?php echo $ticket_id; ?>&status=open">Open</a></li>
                        <li><a class="dropdown-item" href="support_tickets.php?update_status=<?php echo $ticket_id; ?>&status=in_progress">In Progress</a></li>
                        <li><a class="dropdown-item" href="support_tickets.php?update_status=<?php echo $ticket_id; ?>&status=resolved">Resolved</a></li>
                        <li><a class="dropdown-item" href="support_tickets.php?update_status=<?php echo $ticket_id; ?>&status=closed">Closed</a></li>
                    </ul>
                </div>
                <?php if ($ticket['status'] !== 'closed'): ?>
                <button class="btn btn-dark me-2" onclick="closeTicket(<?php echo $ticket_id; ?>, '<?php echo htmlspecialchars($ticket['subject'], ENT_QUOTES); ?>')">
                    <i class="bi bi-x-circle me-1"></i>Close Ticket
                </button>
                <?php endif; ?>
                <?php if (!$ticket['assigned_to']): ?>
                    <button class="btn btn-warning" onclick="assignTicket(<?php echo $ticket_id; ?>)">
                        <i class="bi bi-person-plus me-1"></i>Assign
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ticket Info Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="info-card">
                <h6 class="info-title"><i class="bi bi-building me-2"></i>Client Information</h6>
                <p class="mb-1"><strong><?php echo htmlspecialchars($ticket['company_name']); ?></strong></p>
                <p class="mb-1"><i class="bi bi-person me-2 text-muted"></i><?php echo htmlspecialchars($ticket['contact_name']); ?></p>
                <p class="mb-0"><i class="bi bi-envelope me-2 text-muted"></i>
                    <a href="mailto:<?php echo $ticket['contact_email']; ?>"><?php echo $ticket['contact_email']; ?></a>
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <h6 class="info-title"><i class="bi bi-info-circle me-2"></i>Ticket Details</h6>
                <p class="mb-1">
                    <strong>Status:</strong> 
                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo str_replace('_', ' ', $ticket['status']); ?></span>
                </p>
                <p class="mb-1">
                    <strong>Priority:</strong> 
                    <span class="badge bg-<?php echo $priority_class; ?>"><?php echo ucfirst($ticket['priority']); ?></span>
                </p>
                <p class="mb-1"><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></p>
                <p class="mb-0"><strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($ticket['updated_at'])); ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <h6 class="info-title"><i class="bi bi-person-badge me-2"></i>Assignment</h6>
                <?php if ($ticket['assigned_to']): ?>
                    <p class="mb-1"><strong><?php echo htmlspecialchars($ticket['assigned_to_name']); ?></strong></p>
                    <p class="mb-0"><i class="bi bi-envelope me-2 text-muted"></i>
                        <a href="mailto:<?php echo $ticket['assigned_email']; ?>"><?php echo $ticket['assigned_email']; ?></a>
                    </p>
                <?php else: ?>
                    <p class="mb-1 text-muted">Not assigned</p>
                    <?php if ($can_manage_tickets): ?>
                        <button class="btn btn-sm btn-warning mt-2" onclick="assignTicket(<?php echo $ticket_id; ?>)">
                            <i class="bi bi-person-plus me-1"></i>Assign Now
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Original Message Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-chat-left-text me-2"></i>Original Message
            </h6>
            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></small>
        </div>
        <div class="card-body">
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
        </div>
    </div>

    <!-- Replies Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Replies</h6>
        </div>
        <div class="card-body">
            <?php if ($replies_result && mysqli_num_rows($replies_result) > 0): ?>
                <div class="replies-timeline">
                    <?php while($reply = mysqli_fetch_assoc($replies_result)): ?>
                        <div class="reply-item <?php echo $reply['is_staff'] ? 'staff-reply' : ''; ?>">
                            <div class="reply-avatar">
                                <?php if ($reply['is_staff']): ?>
                                    <i class="bi bi-shield-check text-primary"></i>
                                <?php else: ?>
                                    <i class="bi bi-person-circle text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="reply-content">
                                <div class="reply-header">
                                    <strong><?php echo htmlspecialchars($reply['user_name']); ?></strong>
                                    <?php if ($reply['is_staff']): ?>
                                        <span class="badge bg-primary ms-2">Staff</span>
                                    <?php endif; ?>
                                    <small class="text-muted float-end">
                                        <?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="reply-body">
                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">No replies yet. Be the first to respond.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Form -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Add Reply</h6>
        </div>
        <div class="card-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="replyForm">
                <div class="mb-3">
                    <textarea class="form-control" name="message" rows="4" placeholder="Type your reply here..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" name="submit_reply" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    height: 100%;
}

.info-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
}

.replies-timeline {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.reply-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.reply-item.staff-reply {
    background: #e7f3ff;
    border-left: 4px solid #0d6efd;
}

.reply-avatar {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    border: 2px solid #dee2e6;
}

.reply-item.staff-reply .reply-avatar {
    border-color: #0d6efd;
    background: white;
}

.reply-content {
    flex: 1;
}

.reply-header {
    margin-bottom: 8px;
}

.reply-body {
    font-size: 0.95rem;
    line-height: 1.5;
}
</style>

<script>
// Show close ticket confirmation modal (reuses modal from view_support_tickets.php if present)
function closeTicket(id, subject) {
    // If modal doesn't exist, create it
    if (!document.getElementById('closeTicketModal')) {
        const modalHtml = `
        <div class="modal fade" id="closeTicketModal" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="closeTicketModalLabel">Confirm Close Ticket</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to <strong>close</strong> ticket: <strong><span id="closeTicketSubject"></span></strong>?</p>
                        <p class="text-warning"><small>This will mark the ticket as closed. You can reopen it later if needed.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmCloseBtn" class="btn btn-dark">Close Ticket</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    document.getElementById('closeTicketSubject').textContent = subject;
    const modal = new bootstrap.Modal(document.getElementById('closeTicketModal'));
    document.getElementById('confirmCloseBtn').onclick = function() {
        fetch('includes/ajax/close_ticket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ticket_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ticket closed successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to close ticket.');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
        modal.hide();
    };
    modal.show();
}
</script>