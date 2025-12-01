<?php
// includes/generate_proforma.php
session_start();

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

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning">
                    <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Generate Proforma Invoice</h4>
                </div>
                <div class="card-body">
                    <!-- Client Info -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Client Information</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Company:</strong> <?php echo htmlspecialchars($client['company_name']); ?></p>
                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($client['contact_name']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($client['contact_email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($client['contact_mobile']); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Service:</strong> <?php echo htmlspecialchars($client['cat_title']); ?></p>
                                    <p><strong>Amount:</strong> <?php echo number_format($client['service_total_fee'], 2) . ' ' . $client['payment_currency']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Generation Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle me-2"></i>Generate New Proforma Invoice</h5>
                                <p>Click the button below to create a new proforma invoice document.</p>
                            </div>
                            
                            <button id="generateBtn" class="btn btn-warning btn-lg" onclick="generateProformaNow(<?php echo $client_id; ?>)">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Generate Proforma PDF
                            </button>
                        </div>
                    </div>
                    
                    <!-- Generated Document Display Area -->
                    <div class="row mt-5" id="documentArea" style="display: none;">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Proforma Generated Successfully</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="bi bi-file-earmark-pdf text-success" style="font-size: 4rem;"></i>
                                        <h4 class="mt-3" id="documentTitle"></h4>
                                        <p class="text-muted" id="documentInfo"></p>
                                    </div>
                                    
                                    <div class="btn-group" role="group">
                                        <button id="viewBtn" class="btn btn-outline-primary" onclick="viewDocument()">
                                            <i class="bi bi-eye me-2"></i>View Proforma
                                        </button>
                                        <button id="downloadBtn" class="btn btn-outline-success" onclick="downloadDocument()">
                                            <i class="bi bi-download me-2"></i>Download
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="sendEmail()">
                                            <i class="bi bi-envelope me-2"></i>Send via Email
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="clients.php" class="btn btn-secondary me-2">
                                        <i class="bi bi-arrow-left me-2"></i>Back to Clients
                                    </a>
                                    <a href="clients.php?source=generate_proposal&client_id=<?php echo $client_id; ?>" class="btn btn-primary">
                                        <i class="bi bi-file-earmark-plus me-2"></i>Generate Proposal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading Spinner -->
                    <div class="row mt-5" id="loadingSpinner" style="display: none;">
                        <div class="col-md-12 text-center">
                            <div class="spinner-border text-warning" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Generating...</span>
                            </div>
                            <h5 class="mt-3" id="loadingText">Generating Proforma Invoice...</h5>
                        </div>
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
    // Show loading spinner
    $('#generateBtn').prop('disabled', true);
    $('#loadingSpinner').show();
    $('#loadingText').text('Generating Proforma Invoice...');
    
    $.ajax({
        url: 'generate_proforma.php',
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
                    generatedDocumentRef = result.invoice_ref;
                    
                    // Update document info
                    $('#documentTitle').text('Proforma: ' + result.invoice_ref);
                    $('#documentInfo').html('Generated: ' + new Date().toLocaleString() + '<br>Version: ' + (result.version || '1.0'));
                    
                    // Show document area
                    $('#documentArea').show();
                    
                    // Hide generation button
                    $('#generateBtn').hide();
                    
                } else {
                    $('#loadingText').text('Error: ' + result.message);
                    setTimeout(() => {
                        $('#loadingSpinner').hide();
                        $('#generateBtn').prop('disabled', false);
                        alert('Error: ' + result.message);
                    }, 1000);
                }
            } catch (e) {
                $('#loadingText').text('Error parsing response');
                setTimeout(() => {
                    $('#loadingSpinner').hide();
                    $('#generateBtn').prop('disabled', false);
                    alert('Error parsing server response');
                }, 1000);
                console.error('Parse error:', e);
            }
        },
        error: function(xhr, status, error) {
            $('#loadingText').text('Request failed');
            setTimeout(() => {
                $('#loadingSpinner').hide();
                $('#generateBtn').prop('disabled', false);
                alert('Request failed: ' + error);
            }, 1000);
        }
    });
}

function viewDocument() {
    if(generatedFilePath) {
        window.open(generatedFilePath, '_blank');
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
    }
}

function sendEmail() {
    alert('Email sending feature will be implemented soon!');
}
</script>