<?php

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post_title = $post_content = $post_excerpt = $meta_title = $meta_description = $meta_keywords = '';
$post_status = 'published';
$post_author = '';
$current_image = '';
$message = '';
$message_type = '';

// Get all users for the author dropdown
$users_sql = "SELECT user_id, first_name, last_name FROM users ORDER BY first_name, last_name";
$users_result = $connection->query($users_sql);

// Fetch post data if editing existing post
if ($post_id > 0) {
    $sql = "SELECT * FROM posts WHERE post_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $post = $result->fetch_assoc();
        $post_title = $post['post_title'];
        $post_content = $post['post_content'];
        $post_excerpt = $post['post_excerpt'];
        $post_status = $post['post_status'];
        $post_author = $post['post_author'];
        $current_image = $post['post_image'];
        $meta_title = $post['meta_title'];
        $meta_description = $post['meta_description'];
        $meta_keywords = $post['meta_keywords'];
    } else {
        $message = "Post not found.";
        $message_type = "error";
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = intval($_POST['post_id']);
    $post_title = trim($_POST['post_title']);
    $post_content = trim($_POST['post_content']);  // Don't sanitize - store raw HTML from TinyMCE
    $post_excerpt = trim($_POST['post_excerpt'] ?? '');
    $post_status = trim($_POST['post_status']);
    $post_author = trim($_POST['post_author']);
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');

    // Handle file upload
    $post_image = $current_image;
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['post_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/posts/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "post_" . time() . "_" . rand(1000, 9999) . ".{$ext}";
            $target = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // Delete old image if it exists
                if (!empty($current_image) && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
                $post_image = $new_filename;
            }
        }
    }

    // Validate required fields
    if (empty($post_title) || empty($post_content) || empty($post_author)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        // Update database
        $sql = "UPDATE posts SET 
                post_title = ?, 
                post_content = ?, 
                post_excerpt = ?, 
                post_status = ?, 
                post_author = ?, 
                post_image = ?, 
                meta_title = ?, 
                meta_description = ?, 
                meta_keywords = ?,
                updated_at = NOW()
                WHERE post_id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssssissssi", $post_title, $post_content, $post_excerpt, 
                         $post_status, $post_author, $post_image, $meta_title, 
                         $meta_description, $meta_keywords, $post_id);

        if ($stmt->execute()) {
            $stmt->close();
            
            // Show success modal
            echo "
            <script>
                window.addEventListener('load', function() {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>
            ";
        } else {
            $message = "Failed to update post. Error: " . $connection->error;
            $message_type = "error";
            $stmt->close();
        }
    }
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4"><?php echo $post_id > 0 ? 'Edit Post' : 'Add New Post'; ?></h2>
            <a href="blog.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Posts
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Post Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="post_title" class="form-label">Post Title *</label>
                                        <input type="text" id="post_title" name="post_title" class="form-control" 
                                               value="<?php echo htmlspecialchars($post_title); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_content" class="form-label">Content *</label>
                                        <textarea id="post_content" name="post_content" class="form-control" rows="12" required>
                                        <?php echo $post_content; ?>
                                        </textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_excerpt" class="form-label">Excerpt</label>
                                        <textarea id="post_excerpt" name="post_excerpt" class="form-control" 
                                                  rows="3"><?php echo htmlspecialchars($post_excerpt); ?></textarea>
                                        <div class="form-text">Short summary of your post (optional).</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="post_author" class="form-label">Author *</label>
                                        <select id="post_author" name="post_author" class="form-control" required>
                                            <option value="">Select Author</option>
                                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                                <option value="<?php echo $user['user_id']; ?>" 
                                                    <?php echo ($post_author == $user['user_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_status" class="form-label">Status</label>
                                        <select id="post_status" name="post_status" class="form-control">
                                            <option value="draft" <?php echo ($post_status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo ($post_status == 'published') ? 'selected' : ''; ?>>Published</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_image" class="form-label">Featured Image</label>
                                        <input type="file" id="post_image" name="post_image" class="form-control" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        <?php if (!empty($current_image)): ?>
                                        <div class="mt-2">
                                            <small>Current Image: </small>
                                            <?php
                                            $image_url = "";
                                            if (!empty($current_image) && file_exists("../uploads/posts/" . $current_image)) {
                                                $image_url = "../uploads/posts/" . $current_image;
                                            } else {
                                                $image_url = "https://via.placeholder.com/150/f1bf70/0f172a?text=Post";
                                            }
                                            ?>
                                            <img src="<?php echo $image_url; ?>" 
                                                 alt="Current Post Image" 
                                                 class="rounded ms-2" width="60" height="40"
                                                 onerror="this.src='https://via.placeholder.com/60x40/f1bf70/0f172a?text=Post'">
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-light">SEO Settings</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="meta_title" class="form-label">Meta Title</label>
                                                <input type="text" id="meta_title" name="meta_title" class="form-control" 
                                                       value="<?php echo htmlspecialchars($meta_title); ?>">
                                                <div class="form-text">Title for search engines (optional).</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <textarea id="meta_description" name="meta_description" class="form-control" 
                                                          rows="2"><?php echo htmlspecialchars($meta_description); ?></textarea>
                                                <div class="form-text">Description for search engines (optional).</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" 
                                                       value="<?php echo htmlspecialchars($meta_keywords); ?>">
                                                <div class="form-text">Comma-separated keywords (optional).</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> <?php echo $post_id > 0 ? 'Update Post' : 'Create Post'; ?>
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <h4 class="my-3">Post Updated Successfully!</h4>
                <p>The post has been updated in the database.</p>
            </div>
            <div class="modal-footer">
                <a href="posts.php" class="btn btn-secondary">View All Posts</a>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        border-radius: 12px 12px 0 0 !important;
    }
    .btn-primary {
        background: #f1bf70;
        border-color: #f1bf70;
        color: #0f172a;
        font-weight: 600;
    }
    .btn-primary:hover {
        background: #e5b465;
        border-color: #e5b465;
        color: #0f172a;
    }
</style>