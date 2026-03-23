<?php
// Get categories for dropdown
$categories_query = "SELECT * FROM document_categories WHERE is_active = 1 ORDER BY category_name";
$categories_result = mysqli_query($connection, $categories_query);

// Get clients for specific document selection
$clients_query = "SELECT client_id, company_name, contact_name, contact_email 
                  FROM clients 
                  WHERE client_status NOT IN ('New Lead', 'Rejected by Manager', 'Rejected by CEO')
                  ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_document'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $doc_type = mysqli_real_escape_string($connection, $_POST['document_type']);
    $expires_at = !empty($_POST['expires_at']) ? mysqli_real_escape_string($connection, $_POST['expires_at']) : null;
    $requires_approval = isset($_POST['requires_approval']) ? 1 : 0;
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    $error = '';
    $success = '';
    
    // Handle file upload
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $upload_dir = '../uploads/client_documents/';
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['document_file']['name'];
        $file_tmp = $_FILES['document_file']['tmp_name'];
        $file_size = $_FILES['document_file']['size'];
        $file_type = $_FILES['document_file']['type'];
        
        // Validate file size (max 10MB)
        if ($file_size > 10 * 1024 * 1024) {
            $error = "File size exceeds 10MB limit.";
        } else {
            // Generate unique file name
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $unique_filename;
            
            // Allowed file types
            $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'txt', 'csv'];
            
            if (in_array($file_extension, $allowed_types)) {
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert document record
                    $expires_at_sql = $expires_at ? "'$expires_at'" : "NULL";
                    $query = "INSERT INTO client_documents (
                        document_title, document_description, document_type, 
                        file_name, file_original_name, file_path, file_size, 
                        file_mime_type, uploaded_by, expires_at, requires_approval
                    ) VALUES (
                        '$title', '$description', '$doc_type',
                        '$unique_filename', '$file_name', '$file_path', $file_size,
                        '$file_type', {$_SESSION['user_id']}, $expires_at_sql, $requires_approval
                    )";
                    
                    if (mysqli_query($connection, $query)) {
                        $document_id = mysqli_insert_id($connection);
                        
                        // Add categories
                        foreach ($category_ids as $cat_id) {
                            $cat_id = intval($cat_id);
                            $cat_query = "INSERT INTO document_category_mapping (document_id, category_id) VALUES ($document_id, $cat_id)";
                            mysqli_query($connection, $cat_query);
                        }
                        
                        // Handle client access for specific documents
                        if ($doc_type == 'specific' && isset($_POST['selected_clients'])) {
                            $selected_clients = $_POST['selected_clients'];
                            foreach ($selected_clients as $client_id) {
                                $client_id = intval($client_id);
                                $access_query = "INSERT INTO document_client_access (document_id, client_id, granted_by) 
                                                VALUES ($document_id, $client_id, {$_SESSION['user_id']})";
                                mysqli_query($connection, $access_query);
                            }
                        }
                        
                        $success = "Document uploaded successfully!";
                        
                        // Redirect after 2 seconds
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = '?action=list';
                            }, 2000);
                        </script>";
                    } else {
                        $error = "Database error: " . mysqli_error($connection);
                    }
                } else {
                    $error = "Failed to upload file. Please check directory permissions.";
                }
            } else {
                $error = "File type not allowed. Allowed types: " . implode(', ', $allowed_types);
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>
                    Upload New Document
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Title *</label>
                            <input type="text" class="form-control" name="title" required 
                                   placeholder="Enter document title">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Type *</label>
                            <select class="form-control" name="document_type" id="docType" required>
                                <option value="general">General (All clients can access)</option>
                                <option value="specific">Specific (Selected clients only)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Enter document description..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categories</label>
                            <select class="form-control" name="categories[]" multiple size="4">
                                <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                                    <option value="<?php echo $cat['category_id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name']); ?> 
                                        - <?php echo htmlspecialchars($cat['category_description']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple categories</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiration Date (Optional)</label>
                            <input type="date" class="form-control" name="expires_at">
                            <small class="text-muted">Leave empty for no expiration</small>
                        </div>
                        <div class="col-12 mb-3" id="clientSelection" style="display:none;">
                            <label class="form-label">Select Clients (for specific documents)</label>
                            <select class="form-control" name="selected_clients[]" multiple size="6">
                                <?php 
                                mysqli_data_seek($clients_result, 0);
                                while ($client = mysqli_fetch_assoc($clients_result)): 
                                ?>
                                    <option value="<?php echo $client['client_id']; ?>">
                                        <?php echo htmlspecialchars($client['company_name']); ?> 
                                        (<?php echo htmlspecialchars($client['contact_name']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple clients</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval">
                                <label class="form-check-label" for="requiresApproval">
                                    Requires Approval before publishing
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Upload File *</label>
                            <input type="file" class="form-control" name="document_file" required 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.csv">
                            <small class="text-muted">
                                Allowed: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, TXT, CSV (Max 10MB)
                            </small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="upload_document" class="btn btn-primary">
                            <i class="bi bi-cloud-upload"></i> Upload Document
                        </button>
                        <a href="?action=list" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Upload Tips -->
        <div class="pro-tip-card mt-4">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Upload Tips
                    </h6>
                    <p class="text-white-50 small mb-md-0">
                        • Use clear, descriptive titles for easy searching<br>
                        • Add relevant categories to organize documents better<br>
                        • Set expiration dates for time-sensitive documents<br>
                        • For specific documents, select clients who should have access
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-file-earmark-arrow-up display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle client selection based on document type
document.getElementById('docType').addEventListener('change', function() {
    const clientSelection = document.getElementById('clientSelection');
    clientSelection.style.display = this.value === 'specific' ? 'block' : 'none';
});
</script>