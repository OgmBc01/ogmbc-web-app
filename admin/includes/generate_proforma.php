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
                <h2 class="h4 mb-0"><i class="bi bi-receipt me-2"></i>Generate Proforma Invoice</h2>
            </div>
            <div>
                <a href="clients.php?source=generate_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary">
                    <i class="bi bi-file-earmark-plus me-1"></i> Generate Proposal
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
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Company</div>
                                        <div class="h6 mb-0"><?php echo htmlspecialchars($client['company_name']); ?></div>
                                    </div>
                                    <div class="text-warning">
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
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-muted small">Contact Details</div>
                                        <div class="h6 mb-0">
                                            <?php echo htmlspecialchars($client['contact_email']); ?><br>
                                            <?php echo htmlspecialchars($client['contact_mobile']); ?>
                                        </div>
                                    </div>
                                    <div class="text-primary">
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
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-magic me-2"></i>Generate New Proforma Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="alert alert-info" style="animation: none;">
                                    <h6><i class="bi bi-info-circle me-2"></i>Important Information</h6>
                                    <ul class="mb-0">
                                        <li>This will create a professionally formatted proforma invoice PDF</li>
                                        <li>The proforma will include all client details, payment terms, and bank details</li>
                                        <li>A new version number will be assigned automatically</li>
                                        <li>Proforma is valid for 30 days from date of issue</li>
                                        <li>Includes detailed payment schedule and terms & conditions</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-file-earmark-pdf text-warning" style="font-size: 5rem;"></i>
                                </div>
                                <button id="generateBtn" class="btn btn-warning btn-lg w-100" onclick="generateProformaNow(<?php echo $client_id; ?>)">
                                    <i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma
                                </button>
                                <p class="text-muted small mt-2">Click to create the proforma invoice document</p>
                            </div>
                        </div>
                        
                        <!-- Loading Spinner -->
                        <div class="row mt-4" id="loadingSpinner" style="display: none;">
                            <div class="col-md-12 text-center">
                                <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Generating...</span>
                                </div>
                                <h5 class="mt-3" id="loadingText">Generating Proforma Invoice...</h5>
                                <p class="text-muted">Please wait while we create your proforma invoice document.</p>
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
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Proforma Invoice Generated Successfully</h5>
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
                                        <strong>Success!</strong> The proforma invoice has been generated and saved.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="btn-group-vertical w-100" role="group">
                                    <button id="viewBtn" class="btn btn-outline-primary mb-2" onclick="viewDocument()">
                                        <i class="bi bi-eye me-2"></i>View Proforma
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
                                                    <p class="text-muted small">Email the proforma invoice to the client for payment</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded p-3">
                                                    <i class="bi bi-file-earmark-plus fs-1 text-info"></i>
                                                    <h6 class="mt-2">Create Proposal</h6>
                                                    <p class="text-muted small">Generate a formal proposal for this client</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border rounded p-3">
                                                    <i class="bi bi-people fs-1 text-secondary"></i>
                                                    <h6 class="mt-2">Back to Clients</h6>
                                                    <p class="text-muted small">Return to the client management dashboard</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <a href="clients.php?source=generate_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary me-2">
                                                <i class="bi bi-file-earmark-plus me-2"></i>Generate Proposal
                                            </a>
                                            <a href="clients.php" class="btn btn-secondary me-2">
                                                <i class="bi bi-people me-2"></i>All Clients
                                            </a>
                                            <a href="clients.php?source=edit_client&edit_client=<?php echo $client_id; ?>" class="btn btn-outline-warning">
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
                        // Check if proforma_invoices table exists first
                        $table_check = $connection->query("SHOW TABLES LIKE 'proforma_invoices'");
                        if($table_check && $table_check->num_rows > 0) {
                            // Get existing proformas for this client - CORRECTED COLUMN NAMES
                            $proformas_sql = "SELECT invoice_id, invoice_ref, version, prepared_at, total_amount, file_path FROM proforma_invoices WHERE client_id = ? ORDER BY prepared_at DESC LIMIT 5";
                            $proformas_stmt = $connection->prepare($proformas_sql);
                            
                            if($proformas_stmt) {
                                $proformas_stmt->bind_param("i", $client_id);
                                $proformas_stmt->execute();
                                $proformas_result = $proformas_stmt->get_result();
                                
                                if($proformas_result->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Reference</th>
                                                    <th>Version</th>
                                                    <th>Prepared Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($proforma = $proformas_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($proforma['invoice_ref']); ?></td>
                                                    <td><span class="badge bg-secondary">V<?php echo $proforma['version']; ?></span></td>
                                                    <td><?php echo date('M j, Y H:i', strtotime($proforma['prepared_at'])); ?></td>
                                                    <td><?php echo number_format($proforma['total_amount'], 2); ?></td>
                                                    <td>
                                                        <?php if(!empty($proforma['file_path']) && file_exists($proforma['file_path'])): ?>
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
                                        <p class="text-muted mt-3">No previous proforma invoices found for this client.</p>
                                    </div>
                                <?php endif;
                                $proformas_stmt->close();
                            } else {
                                echo '<div class="alert alert-warning">Could not prepare query: ' . $connection->error . '</div>';
                            }
                        } else {
                            echo '<div class="alert alert-info">Proforma invoices table not found in database.</div>';
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

function generateProformaNow(clientId) {
    console.log("Generating proforma for client ID:", clientId);
    
    // Show loading spinner
    $('#generateBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Generating...');
    $('#loadingSpinner').show();
    $('#loadingText').text('Generating Proforma Invoice...');
    
    $.ajax({
        url: 'generate_proforma_ajax.php',
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
                    generatedDocumentRef = result.invoice_ref;
                    
                    // Update document info
                    $('#documentTitle').text('Proforma: ' + result.invoice_ref);
                    $('#documentInfo').html('Generated: ' + new Date().toLocaleString() + '<br>Version: ' + (result.version || '1.0') + '<br>Valid Until: ' + result.valid_until);
                    
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
                        $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                        alert('Error: ' + result.message);
                    }, 1000);
                }
            } catch (e) {
                console.error("Parse error:", e, "Raw response:", response);
                $('#loadingText').text('Error parsing response');
                setTimeout(() => {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                    alert('Error parsing server response: ' + e.message);
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", status, error);
            $('#loadingText').text('Request failed');
            setTimeout(() => {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                alert('Request failed: ' + error + '\nStatus: ' + status);
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
        alert('Email sending feature will be implemented soon!\n\nProforma Reference: ' + generatedDocumentRef);
    }
}

function generateAnother() {
    if(confirm('Generate a new version of this proforma invoice?')) {
        location.reload();
    }
}


function generateProformaNow(clientId) {
    console.log("Generating proforma for client ID:", clientId);
    
    // Show loading spinner
    $('#generateBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Generating...');
    $('#loadingSpinner').show();
    $('#loadingText').text('Generating Proforma Invoice...');
    
    $.ajax({
        url: 'generate_proforma_ajax.php',
        type: 'POST',
        data: { client_id: clientId },
        success: function(response) {
            console.log("Raw response received:", response);
            console.log("Response type:", typeof response);
            console.log("Response length:", response.length);
            
            try {
                var result = JSON.parse(response);
                console.log("Parsed result:", result);
                
                if(result.success) {
                    // Hide loading spinner
                    $('#loadingSpinner').hide();
                    
                    // Show success message
                    generatedFilePath = result.file_path;
                    generatedDocumentRef = result.invoice_ref;
                    
                    // Update document info
                    $('#documentTitle').text('Proforma: ' + result.invoice_ref);
                    $('#documentInfo').html('Generated: ' + new Date().toLocaleString() + '<br>Version: ' + (result.version || '1.0') + '<br>Valid Until: ' + result.valid_until);
                    
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
                        $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                        alert('Error: ' + result.message);
                    }, 1000);
                }
            } catch (e) {
                console.error("Parse error:", e, "Raw response:", response);
                $('#loadingText').text('Error parsing response');
                setTimeout(() => {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                    alert('Error parsing server response: ' + e.message + '\n\nResponse preview: ' + response.substring(0, 200));
                }, 1000);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX error:", status, error, xhr.responseText);
            $('#loadingText').text('Request failed');
            setTimeout(() => {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false).html('<i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma');
                alert('Request failed: ' + error + '\nStatus: ' + status + '\n\nResponse: ' + xhr.responseText.substring(0, 500));
            }, 1000);
        }
    });
}
</script>