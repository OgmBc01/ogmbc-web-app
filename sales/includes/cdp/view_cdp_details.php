<?php
// Set user_id from session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'cdp.php';</script>";
    exit();
}

$cdp_id = (int)$_GET['id'];

// Fetch record and verify ownership
$query = "SELECT c.*, 
          CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
          FROM cdp_records c
          LEFT JOIN users u ON c.approved_by = u.user_id
          WHERE c.cdp_id = $cdp_id AND c.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>window.location.href = 'cdp.php';</script>";
    exit();
}

$record = mysqli_fetch_assoc($result);

// Determine type icon and color
$type_icon = '';
$type_color = '';
switch($record['cdp_type']) {
    case 'CERTIFICATE':
        $type_icon = 'patch-check';
        $type_color = 'success';
        break;
    case 'COURSE':
        $type_icon = 'book';
        $type_color = 'info';
        break;
    case 'LOYALTY':
        $type_icon = 'star';
        $type_color = 'warning';
        break;
    case 'BEHAVIOR':
        $type_icon = 'heart';
        $type_color = 'primary';
        break;
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header with Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-mortarboard me-2"></i>CDP Record Details</h4>
                <div>
                    <a href="cdp.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Back to List
                    </a>
                    <?php if ($record['status'] == 'PENDING'): ?>
                        <a href="cdp.php?source=edit&id=<?php echo $cdp_id; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Header with Status -->
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-2"><?php echo htmlspecialchars($record['title']); ?></h4>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-<?php echo $type_color; ?>">
                                    <i class="bi bi-<?php echo $type_icon; ?> me-1"></i>
                                    <?php echo $record['cdp_type']; ?>
                                </span>
                                <?php if ($record['uplift_percentage']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-percent me-1"></i>+<?php echo $record['uplift_percentage']; ?>% Uplift
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge bg-<?php 
                            echo $record['status'] == 'APPROVED' ? 'success' : 
                                ($record['status'] == 'REJECTED' ? 'danger' : 'warning'); 
                        ?> px-3 py-2">
                            <i class="bi bi-<?php 
                                echo $record['status'] == 'APPROVED' ? 'check-circle' : 
                                    ($record['status'] == 'REJECTED' ? 'x-circle' : 'clock-history'); 
                            ?> me-1"></i>
                            <?php echo $record['status']; ?>
                        </span>
                    </div>

                    <!-- Details Grid -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="detail-box">
                                <span class="detail-label">Effective Date</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($record['effective_date'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-box">
                                <span class="detail-label">Submitted On</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($record['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-box">
                                <span class="detail-label">Last Updated</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($record['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($record['description'])): ?>
                    <div class="mb-4">
                        <h6 class="section-title"><i class="bi bi-file-text me-2"></i>Description</h6>
                        <div class="section-content">
                            <?php echo nl2br(htmlspecialchars($record['description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Document -->
                    <?php if (!empty($record['document_file'])): ?>
                    <div class="mb-4">
                        <h6 class="section-title"><i class="bi bi-file-earmark me-2"></i>Document</h6>
                        <div class="section-content">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text file-icon me-3"></i>
                                <span class="file-name"><?php echo basename($record['document_file']); ?></span>
                                <a href="../uploads/cdp_documents/<?php echo $record['document_file']; ?>" class="btn btn-sm btn-outline-primary ms-auto" download>
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Approval Details -->
                    <?php if ($record['status'] == 'APPROVED' && $record['approved_by_name']): ?>
                    <div class="approval-details">
                        <h6 class="section-title text-success"><i class="bi bi-check-circle me-2"></i>Approval Details</h6>
                        <div class="section-content bg-success-soft">
                            <div class="row">
                                <div class="col-md-6">
                                    <span class="detail-label">Approved By</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($record['approved_by_name']); ?></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="detail-label">Approved On</span>
                                    <span class="detail-value"><?php echo date('F d, Y H:i', strtotime($record['approved_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($record['status'] == 'REJECTED' && !empty($record['approval_notes'])): ?>
                    <div class="rejection-details">
                        <h6 class="section-title text-danger"><i class="bi bi-x-circle me-2"></i>Rejection Details</h6>
                        <div class="section-content bg-danger-soft">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($record['approval_notes'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Actions -->
            <div class="related-actions mt-4">
                <h6 class="mb-3">Related Actions</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="cdp.php" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul me-2"></i>View All Records
                    </a>
                    <a href="cdp.php?source=add" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle me-2"></i>Add New Record
                    </a>
                    <?php if ($record['status'] == 'PENDING'): ?>
                        <a href="cdp.php?source=edit&id=<?php echo $cdp_id; ?>" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Record
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.detail-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    height: 100%;
}

.detail-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
}

.section-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
}

.bg-success-soft {
    background: rgba(40, 167, 69, 0.1);
}

.bg-danger-soft {
    background: rgba(220, 53, 69, 0.1);
}

.file-icon {
    font-size: 1.5rem;
    color: #f1bf70;
}

.file-name {
    font-family: monospace;
    word-break: break-all;
}

.related-actions {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}
</style>