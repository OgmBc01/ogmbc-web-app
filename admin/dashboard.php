<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// admin/dashboard.php

// Optional: Check if user has admin role
// Uncomment if you want to restrict access to specific roles

$admin_roles = ['admin', 'super_admin', 'moderator'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $admin_roles)) {
    // Not authorized - redirect to home page
    header("Location: ../index.php?error=access_denied");
    exit();
}
?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4">Dashboard Overview</h2>
                    <p class="text-muted">Welcome back, Admin! Here's what's happening today.</p>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="row mb-4">
                <?php

                // Count total posts
                $post_sql = "SELECT COUNT(*) as total_posts FROM posts";
                $post_result = $connection->query($post_sql);
                $post_count = $post_result->fetch_assoc()['total_posts'];

                // Count total users
                $user_sql = "SELECT COUNT(*) as total_users FROM users";
                $user_result = $connection->query($user_sql);
                $user_count = $user_result->fetch_assoc()['total_users'];
                ?>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="bi bi-file-post" style="color: #f1bf70; font-size: 2rem;"></i>
                            </div>
                            <div class="stat-number"><?php echo $post_count; ?></div>
                            <div class="stat-title">Total Posts</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="bi bi-people" style="color: #f1bf70; font-size: 2rem;"></i>
                            </div>
                            <div class="stat-number"><?php echo $user_count; ?></div>
                            <div class="stat-title">Total Users</div>
                        </div>
                    </div>
                </div>

                <?php
                // Close result sets
                if ($post_result) {
                    $post_result->close();
                }
                if ($user_result) {
                    $user_result->close();
                }
                ?>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-number">956</div>
                            <div class="stat-title">Page Views</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-number">83%</div>
                            <div class="stat-title">Engagement Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            Recent Activity
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-plus-circle-fill text-success me-2"></i>
                                        New post created
                                    </div>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-person-plus-fill text-primary me-2"></i>
                                        New user registered
                                    </div>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-pencil-fill text-warning me-2"></i>
                                        Post updated
                                    </div>
                                    <small class="text-muted">Yesterday</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-trash-fill text-danger me-2"></i>
                                        User deleted
                                    </div>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a class="btn btn-primary mb-2" href="posts.php?source=add_post">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Post
                                </a>
                                <a class="btn btn-success mb-2" href="employees.php?source=add_employee">
                                    <i class="bi bi-person-plus me-2"></i> Add New Employee
                                </a>
                                <button class="btn btn-warning mb-2">
                                    <i class="bi bi-gear me-2"></i> Settings
                                </button>
                                <button class="btn btn-info">
                                    <i class="bi bi-graph-up me-2"></i> View Reports
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include 'includes/footer.php'
?>