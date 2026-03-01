<?php
// Ensure client_id is defined
if (!isset($client_id)) {
    $client_id = $_SESSION['client_id'] ?? 0;
}

if ($client_id <= 0) {
    echo '<div class="alert alert-danger">Invalid client ID</div>';
    return;
}

// Initialize variables
$feedback_result = null;
$stats = [
    'total' => 0,
    'with_feedback' => 0,
    'average_rating' => 0
];

// Check if client_feedback table exists
$table_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_feedback'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    // Get all feedback for this client
    $query = "SELECT f.*, 
              e.title as engagement_title,
              e.engagement_id,
              s.service_name,
              CONCAT(u.first_name, ' ', u.last_name) as employee_name,
              DATE_FORMAT(f.created_at, '%M %d, %Y') as formatted_date
              FROM client_feedback f
              LEFT JOIN engagements e ON f.engagement_id = e.engagement_id
              LEFT JOIN service_types s ON e.service_id = s.service_id
              LEFT JOIN users u ON f.employee_id = u.user_id
              WHERE f.client_id = " . intval($client_id) . "
              ORDER BY f.created_at DESC";
    
    $feedback_result = mysqli_query($connection, $query);
    
    if (!$feedback_result) {
        error_log("Feedback query failed: " . mysqli_error($connection));
        $feedback_result = null;
    }

    // Get feedback statistics
    $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN feedback_text IS NOT NULL AND feedback_text != '' THEN 1 ELSE 0 END) as with_feedback,
                    AVG(rating) as average_rating
                    FROM client_feedback 
                    WHERE client_id = " . intval($client_id);
    $stats_result = mysqli_query($connection, $stats_query);
    if ($stats_result) {
        $stats = mysqli_fetch_assoc($stats_result);
    }
}

// Ensure stats values are set
$stats['total'] = $stats['total'] ?? 0;
$stats['with_feedback'] = $stats['with_feedback'] ?? 0;
$stats['average_rating'] = $stats['average_rating'] ?? 0;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">My Feedback</h1>
        <a href="feedback.php?source=submit" class="btn btn-primary">
            <i class="bi bi-star"></i> Submit New Feedback
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2><?php echo $stats['total']; ?></h2>
                    <div>Total Feedback Submitted</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2><?php echo $stats['with_feedback']; ?></h2>
                    <div>Engagements with Feedback</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h2>
                        <?php 
                        if ($stats['average_rating'] > 0) {
                            echo number_format($stats['average_rating'], 1) . ' / 5';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </h2>
                    <div>Average Rating</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-star me-2"></i>My Feedback History</h5>
        </div>
        <div class="card-body">
            <?php if ($feedback_result && mysqli_num_rows($feedback_result) > 0): ?>
                <div class="timeline">
                    <?php while($feedback = mysqli_fetch_assoc($feedback_result)): 
                        // Generate star rating display
                        $rating = intval($feedback['rating'] ?? 0);
                        $stars = '';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                $stars .= '<i class="bi bi-star-fill text-warning me-1"></i>';
                            } else {
                                $stars .= '<i class="bi bi-star text-warning me-1"></i>';
                            }
                        }
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($feedback['engagement_title'] ?? 'General Feedback'); ?>
                                        <small class="text-muted">#<?php echo $feedback['engagement_id']; ?></small>
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        <?php echo $feedback['service_name'] ?? 'N/A'; ?> • 
                                        <?php echo $feedback['formatted_date']; ?>
                                    </p>
                                </div>
                                <div>
                                    <?php echo $stars; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($feedback['feedback_text'])): ?>
                            <div class="bg-light p-3 rounded mt-2">
                                <p class="mb-0">"<?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>"</p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($feedback['employee_name'])): ?>
                            <div class="mt-2 text-end">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> 
                                    Acknowledged by: <?php echo htmlspecialchars($feedback['employee_name']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($feedback['is_public'] ?? false): ?>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="bi bi-globe"></i> Public Testimonial
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-star display-1 text-muted"></i>
                    <h4 class="mt-3">No Feedback Yet</h4>
                    <p class="text-muted">Your feedback helps us improve our services.</p>
                    <a href="feedback.php?source=submit" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Submit Your First Feedback
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline .card {
    border-left: 4px solid #f1bf70;
    transition: transform 0.2s;
}
.timeline .card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>