<?php
// Check if client ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify client access
$check_query = "SELECT c.* 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                GROUP BY c.client_id";
$check_result = mysqli_query($connection, $check_query);
$client = mysqli_fetch_assoc($check_result);

if (!$client) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

// Get all files
$files_query = "SELECT cf.*, 
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                e.title as engagement_title
                FROM client_files cf
                LEFT JOIN users u ON cf.uploaded_by = u.user_id
                LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
                WHERE cf.client_id = $client_id
                ORDER BY cf.uploaded_at DESC";
$files_result = mysqli_query($connection, $files_query);

// Get file stats
$stats_query = "SELECT 
                COUNT(*) as total_files,
                SUM(CASE WHEN uploaded_by = 'client' THEN 1 ELSE 0 END) as from_client,
                SUM(CASE WHEN uploaded_by = 'staff' THEN 1 ELSE 0 END) as from_staff,
                SUM(file_size) as total_size
                FROM client_files
                WHERE client_id = $client_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get engagements for upload form
$engagements_query = "SELECT engagement_id, title 
                     FROM engagements 
                     WHERE client_id = $client_id AND assigned_to = $user_id
                     ORDER BY created_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="client-header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-2">File Exchange - <?php echo htmlspecialchars($client['company_name']); ?></h4>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-folder me-2"></i><?php echo $stats['total_files'] ?? 0; ?> files
                            <span class="mx-3">|</span>
                            <i class="bi bi-arrow-down-circle me-1"></i><?php echo $stats['from_client'] ?? 0; ?> from client
                            <span class="mx-3">|</span>
                            <i class="bi bi-arrow-up-circle me-1"></i><?php echo $stats['from_staff'] ?? 0; ?> from you
                        </p>
                    </div>
                    <div>
                        <a href="clients.php?source=view&id=<?php echo $client_id; ?>" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>Back to Client
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="bi bi-cloud-upload me-1"></i>Upload File
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-files text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total_files'] ?? 0; ?></h3>
                    <p class="stat-label">Total Files</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-cloud-download text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['from_client'] ?? 0; ?></h3>
                    <p class="stat-label">From Client</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-cloud-upload text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['from_staff'] ?? 0; ?></h3>
                    <p class="stat-label">From You</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-small">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-hdd-stack text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total_size'] ? round($stats['total_size'] / 1048576, 2) : 0; ?> MB</h3>
                    <p class="stat-label">Total Size</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-files me-2"></i>All Files
            </h5>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-light" onclick="filterFiles('all')">All</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterFiles('client')">From Client</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterFiles('staff')">From Staff</button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($files_result && mysqli_num_rows($files_result) > 0): ?>
                <div class="files-grid" id="filesGrid">
                    <?php while($file = mysqli_fetch_assoc($files_result)): 
                        $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                        $icon = 'file-earmark';
                        $color = 'secondary';
                        
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $icon = 'file-earmark-image';
                            $color = 'success';
                        } elseif ($ext == 'pdf') {
                            $icon = 'file-earmark-pdf';
                            $color = 'danger';
                        } elseif (in_array($ext, ['doc', 'docx'])) {
                            $icon = 'file-earmark-word';
                            $color = 'primary';
                        } elseif (in_array($ext, ['xls', 'xlsx'])) {
                            $icon = 'file-earmark-excel';
                            $color = 'success';
                        }
                    ?>
                    <div class="file-grid-item" data-uploader="<?php echo $file['uploaded_by']; ?>">
                        <div class="file-card">
                            <div class="file-icon text-<?php echo $color; ?>">
                                <i class="bi bi-<?php echo $icon; ?>"></i>
                            </div>
                            <div class="file-details">
                                <h6 class="file-name" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                    <?php echo htmlspecialchars(substr($file['file_name'], 0, 20)) . (strlen($file['file_name']) > 20 ? '...' : ''); ?>
                                </h6>
                                <div class="file-meta">
                                    <span class="badge bg-<?php echo $file['uploaded_by'] == 'client' ? 'info' : 'success'; ?>">
                                        <i class="bi bi-<?php echo $file['uploaded_by'] == 'client' ? 'cloud-download' : 'cloud-upload'; ?> me-1"></i>
                                        <?php echo ucfirst($file['uploaded_by']); ?>
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        <?php echo round($file['file_size'] / 1024, 1); ?> KB
                                    </small>
                                </div>
                                <?php if ($file['engagement_title']): ?>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars(substr($file['engagement_title'], 0, 15)) . '...'; ?>
                                    </small>
                                <?php endif; ?>
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?>
                                </small>
                            </div>
                            <div class="file-actions">
                                <a href="../uploads/client_files/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="../uploads/client_files/<?php echo $file['file_path']; ?>" class="btn btn-sm btn-outline-success" download title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-files display-1 text-muted"></i>
                    <h5 class="mt-3">No Files Yet</h5>
                    <p class="text-muted">Upload files to share with this client.</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="bi bi-cloud-upload me-2"></i>Upload First File
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Upload File
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="includes/ajax/upload_client_file.php" enctype="multipart/form-data" id="uploadForm">
                <div class="modal-body">
                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Related Engagement (Optional)</label>
                        <select name="engagement_id" class="form-select">
                            <option value="">Select Engagement</option>
                            <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                <option value="<?php echo $eng['engagement_id']; ?>">
                                    <?php echo htmlspecialchars($eng['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Description (Optional)</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief description of the file">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Max file size: 10MB. Allowed: PDF, Images, DOC, DOCX, XLS, XLSX</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card files-tip">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        File Management Tips
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>📁 Use clear, descriptive file names (e.g., "VAT_Return_Q1_2024.pdf")</li>
                        <li>🔗 Link files to specific engagements for easy reference</li>
                        <li>⏱️ Upload files promptly to keep clients updated</li>
                        <li>📊 Organize files by engagement and file type</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-folder2-open display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.client-header-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 25px;
}

.files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.file-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    transition: all 0.3s ease;
    border: 1px solid #eee;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.file-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    background: white;
}

.file-icon {
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 10px;
}

.file-details {
    flex: 1;
}

.file-name {
    font-size: 0.95rem;
    margin-bottom: 8px;
    word-break: break-word;
}

.file-meta {
    margin-bottom: 8px;
}

.file-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 10px;
}

.pro-tip-card.files-tip {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .client-header-card .d-flex {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .files-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function filterFiles(uploader) {
    const items = document.querySelectorAll('.file-grid-item');
    items.forEach(item => {
        if (uploader === 'all' || item.dataset.uploader === uploader) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Handle upload form submission
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');
    const originalText = uploadBtn.innerHTML;
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
    
    fetch('includes/ajax/upload_client_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
            modal.hide();
            location.reload(); // Refresh to show new file
        } else {
            alert('Error: ' + data.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('Error uploading file');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
    });
});
</script>