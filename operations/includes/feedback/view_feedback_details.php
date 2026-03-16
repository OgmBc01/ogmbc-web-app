<?php
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'feedback.php';</script>";
    exit();
}

$feedback_id = (int)$_GET['id'];

// Get feedback details
$query = "SELECT 
    cf.*,
    c.company_name,
    c.contact_name,
    c.contact_email,
    c.contact_mobile,
    e.title as engagement_title,
    e.engagement_id,
    CONCAT(u.first_name, ' ', u.last_name) as validated_by_name,
    CONCAT(cr.first_name, ' ', cr.last_name) as created_by_name
    FROM client_feedback cf
    JOIN clients c ON cf.client_id = c.client_id
    LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
    LEFT JOIN users u ON cf.validated_by = u.user_id
    LEFT JOIN users cr ON cf.created_by = cr.user_id
    WHERE cf.feedback_id = $feedback_id AND cf.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'feedback.php';</script>";
    exit();
}

$feedback = mysqli_fetch_assoc($result);

// Set status badge
$status_class = 'warning';
$status_text = 'Pending';
$status_icon = 'clock';

if ($feedback['is_rejected']) {
    $status_class = 'danger';
    $status_text = 'Rejected';
    $status_icon = 'x-circle';
} elseif ($feedback['is_validated']) {
    $status_class = 'success';
    $status_text = 'Validated';
    $status_icon = 'check-circle';
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-star me-2"></i>Feedback Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="feedback.php">Feedback</a></li>
                    <li class="breadcrumb-item active">View Feedback</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="feedback.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-<?php echo $status_class; ?> text-white">
            <div class="d-flex align-items-center">
                <i class="bi bi-<?php echo $status_icon; ?> fs-2 me-3"></i>
                <div>
                    <h5 class="mb-1">Feedback from <?php echo htmlspecialchars($feedback['company_name']); ?></h5>
                    <small>
                        <i class="bi bi-calendar me-1"></i><?php echo date('F d, Y \a\t h:i A', strtotime($feedback['created_at'])); ?>
                        <span class="ms-3 badge bg-light text-dark">
                            Status: <?php echo $status_text; ?>
                        </span>
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Left Column - Client & Engagement Info -->
                <div class="col-md-5 mb-4">
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="bi bi-building me-2"></i>Client Information
                        </h6>
                        <p class="mb-2"><strong><?php echo htmlspecialchars($feedback['company_name']); ?></strong></p>
                        <p class="mb-1">
                            <i class="bi bi-person me-2 text-muted"></i>
                            <?php echo htmlspecialchars($feedback['contact_name']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <a href="mailto:<?php echo $feedback['contact_email']; ?>"><?php echo $feedback['contact_email']; ?></a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2 text-muted"></i>
                            <?php echo $feedback['contact_mobile']; ?>
                        </p>

                        <?php if (!empty($feedback['engagement_title'])): ?>
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="info-title">
                                    <i class="bi bi-briefcase me-2"></i>Related Engagement
                                </h6>
                                <p class="mb-0">
                                    <a href="engagements.php?source=view&id=<?php echo $feedback['engagement_id']; ?>">
                                        <?php echo htmlspecialchars($feedback['engagement_title']); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column - Feedback Details -->
                <div class="col-md-7">
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="bi bi-star me-2"></i>Feedback Details
                        </h6>

                        <!-- Rating -->
                        <div class="mb-3">
                            <strong>Rating:</strong>
                            <div class="rating-large mt-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill' : ''; ?> text-warning fs-3"></i>
                                <?php endfor; ?>
                                <span class="ms-3 fw-bold fs-4"><?php echo $feedback['rating']; ?>/5</span>
                            </div>
                        </div>

                        <!-- Feedback Text -->
                        <div class="mb-3">
                            <strong>Feedback:</strong>
                            <div class="feedback-box bg-light p-3 rounded mt-2">
                                <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                            </div>
                        </div>

                        <!-- Points Awarded -->
                        <?php if ($feedback['points_awarded'] > 0): ?>
                            <div class="mb-3">
                                <strong>Points Awarded:</strong>
                                <span class="badge bg-success fs-6 ms-2">+<?php echo $feedback['points_awarded']; ?> points</span>
                            </div>
                        <?php endif; ?>

                        <!-- Rejection Reason (if rejected) -->
                        <?php if ($feedback['is_rejected'] && !empty($feedback['rejection_reason'])): ?>
                            <div class="mb-3">
                                <strong class="text-danger">Rejection Reason:</strong>
                                <div class="bg-danger-soft p-3 rounded mt-2">
                                    <?php echo nl2br(htmlspecialchars($feedback['rejection_reason'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Review Notes -->
                        <?php if (!empty($feedback['review_notes'])): ?>
                            <div class="mb-3">
                                <strong>Review Notes:</strong>
                                <div class="bg-light p-3 rounded mt-2">
                                    <?php echo nl2br(htmlspecialchars($feedback['review_notes'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Evidence File -->
                        <?php if (!empty($feedback['evidence_file'])): ?>
                            <div class="mb-3">
                                <strong>Evidence File:</strong>
                                <div class="mt-2">
                                    <a href="../uploads/feedback_evidence/<?php echo $feedback['evidence_file']; ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bi bi-download me-1"></i>Download Evidence
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Validation Details -->
            <?php if ($feedback['is_validated'] || $feedback['is_rejected']): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="info-card">
                            <h6 class="info-title">
                                <i class="bi bi-check-circle me-2"></i>Review Details
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Reviewed By:</strong> 
                                        <?php echo htmlspecialchars($feedback['validated_by_name']); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>Reviewed At:</strong> 
                                        <?php echo date('F d, Y H:i', strtotime($feedback['reviewed_at'] ?: $feedback['validated_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
.rating-large {
    display: flex;
    align-items: center;
}
.feedback-box {
    white-space: pre-wrap;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
}
.bg-danger-soft {
    background: rgba(220, 53, 69, 0.1);
}
</style>