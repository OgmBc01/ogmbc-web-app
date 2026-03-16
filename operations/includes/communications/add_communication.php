<?php
ob_start();

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$client_id = isset($_GET['client']) ? (int)$_GET['client'] : 0;

// If editing, fetch existing communication
$comm = null;
if ($edit_id > 0) {
    $query = "SELECT * FROM client_communications WHERE comm_id = $edit_id AND user_id = $user_id";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $comm = mysqli_fetch_assoc($result);
        $client_id = $comm['client_id'];
    }
}

// Get clients for dropdown
$clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                 FROM clients c
                 JOIN engagements e ON c.client_id = e.client_id
                 WHERE e.assigned_to = $user_id
                 ORDER BY c.company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_communication'])) {
    
    $client_id = (int)$_POST['client_id'];
    $engagement_id = !empty($_POST['engagement_id']) ? (int)$_POST['engagement_id'] : 'NULL';
    $comm_type = mysqli_real_escape_string($connection, $_POST['comm_type']);
    $direction = mysqli_real_escape_string($connection, $_POST['direction']);
    $subject = mysqli_real_escape_string($connection, trim($_POST['subject'] ?? ''));
    $message = mysqli_real_escape_string($connection, trim($_POST['message'] ?? ''));
    
    if (empty($client_id) || empty($comm_type) || empty($direction)) {
        $error = "Please fill in all required fields.";
    } else {
        $engagement_value = ($engagement_id !== 'NULL') ? $engagement_id : 'NULL';
        
        if ($edit_id > 0) {
            // Update existing
            $query = "UPDATE client_communications SET 
                     client_id = $client_id,
                     engagement_id = $engagement_value,
                     comm_type = '$comm_type',
                     direction = '$direction',
                     subject = '$subject',
                     message = '$message'
                     WHERE comm_id = $edit_id AND user_id = $user_id";
        } else {
            // Insert new
            $query = "INSERT INTO client_communications 
                     (client_id, user_id, comm_type, direction, subject, message, engagement_id, created_at)
                     VALUES ($client_id, $user_id, '$comm_type', '$direction', '$subject', '$message', $engagement_value, NOW())";
        }
        
        if (mysqli_query($connection, $query)) {
            $_SESSION['success_message'] = $edit_id > 0 ? "Communication updated successfully!" : "Communication logged successfully!";
            echo "<script>window.location.href = 'communications.php';</script>";
            exit();
        } else {
            $error = "Error: " . mysqli_error($connection);
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-<?php echo $edit_id > 0 ? 'pencil' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_id > 0 ? 'Edit Communication' : 'Log New Communication'; ?>
                    </h5>
                    <a href="communications.php" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Back
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="commForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="client_id" id="client_id" class="form-select" required>
                                    <option value="">Select Client</option>
                                    <?php while($client = mysqli_fetch_assoc($clients_result)): ?>
                                        <option value="<?php echo $client['client_id']; ?>" <?php echo $client_id == $client['client_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['company_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Communication Type <span class="text-danger">*</span></label>
                                <select name="comm_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="call" <?php echo ($comm && $comm['comm_type'] == 'call') ? 'selected' : ''; ?>>📞 Phone Call</option>
                                    <option value="email" <?php echo ($comm && $comm['comm_type'] == 'email') ? 'selected' : ''; ?>>✉️ Email</option>
                                    <option value="whatsapp" <?php echo ($comm && $comm['comm_type'] == 'whatsapp') ? 'selected' : ''; ?>>💬 WhatsApp</option>
                                    <option value="meeting" <?php echo ($comm && $comm['comm_type'] == 'meeting') ? 'selected' : ''; ?>>🤝 Meeting</option>
                                    <option value="note" <?php echo ($comm && $comm['comm_type'] == 'note') ? 'selected' : ''; ?>>📝 Note</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Direction <span class="text-danger">*</span></label>
                                <select name="direction" class="form-select" required>
                                    <option value="outgoing" <?php echo ($comm && $comm['direction'] == 'outgoing') ? 'selected' : ''; ?>>Outgoing (To Client)</option>
                                    <option value="incoming" <?php echo ($comm && $comm['direction'] == 'incoming') ? 'selected' : ''; ?>>Incoming (From Client)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Related Engagement</label>
                                <select name="engagement_id" id="engagement_id" class="form-select">
                                    <option value="">Select Engagement</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?php echo $comm ? htmlspecialchars($comm['subject']) : ''; ?>"
                                   placeholder="Brief subject of communication">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message / Notes</label>
                            <textarea name="message" class="form-control" rows="5" 
                                      placeholder="Enter details of the communication..."><?php echo $comm ? htmlspecialchars($comm['message']) : ''; ?></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_communication" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $edit_id > 0 ? 'Update Communication' : 'Save Communication'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load engagements when client is selected
document.getElementById('client_id')?.addEventListener('change', function() {
    const clientId = this.value;
    const engagementSelect = document.getElementById('engagement_id');
    const selectedEngagement = <?php echo $comm && $comm['engagement_id'] ? $comm['engagement_id'] : 0; ?>;
    
    if (!clientId) {
        engagementSelect.innerHTML = '<option value="">Select Engagement</option>';
        return;
    }
    
    fetch('includes/ajax/get_client_engagements.php?client_id=' + clientId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Engagement</option>';
            if (data.success && data.engagements.length > 0) {
                data.engagements.forEach(eng => {
                    const selected = eng.engagement_id == selectedEngagement ? 'selected' : '';
                    options += `<option value="${eng.engagement_id}" ${selected}>${eng.title} (${eng.status})</option>`;
                });
            }
            engagementSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading engagements:', error);
        });
});

// Trigger change if editing and client is selected
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('client_id').value) {
        document.getElementById('client_id').dispatchEvent(new Event('change'));
    }
});
</script>

<style>
.dark-header {
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>