<?php
// Ensure client_id is defined
// if (!isset($client_id)) {
//     $client_id = $_SESSION['client_id'] ?? 0;
// }

if ($client_id <= 0) {
    echo '<div class="alert alert-danger">Invalid client ID</div>';
    return;
}

// Get filter parameters
$engagement_filter = isset($_GET['engagement_id']) ? (int)$_GET['engagement_id'] : 0;
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

// Initialize variables
$files_result = null;
$engagements_result = null;

// Check if client_files table exists
$table_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_files'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    // Build query - FIXED THE SYNTAX ERROR
    $where = ["cf.client_id = " . intval($client_id)];

    if ($engagement_filter > 0) {
        $where[] = "cf.engagement_id = " . intval($engagement_filter);
    }

    if ($type_filter == 'mine') {
        $where[] = "cf.uploaded_by = 'client'";
    } elseif ($type_filter == 'staff') {
        $where[] = "cf.uploaded_by = 'staff'";
    }

    $where_clause = implode(' AND ', $where);

    $files_query = "SELECT cf.*, 
                    e.title as engagement_title,
                    CASE 
                        WHEN cf.uploaded_by = 'staff' THEN CONCAT(u.first_name, ' ', u.last_name)
                        ELSE 'You'
                    END as uploaded_by_name
                    FROM client_files cf
                    LEFT JOIN engagements e ON cf.engagement_id = e.engagement_id
                    LEFT JOIN users u ON cf.uploaded_by = 'staff' AND u.user_id = cf.uploaded_by
                    WHERE $where_clause
                    ORDER BY cf.uploaded_at DESC";

    $files_result = mysqli_query($connection, $files_query);
    
    if (!$files_result) {
        error_log("Files query failed: " . mysqli_error($connection));
        $files_result = null;
    }
}

// Get engagements for filter dropdown - with error handling
$engagements_query = "SELECT engagement_id, title FROM engagements 
                      WHERE client_id = " . intval($client_id) . " 
                      ORDER BY created_at DESC";
$engagements_result = mysqli_query($connection, $engagements_query);
if (!$engagements_result) {
    $engagements_result = null;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">File Exchange</h1>
        <a href="files.php?source=upload" class="btn btn-primary">
            <i class="bi bi-cloud-upload"></i> Upload New File
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="engagement_filter" class="form-label">Filter by Engagement</label>
                    <select id="engagement_filter" name="engagement_id" class="form-control">
                        <option value="">All Engagements</option>
                        <?php if ($engagements_result && mysqli_num_rows($engagements_result) > 0): ?>
                            <?php while($eng = mysqli_fetch_assoc($engagements_result)): ?>
                                <option value="<?php echo $eng['engagement_id']; ?>" <?php echo ($engagement_filter == $eng['engagement_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($eng['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="type_filter" class="form-label">Filter by Type</label>
                    <select id="type_filter" name="type" class="form-control">
                        <option value="all" <?php echo ($type_filter == 'all') ? 'selected' : ''; ?>>All Files</option>
                        <option value="mine" <?php echo ($type_filter == 'mine') ? 'selected' : ''; ?>>My Uploads</option>
                        <option value="staff" <?php echo ($type_filter == 'staff') ? 'selected' : ''; ?>>Staff Uploads</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="row">
        <?php if ($files_result && mysqli_num_rows($files_result) > 0): ?>
            <?php while($file = mysqli_fetch_assoc($files_result)): 
                $file_icon = 'bi-file-earmark';
                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $file_icon = 'bi-file-earmark-image';
                } elseif ($ext == 'pdf') {
                    $file_icon = 'bi-file-earmark-pdf';
                } elseif (in_array($ext, ['doc', 'docx'])) {
                    $file_icon = 'bi-file-earmark-word';
                } elseif (in_array($ext, ['xls', 'xlsx'])) {
                    $file_icon = 'bi-file-earmark-excel';
                } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                    $file_icon = 'bi-file-earmark-zip';
                }
            ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi <?php echo $file_icon; ?>" style="font-size: 2rem; color: #f1bf70;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($file['file_name']); ?></h6>
                                <small class="text-muted d-block">
                                    <?php echo $file['engagement_title'] ? htmlspecialchars($file['engagement_title']) : 'General'; ?>
                                </small>
                                <small class="text-muted d-block">
                                    Uploaded: <?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?>
                                </small>
                                <?php if ($file['file_size']): ?>
                                <small class="text-muted d-block">
                                    Size: <?php echo round($file['file_size'] / 1024, 2); ?> KB
                                </small>
                                <?php endif; ?>
                                <span class="badge bg-<?php echo $file['uploaded_by'] == 'client' ? 'success' : 'info'; ?> mt-1">
                                    <?php echo $file['uploaded_by'] == 'client' ? 'You' : 'Staff'; ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($file['description'])): ?>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-chat"></i> <?php echo htmlspecialchars($file['description']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100">
                            <a href="includes/ajax/download_file.php?id=<?php echo $file['file_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download
                            </a>
                            <?php if ($file['uploaded_by'] == 'client'): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFile(<?php echo $file['file_id']; ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-folder2-open display-1"></i>
                    <h4 class="mt-3">No files found</h4>
                    <p class="text-muted">Upload your first file to get started.</p>
                    <a href="files.php?source=upload" class="btn btn-primary mt-2">
                        <i class="bi bi-cloud-upload"></i> Upload File
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        fetch('includes/ajax/delete_file.php?id=' + fileId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error deleting file');
            });
    }
}
</script>