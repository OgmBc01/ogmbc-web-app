<?php
include 'includes/database.php';
include 'includes/header-1.php';

// Pagination setup
$posts_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Calculate offset
$offset = ($current_page - 1) * $posts_per_page;

// Get total number of published posts
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE post_status = 'published'";
$count_result = $connection->query($count_sql);
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Ensure current page doesn't exceed total pages
if ($current_page > $total_pages) $current_page = $total_pages;

// Fetch posts with pagination
$sql = "SELECT p.*, u.first_name, u.last_name 
        FROM posts p 
        LEFT JOIN users u ON p.post_author = u.user_id 
        WHERE p.post_status = 'published'
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $posts_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// // Get categories for filter (if you have them)
// $categories = [];
// $cat_sql = "SELECT DISTINCT post_category FROM posts WHERE post_category IS NOT NULL AND post_category != ''";
// $cat_result = $connection->query($cat_sql);
// if ($cat_result) {
//     while ($cat = $cat_result->fetch_assoc()) {
//         if (!empty($cat['post_category'])) {
//             $categories[] = $cat['post_category'];
//         }
//     }
// }
?> 

  <!-- Hero Section -->
  <section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
      <h1 class="display-4 fw-bold">Blog</h1>
      <p class="text-light">Welcome to our blog where we share insights, tips, and news about our industry. Stay tuned for regular updates!</p>
    </div>
  </section>

  <!-- Blog Contents -->
   <section class="py-5" style="background-color: #f8f9fa; color: #0f172a;">
    <div class="container">
      <div class="row g-4 align-items-stretch">

<!-- Main Content -->
            <div class="col-lg-8">
                <!-- Posts Grid -->
                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4">
                                <div class="blog-card">
                                    <!-- Post Image -->
                                    <?php
                                    $image_path = "uploads/posts/" . $post['post_image'];
                                    $image_url = (!empty($post['post_image']) && file_exists($image_path)) 
                                        ? $image_path 
                                        : "https://via.placeholder.com/600x400/f1bf70/0f172a?text=Blog+Post";
                                    ?>
                                    <img src="<?php echo $image_url; ?>" class="blog-image" alt="<?php echo htmlspecialchars($post['post_title']); ?>">
                                    
                                    <div class="p-3">
                                        <!-- Category -->
                                        <?php if (!empty($post['post_category'])): ?>
                                            <div class="blog-category"><?php echo htmlspecialchars($post['post_category']); ?></div>
                                        <?php endif; ?>
                                        
                                        <!-- Title -->
                                        <h3 class="blog-title">
                                            <a href="post_detail.php?slug=<?php echo urlencode($post['post_slug']); ?>">
                                                <?php echo htmlspecialchars($post['post_title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Excerpt -->
                                        <p class="blog-excerpt">
                                            <?php 
                                            $excerpt = !empty($post['post_excerpt']) 
                                                ? $post['post_excerpt'] 
                                                : strip_tags($post['post_content']);
                                            echo htmlspecialchars(substr($excerpt, 0, 120)) . '...'; 
                                            ?>
                                        </p>
                                        
                                        <!-- Meta Information -->
                                        <div class="blog-meta d-flex justify-content-between align-items-center">
                                            
                                            <div>
                                                <i class="bi bi-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-post display-1 text-muted"></i>
                                <h3 class="mt-3">No posts found</h3>
                                <p class="text-muted">Check back later for new content.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Blog pagination">
                    <ul class="pagination justify-content-center mt-5">
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="blog.php?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="blog.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="blog.php?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- About Widget
                <div class="sidebar-widget">
                    <h4 class="widget-title">About Blog</h4>
                    <p>Welcome to our blog where we share insights, tips, and news about our industry. Stay tuned for regular updates!</p>
                </div> -->
                
                <!-- Categories Widget -->
                <?php if (!empty($categories)): ?>
                <div class="sidebar-widget">
                    <h4 class="widget-title">Categories</h4>
                    <ul class="category-list">
                        <?php foreach ($categories as $category): 
                            // Count posts in this category
                            $cat_count_sql = "SELECT COUNT(*) as count FROM posts WHERE post_category = ? AND post_status = 'published'";
                            $cat_count_stmt = $connection->prepare($cat_count_sql);
                            $cat_count_stmt->bind_param("s", $category);
                            $cat_count_stmt->execute();
                            $cat_count_result = $cat_count_stmt->get_result();
                            $cat_count = $cat_count_result->fetch_assoc()['count'];
                            $cat_count_stmt->close();
                        ?>
                            <li>
                                <a href="blog.php?category=<?php echo urlencode($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                    <span class="category-count"><?php echo $cat_count; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Recent Posts Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Recent Posts</h4>
                    <?php
                    $recent_sql = "SELECT post_id, post_title, post_slug, created_at 
                                   FROM posts 
                                   WHERE post_status = 'published' 
                                   ORDER BY created_at DESC 
                                   LIMIT 5";
                    $recent_result = $connection->query($recent_sql);
                    if ($recent_result->num_rows > 0): 
                    ?>
                        <div class="list-group list-group-flush">
                            <?php while ($recent = $recent_result->fetch_assoc()): ?>
                                <a href="post.php?slug=<?php echo urlencode($recent['post_slug']); ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($recent['post_title']); ?></h6>
                                    </div>
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($recent['created_at'])); ?></small>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Subscribe Widget -->
                <div class="sidebar-widget">
                    <h4 class="widget-title">Subscribe</h4>
                    <p>Stay updated with our latest posts by subscribing to our newsletter.</p>
                    <form class="subscribe-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

      </div>
    </div>
  </section>
  
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