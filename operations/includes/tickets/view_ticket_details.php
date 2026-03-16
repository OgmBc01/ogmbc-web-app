<?php
// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'tickets.php';</script>";
    exit();
}

$ticket_id = (int)$_GET['id'];

// Get ticket details
$query = "SELECT t.*, 
          c.company_name, c.contact_name, c.contact_email, c.contact_mobile,
          CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
          FROM support_tickets t
          JOIN clients c ON t.client_id = c.client_id
          LEFT JOIN users u ON t.assigned_to = u.user_id
          WHERE t.ticket_id = $ticket_id AND t.assigned_to = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'tickets.php';</script>";
    exit();
}

$ticket = mysqli_fetch_assoc($result);

// Priority and status classes
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
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-ticket me-2"></i>Ticket #<?php echo $ticket_id; ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="tickets.php">Tickets</a></li>
                    <li class="breadcrumb-item active">Ticket Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="tickets.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button class="btn btn-primary" onclick="quickReply(<?php echo $ticket_id; ?>)">
                <i class="bi bi-reply me-1"></i>Reply
            </button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-<?php echo $status_class; ?> text-white">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="bi bi-<?php echo $status_icon; ?> fs-2 me-3"></i>
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                        <small>
                            <i class="bi bi-calendar me-1"></i><?php echo date('F d, Y \a\t h:i A', strtotime($ticket['created_at'])); ?>
                            <span class="ms-3">
                                <i class="bi bi-arrow-repeat me-1"></i>Updated: <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?>
                            </span>
                        </small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-<?php echo $priority_class; ?> me-2">
                        <i class="bi bi-<?php echo $priority_icon; ?> me-1"></i>
                        <?php echo ucfirst($ticket['priority']); ?>
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                        <?php echo str_replace('_', ' ', $ticket['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Client Information -->
                <div class="col-md-4 mb-4">
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="bi bi-building me-2"></i>Client Information
                        </h6>
                        <p class="mb-2"><strong><?php echo htmlspecialchars($ticket['company_name']); ?></strong></p>
                        <p class="mb-1">
                            <i class="bi bi-person me-2 text-muted"></i>
                            <?php echo htmlspecialchars($ticket['contact_name']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <a href="mailto:<?php echo $ticket['contact_email']; ?>"><?php echo $ticket['contact_email']; ?></a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2 text-muted"></i>
                            <?php echo $ticket['contact_mobile']; ?>
                        </p>
                    </div>
                </div>

                <!-- Ticket Details -->
                <div class="col-md-8">
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="bi bi-info-circle me-2"></i>Ticket Details
                        </h6>

                        <!-- Original Message -->
                        <div class="mb-4">
                            <strong>Original Message:</strong>
                            <div class="message-box bg-light p-3 rounded mt-2">
                                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                            </div>
                        </div>

                        <!-- Status Update Buttons -->
                        <?php if ($ticket['status'] != 'closed'): ?>
                        <div class="mt-4">
                            <strong>Update Status:</strong>
                            <div class="status-buttons mt-2">
                                <?php if ($ticket['status'] == 'open'): ?>
                                    <a href="tickets.php?update_status=<?php echo $ticket_id; ?>&status=in_progress" class="btn btn-info me-2">
                                        <i class="bi bi-play-circle me-1"></i>Start Progress
                                    </a>
                                <?php endif; ?>
                                <?php if ($ticket['status'] == 'in_progress'): ?>
                                    <a href="tickets.php?update_status=<?php echo $ticket_id; ?>&status=resolved" class="btn btn-success me-2">
                                        <i class="bi bi-check-circle me-1"></i>Mark Resolved
                                    </a>
                                <?php endif; ?>
                                <?php if ($ticket['status'] != 'closed'): ?>
                                    <a href="tickets.php?update_status=<?php echo $ticket_id; ?>&status=closed" class="btn btn-secondary" onclick="return confirm('Close this ticket?')">
                                        <i class="bi bi-lock me-1"></i>Close Ticket
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
}
.info-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}
.message-box {
    white-space: pre-wrap;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
}
.status-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
</style>