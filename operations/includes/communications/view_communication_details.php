<?php
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'communications.php';</script>";
    exit();
}

$comm_id = (int)$_GET['id'];

// Get communication details
$query = "SELECT 
    cc.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    c.contact_mobile,
    e.title as engagement_title,
    CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM client_communications cc
    JOIN clients c ON cc.client_id = c.client_id
    LEFT JOIN engagements e ON cc.engagement_id = e.engagement_id
    JOIN users u ON cc.user_id = u.user_id
    WHERE cc.comm_id = $comm_id AND cc.user_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'communications.php';</script>";
    exit();
}

$comm = mysqli_fetch_assoc($result);

// Set icon and color based on type
$icon = 'chat-dots';
$color = 'primary';
switch($comm['comm_type']) {
    case 'email':
        $icon = 'envelope';
        $color = 'info';
        break;
    case 'whatsapp':
        $icon = 'whatsapp';
        $color = 'success';
        break;
    case 'call':
        $icon = 'telephone';
        $color = 'warning';
        break;
    case 'meeting':
        $icon = 'people';
        $color = 'secondary';
        break;
    case 'note':
        $icon = 'sticky';
        $color = 'dark';
        break;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-chat-dots me-2"></i>Communication Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="communications.php">Communications</a></li>
                    <li class="breadcrumb-item active">View Communication</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="communications.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <a href="communications.php?delete=<?php echo $comm_id; ?>" class="btn btn-danger" onclick="return confirm('Delete this communication?')">
                <i class="bi bi-trash me-1"></i>Delete
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-<?php echo $color; ?> text-white">
            <div class="d-flex align-items-center">
                <i class="bi bi-<?php echo $icon; ?> fs-2 me-3"></i>
                <div>
                    <h5 class="mb-1"><?php echo ucfirst($comm['comm_type']); ?> Communication</h5>
                    <small>
                        <i class="bi bi-calendar me-1"></i><?php echo date('F d, Y \a\t h:i A', strtotime($comm['created_at'])); ?>
                        <span class="ms-3">
                            <i class="bi bi-arrow-<?php echo $comm['direction'] == 'outgoing' ? 'right' : 'left'; ?> me-1"></i>
                            <?php echo ucfirst($comm['direction']); ?>
                        </span>
                    </small>
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
                        <p class="mb-2"><strong><?php echo htmlspecialchars($comm['company_name']); ?></strong></p>
                        <p class="mb-1">
                            <i class="bi bi-person me-2 text-muted"></i>
                            <?php echo htmlspecialchars($comm['contact_name']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <a href="mailto:<?php echo $comm['contact_email']; ?>"><?php echo $comm['contact_email']; ?></a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2 text-muted"></i>
                            <?php echo $comm['contact_mobile']; ?>
                        </p>
                    </div>
                </div>

                <!-- Communication Details -->
                <div class="col-md-8">
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="bi bi-info-circle me-2"></i>Communication Details
                        </h6>
                        
                        <?php if (!empty($comm['subject'])): ?>
                            <div class="mb-3">
                                <strong>Subject:</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($comm['subject']); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <strong>Message / Notes:</strong>
                            <div class="message-box bg-light p-3 rounded mt-2">
                                <?php echo nl2br(htmlspecialchars($comm['message'] ?: 'No message provided.')); ?>
                            </div>
                        </div>

                        <?php if (!empty($comm['engagement_title'])): ?>
                            <div class="mt-3">
                                <strong>Related Engagement:</strong>
                                <p class="mb-0">
                                    <i class="bi bi-briefcase me-1"></i>
                                    <?php echo htmlspecialchars($comm['engagement_title']); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>Logged by: <?php echo htmlspecialchars($comm['user_name']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="quick-actions">
                        <?php if ($comm['comm_type'] == 'email'): ?>
                            <a href="mailto:<?php echo $comm['contact_email']; ?>?subject=<?php echo urlencode('Re: ' . $comm['subject']); ?>" class="btn btn-outline-info me-2">
                                <i class="bi bi-envelope me-1"></i>Reply via Email
                            </a>
                        <?php elseif ($comm['comm_type'] == 'whatsapp'): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $comm['contact_mobile']); ?>" target="_blank" class="btn btn-outline-success me-2">
                                <i class="bi bi-whatsapp me-1"></i>Open WhatsApp
                            </a>
                        <?php endif; ?>
                        <a href="communications.php?source=add&client=<?php echo $comm['client_id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i>Add New Communication
                        </a>
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
}

.quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

@media (max-width: 768px) {
    .quick-actions {
        flex-direction: column;
    }
    .quick-actions .btn {
        width: 100%;
    }
}
</style>