<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
    return;
}

$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($client_id > 0) {
    // Get client documents
    $sql = "SELECT cd.*, u.first_name, u.last_name 
            FROM client_documents cd 
            LEFT JOIN users u ON cd.uploaded_by = u.user_id 
            WHERE cd.client_id = ? 
            ORDER BY cd.uploaded_at DESC";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-files me-2"></i>Client Documents</h6>
        </div>
        <div class="card-body">
            <!-- Document Upload Form -->
            <div class="mb-4">
                <form id="documentUploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="document_title" class="form-label">Document Title</label>
                                <input type="text" id="document_title" name="document_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="document_type" class="form-label">Document Type</label>
                                <select id="document_type" name="document_type" class="form-control">
                                    <option value="trade_license">Trade License</option>
                                    <option value="bank_statement">Bank Statement</option>
                                    <option value="signed_proposal">Signed Proposal</option>
                                    <option value="signed_proforma">Signed Proforma</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="document_file" class="form-label">Select File</label>
                                <input type="file" id="document_file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i> Upload Document
                    </button>
                </form>
            </div>

            <!-- Documents List -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Document Title</th>
                            <th>Type</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($doc = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['document_title']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></td>
                                    <td><?php echo date('M j, Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo $doc['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="<?php echo $doc['file_path']; ?>" download class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDocument(<?php echo $doc['doc_id']; ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-folder-x display-4"></i>
                                    <p class="mt-2">No documents uploaded yet</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // Handle document upload
    $('#documentUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: 'upload_document.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if(result.success) {
                        showAlert('Document uploaded successfully!', 'success');
                        $('#documentUploadForm')[0].reset();
                        // Reload the documents section
                        loadClientDocuments(<?php echo $client_id; ?>);
                    } else {
                        showAlert('Error uploading document: ' + result.message, 'error');
                    }
                } catch (e) {
                    showAlert('Error parsing response', 'error');
                }
            },
            error: function(xhr, status, error) {
                showAlert('Error uploading document: ' + error, 'error');
            }
        });
    });

    function deleteDocument(docId) {
        if (confirm('Are you sure you want to delete this document?')) {
            $.ajax({
                url: 'delete_document.php',
                type: 'POST',
                data: { doc_id: docId },
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if(result.success) {
                            showAlert('Document deleted successfully!', 'success');
                            // Reload the documents section
                            loadClientDocuments(<?php echo $client_id; ?>);
                        } else {
                            showAlert('Error deleting document: ' + result.message, 'error');
                        }
                    } catch (e) {
                        showAlert('Error parsing response', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('Error deleting document: ' + error, 'error');
                }
            });
        }
    }

    function loadClientDocuments(clientId) {
        $.ajax({
            url: 'client_documents.php',
            type: 'GET',
            data: { id: clientId },
            success: function(response) {
                $('#documentsSection').html(response);
            }
        });
    }
    </script>
    <?php
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Invalid client ID.</div>';
}
?>