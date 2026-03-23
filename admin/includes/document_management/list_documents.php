<?php
// Get filter parameters
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query with filters
$where_conditions = ["d.is_active = 1"];
if ($filter_type != 'all') {
    $where_conditions[] = "d.document_type = '$filter_type'";
}
if ($search) {
    $where_conditions[] = "(d.document_title LIKE '%$search%' OR d.document_description LIKE '%$search%')";
}
if ($category_filter > 0) {
    $where_conditions[] = "EXISTS (SELECT 1 FROM document_category_mapping m WHERE m.document_id = d.document_id AND m.category_id = $category_filter)";
}

$where_clause = implode(" AND ", $where_conditions);

// Get documents
$documents_query = "SELECT d.*, u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM document_access_logs WHERE document_id = d.document_id) as total_views,
                    (SELECT COUNT(*) FROM document_client_access WHERE document_id = d.document_id) as client_count,
                    GROUP_CONCAT(DISTINCT c.category_name) as categories
                    FROM client_documents d
                    LEFT JOIN users u ON d.uploaded_by = u.user_id
                    LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
                    LEFT JOIN document_categories c ON m.category_id = c.category_id
                    WHERE $where_clause
                    GROUP BY d.document_id
                    ORDER BY d.created_at DESC";
$documents_result = mysqli_query($connection, $documents_query);

// Get categories for filter
$categories_query = "SELECT * FROM document_categories WHERE is_active = 1 ORDER BY category_name";
$categories_result = mysqli_query($connection, $categories_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN document_type = 'general' THEN 1 ELSE 0 END) as `general`,
    SUM(CASE WHEN document_type = 'specific' THEN 1 ELSE 0 END) as `specific`
    FROM client_documents WHERE is_active = 1";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card" onclick="window.location.href='?filter_type=all'">
            <div class="stat-card-body">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-files text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['total']); ?></h3>
                    <p class="stat-label">Total Documents</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" onclick="window.location.href='?filter_type=general'">
            <div class="stat-card-body">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-globe text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['general'] ?? 0); ?></h3>
                    <p class="stat-label">General Documents</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" onclick="window.location.href='?filter_type=specific'">
            <div class="stat-card-body">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-lock text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['specific'] ?? 0); ?></h3>
                    <p class="stat-label">Specific Documents</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" onclick="$('#filterModal').modal('show')">
            <div class="stat-card-body">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-funnel text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">
                        <?php 
                        if ($category_filter > 0) {
                            $cat_query = "SELECT category_name FROM document_categories WHERE category_id = $category_filter";
                            $cat_result = mysqli_query($connection, $cat_query);
                            $cat = mysqli_fetch_assoc($cat_result);
                            echo htmlspecialchars($cat['category_name']);
                        } else {
                            echo "All";
                        }
                        ?>
                    </h3>
                    <p class="stat-label">Category Filter</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="action" value="list">
            <input type="hidden" name="filter_type" value="<?php echo $filter_type; ?>">
            <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
            <input type="text" name="search" class="form-control" placeholder="Search by title or description..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
            </button>
            <?php if ($search || $category_filter): ?>
                <a href="?action=list" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="window.location.href='?action=reports'">
                <i class="bi bi-bar-chart"></i> Reports
            </button>
            <button class="btn btn-outline-info" onclick="window.location.href='?action=categories'">
                <i class="bi bi-tags"></i> Categories
            </button>
        </div>
    </div>
</div>

<!-- Documents Table -->
<div class="row">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2 text-primary"></i>
                    Document List
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-success me-2" onclick="exportDocuments()">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="documentsTable">
                        <thead class="table-light">
                             <tr>
                                <th width="50">#</th>
                                <th>Document Details</th>
                                <th width="100">Type</th>
                                <th width="150">Categories</th>
                                <th width="80">Size</th>
                                <th width="100">Stats</th>
                                <th width="120">Uploaded</th>
                                <th width="100">Status</th>
                                <th width="150">Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            if ($documents_result && mysqli_num_rows($documents_result) > 0):
                                while ($doc = mysqli_fetch_assoc($documents_result)):
                                    $file_size = $doc['file_size'] ? round($doc['file_size'] / 1024, 2) . ' KB' : 'N/A';
                                    $status_class = $doc['is_active'] ? 'success' : 'secondary';
                                    $status_text = $doc['is_active'] ? 'Active' : 'Inactive';
                                    $is_expired = $doc['expires_at'] && strtotime($doc['expires_at']) < time();
                                    
                                    // Get file icon
                                    $ext = strtolower(pathinfo($doc['file_original_name'], PATHINFO_EXTENSION));
                                    $file_icon = 'bi-file-earmark-text';
                                    if ($ext == 'pdf') $file_icon = 'bi-file-earmark-pdf';
                                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $file_icon = 'bi-file-earmark-image';
                                    elseif (in_array($ext, ['doc', 'docx'])) $file_icon = 'bi-file-earmark-word';
                                    elseif (in_array($ext, ['xls', 'xlsx'])) $file_icon = 'bi-file-earmark-excel';
                                    elseif (in_array($ext, ['ppt', 'pptx'])) $file_icon = 'bi-file-earmark-slides';
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi <?php echo $file_icon; ?> fs-4 me-3 text-primary"></i>
                                            <div>
                                                <strong><?php echo htmlspecialchars($doc['document_title']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($doc['document_description'], 0, 60)); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($doc['document_type'] == 'general'): ?>
                                            <span class="badge bg-success">General</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Specific</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $categories = explode(',', $doc['categories']);
                                        foreach ($categories as $cat):
                                            if ($cat):
                                        ?>
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($cat); ?></span>
                                        <?php 
                                            endif;
                                        endforeach;
                                        ?>
                                    </td>
                                    <td><?php echo $file_size; ?></td>
                                    <td>
                                        <small><i class="bi bi-eye"></i> <?php echo number_format($doc['view_count']); ?></small>
                                        <br>
                                        <small><i class="bi bi-download"></i> <?php echo number_format($doc['download_count']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($doc['created_at'])); ?></small>
                                        <br>
                                        <small class="text-muted">by <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        <?php if ($is_expired): ?>
                                            <br><span class="badge bg-danger mt-1">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=view&id=<?php echo $doc['document_id']; ?>" class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?php echo $doc['document_id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($doc['document_type'] == 'specific'): ?>
                                                <a href="?action=manage_access&id=<?php echo $doc['document_id']; ?>" class="btn btn-outline-warning" title="Manage Access">
                                                    <i class="bi bi-people"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger" onclick="deleteDocument(<?php echo $doc['document_id']; ?>, '<?php echo htmlspecialchars($doc['document_title']); ?>')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-folder-x display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No documents found</p>
                                        <a href="?action=upload" class="btn btn-primary mt-2">
                                            <i class="bi bi-cloud-upload"></i> Upload First Document
                                        </a>
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

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter by Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="?action=list&category=0" class="list-group-item list-group-item-action <?php echo $category_filter == 0 ? 'active' : ''; ?>">
                        <i class="bi bi-files me-2"></i> All Documents
                    </a>
                    <?php 
                    mysqli_data_seek($categories_result, 0);
                    while ($cat = mysqli_fetch_assoc($categories_result)):
                    ?>
                        <a href="?action=list&category=<?php echo $cat['category_id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $category_filter == $cat['category_id'] ? 'active' : ''; ?>">
                            <i class="bi bi-tag me-2"></i> <?php echo htmlspecialchars($cat['category_name']); ?>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($cat['category_description']); ?></small>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteDocument(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        $.ajax({
            url: 'includes/ajax/delete_document.php',
            type: 'POST',
            data: { document_id: id },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

function refreshTable() {
    location.reload();
}

function exportDocuments() {
    window.location.href = 'includes/ajax/export_documents.php?filter_type=<?php echo $filter_type; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>';
}
</script>