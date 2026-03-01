<?php
ob_start();

if (!isset($_SESSION['client_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$client_id = $_SESSION['client_id'];

// Get ticket ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'support.php';</script>";
    exit();
}

$ticket_id = (int)$_GET['id'];

// Fetch ticket details
$ticket_query = "SELECT t.*, 
                 CONCAT(c.first_name, ' ', c.last_name) as client_name
                 FROM support_tickets t
                 LEFT JOIN users c ON t.assigned_to = c.user_id
                 WHERE t.ticket_id = $ticket_id AND t.client_id = $client_id";
$ticket_result = mysqli_query($connection, $ticket_query);

if (mysqli_num_rows($ticket_result) == 0) {
    echo "<script>window.location.href = 'support.php';</script>";
    exit();
}

$ticket = mysqli_fetch_assoc($ticket_result);

// Fetch replies
$replies_query = "SELECT r.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  u.user_role
                  FROM ticket_replies r
                  JOIN users u ON r.user_id = u.user_id
                  WHERE r.ticket_id = $ticket_id
                  ORDER BY r.created_at ASC";
$replies_result = mysqli_query($connection, $replies_query);
?>

<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-ticket me-2"></i>
                Ticket #<?php echo $ticket_id; ?>: <?php echo htmlspecialchars($ticket['subject']); ?>
            </h5>
            <a href="support.php" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Tickets
            </a>
        </div>
        <div class="card-body">
            <!-- Ticket Status Bar -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <strong>Status:</strong>
                    <?php
                    $status_class = 'secondary';
                    if ($ticket['status'] == 'open') $status_class = 'warning';
                    if ($ticket['status'] == 'in_progress') $status_class = 'info';
                    if ($ticket['status'] == 'resolved') $status_class = 'success';
                    if ($ticket['status'] == 'closed') $status_class = 'dark';
                    ?>
                    <span class="badge bg-<?php echo $status_class; ?> ms-2"><?php echo ucfirst($ticket['status']); ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Priority:</strong>
                    <?php
                    $priority_class = 'secondary';
                    if ($ticket['priority'] == 'high') $priority_class = 'danger';
                    if ($ticket['priority'] == 'urgent') $priority_class = 'danger';
                    if ($ticket['priority'] == 'medium') $priority_class = 'warning';
                    ?>
                    <span class="badge bg-<?php echo $priority_class; ?> ms-2"><?php echo ucfirst($ticket['priority']); ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Created:</strong>
                    <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                </div>
                <div class="col-md-3">
                    <strong>Assigned to:</strong>
                    <?php echo $ticket['assigned_to'] ? htmlspecialchars($ticket['client_name']) : '<span class="text-muted">Not assigned</span>'; ?>
                </div>
            </div>

            <!-- Original Message -->
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>You wrote:</strong>
                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></small>
                    </div>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                </div>
            </div>

            <!-- Replies -->
            <?php if ($replies_result && mysqli_num_rows($replies_result) > 0): ?>
                <h6 class="mb-3">Replies</h6>
                <?php while($reply = mysqli_fetch_assoc($replies_result)): ?>
                    <div class="card mb-3 <?php echo $reply['is_staff'] ? 'border-primary' : ''; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>
                                    <?php if ($reply['is_staff']): ?>
                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                        <?php echo htmlspecialchars($reply['user_name']); ?> (Staff)
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($reply['user_name']); ?> (You)
                                    <?php endif; ?>
                                </strong>
                                <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                            </div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <!-- Reply Form -->
            <?php if ($ticket['status'] != 'closed'): ?>
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Add Reply</h6>
                    </div>
                    <div class="card-body">
                        <form id="replyForm" method="POST">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                            <div class="mb-3">
                                <textarea class="form-control" id="message" name="message" rows="3" placeholder="Type your reply here..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary">
                    <i class="bi bi-info-circle me-2"></i>
                    This ticket is closed. If you need further assistance, please create a new ticket.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('replyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/ajax/submit_ticket_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error submitting reply');
    });
});
</script>

<?php ob_end_flush(); ?>