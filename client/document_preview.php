<?php

include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login.php");
    exit();
}

$client_id = $_SESSION['client_id'];
$document_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : 0;

if ($document_id <= 0) {
    $_SESSION['error'] = "Invalid document ID.";
    header("Location: documents.php");
    exit();
}

// Verify client has access to this document
$access_query = "SELECT d.*, 
                 GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as categories
                 FROM client_documents d
                 LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
                 LEFT JOIN document_categories c ON m.category_id = c.category_id
                 WHERE d.document_id = $document_id 
                 AND d.is_active = 1
                 AND (d.document_type = 'general' 
                      OR EXISTS (SELECT 1 FROM document_client_access 
                                 WHERE document_id = d.document_id 
                                 AND client_id = $client_id))
                 AND (d.expires_at IS NULL OR d.expires_at > CURDATE())
                 GROUP BY d.document_id";
$access_result = mysqli_query($connection, $access_query);
$document = mysqli_fetch_assoc($access_result);

if (!$document) {
    $_SESSION['error'] = "You don't have permission to view this document or it has expired.";
    header("Location: documents.php");
    exit();
}

$file_extension = strtolower(pathinfo($document['file_original_name'], PATHINFO_EXTENSION));
$file_size_formatted = $document['file_size'] ? round($document['file_size'] / 1024, 2) . ' KB' : 'N/A';
$is_expired = $document['expires_at'] && strtotime($document['expires_at']) < time();
$is_expiring_soon = $document['expires_at'] && strtotime($document['expires_at']) < strtotime('+7 days') && !$is_expired;

// Determine file icon and color
$file_icon = 'bi-file-earmark-text';
$file_color = 'text-primary';
if ($file_extension == 'pdf') {
    $file_icon = 'bi-file-earmark-pdf';
    $file_color = 'text-danger';
} elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $file_icon = 'bi-file-earmark-image';
    $file_color = 'text-success';
} elseif (in_array($file_extension, ['doc', 'docx'])) {
    $file_icon = 'bi-file-earmark-word';
    $file_color = 'text-info';
} elseif (in_array($file_extension, ['xls', 'xlsx'])) {
    $file_icon = 'bi-file-earmark-excel';
    $file_color = 'text-success';
} elseif (in_array($file_extension, ['ppt', 'pptx'])) {
    $file_icon = 'bi-file-earmark-slides';
    $file_color = 'text-warning';
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="document-icon-large me-3">
                                    <i class="bi <?php echo $file_icon; ?> <?php echo $file_color; ?> fs-1"></i>
                                </div>
                                <div>
                                    <h2 class="welcome-title mb-1">
                                        <?php echo htmlspecialchars($document['document_title']); ?>
                                    </h2>
                                    <p class="welcome-subtitle mb-0">
                                        <i class="bi bi-filetype-<?php echo $file_extension; ?> me-1"></i>
                                        <?php echo strtoupper($file_extension); ?> • 
                                        <?php echo $file_size_formatted; ?> • 
                                        <i class="bi bi-calendar3 ms-2 me-1"></i>
                                        Uploaded <?php echo date('F j, Y', strtotime($document['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="btn-group">
                                <a href="documents.php?download=1&document_id=<?php echo $document_id; ?>" class="btn btn-light">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <a href="documents.php" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Badges -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($document['document_type'] == 'general'): ?>
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-globe me-1"></i> General Access
                        </span>
                    <?php else: ?>
                        <span class="badge bg-info px-3 py-2">
                            <i class="bi bi-lock me-1"></i> Restricted Access
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($is_expired): ?>
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i> Expired
                        </span>
                    <?php elseif ($is_expiring_soon): ?>
                        <span class="badge bg-warning px-3 py-2">
                            <i class="bi bi-clock-history me-1"></i> Expires Soon
                        </span>
                    <?php elseif ($document['expires_at']): ?>
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-calendar-check me-1"></i> Expires: <?php echo date('M d, Y', strtotime($document['expires_at'])); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($document['categories']): ?>
                        <span class="badge bg-primary px-3 py-2">
                            <i class="bi bi-tags me-1"></i> <?php echo htmlspecialchars($document['categories']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Preview Content -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header px-3 py-2">
                        <h5 class="card-title">
                            <i class="bi bi-eye me-2" style="color:#C9A13B;"></i>
                            Document Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (file_exists($document['file_path'])) {
                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                echo '<div class="text-center">
                                        <img src="' . $document['file_path'] . '" 
                                             class="img-fluid rounded shadow-sm" 
                                             alt="' . htmlspecialchars($document['document_title']) . '" 
                                             style="max-height: 70vh;">
                                      </div>';
                            } elseif ($file_extension == 'pdf') {
                                echo '<div class="pdf-preview">
                                        <embed src="' . $document['file_path'] . '#toolbar=0&navpanes=0&scrollbar=0" 
                                               type="application/pdf" 
                                               width="100%" 
                                               height="600px"
                                               class="rounded shadow-sm">
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                If PDF doesn\'t load, 
                                                <a href="' . $document['file_path'] . '" target="_blank">click here to open in new tab</a>
                                            </small>
                                        </div>
                                      </div>';
                            } elseif (in_array($file_extension, ['txt', 'csv', 'json', 'xml'])) {
                                $content = file_get_contents($document['file_path']);
                                $lines = explode("\n", $content);
                                $preview_lines = array_slice($lines, 0, 100);
                                echo '<div class="text-preview bg-light p-3 rounded" style="max-height: 70vh; overflow-y: auto;">
                                        <pre class="mb-0" style="font-family: monospace; font-size: 0.85rem; white-space: pre-wrap;">' . htmlspecialchars(implode("\n", $preview_lines)) . '</pre>';
                                if (count($lines) > 100) {
                                    echo '<div class="text-center mt-3">
                                            <span class="badge bg-secondary">Showing first 100 lines of ' . count($lines) . ' total lines</span>
                                          </div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="text-center py-5">
                                        <i class="bi ' . $file_icon . ' display-1 text-muted"></i>
                                        <h5 class="mt-3 text-muted">Preview not available for this file type</h5>
                                        <p class="text-muted">Please download the file to view its contents.</p>
                                        <a href="documents.php?download=1&document_id=' . $document_id . '" class="btn btn-primary mt-2">
                                            <i class="bi bi-download"></i> Download File
                                        </a>
                                      </div>';
                            }
                        } else {
                            echo '<div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    The document file is missing. Please contact the administrator.
                                  </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Document Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header px-3 py-2">
                        <h5 class="card-title">
                            <i class="bi bi-info-circle me-2" style="color:#C9A13B;"></i>
                            Document Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">File Name:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($document['file_original_name']); ?></dd>
                                    
                                    <dt class="col-sm-4">File Size:</dt>
                                    <dd class="col-sm-8"><?php echo $file_size_formatted; ?></dd>
                                    
                                    <dt class="col-sm-4">File Type:</dt>
                                    <dd class="col-sm-8"><?php echo strtoupper($file_extension); ?></dd>
                                    
                                    <dt class="col-sm-4">Uploaded:</dt>
                                    <dd class="col-sm-8"><?php echo date('F j, Y \a\t g:i A', strtotime($document['created_at'])); ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Views:</dt>
                                    <dd class="col-sm-8">
                                        <i class="bi bi-eye me-1"></i> <?php echo number_format($document['view_count']); ?>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Downloads:</dt>
                                    <dd class="col-sm-8">
                                        <i class="bi bi-download me-1"></i> <?php echo number_format($document['download_count']); ?>
                                    </dd>
                                    
                                    <?php if ($document['expires_at']): ?>
                                        <dt class="col-sm-4">Expires:</dt>
                                        <dd class="col-sm-8">
                                            <?php echo date('F j, Y', strtotime($document['expires_at'])); ?>
                                            <?php if ($is_expired): ?>
                                                <span class="badge bg-danger ms-2">Expired</span>
                                            <?php elseif ($is_expiring_soon): ?>
                                                <span class="badge bg-warning ms-2">Expiring Soon</span>
                                            <?php endif; ?>
                                        </dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        </div>
                        
                        <?php if ($document['document_description']): ?>
                            <hr>
                            <label class="text-muted small text-uppercase fw-bold mb-2">Description</label>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($document['document_description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="pro-tip-card">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h6 class="text-white mb-2">
                                <i class="bi bi-lightbulb me-2"></i>
                                Need Help?
                            </h6>
                            <p class="text-white-50 small mb-md-0">
                                If you're having trouble accessing or viewing this document, please contact our support team.
                            </p>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <i class="bi bi-question-circle display-4 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.document-icon-large {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pdf-preview embed {
    border: none;
}

.text-preview pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

@media (max-width: 768px) {
    .document-icon-large {
        width: 50px;
        height: 50px;
    }
    
    .document-icon-large i {
        font-size: 1.5rem;
    }
}
</style>

<?php include 'includes/footer_client.php'; ?>