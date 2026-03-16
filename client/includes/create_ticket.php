<?php
ob_start();

$client_id = $_SESSION['user_id'] ?? 0; // Define client_id from session (user_id for clients)

// Initialize variables
$subject = '';
$message = '';
$priority = 'medium';
$engagement_id = '';
$showSuccessModal = false;
$ticket_id = null;
$message_error = '';
$message_type = '';

// Get engagements for dropdown - with error handling
$engagements_result = null;
$engagements_query = "SELECT engagement_id, title FROM engagements 
                      WHERE client_id = " . intval($client_id) . " AND status != 'CLOSED'
                      ORDER BY created_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);

if (!$engagements_result) {
    // Log error but continue - table might not exist
    error_log("Engagements query failed: " . mysqli_error($connection));
    $engagements_result = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    
    $subject = mysqli_real_escape_string($connection, trim($_POST['subject']));
    $message = mysqli_real_escape_string($connection, trim($_POST['message']));
    $priority = mysqli_real_escape_string($connection, $_POST['priority']);
    $engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
    
    if (empty($subject) || empty($message)) {
        $message_error = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Check if support_tickets table exists
        $table_check = mysqli_query($connection, "SHOW TABLES LIKE 'support_tickets'");
        if (mysqli_num_rows($table_check) == 0) {
            $message_error = "Support tickets system is not set up yet. Please contact administrator.";
            $message_type = "danger";
        } else {
            // Insert ticket
            $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
            
            $insert_query = "INSERT INTO support_tickets 
                            (client_id, subject, message, priority, status, created_at)
                            VALUES 
                            ($client_id, '$subject', '$message', '$priority', 'open', NOW())";
            
            if (mysqli_query($connection, $insert_query)) {
                $ticket_id = mysqli_insert_id($connection);
                
                // Log activity if table exists
                $log_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_activity_log'");
                if (mysqli_num_rows($log_check) > 0) {
                    $log_query = "INSERT INTO client_activity_log 
                                 (client_id, activity_type, description, ip_address)
                                 VALUES 
                                 ($client_id, 'ticket_created', 'Created support ticket #$ticket_id: $subject', '{$_SERVER['REMOTE_ADDR']}')";
                    mysqli_query($connection, $log_query);
                }
                
                $showSuccessModal = true;
                $subject = $message = '';
                $priority = 'medium';
                $engagement_id = '';
            } else {
                $message_error = "Error creating ticket: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create Support Ticket</h5>
                    <a href="support.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Tickets
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message_error) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="ticketForm">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($subject); ?>" 
                                   placeholder="Brief summary of your issue" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="low" <?php echo $priority == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $priority == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $priority == 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo $priority == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="engagement_id" class="form-label">Related Engagement (Optional)</label>
                                <select class="form-control" id="engagement_id" name="engagement_id">
                                    <option value="">None</option>
                                    <?php if ($engagements_result && mysqli_num_rows($engagements_result) > 0): ?>
                                        <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                            <option value="<?php echo $eng['engagement_id']; ?>">
                                                <?php echo htmlspecialchars($eng['title']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      placeholder="Describe your issue in detail..." required><?php echo htmlspecialchars($message); ?></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_ticket" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Ticket Created!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-ticket-check text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Your support ticket has been created</h5>
                <p class="text-muted">We'll get back to you as soon as possible.</p>
                <?php if ($ticket_id): ?>
                    <p class="small">Ticket #<?php echo $ticket_id; ?></p>
                <?php endif; ?>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="support.php" class="btn btn-success px-4">View My Tickets</a>
                <a href="support.php?source=new" class="btn btn-outline-success px-4">Create Another</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
<?php endif; ?>