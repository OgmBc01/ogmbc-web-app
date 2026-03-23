<?php

// Handle category actions
$action = isset($_GET['cat_action']) ? $_GET['cat_action'] : 'list';
$category_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = mysqli_real_escape_string($connection, $_POST['category_name']);
        $category_description = mysqli_real_escape_string($connection, $_POST['category_description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if category already exists
        $check_query = "SELECT category_id FROM document_categories WHERE category_name = '$category_name'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Category already exists!";
        } else {
            $query = "INSERT INTO document_categories (category_name, category_description, is_active) 
                      VALUES ('$category_name', '$category_description', $is_active)";
            
            if (mysqli_query($connection, $query)) {
                $message = "Category added successfully!";
                $action = 'list';
            } else {
                $error = "Database error: " . mysqli_error($connection);
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        $category_name = mysqli_real_escape_string($connection, $_POST['category_name']);
        $category_description = mysqli_real_escape_string($connection, $_POST['category_description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if category name already exists for another category
        $check_query = "SELECT category_id FROM document_categories 
                        WHERE category_name = '$category_name' 
                        AND category_id != $category_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Category name already exists!";
        } else {
            $query = "UPDATE document_categories SET 
                      category_name = '$category_name',
                      category_description = '$category_description',
                      is_active = $is_active
                      WHERE category_id = $category_id";
            
            if (mysqli_query($connection, $query)) {
                $message = "Category updated successfully!";
                $action = 'list';
            } else {
                $error = "Database error: " . mysqli_error($connection);
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $category_id = intval($_POST['category_id']);
        
        // Check if category has documents
        $check_query = "SELECT COUNT(*) as doc_count FROM document_category_mapping 
                        WHERE category_id = $category_id";
        $check_result = mysqli_query($connection, $check_query);
        $check = mysqli_fetch_assoc($check_result);
        
        if ($check['doc_count'] > 0) {
            $error = "Cannot delete category! It is associated with {$check['doc_count']} document(s).";
        } else {
            $query = "DELETE FROM document_categories WHERE category_id = $category_id";
            if (mysqli_query($connection, $query)) {
                $message = "Category deleted successfully!";
            } else {
                $error = "Database error: " . mysqli_error($connection);
            }
        }
    }
}

// Get all categories with statistics
$categories_query = "SELECT c.*,
                     COUNT(DISTINCT m.document_id) as document_count,
                     SUM(d.view_count) as total_views,
                     SUM(d.download_count) as total_downloads
                     FROM document_categories c
                     LEFT JOIN document_category_mapping m ON c.category_id = m.category_id
                     LEFT JOIN client_documents d ON m.document_id = d.document_id
                     GROUP BY c.category_id
                     ORDER BY c.category_name ASC";
$categories_result = mysqli_query($connection, $categories_query);

// Get category for editing
$edit_category = null;
if ($action == 'edit' && $category_id > 0) {
    $edit_query = "SELECT * FROM document_categories WHERE category_id = $category_id";
    $edit_result = mysqli_query($connection, $edit_query);
    $edit_category = mysqli_fetch_assoc($edit_result);
    
    if (!$edit_category) {
        $action = 'list';
        $error = "Category not found!";
    }
}

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_categories,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_categories,
                COUNT(DISTINCT m.document_id) as documents_with_categories,
                (SELECT COUNT(*) FROM client_documents) as total_documents
                FROM document_categories c
                LEFT JOIN document_category_mapping m ON c.category_id = m.category_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!-- Categories Management Interface -->
<div class="categories-management">
    
    <!-- Header with Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-2">
                                <i class="bi bi-tags me-2 text-primary"></i>
                                Document Categories
                            </h5>
                            <p class="text-muted mb-0">
                                Organize documents with categories for better management and search
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <?php if ($action == 'list'): ?>
                                <button class="btn btn-primary" onclick="window.location.href='?action=categories&cat_action=add'">
                                    <i class="bi bi-plus-circle me-2"></i> New Category
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary" onclick="window.location.href='?action=categories'">
                                    <i class="bi bi-arrow-left me-2"></i> Back to Categories
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-primary-soft">
                        <i class="bi bi-tags text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo number_format($stats['total_categories']); ?></h3>
                        <p class="stat-label">Total Categories</p>
                        <small class="text-success"><?php echo $stats['active_categories']; ?> active</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-file-earmark-text text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo number_format($stats['documents_with_categories']); ?></h3>
                        <p class="stat-label">Categorized Documents</p>
                        <small class="text-muted">of <?php echo number_format($stats['total_documents']); ?> total</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-info-soft">
                        <i class="bi bi-bar-chart text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo number_format($stats['total_categories'] > 0 ? round($stats['documents_with_categories'] / $stats['total_categories'], 1) : 0, 1); ?></h3>
                        <p class="stat-label">Avg Docs per Category</p>
                        <small class="text-muted">Average distribution</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-graph-up text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $stats['total_categories'] > 0 ? round(($stats['documents_with_categories'] / max($stats['total_documents'], 1)) * 100) : 0; ?>%</h3>
                        <p class="stat-label">Categorization Rate</p>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $stats['total_categories'] > 0 ? round(($stats['documents_with_categories'] / max($stats['total_documents'], 1)) * 100) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Add/Edit Category Form -->
    <?php if ($action == 'add' || $action == 'edit'): ?>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-<?php echo $action == 'add' ? 'plus-circle' : 'pencil-square'; ?> me-2 text-primary"></i>
                            <?php echo $action == 'add' ? 'Create New Category' : 'Edit Category'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Category Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-tag"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-lg" name="category_name" 
                                           value="<?php echo $action == 'edit' ? htmlspecialchars($edit_category['category_name']) : ''; ?>" 
                                           placeholder="Enter category name" required>
                                </div>
                                <small class="text-muted">Example: Contracts, Invoices, Reports, etc.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Description</label>
                                <textarea class="form-control" name="category_description" rows="4" 
                                          placeholder="Describe what this category is for..."><?php echo $action == 'edit' ? htmlspecialchars($edit_category['category_description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" 
                                           id="isActive" <?php echo ($action == 'edit' && $edit_category['is_active']) || $action == 'add' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="isActive">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        Active Category
                                    </label>
                                </div>
                                <small class="text-muted">Inactive categories won't appear in document upload forms</small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" name="<?php echo $action == 'add' ? 'add_category' : 'edit_category'; ?>" 
                                        class="btn btn-primary btn-lg">
                                    <i class="bi bi-<?php echo $action == 'add' ? 'save' : 'check-lg'; ?> me-2"></i>
                                    <?php echo $action == 'add' ? 'Create Category' : 'Save Changes'; ?>
                                </button>
                                <a href="?action=categories" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle me-2"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- Categories List -->
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>
                            All Categories
                        </h5>
                        <div style="min-width:250px;">
                            <input type="text" id="searchCategory" class="form-control form-control-sm" 
                                   placeholder="Search categories...">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($categories_result) > 0): ?>
                            <div class="categories-grid p-3">
                                <div class="row">
                                    <?php while ($category = mysqli_fetch_assoc($categories_result)): 
                                        $color_variants = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                                        $color_index = $category['category_id'] % count($color_variants);
                                        $color = $color_variants[$color_index];
                                    ?>
                                        <div class="col-lg-4 col-md-6 mb-3 category-item" 
                                             data-name="<?php echo strtolower($category['category_name']); ?>">
                                            <div class="category-card card h-100 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="category-icon bg-<?php echo $color; ?>-soft rounded-circle p-3">
                                                            <i class="bi bi-tag-fill text-<?php echo $color; ?> fs-4"></i>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item" href="?action=categories&cat_action=edit&cat_id=<?php echo $category['category_id']; ?>">
                                                                        <i class="bi bi-pencil me-2"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="deleteCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>')">
                                                                        <i class="bi bi-trash me-2"></i> Delete
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    
                                                    <h6 class="category-name mb-2 fw-bold">
                                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                                        <?php if (!$category['is_active']): ?>
                                                            <span class="badge bg-secondary ms-2">Inactive</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    
                                                    <p class="category-description text-muted small mb-3">
                                                        <?php echo htmlspecialchars(substr($category['category_description'] ?? 'No description', 0, 100)); ?>
                                                        <?php if (strlen($category['category_description'] ?? '') > 100): ?>...<?php endif; ?>
                                                    </p>
                                                    
                                                    <div class="category-stats mt-2">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <div class="stat-item">
                                                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                                                    <div class="fw-bold"><?php echo number_format($category['document_count']); ?></div>
                                                                    <small class="text-muted">Documents</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="stat-item">
                                                                    <i class="bi bi-eye text-info"></i>
                                                                    <div class="fw-bold"><?php echo number_format($category['total_views'] ?? 0); ?></div>
                                                                    <small class="text-muted">Views</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="stat-item">
                                                                    <i class="bi bi-download text-success"></i>
                                                                    <div class="fw-bold"><?php echo number_format($category['total_downloads'] ?? 0); ?></div>
                                                                    <small class="text-muted">Downloads</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($category['document_count'] > 0): ?>
                                                        <div class="mt-3">
                                                            <div class="progress" style="height: 4px;">
                                                                <?php 
                                                                $percentage = $stats['total_documents'] > 0 ? 
                                                                    round(($category['document_count'] / $stats['total_documents']) * 100) : 0;
                                                                ?>
                                                                <div class="progress-bar bg-<?php echo $color; ?>" 
                                                                     style="width: <?php echo $percentage; ?>%"></div>
                                                            </div>
                                                            <small class="text-muted"><?php echo $percentage; ?>% of total documents</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-tags display-1 text-muted"></i>
                                <h5 class="text-muted mt-3">No categories found</h5>
                                <p class="text-muted">Create your first category to organize documents</p>
                                <a href="?action=categories&cat_action=add" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-2"></i> Create Category
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Category Tips -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="pro-tip-card">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h6 class="text-white mb-2">
                                <i class="bi bi-lightbulb me-2"></i>
                                Category Management Tips
                            </h6>
                            <p class="text-white-50 small mb-md-0">
                                • Use clear, descriptive category names for easy document organization<br>
                                • Categories help clients find relevant documents faster<br>
                                • You can assign multiple categories to a single document<br>
                                • Inactive categories won't appear in upload forms but existing documents retain them
                            </p>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <i class="bi bi-tags display-4 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete category "<strong id="deleteCategoryName"></strong>"?</p>
                <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="category_id" id="deleteCategoryId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_category" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.categories-grid {
    max-height: 600px;
    overflow-y: auto;
}

.category-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

.category-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }

.text-primary-soft { color: #667eea; }
.text-success-soft { color: #28a745; }
.text-info-soft { color: #17a2b8; }
.text-warning-soft { color: #ffc107; }
.text-danger-soft { color: #dc3545; }

.category-name {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.category-description {
    font-size: 0.85rem;
    line-height: 1.4;
    min-height: 60px;
}

.category-stats .stat-item {
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.category-stats .stat-item:hover {
    background: #f8f9fa;
}

.category-stats i {
    font-size: 1.2rem;
    margin-bottom: 4px;
    display: inline-block;
}

.category-stats .fw-bold {
    font-size: 1.1rem;
    margin: 4px 0;
}

@media (max-width: 768px) {
    .category-icon {
        width: 40px;
        height: 40px;
    }
    
    .category-icon i {
        font-size: 1.2rem;
    }
    
    .category-stats .fw-bold {
        font-size: 0.9rem;
    }
    
    .category-stats small {
        font-size: 0.7rem;
    }
}

/* Animation for new categories */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.category-item {
    animation: slideIn 0.3s ease-out;
}
</style>

<script>
// Search functionality
document.getElementById('searchCategory')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const categories = document.querySelectorAll('.category-item');
    
    categories.forEach(category => {
        const name = category.getAttribute('data-name') || '';
        if (name.includes(searchTerm)) {
            category.style.display = '';
        } else {
            category.style.display = 'none';
        }
    });
});

// Delete category function
function deleteCategory(id, name) {
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    modal.show();
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>