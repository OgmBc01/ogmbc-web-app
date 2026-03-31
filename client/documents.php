<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

$client_id = $_SESSION['client_id'];

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
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div>
                        <h2 class="welcome-title">
                            <i class="bi bi-folder2-open me-2"></i>My Documents
                        </h2>
                        <p class="welcome-subtitle">Access and download important documents shared with you</p>
                    </div>
                    <div class="current-date mt-3 mt-md-0">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-files text-primary"></i>
                        </div>
                        <div class="stat-content ms-3">
                            <h3 class="stat-value mb-0"><?php echo number_format($stats['total_documents']); ?></h3>
                            <p class="stat-label mb-0">Available Documents</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-card-info">
                    <div class="stat-card-body d-flex align-items-center">
                        <div class="stat-icon bg-info-soft">
                            <i class="bi bi-eye text-info"></i>
                        </div>
                        <div class="stat-content ms-3">
                            <h3 class="stat-value mb-0"><?php echo number_format($stats['total_views']); ?></h3>
                            <p class="stat-label mb-0">Total Views</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-download text-success"></i>
                        </div>
                        <div class="stat-content ms-3">
                            <h3 class="stat-value mb-0"><?php echo number_format($stats['total_downloads']); ?></h3>
                            <p class="stat-label mb-0">Total Downloads</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Documents List -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="card-header dark-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="bi bi-folder2-open me-2"></i>Available Documents
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
                                    $file_color = 'primary';
                                    
                                    if ($ext == 'pdf') {
                                        $file_icon = 'bi-file-earmark-pdf';
                                        $file_color = 'danger';
                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $file_icon = 'bi-file-earmark-image';
                                        $file_color = 'success';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        $file_icon = 'bi-file-earmark-word';
                                        $file_color = 'info';
                                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                        $file_icon = 'bi-file-earmark-excel';
                                        $file_color = 'success';
                                    }
                                    
                                    $is_expiring_soon = $doc['expires_at'] && strtotime($doc['expires_at']) < strtotime('+7 days') && strtotime($doc['expires_at']) > time();
                                ?>
                                    <div class="col-md-6 mb-3 document-item" data-title="<?php echo strtolower($doc['document_title']); ?>">
                                        <div class="document-card">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="document-icon me-3">
                                                    <i class="bi <?php echo $file_icon; ?> fs-2 text-<?php echo $file_color; ?>"></i>
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
                                                <a href="preview_document.php?id=<?php echo $doc['document_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Preview
                                                </a>
                                                <a href="download_document.php?id=<?php echo $doc['document_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-folder-x display-1 text-muted"></i>
                                <h5 class="mt-3">No Documents Available</h5>
                                <p class="text-muted">There are no documents shared with you at this time.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Activity -->
                <div class="dashboard-card mb-4">
                    <div class="card-header dark-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>Recent Activity
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (mysqli_num_rows($recent_result) > 0): ?>
                            <div class="activity-feed">
                                <?php while ($recent = mysqli_fetch_assoc($recent_result)): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-<?php echo $recent['access_type'] == 'view' ? 'info-soft' : 'success-soft'; ?>">
                                            <i class="bi bi-<?php echo $recent['access_type'] == 'view' ? 'eye' : 'download'; ?> text-<?php echo $recent['access_type'] == 'view' ? 'info' : 'success'; ?>"></i>
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
                            <div class="empty-state py-4">
                                <i class="bi bi-activity display-4 text-muted"></i>
                                <p class="text-muted mt-2">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Document Tips -->
                <div class="dashboard-card">
                    <div class="card-header dark-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightbulb me-2"></i>Quick Tips
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-eye-fill me-2 mt-1 text-primary"></i>
                                <span class="small">Click <strong>Preview</strong> to view documents online without downloading</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-download-fill me-2 mt-1 text-success"></i>
                                <span class="small">Use <strong>Download</strong> to save a copy to your device</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-hourglass-split me-2 mt-1 text-warning"></i>
                                <span class="small">Check expiration dates for time-sensitive documents</span>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="bi bi-envelope-fill me-2 mt-1 text-info"></i>
                                <span class="small">Contact support if you can't access a document</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Welcome Card */
.welcome-card {
    background: linear-gradient(135deg, #0a2240 0%, #1a2f4f 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(10, 34, 64, 0.3);
}
.welcome-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}
.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0;
}
.current-date {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
}

/* Stat Cards */
.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border-left: 6px solid #e0e0e0;
    padding: 0;
    transition: box-shadow 0.2s;
    height: 100%;
}
.stat-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-success { border-left-color: #38c172; }

.stat-card-body {
    padding: 20px;
    display: flex;
    align-items: center;
}
.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: #f5f6fa;
    border-radius: 50%;
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #222;
    line-height: 1.2;
}
.stat-label {
    font-size: 0.85rem;
    color: #666;
    margin-top: 2px;
}

/* Dark Header */
.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

/* Document Cards */
.document-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    height: 100%;
}
.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    background: white;
}
.document-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
}
.document-meta {
    font-size: 0.75rem;
}
.document-actions .btn {
    padding: 4px 12px;
    font-size: 0.8rem;
}

/* Activity Feed */
.activity-feed {
    max-height: 350px;
    overflow-y: auto;
}
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.activity-item:hover {
    background: #f8f9fa;
}
.activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.activity-content {
    flex: 1;
}
.activity-text {
    margin-bottom: 2px;
    font-size: 0.9rem;
}
.activity-details {
    font-size: 0.75rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}
.empty-state i {
    color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    .welcome-card {
        padding: 18px;
        text-align: center;
    }
    .stat-card-body {
        padding: 15px;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    .stat-value {
        font-size: 1.2rem;
    }
}
</style>

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