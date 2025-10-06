<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

<?php

// Get the post slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Fetch post details from database
$post = null;
if (!empty($slug)) {
    $sql = "SELECT p.*, u.first_name, u.last_name 
            FROM posts p 
            LEFT JOIN users u ON p.post_author = u.user_id 
            WHERE p.post_slug = ? AND p.post_status = 'published'";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
    $stmt->close();
}

// If post not found, show 404
if (!$post) {

} else {
    $page_title = htmlspecialchars($post['post_title']);
    $meta_description = htmlspecialchars($post['meta_description'] ?? $post['post_excerpt'] ?? '');
    $meta_keywords = htmlspecialchars($post['meta_keywords'] ?? '');
}

?>
</head>

<!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
          <h1 class="post-title"><?php echo htmlspecialchars($post['post_title']); ?></h1>
          <div class="post-meta">
              <span><i class="bi bi-person"></i> By <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
              <span class="mx-2">•</span>
              <span><i class="bi bi-calendar"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
              <span class="mx-2">•</span>
              <?php if (!empty($post['post_read_time'])): ?>
                  <span class="mx-2">•</span>
                  <span><i class="bi bi-clock"></i> <?php echo $post['post_read_time']; ?> min read</span>
              <?php endif; ?>
          </div>
        </div>
    </div>
  </div>
</section>

    <?php if (!$post): ?>
        <!-- 404 Error Message -->
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                    <h1 class="mt-3">Post Not Found</h1>
                    <p class="lead">The post you're looking for doesn't exist or has been removed.</p>
                    <a href="../index.php" class="btn btn-primary mt-3">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Post Header -->
      <section class="py-5" style="background-color: #f8f9fa; color: #0f172a;">
        <!-- Post Content -->
        <div class="container">
            <div class="row justify-content-center">
                <main class="col-lg-8">
                    <!-- Featured Image -->
                    <?php if (!empty($post['post_image'])): ?>
                        <?php
                        $image_path = "uploads/posts/" . $post['post_image'];
                        if (file_exists($image_path)) {
                            echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($post['post_title']) . '" class="post-image">';
                        }
                        ?>
                    <?php endif; ?>

                    <!-- Post Content -->
                    <article class="post-content">
                        <?php echo $post['post_content']; ?>
                    </article>

                    <!-- Social Sharing -->
                    <div class="social-share">
                        <h5>Share this post:</h5>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" 
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-facebook"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&text=<?php echo urlencode($post['post_title']); ?>" 
                           target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-twitter"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&title=<?php echo urlencode($post['post_title']); ?>" 
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-linkedin"></i> LinkedIn
                        </a>
                    </div>

                    <!-- Comments Section (Optional) -->
                    <div class="comments-section mt-5">
                        <h4>Comments</h4>
                        <p class="text-muted">Comments are disabled for this post.</p>
                        <!-- You can integrate a comments system like Disqus here -->
                    </div>
                </main>
            </div>

            <!-- Related Posts -->
            <?php
            // Fetch related posts
            $related_sql = "SELECT post_id, post_title, post_slug, post_excerpt, post_image, created_at
                            FROM posts
                            WHERE post_status = 'published' AND post_id != ?
                            ORDER BY created_at DESC
                            LIMIT 3";
            $related_stmt = $connection->prepare($related_sql);
            $related_stmt->bind_param("i", $post['post_id']);
            $related_stmt->execute();
            $related_result = $related_stmt->get_result();
            
            if ($related_result->num_rows > 0): ?>
            <div class="row related-posts">
                <div class="col-12">
                    <h3 class="mb-4">Related Posts</h3>
                </div>
                <?php while ($related_post = $related_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card related-post-card h-100">
                        <?php
                        $related_image_path = "uploads/posts/" . $related_post['post_image'];
                        $related_image_url = file_exists($related_image_path) ? $related_image_path : "https://via.placeholder.com/300x200/f1bf70/0f172a?text=Blog";
                        ?>
                        <img src="<?php echo $related_image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related_post['post_title']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($related_post['post_title']); ?></h5>
                            <p class="card-text text-muted"><?php echo date('F j, Y', strtotime($related_post['created_at'])); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars(substr($related_post['post_excerpt'], 0, 100)); ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="post_detail.php?slug=<?php echo urlencode($related_post['post_slug']); ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; 
            $related_stmt->close();
            ?>
        </div>
        <?php endif; ?>

      </section>
</body>
</html>

<!-- Floating Action Buttons -->
<div class="floating-buttons">
    <!-- WhatsApp Button -->
    <a href="https://wa.me/+971502923136" class="floating-btn whatsapp-btn" target="_blank" rel="noopener">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <!-- Back to Top Button -->
    <a href="#" class="floating-btn back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>
</div>

  <!-- Footer (same as home page) -->
<?php
include 'includes/footer.php'
?>