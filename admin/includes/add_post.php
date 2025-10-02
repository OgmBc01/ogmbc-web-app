<?php

// Check if user is logged in and has permission to create posts
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize all variables with empty values
$post_title = $post_content = $post_excerpt = $meta_title = $meta_description = $meta_keywords = '';
$post_status = 'published'; // Set default value
$message = '';
$message_type = '';

// Function to generate slug from title
function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_title = trim($_POST['post_title']);
    $post_content = trim($_POST['post_content']);
    $post_excerpt = trim($_POST['post_excerpt'] ?? '');
    $post_status = trim($_POST['post_status']);
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $post_author = $_SESSION['user_id']; // Get author from session
    
    // Generate slug from title
    $post_slug = generateSlug($post_title);
    
    // Handle image upload
    $post_image = '';
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
                $post_image = $new_filename;
            }
        }
    }
    
    // Validate required fields
    if (empty($post_title) || empty($post_content)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        // Insert into database - Fixed the query to remove duplicate post_author
        $sql = "INSERT INTO posts (post_title, post_slug, post_content, post_author, post_excerpt, 
                post_status, post_image, meta_title, meta_description, meta_keywords) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssssssssss", $post_title, $post_slug, $post_content, $post_author, $post_excerpt, 
                        $post_status, $post_image, $meta_title, $meta_description, $meta_keywords);
        
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
            $message = "Failed to create post. Error: " . $connection->error;
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
            <h2 class="h4">Create New Post</h2>
            <a href="./posts.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> View Aall Posts
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>New Post</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="post_title" class="form-label">Post Title *</label>
                                        <input type="text" id="post_title" name="post_title" class="form-control" 
                                               value="<?php echo htmlspecialchars($post_title); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_content" class="form-label">Content *</label>
                                        <textarea id="post_content" name="post_content" class="form-control" 
                                                  rows="12" required><?php echo htmlspecialchars($post_content); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="post_excerpt" class="form-label">Excerpt</label>
                                        <textarea id="post_excerpt" name="post_excerpt" class="form-control" 
                                                  rows="3"><?php echo htmlspecialchars($post_excerpt); ?></textarea>
                                        <div class="form-text">Short summary of the post (optional).</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
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
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-light">SEO Settings</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="meta_title" class="form-label">Meta Title</label>
                                                <input type="text" id="meta_title" name="meta_title" class="form-control" 
                                                       value="<?php echo htmlspecialchars($meta_title); ?>">
                                                <div class="form-text">Title for search engines (optional but important).</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_description" class="form-label">Meta Description</label>
                                                <textarea id="meta_description" name="meta_description" class="form-control" 
                                                          rows="2"><?php echo htmlspecialchars($meta_description); ?></textarea>
                                                <div class="form-text">Description for search engines (optional but important).</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" 
                                                       value="<?php echo htmlspecialchars($meta_keywords); ?>">
                                                <div class="form-text">Comma-separated keywords (optional but important).</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Publish Post
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
                <h4 class="my-3">Post Created Successfully!</h4>
                <p>Your post has been published and is now live on your blog.</p>
            </div>
            <div class="modal-footer">
                <a href="./posts.php" class="btn btn-secondary">View Blog</a>
                <a href="posts.php?source=add_post" class="btn btn-success">Create Another Post</a>
            </div>
        </div>
    </div>
</div>

