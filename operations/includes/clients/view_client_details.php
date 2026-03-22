<?php
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify client access and fetch details
$query = "SELECT 
    c.*,
    COUNT(DISTINCT e.engagement_id) as total_engagements,
    SUM(CASE WHEN e.status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as active_engagements,
    MAX(e.updated_at) as last_activity,
    (SELECT COUNT(*) FROM client_communications WHERE client_id = c.client_id) as total_comms,
    (SELECT COUNT(*) FROM client_files WHERE client_id = c.client_id) as total_files,
    (SELECT COUNT(*) FROM client_feedback WHERE client_id = c.client_id AND is_validated = 1) as total_feedback
    FROM clients c
    LEFT JOIN engagements e ON c.client_id = e.client_id AND e.assigned_to = $user_id
    WHERE c.client_id = $client_id
    GROUP BY c.client_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client = mysqli_fetch_assoc($result);

// Get recent engagements
$engagements_query = "SELECT 
    e.engagement_id,
    e.title,
    e.status,
    COALESCE(e.approved_deadline, e.original_deadline) as deadline,
    s.service_name,
    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining
    FROM engagements e
    JOIN service_types s ON e.service_id = s.service_id
    WHERE e.client_id = $client_id AND e.assigned_to = $user_id
    ORDER BY 
        CASE e.status
            WHEN 'IN_PROGRESS' THEN 1
            WHEN 'AWAITING_REVIEW' THEN 2
            WHEN 'ASSIGNED' THEN 3
            WHEN 'SUBMITTED' THEN 4
            WHEN 'CLOSED' THEN 5
        END,
        deadline ASC
    LIMIT 5";
$engagements_result = mysqli_query($connection, $engagements_query);

// Get recent communications
$comms_query = "SELECT cc.*, 
                CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM client_communications cc
                JOIN users u ON cc.user_id = u.user_id
                WHERE cc.client_id = $client_id
                ORDER BY cc.created_at DESC
                LIMIT 5";
$comms_result = mysqli_query($connection, $comms_query);

// Get recent files
$files_query = "SELECT * FROM client_files 
                WHERE client_id = $client_id 
                ORDER BY uploaded_at DESC 
                LIMIT 5";
$files_result = mysqli_query($connection, $files_query);

// Get feedback summary
$feedback_query = "SELECT 
    AVG(rating) as avg_rating,
    COUNT(*) as total_feedback,
    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_feedback
    FROM client_feedback 
    WHERE client_id = $client_id AND is_validated = 1";
$feedback_result = mysqli_query($connection, $feedback_query);
$feedback = mysqli_fetch_assoc($feedback_result);
?>

<div class="container-fluid">
    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><?php echo htmlspecialchars($client['company_name']); ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="clients.php">Clients</a></li>
                    <li class="breadcrumb-item active">Client Details</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="clients.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back to List
            </a>
            <button class="btn btn-primary" onclick="quickComm(<?php echo $client_id; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')">
                <i class="bi bi-chat-dots me-1"></i>Quick Comm
            </button>
        </div>
    </div>

    <!-- Client Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-briefcase text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $client['total_engagements'] ?? 0; ?></h3>
                    <p class="stat-label">Total Engagements</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-play-circle text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $client['active_engagements'] ?? 0; ?></h3>
                    <p class="stat-label">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-chat-dots text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $client['total_comms'] ?? 0; ?></h3>
                    <p class="stat-label">Communications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-files text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $client['total_files'] ?? 0; ?></h3>
                    <p class="stat-label">Files</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Left Column - Client Information -->
        <div class="col-lg-4">
            <!-- Contact Card -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-person me-2"></i>Contact Information
                </h6>
                <div class="info-content">
                    <p class="mb-2"><strong><?php echo htmlspecialchars($client['contact_name']); ?></strong></p>
                    <?php if (!empty($client['contact_designation'])): ?>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($client['contact_designation']); ?></p>
                    <?php endif; ?>
                    
                    <div class="contact-details">
                        <p class="mb-2">
                            <i class="bi bi-envelope me-2 text-primary"></i>
                            <a href="mailto:<?php echo $client['contact_email']; ?>"><?php echo $client['contact_email']; ?></a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2 text-primary"></i>
                            <?php echo $client['contact_mobile']; ?>
                        </p>
                    </div>
                    
                    <div class="quick-contact-buttons mt-3">
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $client['contact_mobile']); ?>" target="_blank" class="btn btn-sm btn-success me-2">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        <a href="mailto:<?php echo $client['contact_email']; ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-envelope"></i> Email
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company Details Card -->
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-building me-2"></i>Company Details
                </h6>
                <div class="info-content">
                    <div class="detail-row">
                        <span class="detail-label">Trade License</span>
                        <span class="detail-value"><?php echo htmlspecialchars($client['trade_license_no'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Country</span>
                        <span class="detail-value"><?php echo htmlspecialchars($client['country'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Emirate/Zone</span>
                        <span class="detail-value"><?php echo htmlspecialchars($client['emirate_zone'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Client Since</span>
                        <span class="detail-value"><?php echo date('M d, Y', strtotime($client['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Business Activity Card -->
            <?php if (!empty($client['business_activity']) || !empty($client['address'])): ?>
            <div class="info-card mb-4">
                <h6 class="info-title">
                    <i class="bi bi-geo-alt me-2"></i>Business Information
                </h6>
                <div class="info-content">
                    <?php if (!empty($client['business_activity'])): ?>
                        <p class="mb-1"><strong>Business Activity:</strong></p>
                        <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($client['business_activity'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['address'])): ?>
                        <p class="mb-1"><strong>Address:</strong></p>
                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($client['address'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feedback Summary -->
            <?php if (($feedback['total_feedback'] ?? 0) > 0): ?>
            <div class="info-card">
                <h6 class="info-title">
                    <i class="bi bi-star me-2"></i>Client Feedback
                </h6>
                <div class="info-content">
                    <div class="feedback-summary">
                        <div class="rating-display">
                            <span class="rating-value"><?php echo number_format($feedback['avg_rating'], 1); ?></span>
                            <span class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= round($feedback['avg_rating']) ? '-fill' : ''; ?> text-warning"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <div class="feedback-stats mt-2">
                            <small class="text-muted">
                                <?php echo $feedback['positive_feedback']; ?> positive out of <?php echo $feedback['total_feedback']; ?> reviews
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Activity & Records -->
        <div class="col-lg-8">
            <!-- Quick Actions Tabs -->
            <ul class="nav nav-tabs mb-4" id="clientTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="engagements-tab" data-bs-toggle="tab" data-bs-target="#engagements" type="button" role="tab">
                        <i class="bi bi-briefcase me-2"></i>Engagements
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="communications-tab" data-bs-toggle="tab" data-bs-target="#communications" type="button" role="tab">
                        <i class="bi bi-chat-dots me-2"></i>Communications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab">
                        <i class="bi bi-files me-2"></i>Files
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="clientTabsContent">
                <!-- Engagements Tab -->
                <div class="tab-pane fade show active" id="engagements" role="tabpanel">
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Recent Engagements</h6>
                            <a href="clients.php?source=engagements&id=<?php echo $client_id; ?>" class="btn btn-sm btn-outline-primary">
                                View All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        
                        <?php if ($engagements_result && mysqli_num_rows($engagements_result) > 0): ?>
                            <div class="engagement-list">
                                <?php while($eng = mysqli_fetch_assoc($engagements_result)): 
                                    $status_class = $eng['status'] == 'IN_PROGRESS' ? 'primary' : 
                                        ($eng['status'] == 'AWAITING_REVIEW' ? 'warning' : 
                                        ($eng['status'] == 'SUBMITTED' ? 'success' : 
                                        ($eng['status'] == 'CLOSED' ? 'dark' : 'secondary')));
                                    
                                    $is_overdue = ($eng['days_remaining'] < 0 && $eng['status'] != 'CLOSED' && $eng['status'] != 'SUBMITTED');
                                ?>
                                <div class="engagement-list-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="engagements.php?source=view&id=<?php echo $eng['engagement_id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($eng['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($eng['service_name']); ?></small>
                                        </div>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $eng['status']; ?></span>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-<?php echo $is_overdue ? 'danger' : ($eng['days_remaining'] <= 3 ? 'warning' : 'success'); ?>">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php 
                                            if ($is_overdue) echo abs($eng['days_remaining']) . ' days overdue';
                                            elseif ($eng['days_remaining'] == 0) echo 'Due today';
                                            elseif ($eng['days_remaining'] > 0) echo $eng['days_remaining'] . ' days left';
                                            else echo 'Completed';
                                            ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No engagements found for this client.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Communications Tab -->
                <div class="tab-pane fade" id="communications" role="tabpanel">
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Recent Communications</h6>
                            <button class="btn btn-sm btn-primary" onclick="quickComm(<?php echo $client_id; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')">
                                <i class="bi bi-plus-circle me-1"></i>New
                            </button>
                        </div>
                        
                        <?php if ($comms_result && mysqli_num_rows($comms_result) > 0): ?>
                            <div class="communication-list">
                                <?php while($comm = mysqli_fetch_assoc($comms_result)): 
                                    $icon = $comm['comm_type'] == 'email' ? 'envelope' : 
                                        ($comm['comm_type'] == 'whatsapp' ? 'whatsapp' : 
                                        ($comm['comm_type'] == 'call' ? 'telephone' : 'chat-dots'));
                                    $color = $comm['comm_type'] == 'email' ? 'info' : 
                                        ($comm['comm_type'] == 'whatsapp' ? 'success' : 
                                        ($comm['comm_type'] == 'call' ? 'warning' : 'primary'));
                                ?>
                                <div class="communication-item">
                                    <div class="comm-icon bg-<?php echo $color; ?>-soft">
                                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                    </div>
                                    <div class="comm-content">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo ucfirst($comm['comm_type']); ?></strong>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($comm['created_at'])); ?></small>
                                        </div>
                                        <?php if (!empty($comm['subject'])): ?>
                                            <p class="mb-1"><strong><?php echo htmlspecialchars($comm['subject']); ?></strong></p>
                                        <?php endif; ?>
                                        <?php if (!empty($comm['message'])): ?>
                                            <p class="mb-0 small text-muted"><?php echo htmlspecialchars(substr($comm['message'], 0, 100)) . (strlen($comm['message']) > 100 ? '...' : ''); ?></p>
                                        <?php endif; ?>
                                        <small class="text-muted">by <?php echo htmlspecialchars($comm['user_name']); ?></small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="clients.php?source=communications&id=<?php echo $client_id; ?>" class="btn btn-outline-primary btn-sm">
                                    View All Communications
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No communications yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Files Tab -->
                <div class="tab-pane fade" id="files" role="tabpanel">
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Recent Files</h6>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                <i class="bi bi-cloud-upload me-1"></i>Upload
                            </button>
                        </div>
                        
                        <?php if ($files_result && mysqli_num_rows($files_result) > 0): ?>
                            <div class="files-list">
                                <?php while($file = mysqli_fetch_assoc($files_result)): 
                                    $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                    $icon = 'file-earmark';
                                    if ($ext == 'pdf') $icon = 'file-earmark-pdf';
                                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'file-earmark-image';
                                    elseif (in_array($ext, ['doc', 'docx'])) $icon = 'file-earmark-word';
                                    elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'file-earmark-excel';
                                ?>
                                <div class="file-item">
                                    <i class="bi bi-<?php echo $icon; ?> file-icon"></i>
                                    <div class="file-info">
                                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                        <small class="text-muted">
                                            <?php echo ucfirst($file['uploaded_by']); ?> • <?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?>
                                        </small>
                                    </div>
                                    <a href="../uploads/client_files/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="clients.php?source=files&id=<?php echo $client_id; ?>" class="btn btn-outline-primary btn-sm">
                                    View All Files
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">No files shared yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload File Modal (reused from client_files.php) -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Upload File
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="includes/ajax/upload_client_file.php" enctype="multipart/form-data" id="uploadForm">
                <div class="modal-body">
                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">File Description (Optional)</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description of the file">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Max file size: 10MB. Allowed: PDF, Images, DOC, DOCX, XLS, XLSX</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Client Management Tips
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        ✅ Log every client interaction for future reference<br>
                        ✅ Use the Quick Comm button to quickly log calls or messages<br>
                        ✅ Upload files promptly to keep client informed<br>
                        ✅ Check active engagements before contacting client
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-people display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    height: 100%;
}

.stat-card-small .stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.stat-card-small .stat-value {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 2px;
}

.stat-card-small .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin: 0;
}

.info-card {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 15px;
    height: fit-content;
}

.info-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

.info-content {
    margin-bottom: 0;
}

.info-content p:last-child {
    margin-bottom: 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #dee2e6;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-row:first-child {
    padding-top: 0;
}

.detail-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.detail-value {
    font-weight: 500;
    color: #2c3e50;
}

.quick-contact-buttons {
    display: flex;
    gap: 8px;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rating-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f1bf70;
    line-height: 1.2;
}

.rating-stars {
    font-size: 1.1rem;
}

.engagement-list-item {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.engagement-list-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.engagement-list-item:first-child {
    padding-top: 0;
}

.communication-item {
    display: flex;
    gap: 12px;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.communication-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.communication-item:first-child {
    padding-top: 0;
}

.comm-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.comm-content {
    flex: 1;
}

.comm-content p:last-child {
    margin-bottom: 0;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    margin-bottom: 8px;
}

.file-item:last-child {
    margin-bottom: 0;
}

.file-icon {
    font-size: 1.3rem;
    color: #f1bf70;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    display: block;
    margin-bottom: 2px;
}

.pro-tip-card {
    background: linear-gradient(90deg, #0a2240 0%, #003366 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

/* Tab styles */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 10px 20px;
}

.nav-tabs .nav-link.active {
    color: #f1bf70;
    background: transparent;
    border-bottom: 3px solid #f1bf70;
}

@media (max-width: 768px) {
    .stat-card-small {
        padding: 12px;
    }
    
    .quick-contact-buttons {
        flex-direction: column;
    }
    
    .rating-display {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Handle upload form submission
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');
    const originalText = uploadBtn.innerHTML;
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
    
    fetch('includes/ajax/upload_client_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
            modal.hide();
            location.reload(); // Refresh to show new file
        } else {
            alert('Error: ' + data.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('Error uploading file');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
    });
});
</script>