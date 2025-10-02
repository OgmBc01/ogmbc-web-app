<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post = null;
$message = '';
$message_type = '';

if ($post_id > 0) {
    $sql = "SELECT * FROM posts WHERE post_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
} else {
    $message = "Invalid post ID.";
    $message_type = "error";
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $confirm_post_id = intval($_POST['post_id']);
    $confirm_text = strtoupper(trim($_POST['confirm_text']));

    if ($confirm_post_id > 0 && $confirm_text === 'DELETE') {
        $sql = "DELETE FROM posts WHERE post_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $confirm_post_id);

        if ($stmt->execute()) {
            $stmt->close();
            echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>";
        } else {
            $message = "Failed to delete post. Error: " . $connection->error;
            $message_type = "error";
            $stmt->close();
        }
    } else {
        $message = "Invalid confirmation. Type DELETE to proceed.";
        $message_type = "error";
    }
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Delete Post</h1>
            <a href="posts.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Posts
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Post Deletion</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if ($post): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Warning:</strong> You are about to delete this post. This action cannot be undone.
                        </div>

                        <div class="post-details mb-4 p-3 border rounded">
                            <h4>Post Details</h4>
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($post['post_title']); ?></p>
                            <p><strong>Author ID:</strong> <?php echo htmlspecialchars($post['post_author']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($post['post_status']); ?></p>
                            <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($post['created_at'])); ?></p>
                            <p><strong>Excerpt:</strong> <?php echo htmlspecialchars($post['post_excerpt']); ?></p>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-1"></i> Delete Post
                            </button>
                            <a href="posts.php" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-question-circle" style="font-size: 3rem;"></i>
                            <h4 class="my-3">Post Not Found</h4>
                            <a href="posts.php" class="btn btn-primary">Back to Posts</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Type DELETE to confirm permanent deletion of this post.</p>
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <input type="text" name="confirm_text" id="confirm_text" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="confirm_delete" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Post Deleted Successfully!</h4>
                <p>The post has been permanently removed.</p>
            </div>
            <div class="modal-footer">
                <a href="posts.php" class="btn btn-secondary">Back to Posts</a>
                <a href="posts.php?source=add_post" class="btn btn-success">Add New Post</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmText = document.getElementById('confirm_text');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmText.addEventListener('input', function() {
        confirmBtn.disabled = this.value.toUpperCase() !== 'DELETE';
    });
});
</script>
