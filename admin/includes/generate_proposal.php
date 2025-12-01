<?php
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// Get client details
$sql = "SELECT c.*, cat.cat_title 
        FROM clients c 
        LEFT JOIN categories cat ON c.service_id = cat.cat_id 
        WHERE c.client_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

if(!$client) {
    echo "<div class='alert alert-danger'>Client not found.</div>";
    return;
}
?>

<!-- Main Content - Using standard class from style.css -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Page Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="clients.php" class="btn btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h2 class="h4 mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Generate Proposal</h2>
            </div>
            <div>
                <a href="clients.php?source=generate_proforma&client_id=<?php echo $client_id; ?>" class="btn btn-warning">
                    <i class="bi bi-receipt me-1"></i> Generate Proforma
                </a>
            </div>
        </div>

        <!-- Client Information Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Client Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Company</div>
                                        <div class="h6 mb-0"><?php echo htmlspecialchars($client['company_name']); ?></div>
                                    </div>
                                    <div class="text-primary">
                                        <i class="bi bi-building fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Contact Person</div>
                                        <div class="h6 mb-0"><?php echo htmlspecialchars($client['contact_name']); ?></div>
                                    </div>
                                    <div class="text-info">
                                        <i class="bi bi-person fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Service & Amount</div>
                                        <div class="h6 mb-0">
                                            <?php echo htmlspecialchars($client['cat_title']); ?><br>
                                            <span class="text-success">
                                                <?php echo number_format($client['service_total_fee'], 2) . ' ' . $client['payment_currency']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-success">
                                        <i class="bi bi-currency-dollar fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Contact Details</div>
                                        <div class="h6 mb-0">
                                            <?php echo htmlspecialchars($client['contact_email']); ?><br>
                                            <?php echo htmlspecialchars($client['contact_mobile']); ?>
                                        </div>
                                    </div>
                                    <div class="text-warning">
                                        <i class="bi bi-telephone fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Client Details -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Designation:</td>
                                <td><?php echo htmlspecialchars($client['contact_designation']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address:</td>
                                <td><?php echo htmlspecialchars($client['address']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Country:</td>
                                <td><?php echo htmlspecialchars($client['country']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Payment Term:</td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($client['payment_term']); ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Service Description:</td>
                                <td><?php echo htmlspecialchars($client['service_description']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($client['client_status']); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generation Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-magic me-2"></i>Generate New Proposal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-info-circle me-2"></i>Important Information</h6>
                                    <ul class="mb-0">
                                        <li>This will create a professionally formatted proposal PDF</li>
                                        <li>The proposal will include all client details and payment terms</li>
                                        <li>A new version number will be assigned automatically</li>
                                        <li>Client status will be updated to "Proposal Drafted"</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-file-earmark-pdf text-primary" style="font-size: 5rem;"></i>
                                </div>
                                <button id="generateBtn" class="btn btn-primary btn-lg w-100" onclick="generateProposalNow(<?php echo $client_id; ?>)">
                                    <i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal
                                </button>
                                <p class="text-muted small mt-2">Click to create the proposal document</p>
                            </div>
                        </div>
                        
                        <!-- Loading Spinner -->
                        <div class="row mt-4" id="loadingSpinner" style="display: none;">
                            <div class="col-md-12 text-center">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Generating...</span>
                                </div>
                                <h5 class="mt-3" id="loadingText">Generating Proposal...</h5>
                                <p class="text-muted">Please wait while we create your proposal document.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Document Display Area -->
        <div class="row mt-4" id="documentArea" style="display: none;">
            <div class="col-md-12">
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Proposal Generated Successfully</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <i class="bi bi-file-earmark-pdf text-success" style="font-size: 5rem;"></i>
                            </div>
                            <div class="col-md-6">
                                <h4 id="documentTitle" class="text-success"></h4>
                                <p id="documentInfo" class="text-muted mb-0"></p>
                                <div class="mt-3">
                                    <div class="alert alert-success">
                                        <i class="bi bi-check2-circle me-2"></i>
                                        <strong>Success!</strong> The proposal has been generated and saved.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="btn-group-vertical w-100" role="group">
                                    <button id="viewBtn" class="btn btn-outline-primary mb-2" onclick="viewDocument()">
                                        <i class="bi bi-eye me-2"></i>View Proposal
                                    </button>
                                    <button id="downloadBtn" class="btn btn-outline-success mb-2" onclick="downloadDocument()">
                                        <i class="bi bi-download me-2"></i>Download PDF
                                    </button>
                                    <button class="btn btn-outline-secondary mb-2" onclick="sendEmail()">
                                        <i class="bi bi-envelope me-2"></i>Send via Email
                                    </button>
                                    <button class="btn btn-outline-info" onclick="generateAnother()">
                                        <i class="bi bi-plus-circle me-2"></i>New Version
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Next Steps</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <div class="border rounded p-3">
                                                    <i class="bi bi-envelope fs-1 text-primary"></i>
                                                    <h6 class="mt-2">Send to Client</h6>
                                                    <p class="text-muted small">Email the proposal to the client for review</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded p-3">
                                                    <i class="bi bi-receipt fs-1 text-warning"></i>
                                                    <h6 class="mt-2">Create Proforma</h6>
                                                    <p class="text-muted small">Generate a proforma invoice for this proposal</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded p-3">
                                                    <i class="bi bi-people fs-1 text-info"></i>
                                                    <h6 class="mt-2">Back to Clients</h6>
                                                    <p class="text-muted small">Return to the client management dashboard</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <a href="clients.php?source=generate_proforma&client_id=<?php echo $client_id; ?>" class="btn btn-warning me-2">
                                                <i class="bi bi-receipt me-2"></i>Generate Proforma
                                            </a>
                                            <a href="clients.php" class="btn btn-secondary me-2">
                                                <i class="bi bi-people me-2"></i>All Clients
                                            </a>
                                            <a href="clients.php?source=edit_client&edit_client=<?php echo $client_id; ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-pencil me-2"></i>Edit Client
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
       <!-- Quick Stats (Optional) -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Document History</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        // Check if proposals table exists first
                        $table_check = $connection->query("SHOW TABLES LIKE 'proposals'");
                        if($table_check && $table_check->num_rows > 0) {
                            // Get existing proposals for this client
                            $proposals_sql = "SELECT proposal_id, proposal_ref, version, created_at, total_amount, file_path FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 5";
                            $proposals_stmt = $connection->prepare($proposals_sql);
                            
                            if($proposals_stmt) {
                                $proposals_stmt->bind_param("i", $client_id);
                                $proposals_stmt->execute();
                                $proposals_result = $proposals_stmt->get_result();
                                
                                if($proposals_result->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Reference</th>
                                                    <th>Version</th>
                                                    <th>Created Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($proposal = $proposals_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($proposal['proposal_ref']); ?></td>
                                                    <td><span class="badge bg-secondary">V<?php echo $proposal['version']; ?></span></td>
                                                    <td><?php echo date('M j, Y H:i', strtotime($proposal['created_at'])); ?></td>
                                                    <td><?php echo number_format($proposal['total_amount'], 2); ?></td>
                                                    <td>
                                                        <?php if(!empty($proposal['file_path']) && file_exists("../" . $proposal['file_path'])): ?>
                                                            <span class="badge bg-success">Available</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Missing</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="bi bi-file-earmark-text display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No previous proposals found for this client.</p>
                                    </div>
                                <?php endif;
                                $proposals_stmt->close();
                            } else {
                                echo '<div class="alert alert-warning">Could not prepare query: ' . $connection->error . '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-info">Proposals table not found in database.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let generatedFilePath = '';
let generatedDocumentRef = '';

function generateProposalNow(clientId) {
    // Show loading spinner
    $('#generateBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Generating...');
    $('#loadingSpinner').show();
    $('#loadingText').text('Generating Proposal...');
    
    $.ajax({
        url: 'generate_proposal_ajax.php',
        type: 'POST',
        data: { client_id: clientId },
        success: function(response) {
            try {
                var result = JSON.parse(response);
                if(result.success) {
                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    
                    // Show success message
                    generatedFilePath = result.file_path;
                    generatedDocumentRef = result.proposal_ref;
                    
                    // Update document info
                    $('#documentTitle').text('Proposal: ' + result.proposal_ref);
                    $('#documentInfo').html('Generated: ' + new Date().toLocaleString() + '<br>Version: ' + (result.version || '1.0'));
                    
                    // Show document area
                    $('#documentArea').show();
                    
                    // Scroll to document area
                    $('html, body').animate({
                        scrollTop: $('#documentArea').offset().top - 100
                    }, 500);
                    
                    // Update button text
                    $('#generateBtn').html('<i class="bi bi-check-circle me-2"></i>Generated Successfully');
                    
                } else {
                    $('#loadingText').text('Error: ' + result.message);
                    setTimeout(() => {
                        $('#loadingSpinner').hide();
                        $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                        alert('Error: ' + result.message);
                    }, 1000);
                }
            } catch (e) {
                $('#loadingText').text('Error parsing response');
                setTimeout(() => {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                    alert('Error parsing server response');
                }, 1000);
                console.error('Parse error:', e);
            }
        },
        error: function(xhr, status, error) {
            $('#loadingText').text('Request failed');
            setTimeout(() => {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                alert('Request failed: ' + error);
            }, 1000);
        }
    });
}

function viewDocument() {
    if(generatedFilePath) {
        window.open(generatedFilePath, '_blank');
    } else {
        alert('No document available to view.');
    }
}

function downloadDocument() {
    if(generatedFilePath) {
        // Create a temporary link and trigger download
        var link = document.createElement('a');
        link.href = generatedFilePath;
        link.download = generatedFilePath.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        alert('No document available to download.');
    }
}

function sendEmail() {
    if(generatedDocumentRef) {
        alert('Email sending feature will be implemented soon!\n\nProposal Reference: ' + generatedDocumentRef);
        // Future implementation:
        // window.location.href = 'send_email.php?type=proposal&client_id=' + <?php echo $client_id; ?> + '&ref=' + generatedDocumentRef;
    }
}

function generateAnother() {
    if(confirm('Generate a new version of this proposal?')) {
        location.reload();
    }
}


function generateProposalNow(clientId) {
    console.log("Generating proposal for client ID:", clientId);
    
    // Show loading spinner
    $('#generateBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Generating...');
    $('#loadingSpinner').show();
    $('#loadingText').text('Generating Proposal...');
    
    $.ajax({
        url: 'generate_proposal_ajax.php',
        type: 'POST',
        data: { client_id: clientId },
        success: function(response) {
            console.log("Response received:", response);
            
            try {
                var result = JSON.parse(response);
                console.log("Parsed result:", result);
                
                if(result.success) {
                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    
                    // Show success message
                    generatedFilePath = result.file_path;
                    generatedDocumentRef = result.proposal_ref;
                    
                    // Update document info
                    $('#documentTitle').text('Proposal: ' + result.proposal_ref);
                    $('#documentInfo').html('Generated: ' + new Date().toLocaleString() + '<br>Version: ' + (result.version || '1.0'));
                    
                    // Show document area
                    $('#documentArea').show();
                    
                    // Scroll to document area
                    $('html, body').animate({
                        scrollTop: $('#documentArea').offset().top - 100
                    }, 500);
                    
                    // Update button text
                    $('#generateBtn').html('<i class="bi bi-check-circle me-2"></i>Generated Successfully');
                    
                } else {
                    $('#loadingText').text('Error: ' + result.message);
                    setTimeout(() => {
                        $('#loadingSpinner').hide();
                        $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                        alert('Error: ' + result.message);
                    }, 1000);
                }
            } catch (e) {
                console.error("Parse error:", e, "Raw response:", response);
                $('#loadingText').text('Error parsing response');
                setTimeout(() => {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                    alert('Error parsing server response: ' + e.message);
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", status, error);
            $('#loadingText').text('Request failed');
            setTimeout(() => {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proposal');
                alert('Request failed: ' + error + '\nStatus: ' + status);
            }, 1000);
        }
    });
}
</script>