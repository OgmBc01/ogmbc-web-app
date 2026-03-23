<?php

include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Initialize session with security settings
initSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../index.php");
    exit();
}

// Get the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

// Page title mapping
$page_titles = [
    'list' => 'Document Management',
    'upload' => 'Upload New Document',
    'edit' => 'Edit Document',
    'view' => 'View Document',
    'manage_access' => 'Manage Document Access',
    'categories' => 'Document Categories',
    'logs' => 'Document Access Logs',
    'reports' => 'Document Reports'
];

$current_title = isset($page_titles[$action]) ? $page_titles[$action] : 'Document Management';
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
                                <i class="bi bi-folder2-open me-2"></i>
                                <?php echo $current_title; ?>
                            </h2>
                            <p class="welcome-subtitle">
                                Manage client documents, control access, and track downloads
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php if ($action != 'upload'): ?>
                                <a href="?action=upload" class="btn btn-light">
                                    <i class="bi bi-cloud-upload"></i> Upload Document
                                </a>
                            <?php endif; ?>
                            <?php if ($action == 'list'): ?>
                                <button class="btn btn-outline-light ms-2" onclick="window.location.href='?action=reports'">
                                    <i class="bi bi-graph-up"></i> Reports
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content based on action -->
        <?php
        // Include the appropriate file based on action
        $base_path = 'includes/document_management/';
        
        switch ($action) {
            case 'upload':
                include $base_path . 'upload_document.php';
                break;
                
            case 'edit':
                if ($document_id > 0) {
                    include $base_path . 'edit_document.php';
                } else {
                    include $base_path . 'list_documents.php';
                }
                break;
                
            case 'view':
                if ($document_id > 0) {
                    include $base_path . 'view_document.php';
                } else {
                    include $base_path . 'list_documents.php';
                }
                break;
                
            case 'manage_access':
                if ($document_id > 0) {
                    include $base_path . 'manage_access.php';
                } else {
                    include $base_path . 'list_documents.php';
                }
                break;
                
            case 'categories':
                include $base_path . 'categories.php';
                break;
                
            case 'logs':
                include $base_path . 'document_logs.php';
                break;
                
            case 'reports':
                include $base_path . 'reports.php';
                break;
                
            case 'list':
            default:
                include $base_path . 'list_documents.php';
                break;
        }
        ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>