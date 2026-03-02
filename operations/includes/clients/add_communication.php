<?php
ob_start();

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get client ID
if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client_id = (int)$_GET['client_id'];

// Verify client access
$check_query = "SELECT c.* 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                GROUP BY c.client_id";
$check_result = mysqli_query($connection, $check_query);
$client = mysqli_fetch_assoc($check_result);

if (!$client) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_communication'])) {
    
    $comm_type = mysqli_real_escape_string($connection, $_POST['comm_type']);
    $direction = mysqli_real_escape_string($connection, $_POST['direction']);
    $subject = mysqli_real_escape_string($connection, trim($_POST['subject'] ?? ''));
    $message_text = mysqli_real_escape_string($connection, trim($_POST['message'] ?? ''));
    $engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
    
    if (empty($comm_type) || empty($direction)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
        
        $insert_query = "INSERT INTO client_communications 
                        (client_id, user_id, comm_type, direction, subject, message, engagement_id, created_at)
                        VALUES ($client_id, $user_id, '$comm_type', '$direction', '$subject', '$message_text', $engagement_value, NOW())";
        
        if (mysqli_query($connection, $insert_query)) {
            $showSuccessModal = true;
        } else {
            $message = "Error logging communication: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

// Get engagements for dropdown
$engagements_query = "SELECT engagement_id, title FROM engagements 
                      WHERE client_id = $client_id AND assigned_to = $user_id
                      ORDER BY created_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-plus-circle me-2"></i>Log Communication
                    </h5>
                    <a href="clients.php?source=communications&id=<?php echo $client_id; ?>" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back to Communications
                    </a>
                </div>
                <div class="card-body">
                    
                    <!-- Client Summary -->
                    <div class="client-summary mb-4">
                        <h6><?php echo htmlspecialchars($client['company_name']); ?></h6>
                        <p class="text-muted mb-0">
                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($client['contact_name']); ?>
                            <span class="mx-2">|</span>
                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($client['contact_email']); ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="commForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Communication Type *</label>
                                <select name="comm_type" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="call">📞 Phone Call</option>
                                    <option value="email">✉️ Email</option>
                                    <option value="whatsapp">💬 WhatsApp</option>
                                    <option value="meeting">🤝 Meeting</option>
                                    <option value="note">📝 Internal Note</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Direction *</label>
                                <select name="direction" class="form-select" required>
                                    <option value="outgoing">Outgoing (To Client)</option>
                                    <option value="incoming">Incoming (From Client)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Related Engagement (Optional)</label>
                            <select name="engagement_id" class="form-select">
                                <option value="">Select Engagement</option>
                                <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                    <option value="<?php echo $eng['engagement_id']; ?>">
                                        <?php echo htmlspecialchars($eng['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief subject of communication">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message / Notes</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Enter details of the communication..."></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_communication" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Save Communication
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Communication Logged Successfully!</h5>
                <p class="text-muted">The interaction has been saved.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="clients.php?source=communications&id=<?php echo $client_id; ?>" class="btn btn-success px-4">
                    <i class="bi bi-clock-history me-2"></i>View Communications
                </a>
                <a href="clients.php?source=add_communication&client_id=<?php echo $client_id; ?>" class="btn btn-outline-success px-4">
                    <i class="bi bi-plus-circle me-2"></i>Add Another
                </a>
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

<style>
.client-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border-left: 4px solid #f1bf70;
}

.dark-header {
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 15px 20px;
}
</style>