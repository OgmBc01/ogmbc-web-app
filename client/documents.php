<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

$client_id = $_SESSION['client_id'];

// Handle document download
if (isset($_GET['download']) && isset($_GET['document_id'])) {
    $document_id = intval($_GET['document_id']);
    
    // Check access
    $access_query = "SELECT d.* FROM client_documents d
                     WHERE d.document_id = $document_id 
                     AND d.is_active = 1
                     AND (d.document_type = 'general' 
                          OR EXISTS (SELECT 1 FROM document_client_access 
                                     WHERE document_id = d.document_id 
                                     AND client_id = $client_id))
                     AND (d.expires_at IS NULL OR d.expires_at > CURDATE())";
    $access_result = mysqli_query($connection, $access_query);
    $document = mysqli_fetch_assoc($access_result);
    
    if ($document) {
        // Log download
        $log_query = "INSERT INTO document_access_logs (document_id, client_id, access_type, ip_address, user_agent) 
                      VALUES ($document_id, $client_id, 'download', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')";
        mysqli_query($connection, $log_query);
        
        // Update download count
        mysqli_query($connection, "UPDATE client_documents SET download_count = download_count + 1 WHERE document_id = $document_id");
        
        // Serve file
        $file_path = $document['file_path'];
        $file_name = $document['file_original_name'];
        
        if (file_exists($file_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        }
    }
    exit();
}

// Handle document preview
if (isset($_GET['preview']) && isset($_GET['document_id'])) {
    $document_id = intval($_GET['document_id']);
    
    // Check access
    $access_query = "SELECT d.* FROM client_documents d
                     WHERE d.document_id = $document_id 
                     AND d.is_active = 1
                     AND (d.document_type = 'general' 
                          OR EXISTS (SELECT 1 FROM document_client_access 
                                     WHERE document_id = d.document_id 
                                     AND client_id = $client_id))
                     AND (d.expires_at IS NULL OR d.expires_at > CURDATE())";
    $access_result = mysqli_query($connection, $access_query);
    $document = mysqli_fetch_assoc($access_result);
    
    if ($document) {
        // Log view
        $log_query = "INSERT INTO document_access_logs (document_id, client_id, access_type, ip_address, user_agent) 
                      VALUES ($document_id, $client_id, 'view', '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')";
        mysqli_query($connection, $log_query);
        
        // Update view count
        mysqli_query($connection, "UPDATE client_documents SET view_count = view_count + 1 WHERE document_id = $document_id");
        
        // Store document in session for preview
        $_SESSION['preview_document'] = $document;
        echo '<script>window.location.href = "document_preview.php?document_id=' . $document_id . '";</script>';
        exit();
    } else {
        $_SESSION['error'] = "You don't have permission to view this document.";
        echo '<script>window.location.href = "documents.php";</script>';
        exit();
    }
}

// Get documents for client
$documents_query = "SELECT d.*, 
                    GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as categories
                    FROM client_documents d
                    LEFT JOIN document_category_mapping m ON d.document_id = m.document_id
                    LEFT JOIN document_categories c ON m.category_id = c.category_id
                    WHERE d.is_active = 1
                    AND (d.document_type = 'general' 
                         OR EXISTS (SELECT 1 FROM document_client_access 
                                    WHERE document_id = d.document_id 
                                    AND client_id = $client_id))
                    AND (d.expires_at IS NULL OR d.expires_at > CURDATE())
                    GROUP BY d.document_id
                    ORDER BY d.created_at DESC";
$documents_result = mysqli_query($connection, $documents_query);

// Get recent activity
$recent_query = "SELECT d.document_title, l.access_type, l.accessed_at
                 FROM document_access_logs l
                 JOIN client_documents d ON l.document_id = d.document_id
                 WHERE l.client_id = $client_id
                 ORDER BY l.accessed_at DESC
                 LIMIT 5";
$recent_result = mysqli_query($connection, $recent_query);

// Get document statistics for client
$stats_query = "SELECT 
                COUNT(DISTINCT d.document_id) as total_documents,
                COALESCE(SUM(d.view_count), 0) as total_views,
                COALESCE(SUM(d.download_count), 0) as total_downloads
                FROM client_documents d
                WHERE d.is_active = 1
                AND (d.document_type = 'general' 
                     OR EXISTS (SELECT 1 FROM document_client_access 
                                WHERE document_id = d.document_id 
                                AND client_id = $client_id))
                AND (d.expires_at IS NULL OR d.expires_at > CURDATE())";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Display any messages
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error']);
unset($_SESSION['success']);
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
                            <h2 class="welcome-title">
                                <i class="bi bi-folder2-open me-2" style="color:#C9A13B;"></i>
                                My Documents
                            </h2>
                            <p class="welcome-subtitle">
                                Access and download important documents shared with you
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="current-date">
                                <i class="bi bi-calendar3 me-2" style="color:#C9A13B;"></i>
                                <?php echo date('l, F j, Y'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2" style="color:#C9A13B;"></i> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2" style="color:#C9A13B;"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-card shadow-sm p-2" style="border-radius:10px; min-height:90px; border-left:5px solid #2563eb;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon d-flex align-items-center justify-content-center" style="background:#f0f4ff; border-radius:8px; width:38px; height:38px;">
                            <i class="bi bi-files fs-4" style="color:#C9A13B;"></i>
                        </div>
                        <div class="stat-content ms-2">
                            <div class="stat-value text-primary fw-bold" style="font-size:1.3rem; margin-bottom:2px;"><?php echo number_format($stats['total_documents']); ?></div>
                            <div class="stat-label text-primary" style="font-size:0.95rem;">Available Documents</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card shadow-sm p-2" style="border-radius:10px; min-height:90px; border-left:5px solid #0ea5e9;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon d-flex align-items-center justify-content-center" style="background:#eaf6fb; border-radius:8px; width:38px; height:38px;">
                            <i class="bi bi-eye fs-4" style="color:#C9A13B;"></i>
                        </div>
                        <div class="stat-content ms-2">
                            <div class="stat-value text-info fw-bold" style="font-size:1.3rem; margin-bottom:2px;"><?php echo number_format($stats['total_views']); ?></div>
                            <div class="stat-label text-info" style="font-size:0.95rem;">Total Views</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card shadow-sm p-2" style="border-radius:10px; min-height:90px; border-left:5px solid #22c55e;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon d-flex align-items-center justify-content-center" style="background:#eafbf0; border-radius:8px; width:38px; height:38px;">
                            <i class="bi bi-download fs-4" style="color:#C9A13B;"></i>
                        </div>
                        <div class="stat-content ms-2">
                            <div class="stat-value text-success fw-bold" style="font-size:1.3rem; margin-bottom:2px;"><?php echo number_format($stats['total_downloads']); ?></div>
                            <div class="stat-label text-success" style="font-size:0.95rem;">Total Downloads</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Documents List -->
            <div class="col-lg-8">
                <div class="dashboard-card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 py-2" style="min-height:56px;">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-folder2-open me-2" style="color:#C9A13B;"></i>
                            Available Documents
                        </h5>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchDocuments" class="form-control" placeholder="Search documents...">
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($documents_result) > 0): ?>
                            <div class="row" id="documentsList">
                                <?php while ($doc = mysqli_fetch_assoc($documents_result)): 
                                    $ext = strtolower(pathinfo($doc['file_original_name'], PATHINFO_EXTENSION));
                                    $file_icon = 'bi-file-earmark-text';
                                    $file_color = 'text-primary';
                                    
                                    if ($ext == 'pdf') {
                                        $file_icon = 'bi-file-earmark-pdf';
                                        $file_color = 'text-danger';
                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $file_icon = 'bi-file-earmark-image';
                                        $file_color = 'text-success';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        $file_icon = 'bi-file-earmark-word';
                                        $file_color = 'text-info';
                                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                        $file_icon = 'bi-file-earmark-excel';
                                        $file_color = 'text-success';
                                    } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                        $file_icon = 'bi-file-earmark-slides';
                                        $file_color = 'text-warning';
                                    }
                                    
                                    $is_expiring_soon = $doc['expires_at'] && strtotime($doc['expires_at']) < strtotime('+7 days') && strtotime($doc['expires_at']) > time();
                                ?>
                                    <div class="col-md-6 mb-3 document-item" data-title="<?php echo strtolower($doc['document_title']); ?>">
                                        <div class="document-card">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="document-icon me-3">
                                                    <i class="bi <?php echo $file_icon; ?> fs-2" style="color:#C9A13B;"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="document-title mb-1"><?php echo htmlspecialchars($doc['document_title']); ?></h6>
                                                    <p class="document-description text-muted small mb-2">
                                                        <?php echo htmlspecialchars(substr($doc['document_description'] ?? 'No description', 0, 80)); ?>
                                                        <?php if (strlen($doc['document_description'] ?? '') > 80): ?>...<?php endif; ?>
                                                    </p>
                                                    <?php if ($doc['categories']): ?>
                                                        <div class="mb-2">
                                                            <?php 
                                                            $categories = explode(', ', $doc['categories']);
                                                            foreach (array_slice($categories, 0, 2) as $category):
                                                            ?>
                                                                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($category); ?></span>
                                                            <?php endforeach; ?>
                                                            <?php if (count($categories) > 2): ?>
                                                                <span class="badge bg-light text-muted">+<?php echo count($categories) - 2; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="document-meta small text-muted">
                                                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($doc['created_at'])); ?>
                                                        <?php if ($doc['expires_at']): ?>
                                                            <span class="ms-2">
                                                                <i class="bi bi-hourglass-split"></i> 
                                                                <span class="<?php echo $is_expiring_soon ? 'text-warning' : ''; ?>">
                                                                    Expires: <?php echo date('M d, Y', strtotime($doc['expires_at'])); ?>
                                                                </span>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="document-actions d-flex justify-content-end gap-2">
                                                <a href="?preview=1&document_id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye" style="color:#C9A13B;"></i> Preview
                                                </a>
                                                <a href="?download=1&document_id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-download" style="color:#C9A13B;"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x display-1 text-muted"></i>
                                         <i class="bi bi-folder-x display-1" style="color:#C9A13B;"></i>
                                <h5 class="text-muted mt-3">No Documents Available</h5>
                                <p class="text-muted">There are no documents shared with you at this time.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Activity -->
                <div class="dashboard-card mb-4 shadow-sm">
                    <div class="card-header px-3 py-2">
                        <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2" style="color:#C9A13B;"></i>
                            Recent Activity
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($recent_result) > 0): ?>
                            <div class="activity-feed">
                                <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-<?php echo $recent['access_type'] == 'view' ? 'info' : 'success'; ?>-soft">
                                            <i class="bi bi-<?php echo $recent['access_type'] == 'view' ? 'eye' : 'download'; ?>" style="color:#C9A13B;"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-text mb-0">
                                                <strong><?php echo htmlspecialchars($recent['document_title']); ?></strong>
                                            </p>
                                            <small class="activity-details text-muted">
                                                <?php echo $recent['access_type'] == 'view' ? 'Viewed' : 'Downloaded'; ?> on 
                                                <?php echo date('M d, Y \a\t g:i A', strtotime($recent['accessed_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-activity fs-1 text-muted"></i>
                                         <i class="bi bi-activity fs-1" style="color:#C9A13B;"></i>
                                <p class="text-muted mt-2">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Document Tips -->
                <div class="dashboard-card shadow-sm">
                    <div class="card-header px-3 py-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightbulb me-2" style="color:#C9A13B;"></i>
                            Quick Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-eye-fill me-2 mt-1" style="color:#C9A13B;"></i>
                                <span class="small">Click <strong>Preview</strong> to view documents online without downloading</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-download-fill me-2 mt-1" style="color:#C9A13B;"></i>
                                <span class="small">Use <strong>Download</strong> to save a copy to your device</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-hourglass-split me-2 mt-1" style="color:#C9A13B;"></i>
                                <span class="small">Check expiration dates for time-sensitive documents</span>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="bi bi-envelope-fill me-2 mt-1" style="color:#C9A13B;"></i>
                                <span class="small">Contact support if you can't access a document</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchDocuments')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const documents = document.querySelectorAll('.document-item');
    
    documents.forEach(doc => {
        const title = doc.getAttribute('data-title') || '';
        if (title.includes(searchTerm)) {
            doc.style.display = '';
        } else {
            doc.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/client_footer.php'; ?>