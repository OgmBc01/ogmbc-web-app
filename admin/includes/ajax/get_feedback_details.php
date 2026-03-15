<?php
// Start output buffering
ob_start();

// Disable error display but enable logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Include database connection - adjust path as needed
    require_once __DIR__ . '/../../../includes/database.php';

    if (!$connection) {
        throw new Exception('Database connection failed');
    }

    // Get feedback ID
    $feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$feedback_id) {
        throw new Exception('Invalid feedback ID');
    }

    // Get feedback details with all related information
    $query = "SELECT cf.*, 
                     c.company_name,
                     c.contact_name,
                     c.contact_email,
                     c.contact_mobile,
                     CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                     u.user_email as employee_email,
                     u.user_id,
                     CONCAT(v.first_name, ' ', v.last_name) as validated_by_name,
                     v.user_email as validator_email,
                     e.title as engagement_title,
                     e.engagement_id
              FROM client_feedback cf
              JOIN clients c ON cf.client_id = c.client_id
              LEFT JOIN users u ON cf.employee_id = u.user_id
              LEFT JOIN users v ON cf.validated_by = v.user_id
              LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
              WHERE cf.feedback_id = ?";

    $stmt = mysqli_prepare($connection, $query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare query: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $feedback_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception('Feedback not found');
    }

    $feedback = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Clear any output that might have been generated
    ob_clean();

    // Generate HTML
    $rating = $feedback['rating'] ?? 5;
    $points = $feedback['points_awarded'] ?? 50;
    ?>
    <div class="container-fluid p-3">
        <!-- Header with Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: #f8f9fa; border-left: 4px solid <?php 
                    echo $feedback['is_validated'] ? '#28a745' : ($feedback['is_rejected'] ? '#dc3545' : '#ffc107'); 
                ?>;">
                    <div>
                        <h5 class="mb-1" style="color: #0a2240;">Feedback #<?php echo $feedback_id; ?></h5>
                        <p class="mb-0 text-muted small">
                            <i class="bi bi-calendar me-1"></i><?php echo date('F j, Y \a\t g:i A', strtotime($feedback['created_at'])); ?>
                        </p>
                    </div>
                    <div>
                        <?php if ($feedback['is_validated']): ?>
                            <span class="badge bg-success px-3 py-2">✓ Validated</span>
                        <?php elseif ($feedback['is_rejected']): ?>
                            <span class="badge bg-danger px-3 py-2">✗ Rejected</span>
                        <?php else: ?>
                            <span class="badge bg-warning px-3 py-2">⏳ Pending Review</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Client Information -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom" style="border-bottom-color: #f1bf70 !important;">
                        <h6 class="mb-0" style="color: #f1bf70;; font-weight: 600;">
                            <i class="bi bi-building me-2" style="color: #f1bf70;"></i>Client Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="100" class="text-muted">Company:</td>
                                <td class="fw-bold"><?php echo htmlspecialchars($feedback['company_name']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Contact:</td>
                                <td><?php echo htmlspecialchars($feedback['contact_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><a href="mailto:<?php echo htmlspecialchars($feedback['contact_email']); ?>" style="color: #f1bf70; text-decoration: none;"><?php echo htmlspecialchars($feedback['contact_email']); ?></a></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone:</td>
                                <td><?php echo htmlspecialchars($feedback['contact_mobile'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Employee Information -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom" style="border-bottom-color: #f1bf70 !important;">
                        <h6 class="mb-0" style="color: #f1bf70; font-weight: 600;">
                            <i class="bi bi-person me-2" style="color: #f1bf70;"></i>Employee Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="100" class="text-muted">Name:</td>
                                <td class="fw-bold"><?php echo htmlspecialchars($feedback['employee_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><a href="mailto:<?php echo htmlspecialchars($feedback['employee_email']); ?>" style="color: #f1bf70; text-decoration: none;"><?php echo htmlspecialchars($feedback['employee_email']); ?></a></td>
                            </tr>
                            <tr>
                                <td class="text-muted">User ID:</td>
                                <td><code>#<?php echo $feedback['user_id']; ?></code></td>
                            </tr>
                            <?php if (!empty($feedback['engagement_title'])): ?>
                            <tr>
                                <td class="text-muted">Engagement:</td>
                                <td>
                                    <a href="engagements.php?source=view&id=<?php echo $feedback['engagement_id']; ?>" style="color: #f1bf70; text-decoration: none;">
                                        <?php echo htmlspecialchars($feedback['engagement_title']); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Feedback Content -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom" style="border-bottom-color: #f1bf70 !important;">
                        <h6 class="mb-0" style="color: #f1bf70; font-weight: 600;">
                            <i class="bi bi-chat-quote me-2" style="color: #f1bf70;"></i>Feedback Content
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="text-muted small mb-2">Rating</label>
                            <div>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rating ? '-fill' : ''; ?> me-1" style="color: #f1bf70; font-size: 1.2rem;"></i>
                                <?php endfor; ?>
                                <span class="ms-2 badge bg-light text-dark"><?php echo $rating; ?>/5</span>
                            </div>
                        </div>

                        <!-- Feedback Text -->
                        <div class="mb-3">
                            <label class="text-muted small mb-2">Feedback Message</label>
                            <div class="p-3 rounded" style="background: #f8f9fa;">
                                <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($feedback['evidence_file'])): ?>
                        <div>
                            <label class="text-muted small mb-2">Evidence File</label>
                            <div>
                                <a href="../uploads/feedback/<?php echo $feedback['evidence_file']; ?>" target="_blank" class="btn btn-sm" style="background: #f1bf70; color: #0a2240;">
                                    <i class="bi bi-file-earmark me-1"></i>View Attachment
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Review Information -->
            <?php if ($feedback['is_validated'] || $feedback['is_rejected']): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom" style="border-bottom-color: #f1bf70 !important;">
                        <h6 class="mb-0" style="color: #f1bf70; font-weight: 600;">
                            <i class="bi bi-clipboard-check me-2" style="color: #f1bf70;"></i>Review Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><small class="text-muted">Reviewed By</small></p>
                                <p class="fw-bold"><?php echo htmlspecialchars($feedback['validated_by_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><small class="text-muted">Reviewed On</small></p>
                                <p class="fw-bold"><?php echo !empty($feedback['reviewed_at']) ? date('M d, Y H:i', strtotime($feedback['reviewed_at'])) : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><small class="text-muted">Points Awarded</small></p>
                                <p class="fw-bold">
                                    <?php if ($feedback['is_validated']): ?>
                                        <span class="badge bg-success"><?php echo $points; ?> points</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">0 points</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php if ($feedback['is_rejected'] && !empty($feedback['rejection_reason'])): ?>
                            <div class="col-12 mt-2">
                                <p class="mb-1"><small class="text-muted">Rejection Reason</small></p>
                                <div class="p-2 rounded" style="background: #fff3f3;">
                                    <?php echo nl2br(htmlspecialchars($feedback['rejection_reason'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($feedback['review_notes'])): ?>
                            <div class="col-12 mt-2">
                                <p class="mb-1"><small class="text-muted">Review Notes</small></p>
                                <p><?php echo nl2br(htmlspecialchars($feedback['review_notes'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

    // Get the generated HTML
    $html = ob_get_clean();

    // Return JSON with HTML
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    // Clear any output
    ob_clean();
    
    // Log error
    error_log('get_feedback_details.php error: ' . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>