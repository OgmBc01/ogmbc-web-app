<?php
ob_start();

$client_id = $_SESSION['client_id'];
$engagement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize variables
$rating = 5;
$feedback_text = '';
$is_public = 1;
$message = '';
$message_type = '';
$showSuccessModal = false;

// Get engagement details if ID provided
if ($engagement_id > 0) {
    $check_query = "SELECT e.*, s.service_name 
                    FROM engagements e
                    JOIN service_types s ON e.service_id = s.service_id
                    WHERE e.engagement_id = $engagement_id AND e.client_id = $client_id";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $message = "Engagement not found.";
        $message_type = "danger";
    } else {
        $engagement = mysqli_fetch_assoc($check_result);
    }
}

// Get completed engagements for dropdown
$engagements_query = "SELECT e.engagement_id, e.title, s.service_name
                      FROM engagements e
                      JOIN service_types s ON e.service_id = s.service_id
                      WHERE e.client_id = $client_id AND e.status = 'CLOSED'
                      ORDER BY e.updated_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    
    $engagement_id = (int)$_POST['engagement_id'];
    $rating = (int)$_POST['rating'];
    $feedback_text = mysqli_real_escape_string($connection, trim($_POST['feedback_text']));
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    // Validation
    if (empty($engagement_id) || empty($feedback_text)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Check if feedback already exists for this engagement
        $check_query = "SELECT feedback_id FROM client_feedback WHERE engagement_id = $engagement_id AND client_id = $client_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "You have already submitted feedback for this engagement.";
            $message_type = "danger";
        } else {
            // Insert feedback
            $insert_query = "INSERT INTO client_feedback 
                            (client_id, engagement_id, employee_id, feedback_text, rating, is_public, is_positive, created_by)
                            VALUES 
                            ($client_id, $engagement_id, 
                             (SELECT assigned_to FROM engagements WHERE engagement_id = $engagement_id),
                             '$feedback_text', $rating, $is_public, 1, $client_id)";
            
            if (mysqli_query($connection, $insert_query)) {
                $showSuccessModal = true;
            } else {
                $message = "Error submitting feedback: " . mysqli_error($connection);
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
                    <h5 class="mb-0"><i class="bi bi-star me-2"></i>Submit Feedback</h5>
                    <a href="feedback.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="feedbackForm">
                        <div class="mb-3">
                            <label for="engagement_id" class="form-label">Select Engagement *</label>
                            <select id="engagement_id" name="engagement_id" class="form-control" required>
                                <option value="">Choose an engagement</option>
                                <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                    <option value="<?php echo $eng['engagement_id']; ?>" <?php echo ($engagement_id == $eng['engagement_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($eng['title']); ?> (<?php echo $eng['service_name']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rating *</label>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rating ? '-fill' : ''; ?> text-warning fs-3 me-1" 
                                       style="cursor:pointer;" onclick="setRating(<?php echo $i; ?>)"></i>
                                <?php endfor; ?>
                                <input type="hidden" id="rating" name="rating" value="<?php echo $rating; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="feedback_text" class="form-label">Your Feedback *</label>
                            <textarea id="feedback_text" name="feedback_text" class="form-control" rows="5" 
                                      placeholder="Share your experience with our service..." required><?php echo htmlspecialchars($feedback_text); ?></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" <?php echo $is_public ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_public">
                                I agree this feedback can be displayed publicly as a testimonial
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submit_feedback" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Submit Feedback
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
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Thank You!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-star-fill text-warning" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Feedback Submitted Successfully!</h5>
                <p class="text-muted">We appreciate your feedback and will use it to improve our services.</p>
                <?php if ($is_public): ?>
                    <p class="text-success"><i class="bi bi-check-circle"></i> Your feedback may be featured as a testimonial.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="feedback.php" class="btn btn-success px-4">View My Feedback</a>
                <a href="dashboard.php" class="btn btn-outline-success px-4">Go to Dashboard</a>
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

<script>
function setRating(rating) {
    document.getElementById('rating').value = rating;
    const stars = document.querySelectorAll('.rating-stars i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('bi-star');
            star.classList.add('bi-star-fill');
        } else {
            star.classList.remove('bi-star-fill');
            star.classList.add('bi-star');
        }
    });
}
</script>