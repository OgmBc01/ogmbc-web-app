<?php
session_start();
include dirname(__DIR__) . '/includes/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
    exit();
}

if (isset($_GET['id'])) {
    $client_id = intval($_GET['id']);
    
    // Check database connection
    if (!$connection) {
        echo '<div class="alert alert-danger">Database connection failed.</div>';
        exit();
    }
    
    // Initialize variables to avoid undefined errors
    $stmt = $proposal_stmt = $proforma_stmt = $notes_stmt = $docs_stmt = null;
    
    // Get client details with service information
    $sql = "SELECT c.*, cat.cat_title, cat.cat_price, u.first_name, u.last_name 
            FROM clients c 
            LEFT JOIN categories cat ON c.service_id = cat.cat_id 
            LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
            WHERE c.client_id = ?";
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        echo '<div class="alert alert-danger">Query prepare failed: ' . $connection->error . '</div>';
        exit();
    }
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($client = $result->fetch_assoc()) {
        // Get latest proposal and proforma
        $proposal_sql = "SELECT * FROM proposals WHERE client_id = ? ORDER BY prepared_at DESC LIMIT 1";
        $proposal_stmt = $connection->prepare($proposal_sql);
        if ($proposal_stmt) {
            $proposal_stmt->bind_param("i", $client_id);
            $proposal_stmt->execute();
            $proposal_result = $proposal_stmt->get_result();
            $latest_proposal = $proposal_result->fetch_assoc();
        } else {
            $latest_proposal = null;
        }
        
        $proforma_sql = "SELECT * FROM proforma_invoices WHERE client_id = ? ORDER BY prepared_at DESC LIMIT 1";
        $proforma_stmt = $connection->prepare($proforma_sql);
        if ($proforma_stmt) {
            $proforma_stmt->bind_param("i", $client_id);
            $proforma_stmt->execute();
            $proforma_result = $proforma_stmt->get_result();
            $latest_proforma = $proforma_result->fetch_assoc();
        } else {
            $latest_proforma = null;
        }
        
        // Get client notes
        $notes_sql = "SELECT cn.*, u.first_name, u.last_name 
                     FROM client_notes cn 
                     LEFT JOIN users u ON cn.user_id = u.user_id 
                     WHERE cn.client_id = ? 
                     ORDER BY cn.created_at DESC 
                     LIMIT 10";
        $notes_stmt = $connection->prepare($notes_sql);
        if ($notes_stmt) {
            $notes_stmt->bind_param("i", $client_id);
            $notes_stmt->execute();
            $notes_result = $notes_stmt->get_result();
        } else {
            $notes_result = false;
        }
        
        // Get client documents: show all general documents and specific documents shared with this client
        $docs_sql = "SELECT cd.*, u.first_name, u.last_name
                    FROM client_documents cd
                    LEFT JOIN users u ON cd.uploaded_by = u.user_id
                    WHERE cd.document_type = 'general'
                       OR cd.document_id IN (SELECT document_id FROM document_client_access WHERE client_id = ?)
                    ORDER BY cd.uploaded_at DESC";
        $docs_stmt = $connection->prepare($docs_sql);
        if ($docs_stmt) {
            $docs_stmt->bind_param("i", $client_id);
            $docs_stmt->execute();
            $docs_result = $docs_stmt->get_result();
        } else {
            $docs_result = false;
        }
        ?>
        
        <div class="container-fluid">
            <div class="row">
                <!-- Client Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-building me-2"></i>Company Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Company Name:</th>
                                    <td><?php echo htmlspecialchars($client['company_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Trade License No:</th>
                                    <td><?php echo !empty($client['trade_license_no']) ? htmlspecialchars($client['trade_license_no']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <td><?php echo htmlspecialchars($client['country']); ?></td>
                                </tr>
                                <tr>
                                    <th>Jurisdiction:</th>
                                    <td><?php echo !empty($client['jurisdiction']) ? htmlspecialchars($client['jurisdiction']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Emirate/Zone:</th>
                                    <td><?php echo !empty($client['emirate_zone']) ? htmlspecialchars($client['emirate_zone']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Industry:</th>
                                    <td><?php echo !empty($client['industry']) ? htmlspecialchars($client['industry']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Business Activity:</th>
                                    <td><?php echo !empty($client['business_activity']) ? htmlspecialchars($client['business_activity']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td><?php echo !empty($client['address']) ? nl2br(htmlspecialchars($client['address'])) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Lead Source:</th>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $client['lead_source'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Created Date:</th>
                                    <td><?php echo date('M j, Y H:i', strtotime($client['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td><?php echo date('M j, Y H:i', strtotime($client['updated_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-person me-2"></i>Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Contact Name:</th>
                                    <td><?php echo htmlspecialchars($client['contact_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Designation:</th>
                                    <td><?php echo !empty($client['contact_designation']) ? htmlspecialchars($client['contact_designation']) : 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Mobile:</th>
                                    <td><?php echo htmlspecialchars($client['contact_mobile']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($client['contact_email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Assigned Sales:</th>
                                    <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge <?php 
                                        $status_class = [
                                            'New Lead' => 'bg-secondary',
                                            'Contacted' => 'bg-info',
                                            'Qualified' => 'bg-primary',
                                            'Proposal Drafted' => 'bg-warning',
                                            'Under Manager Review' => 'bg-warning text-dark',
                                            'Rejected by Manager' => 'bg-danger',
                                            'Approved by Manager' => 'bg-success',
                                            'Under CEO Review' => 'bg-warning text-dark',
                                            'Rejected by CEO' => 'bg-danger',
                                            'Final Proposal Ready' => 'bg-success',
                                            'Proposal Sent to Client' => 'bg-info',
                                            'Awaiting Client Action' => 'bg-warning',
                                            'Signed – Move to Finance' => 'bg-success'
                                        ];
                                        echo $status_class[$client['client_status']] ?? 'bg-secondary';
                                        ?>">
                                            <?php echo $client['client_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Service Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Service Type:</strong><br>
                                    <?php echo !empty($client['cat_title']) ? htmlspecialchars($client['cat_title']) : 'N/A'; ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Fee:</strong><br>
                                    <?php echo $client['payment_currency'] . ' ' . number_format($client['service_total_fee'], 2); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Payment Term:</strong><br>
                                    <?php echo htmlspecialchars($client['payment_term']); ?>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <strong>Contract Start Date:</strong><br>
                                    <?php echo !empty($client['contract_start_date']) ? date('M j, Y', strtotime($client['contract_start_date'])) : 'N/A'; ?>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <strong>Contract End Date:</strong><br>
                                    <?php echo !empty($client['contract_end_date']) ? date('M j, Y', strtotime($client['contract_end_date'])) : 'N/A'; ?>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <strong>Payment Currency:</strong><br>
                                    <?php echo htmlspecialchars($client['payment_currency']); ?>
                                </div>
                                <?php if (!empty($client['service_description'])): ?>
                                <div class="col-12 mt-3">
                                    <strong>Service Description:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($client['service_description'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Document Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-file-earmark me-2"></i>Document Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-6 mb-3">
                                    <h6>Proposal</h6>
                                    <?php if ($latest_proposal): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">Version: <?php echo $latest_proposal['version']; ?></small><br>
                                            <small class="text-muted">Prepared: <?php echo date('M j, Y H:i', strtotime($latest_proposal['prepared_at'])); ?></small>
                                        </div>
                                        <a href="<?php echo $latest_proposal['file_path']; ?>" target="_blank" class="btn btn-outline-primary btn-sm m-2">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="<?php echo $latest_proposal['file_path']; ?>" download class="btn btn-outline-success btn-sm m-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <a href="clients.php?source=review_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm m-2">
                                            <i class="bi bi-file-earmark-plus"></i> Review
                                        </a>
                                         <a href="clients.php?source=generate_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm m-2">
                                            <i class="bi bi-file-earmark-plus"></i> Regenerate
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">No proposal generated yet</p>
                                        <a href="clients.php?source=generate_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-file-earmark-plus"></i> Generate Proposal
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6>Proforma Invoice</h6>
                                    <?php if ($latest_proforma): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">Version: <?php echo $latest_proforma['version']; ?></small><br>
                                            <small class="text-muted">Prepared: <?php echo date('M j, Y H:i', strtotime($latest_proforma['prepared_at'])); ?></small>
                                        </div>
                                        <a href="<?php echo $latest_proforma['file_path']; ?>" target="_blank" class="btn btn-outline-primary btn-xs m-2">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="<?php echo $latest_proforma['file_path']; ?>" download class="btn btn-outline-success btn-sm m-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <a href="clients.php?source=review_proforma&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm m-2">
                                            <i class="bi bi-receipt"></i> Review
                                        </a>
                                        <a href="clients.php?source=generate_proforma&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm m-2">
                                            <i class="bi bi-receipt"></i> Regenerate
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">No proforma invoice generated yet</p>    
                                            <a href="clients.php?source=generate_proforma&client_id=<?php echo $client_id; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-receipt"></i> Generate Proforma
                                            </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Notes -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Recent Notes</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($notes_result && $notes_result->num_rows > 0): ?>
                                <div class="timeline">
                                    <?php while ($note = $notes_result->fetch_assoc()): ?>
                                        <div class="timeline-item mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($note['first_name'] . ' ' . $note['last_name']); ?></strong>
                                                    <small class="text-muted ms-2"><?php echo date('M j, Y H:i', strtotime($note['created_at'])); ?></small>
                                                    <span class="badge bg-light text-dark ms-2"><?php echo ucfirst(str_replace('_', ' ', $note['note_type'])); ?></span>
                                                </div>
                                            </div>
                                            <p class="mb-1 mt-1"><?php echo nl2br(htmlspecialchars($note['note_content'])); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">No notes yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header" style="background: var(--dark-blue); color: var(--gold);">
                            <h6 class="mb-0"><i class="bi bi-files me-2"></i>Client Documents</h6>
                        </div>
                        <div class="card-body">
                            <!-- Document Upload Form -->
                            <div class="mb-4">
                                <form id="documentUploadForm" enctype="multipart/form-data" onsubmit="return false;">
                                    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

                                    <div id="documentFieldsWrapper">
                                        <div class="document-field-set border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Document Title</label>
                                                    <input type="text" name="document_title[]" class="form-control" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Document Type</label>
                                                    <select name="document_type[]" class="form-control">
                                                        <option value="trade_license">Trade License</option>
                                                        <option value="bank_statement">Bank Statement</option>
                                                        <option value="signed_proposal">Signed Proposal</option>
                                                        <option value="signed_proforma">Signed Proforma</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Select File</label>
                                                    <input type="file" name="document_file[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger mt-3 removeFieldBtn" style="display:none;">
                                                <i class="bi bi-x-circle"></i> Remove
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" id="addDocumentField" class="btn btn-primary mb-3">
                                        <i class="bi bi-plus-circle"></i> Add Another Document
                                    </button>
                                    <br>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-upload me-1"></i> Upload Documents
                                    </button>
                                </form>
                            </div>

                            <!-- Documents List -->
                            <div class="table-responsive" id="clientDocumentsTable">
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
                                        <?php if ($docs_result && $docs_result->num_rows > 0): ?>
                                            <?php while ($doc = $docs_result->fetch_assoc()): ?>
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
                                                    <?php 
                                                    $file_path = $doc['file_path'];
                                                    $full_file_path = '../' . $file_path;
                                                    $file_exists = file_exists($full_file_path);

                                                    if (!empty($doc['file_path']) && $file_exists):
                                                    ?>
                                                        <a href="<?php echo $full_file_path; ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                        <a href="<?php echo $full_file_path; ?>" download class="btn btn-sm btn-outline-success mb-1">
                                                            <i class="bi bi-download"></i> Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">
                                                            <?php if (empty($doc['file_path'])): ?>
                                                                No file path
                                                            <?php else: ?>
                                                                File not found
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    </td>
                                                    <!-- Delete button removed; document deletion is managed in the document_management module -->
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
                </div>
            </div>
        </div>
        
        <?php
    } else {
        echo '<div class="alert alert-danger">Client not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}

// Close database connections safely
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
if (isset($proposal_stmt) && $proposal_stmt instanceof mysqli_stmt) {
    $proposal_stmt->close();
}
if (isset($proforma_stmt) && $proforma_stmt instanceof mysqli_stmt) {
    $proforma_stmt->close();
}
if (isset($notes_stmt) && $notes_stmt instanceof mysqli_stmt) {
    $notes_stmt->close();
}
if (isset($docs_stmt) && $docs_stmt instanceof mysqli_stmt) {
    $docs_stmt->close();
}
?>