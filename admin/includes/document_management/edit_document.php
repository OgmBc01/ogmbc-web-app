<?php

$document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($document_id == 0) {
    header("Location: ?action=list");
    exit();
}

// Get document details
$doc_query = "SELECT * FROM client_documents WHERE document_id = $document_id";
$doc_result = mysqli_query($connection, $doc_query);
$document = mysqli_fetch_assoc($doc_result);

if (!$document) {
    header("Location: ?action=list");
    exit();
}

// Get categories
$categories_query = "SELECT * FROM document_categories WHERE is_active = 1 ORDER BY category_name";
$categories_result = mysqli_query($connection, $categories_query);

// Get document categories
$doc_cats_query = "SELECT category_id FROM document_category_mapping WHERE document_id = $document_id";
$doc_cats_result = mysqli_query($connection, $doc_cats_query);
$doc_categories = [];
while ($row = mysqli_fetch_assoc($doc_cats_result)) {
    $doc_categories[] = $row['category_id'];
}

// Get clients for specific document
$clients_query = "SELECT client_id, company_name, contact_name, contact_email 
                  FROM clients 
                  WHERE client_status NOT IN ('New Lead', 'Rejected by Manager', 'Rejected by CEO')
                  ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);

// Get client access for specific document
$client_access = [];
if ($document['document_type'] == 'specific') {
    $access_query = "SELECT client_id FROM document_client_access WHERE document_id = $document_id";
    $access_result = mysqli_query($connection, $access_query);
    while ($row = mysqli_fetch_assoc($access_result)) {
        $client_access[] = $row['client_id'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_document'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $doc_type = mysqli_real_escape_string($connection, $_POST['document_type']);
    $expires_at = !empty($_POST['expires_at']) ? "'" . mysqli_real_escape_string($connection, $_POST['expires_at']) . "'" : "NULL";
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $requires_approval = isset($_POST['requires_approval']) ? 1 : 0;
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    $query = "UPDATE client_documents SET 
              document_title = '$title',
              document_description = '$description',
              document_type = '$doc_type',
              expires_at = $expires_at,
              is_active = $is_active,
              requires_approval = $requires_approval,
              updated_at = NOW()
              WHERE document_id = $document_id";
    
    if (mysqli_query($connection, $query)) {
        // Update categories
        mysqli_query($connection, "DELETE FROM document_category_mapping WHERE document_id = $document_id");
        foreach ($category_ids as $cat_id) {
            $cat_id = intval($cat_id);
            $cat_query = "INSERT INTO document_category_mapping (document_id, category_id) VALUES ($document_id, $cat_id)";
            mysqli_query($connection, $cat_query);
        }
        
        // Update client access for specific documents
        if ($doc_type == 'specific') {
            // Remove all existing access
            mysqli_query($connection, "DELETE FROM document_client_access WHERE document_id = $document_id");
            
            // Add new access if clients selected
            if (isset($_POST['selected_clients'])) {
                $selected_clients = $_POST['selected_clients'];
                foreach ($selected_clients as $client_id) {
                    $client_id = intval($client_id);
                    $access_query = "INSERT INTO document_client_access (document_id, client_id, granted_by) 
                                    VALUES ($document_id, $client_id, {$_SESSION['user_id']})";
                    mysqli_query($connection, $access_query);
                }
            }
        } else {
            // Remove any existing access for general documents
            mysqli_query($connection, "DELETE FROM document_client_access WHERE document_id = $document_id");
        }
        
        $success = "Document updated successfully!";
        echo "<script>
            setTimeout(function() {
                window.location.href = '?action=list';
            }, 2000);
        </script>";
    } else {
        $error = "Database error: " . mysqli_error($connection);
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
                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                    Edit Document: <?php echo htmlspecialchars($document['document_title']); ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Title *</label>
                            <input type="text" class="form-control" name="title" 
                                   value="<?php echo htmlspecialchars($document['document_title']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Type *</label>
                            <select class="form-control" name="document_type" id="docType" required>
                                <option value="general" <?php echo $document['document_type'] == 'general' ? 'selected' : ''; ?>>
                                    General (All clients can access)
                                </option>
                                <option value="specific" <?php echo $document['document_type'] == 'specific' ? 'selected' : ''; ?>>
                                    Specific (Selected clients only)
                                </option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($document['document_description']); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categories</label>
                            <select class="form-control" name="categories[]" multiple size="4">
                                <?php 
                                mysqli_data_seek($categories_result, 0);
                                while ($cat = mysqli_fetch_assoc($categories_result)): 
                                    $selected = in_array($cat['category_id'], $doc_categories) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?> 
                                        - <?php echo htmlspecialchars($cat['category_description']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple categories</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" name="expires_at" 
                                   value="<?php echo $document['expires_at']; ?>">
                            <small class="text-muted">Leave empty for no expiration</small>
                        </div>
                        <div class="col-12 mb-3" id="clientSelection" style="<?php echo $document['document_type'] == 'specific' ? 'display:block;' : 'display:none;'; ?>">
                            <label class="form-label">Select Clients (for specific documents)</label>
                            <select class="form-control" name="selected_clients[]" multiple size="6">
                                <?php 
                                mysqli_data_seek($clients_result, 0);
                                while ($client = mysqli_fetch_assoc($clients_result)): 
                                    $selected = in_array($client['client_id'], $client_access) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $client['client_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($client['company_name']); ?> 
                                        (<?php echo htmlspecialchars($client['contact_name']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl to select multiple clients</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                       <?php echo $document['is_active'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isActive">
                                    Active (visible to clients)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval" 
                                       <?php echo $document['requires_approval'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="requiresApproval">
                                    Requires Approval
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="edit_document" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                        <a href="?action=list" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('docType').addEventListener('change', function() {
    const clientSelection = document.getElementById('clientSelection');
    clientSelection.style.display = this.value === 'specific' ? 'block' : 'none';
});
</script>