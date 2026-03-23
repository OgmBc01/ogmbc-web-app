<?php

if (!isset($connection)) {
    include_once __DIR__ . '/../db.php';
}

// Use $document_id from parent scope if available, otherwise fallback to GET
if (!isset($document_id)) {
    $document_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : 0;
}

if ($document_id <= 0) {
    echo '<div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Invalid document ID. Please select a valid document.
          </div>';
    return;
}

// Get document details with enhanced query
 $query = "SELECT d.*, u.first_name, u.last_name, u.user_email as uploader_email,
          GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as categories,
          GROUP_CONCAT(DISTINCT c.category_id) as category_ids
          FROM client_documents d
          LEFT JOIN users u ON d.uploaded_by = u.user_id
          LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
          LEFT JOIN document_categories c ON m.category_id = c.category_id
          WHERE d.document_id = $document_id AND d.is_active = 1
          GROUP BY d.document_id";
$result = mysqli_query($connection, $query);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    echo '<div class="alert alert-warning shadow-sm">
            <i class="bi bi-folder-x me-2"></i>
            Document not found or has been removed.
          </div>';
    return;
}

// Determine file type and icon
$file_extension = strtolower(pathinfo($doc['file_original_name'], PATHINFO_EXTENSION));
$file_icon = 'bi-file-earmark-text';
$file_color = 'text-primary';
$preview_type = 'unknown';

if (in_array($file_extension, ['pdf'])) {
    $file_icon = 'bi-file-earmark-pdf';
    $file_color = 'text-danger';
    $preview_type = 'pdf';
} elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $file_icon = 'bi-file-earmark-image';
    $file_color = 'text-success';
    $preview_type = 'image';
} elseif (in_array($file_extension, ['doc', 'docx'])) {
    $file_icon = 'bi-file-earmark-word';
    $file_color = 'text-info';
    $preview_type = 'word';
} elseif (in_array($file_extension, ['xls', 'xlsx'])) {
    $file_icon = 'bi-file-earmark-excel';
    $file_color = 'text-success';
    $preview_type = 'excel';
} elseif (in_array($file_extension, ['ppt', 'pptx'])) {
    $file_icon = 'bi-file-earmark-slides';
    $file_color = 'text-warning';
    $preview_type = 'powerpoint';
} elseif (in_array($file_extension, ['txt', 'csv', 'json', 'xml'])) {
    $file_icon = 'bi-file-earmark-code';
    $file_color = 'text-secondary';
    $preview_type = 'text';
} elseif (in_array($file_extension, ['zip', 'rar', '7z'])) {
    $file_icon = 'bi-file-earmark-zip';
    $file_color = 'text-secondary';
    $preview_type = 'archive';
}

$file_size_formatted = $doc['file_size'] ? formatFileSize($doc['file_size']) : 'N/A';
$is_expired = $doc['expires_at'] && strtotime($doc['expires_at']) < time();
$is_expiring_soon = $doc['expires_at'] && strtotime($doc['expires_at']) < strtotime('+7 days') && !$is_expired;

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    }
    return 'N/A';
}
?>

<div class="document-preview-container">
    <!-- Document Header -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-0 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <div class="document-icon-large me-3">
                        <i class="bi <?php echo $file_icon; ?> <?php echo $file_color; ?> fs-1"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 fw-bold"><?php echo htmlspecialchars($doc['document_title']); ?></h3>
                        <p class="text-muted mb-2">
                            <i class="bi bi-filetype-<?php echo $file_extension; ?> me-1"></i>
                            <?php echo strtoupper($file_extension); ?> • 
                            <?php echo $file_size_formatted; ?> • 
                            <i class="bi bi-calendar3 ms-2 me-1"></i>
                            Uploaded <?php echo date('F j, Y', strtotime($doc['created_at'])); ?>
                        </p>
                    </div>
                </div>
                <div class="btn-group">
                    <?php if (file_exists($doc['file_path'])): ?>
                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-primary" download>
                            <i class="bi bi-download me-1"></i> Download
                        </a>
                        <button class="btn btn-outline-primary" onclick="togglePreview()">
                            <i class="bi bi-eye me-1"></i> Toggle Preview
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Status Badges -->
            <div class="mb-3">
                <?php if ($doc['document_type'] == 'general'): ?>
                    <span class="badge bg-success me-2 px-3 py-2">
                        <i class="bi bi-globe me-1"></i> General Access
                    </span>
                <?php else: ?>
                    <span class="badge bg-info me-2 px-3 py-2">
                        <i class="bi bi-lock me-1"></i> Restricted Access
                    </span>
                <?php endif; ?>
                
                <?php if ($is_expired): ?>
                    <span class="badge bg-danger me-2 px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i> Expired
                    </span>
                <?php elseif ($is_expiring_soon): ?>
                    <span class="badge bg-warning me-2 px-3 py-2">
                        <i class="bi bi-clock-history me-1"></i> Expires Soon
                    </span>
                <?php elseif ($doc['expires_at']): ?>
                    <span class="badge bg-secondary me-2 px-3 py-2">
                        <i class="bi bi-calendar-check me-1"></i> Expires: <?php echo date('M d, Y', strtotime($doc['expires_at'])); ?>
                    </span>
                <?php endif; ?>
                
                <?php if ($doc['requires_approval']): ?>
                    <span class="badge bg-warning px-3 py-2">
                        <i class="bi bi-shield-shaded me-1"></i> Requires Approval
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Document Details Grid -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Preview Section -->
            <div class="card shadow-sm mb-4 border-0" id="previewSection">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-eye me-2 text-primary"></i>
                        Document Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="previewContent" class="preview-content">
                        <?php if (file_exists($doc['file_path'])): ?>
                            <?php if ($preview_type == 'image'): ?>
                                <div class="text-center">
                                    <img src="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                         class="img-fluid rounded shadow-sm" 
                                         alt="<?php echo htmlspecialchars($doc['document_title']); ?>"
                                         style="max-height: 500px;">
                                </div>
                            <?php elseif ($preview_type == 'pdf'): ?>
                                <div class="pdf-preview">
                                    <embed src="<?php echo htmlspecialchars($doc['file_path']); ?>#toolbar=0&navpanes=0&scrollbar=0" 
                                           type="application/pdf" 
                                           width="100%" 
                                           height="500px"
                                           class="rounded shadow-sm">
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            If PDF doesn't load, 
                                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank">click here to open in new tab</a>
                                        </small>
                                    </div>
                                </div>
                            <?php elseif ($preview_type == 'text'): ?>
                                <?php 
                                $content = file_get_contents($doc['file_path']);
                                $lines = explode("\n", $content);
                                $preview_lines = array_slice($lines, 0, 50);
                                ?>
                                <div class="text-preview bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">
                                    <pre class="mb-0" style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars(implode("\n", $preview_lines)); ?></pre>
                                    <?php if (count($lines) > 50): ?>
                                        <div class="text-center mt-3">
                                            <span class="badge bg-secondary">Showing first 50 lines of <?php echo count($lines); ?> total lines</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi <?php echo $file_icon; ?> <?php echo $file_color; ?> fs-1"></i>
                                    <h5 class="mt-3 text-muted">Preview not available for this file type</h5>
                                    <p class="text-muted">Please download the file to view its contents.</p>
                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-primary mt-2" download>
                                        <i class="bi bi-download me-1"></i> Download File
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                The document file is missing. Please contact the administrator.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2 text-primary"></i>
                        Description
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($doc['document_description']): ?>
                        <div class="document-description p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($doc['document_description'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No description provided for this document.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Document Information Card -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Document Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">File Name</label>
                        <p class="mb-0">
                            <i class="bi bi-file-earmark me-1"></i>
                            <?php echo htmlspecialchars($doc['file_original_name']); ?>
                        </p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">File Size</label>
                        <p class="mb-0">
                            <i class="bi bi-hdd-stack me-1"></i>
                            <?php echo $file_size_formatted; ?>
                        </p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Categories</label>
                        <p class="mb-0">
                            <?php if ($doc['categories']): ?>
                                <?php 
                                $categories = explode(', ', $doc['categories']);
                                foreach ($categories as $category): 
                                ?>
                                    <span class="badge bg-secondary me-1 mb-1">
                                        <i class="bi bi-tag me-1"></i>
                                        <?php echo htmlspecialchars($category); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No categories assigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Uploaded By</label>
                        <p class="mb-0">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($doc['uploader_email']); ?></small>
                        </p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="text-muted small text-uppercase fw-bold mb-1">Upload Date</label>
                        <p class="mb-0">
                            <i class="bi bi-calendar-plus me-1"></i>
                            <?php echo date('F j, Y \a\t g:i A', strtotime($doc['created_at'])); ?>
                        </p>
                    </div>
                    
                    <?php if ($doc['expires_at']): ?>
                        <div class="info-item mb-3">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Expiration Date</label>
                            <p class="mb-0">
                                <i class="bi bi-calendar-x me-1"></i>
                                <?php echo date('F j, Y', strtotime($doc['expires_at'])); ?>
                                <?php if ($is_expired): ?>
                                    <span class="badge bg-danger ms-2">Expired</span>
                                <?php elseif ($is_expiring_soon): ?>
                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Document Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-circle bg-primary-soft p-3 rounded-circle d-inline-block mb-2">
                                <i class="bi bi-eye text-primary fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold"><?php echo number_format($doc['view_count']); ?></h4>
                            <small class="text-muted">Total Views</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-circle bg-success-soft p-3 rounded-circle d-inline-block mb-2">
                                <i class="bi bi-download text-success fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold"><?php echo number_format($doc['download_count']); ?></h4>
                            <small class="text-muted">Total Downloads</small>
                        </div>
                    </div>
                    
                    <?php 
                    $engagement_rate = $doc['view_count'] > 0 ? round(($doc['download_count'] / $doc['view_count']) * 100, 1) : 0;
                    ?>
                    <div class="mt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Engagement Rate</small>
                            <small class="fw-bold"><?php echo $engagement_rate; ?>%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo $engagement_rate > 70 ? 'success' : ($engagement_rate > 40 ? 'warning' : 'secondary'); ?>" 
                                 style="width: <?php echo $engagement_rate; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2 text-primary"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (file_exists($doc['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn btn-primary" download>
                                <i class="bi bi-download me-2"></i> Download Document
                            </a>
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i> Print Details
                            </button>
                        <?php endif; ?>
                        <a href="?action=list" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Back to Documents
                        </a>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="share-section">
                        <label class="text-muted small text-uppercase fw-bold mb-2">Share Document</label>
                        <div class="input-group">
                            <input type="text" id="shareLink" class="form-control form-control-sm" 
                                   value="<?php echo htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" readonly>
                            <button class="btn btn-sm btn-outline-primary" onclick="copyShareLink()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.document-preview-container {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.document-icon-large {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 12px;
}

.stat-circle {
    width: 50px;
    height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-soft {
    background: rgba(102, 126, 234, 0.1);
}

.bg-success-soft {
    background: rgba(40, 167, 69, 0.1);
}

.info-item label {
    display: block;
    letter-spacing: 0.5px;
}

.preview-content {
    min-height: 300px;
    transition: all 0.3s ease;
}

.preview-content iframe,
.preview-content embed {
    border: none;
}

.text-preview pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    font-size: 0.85rem;
    line-height: 1.5;
}

.document-description {
    line-height: 1.6;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

@media (max-width: 768px) {
    .document-icon-large {
        width: 50px;
        height: 50px;
    }
    
    .document-icon-large i {
        font-size: 1.5rem;
    }
    
    h3 {
        font-size: 1.25rem;
    }
}
</style>

<script>
function togglePreview() {
    const previewSection = document.getElementById('previewSection');
    if (previewSection.style.display === 'none') {
        previewSection.style.display = 'block';
    } else {
        previewSection.style.display = 'none';
    }
}

function copyShareLink() {
    const shareLink = document.getElementById('shareLink');
    shareLink.select();
    shareLink.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show temporary tooltip
    const btn = document.querySelector('.share-section .btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = originalText;
    }, 2000);
}


</script>
<style>
@media print {
    .btn-group,
    .quick-actions,
    .share-section,
    .btn {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    .preview-content {
        page-break-inside: avoid;
    }
}
</style>