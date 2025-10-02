<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all posts
$sql = "SELECT p.*, u.first_name, u.last_name 
        FROM posts p 
        LEFT JOIN users u ON p.post_author = u.user_id 
        ORDER BY p.created_at DESC";
$result = $connection->query($sql);

if (!$result) {
    die("Query failed: " . $connection->error);
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Blog Management</h1>
            <a href="posts.php?source=add_post" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Post
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-post me-2"></i>All Posts</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="postsTable">
                        <thead>
                            <tr class="table-dark">
                                <th>#</th>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $serial = 1; while ($post = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $serial++; ?></td>
                                        <td class="text-muted"><?php echo $post['post_id']; ?></td>
                                        <td>
                                            <?php
                                            $image_url = (!empty($post['post_image']) && file_exists("../uploads/posts/" . $post['post_image']))
                                                ? "../uploads/posts/" . $post['post_image']
                                                : "https://via.placeholder.com/60x40/f1bf70/0f172a?text=Post";
                                            ?>
                                            <img src="<?php echo $image_url; ?>" class="rounded" width="60" height="40">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($post['post_title']); ?></strong>
                                            <?php if (!empty($post['post_excerpt'])): ?>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($post['post_excerpt'], 0, 100)); ?>...
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($post['post_status'] == 'published') ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($post['post_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="../post_detail.php?slug=<?php echo urlencode($post['post_slug']); ?>" 
                                                   class="btn btn-sm btn-info flex-fill" target="_blank" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="posts.php?source=edit_post&id=<?php echo $post['post_id']; ?>" 
                                                   class="btn btn-sm btn-warning flex-fill" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="posts.php?source=delete_post&id=<?php echo $post['post_id']; ?>" 
                                                   class="btn btn-sm btn-danger flex-fill" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-file-earmark-post display-4 d-block mb-2"></i>
                                            <h5>No posts found</h5>
                                            <p>Get started by creating your first blog post.</p>
                                            <a href="posts.php?source=add_post" class="btn btn-primary mt-2">
                                                <i class="bi bi-plus-circle"></i> Create Post
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    font-weight: 600;
    background-color: #2c3e50 !important;
    color: white;
}
.table-dark {
    --bs-table-bg: #2c3e50;
    --bs-table-color: white;
}
#postsTable tbody tr:hover {
    background-color: rgba(241, 191, 112, 0.1);
}
.d-flex.gap-1 { gap: 0.25rem !important; }
.flex-fill { flex: 1 1 auto; }
</style>
