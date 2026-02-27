<?php
include '../includes/database.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'ceo', 'admin'])) {
    echo '<div class="alert alert-danger">Unauthorized access.</div>';
    exit();
}

if (isset($_GET['id'])) {
    $client_id = intval($_GET['id']);
    $user_role = $_SESSION['user_role'];
    
    // Get client and latest proposal details
    $sql = "SELECT c.*, p.*, cat.cat_title, u.first_name, u.last_name 
            FROM clients c 
            LEFT JOIN proposals p ON c.client_id = p.client_id 
            LEFT JOIN categories cat ON c.service_id = cat.cat_id 
            LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
            WHERE c.client_id = ? 
            ORDER BY p.created_at DESC 
            LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($data = $result->fetch_assoc()) {
        ?>
        <div class="review-container">
            <!-- Client Summary -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Client Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Company:</strong> <?php echo htmlspecialchars($data['company_name']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Contact:</strong> <?php echo htmlspecialchars($data['contact_name']); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Service:</strong> <?php echo htmlspecialchars($data['cat_title']); ?>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <strong>Total Fee:</strong> <?php echo $data['payment_currency'] . ' ' . number_format($data['service_total_fee'], 2); ?>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <strong>Payment Term:</strong> <?php echo htmlspecialchars($data['payment_term']); ?>
                                </div>
                                <div class="col-md-4 mt-2">
                                    <strong>Sales Person:</strong> <?php echo htmlspecialchars($data['first_name'] . ' ' . $data['last_name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Proposal Preview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Proposal Preview</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($data['file_path'])): ?>
                                <div class="text-center mb-3">
                                    <a href="<?php echo $data['file_path']; ?>" target="_blank" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View Full Proposal
                                    </a>
                                </div>
                                <iframe src="<?php echo $data['file_path']; ?>" width="100%" height="600" style="border: 1px solid #ddd;"></iframe>
                            <?php else: ?>
                                <p class="text-muted text-center">No proposal file available for preview.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Approval Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0">Review Actions</h6>
                        </div>
                        <div class="card-body">
                            <form id="reviewForm" onsubmit="return submitReview(<?php echo $client_id; ?>, '<?php echo $user_role; ?>')">
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Approve Section -->
                                        <div class="approve-section">
                                            <h6 class="text-success">Approve Proposal</h6>
                                            <p class="text-muted small">Click approve if the proposal meets all requirements.</p>
                                            
                                            <?php if ($user_role === 'manager'): ?>
                                                <div class="mb-3" id="managerSignatureSection" style="display: none;">
                                                    <label class="form-label">Add Your Signature</label>
                                                    <div class="signature-pad-container">
                                                        <canvas id="managerSignaturePad" width="400" height="200" style="border: 1px solid #ddd; background: white;"></canvas>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature('manager')">Clear</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="managerSignatureData" name="manager_signature">
                                                </div>
                                            <?php elseif ($user_role === 'ceo'): ?>
                                                <div class="mb-3" id="ceoSignatureSection" style="display: none;">
                                                    <label class="form-label">Add Your Signature</label>
                                                    <div class="signature-pad-container">
                                                        <canvas id="ceoSignaturePad" width="400" height="200" style="border: 1px solid #ddd; background: white;"></canvas>
                                                        <div class="mt-2">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature('ceo')">Clear</button>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="ceoSignatureData" name="ceo_signature">
                                                </div>
                                                
                                                <div class="mb-3" id="stampSection" style="display: none;">
                                                    <label class="form-label">Apply Company Stamp</label>
                                                    <select id="companyStamp" name="company_stamp" class="form-control">
                                                        <option value="">Select Stamp</option>
                                                        <?php
                                                        $stamps_sql = "SELECT * FROM stamps WHERE is_active = 1";
                                                        $stamps_result = mysqli_query($connection, $stamps_sql);
                                                        while ($stamp = $stamps_result->fetch_assoc()) {
                                                            echo "<option value='{$stamp['stamp_id']}' data-image='{$stamp['stamp_data']}'>{$stamp['stamp_name']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-success" onclick="showApproveSections('<?php echo $user_role; ?>')">
                                                <i class="bi bi-check-circle me-1"></i> Approve Proposal
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <!-- Reject Section -->
                                        <div class="reject-section">
                                            <h6 class="text-danger">Reject Proposal</h6>
                                            <p class="text-muted small">Provide reason for rejection (required).</p>
                                            
                                            <div class="mb-3" id="rejectReasonSection" style="display: none;">
                                                <label class="form-label">Rejection Reason</label>
                                                <textarea id="rejectReason" name="reject_reason" class="form-control" rows="3" placeholder="Please specify the reasons for rejection..."></textarea>
                                            </div>
                                            
                                            <button type="button" class="btn btn-danger" onclick="showRejectSection()">
                                                <i class="bi bi-x-circle me-1"></i> Reject Proposal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Submit Buttons (initially hidden) -->
                                <div class="row mt-4" id="submitButtons" style="display: none;">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-success me-2" id="approveBtn" style="display: none;">
                                            <i class="bi bi-check-circle me-1"></i> Confirm Approval
                                        </button>
                                        <button type="button" class="btn btn-danger" id="rejectBtn" style="display: none;" onclick="confirmRejection(<?php echo $client_id; ?>, '<?php echo $user_role; ?>')">
                                            <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="hideAllSections()">
                                            <i class="bi bi-arrow-left me-1"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let managerSignaturePad, ceoSignaturePad;

        function showApproveSections(role) {
            // Hide reject section
            document.getElementById('rejectReasonSection').style.display = 'none';
            document.getElementById('rejectBtn').style.display = 'none';
            
            // Show appropriate approve sections
            if (role === 'manager') {
                document.getElementById('managerSignatureSection').style.display = 'block';
                initializeSignaturePad('manager');
            } else if (role === 'ceo') {
                document.getElementById('ceoSignatureSection').style.display = 'block';
                document.getElementById('stampSection').style.display = 'block';
                initializeSignaturePad('ceo');
            }
            
            // Show submit buttons
            document.getElementById('submitButtons').style.display = 'block';
            document.getElementById('approveBtn').style.display = 'inline-block';
        }

        function showRejectSection() {
            // Hide approve sections
            document.getElementById('managerSignatureSection').style.display = 'none';
            document.getElementById('ceoSignatureSection').style.display = 'none';
            document.getElementById('stampSection').style.display = 'none';
            document.getElementById('approveBtn').style.display = 'none';
            
            // Show reject section
            document.getElementById('rejectReasonSection').style.display = 'block';
            document.getElementById('submitButtons').style.display = 'block';
            document.getElementById('rejectBtn').style.display = 'inline-block';
        }

        function hideAllSections() {
            document.getElementById('managerSignatureSection').style.display = 'none';
            document.getElementById('ceoSignatureSection').style.display = 'none';
            document.getElementById('stampSection').style.display = 'none';
            document.getElementById('rejectReasonSection').style.display = 'none';
            document.getElementById('submitButtons').style.display = 'none';
            document.getElementById('approveBtn').style.display = 'none';
            document.getElementById('rejectBtn').style.display = 'none';
        }

        function initializeSignaturePad(role) {
            const canvas = document.getElementById(role + 'SignaturePad');
            if (canvas) {
                if (role === 'manager') {
                    managerSignaturePad = new SignaturePad(canvas);
                } else {
                    ceoSignaturePad = new SignaturePad(canvas);
                }
            }
        }

        function clearSignature(role) {
            if (role === 'manager' && managerSignaturePad) {
                managerSignaturePad.clear();
                document.getElementById('managerSignatureData').value = '';
            } else if (role === 'ceo' && ceoSignaturePad) {
                ceoSignaturePad.clear();
                document.getElementById('ceoSignatureData').value = '';
            }
        }

        function submitReview(clientId, userRole) {
            // For approval - collect signature data
            if (document.getElementById('approveBtn').style.display !== 'none') {
                if (userRole === 'manager' && managerSignaturePad && !managerSignaturePad.isEmpty()) {
                    document.getElementById('managerSignatureData').value = managerSignaturePad.toDataURL();
                } else if (userRole === 'ceo' && ceoSignaturePad && !ceoSignaturePad.isEmpty()) {
                    document.getElementById('ceoSignatureData').value = ceoSignaturePad.toDataURL();
                }
                
                // Submit approval
                $.ajax({
                    url: 'approve_proposal.php',
                    type: 'POST',
                    data: {
                        client_id: clientId,
                        user_role: userRole,
                        manager_signature: document.getElementById('managerSignatureData').value,
                        ceo_signature: document.getElementById('ceoSignatureData').value,
                        company_stamp: document.getElementById('companyStamp') ? document.getElementById('companyStamp').value : ''
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showAlert('Proposal approved successfully!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert('Error: ' + result.message, 'error');
                        }
                    }
                });
            }
            return false;
        }

        function confirmRejection(clientId, userRole) {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
                showAlert('Please provide a rejection reason', 'error');
                return;
            }

            if (confirm('Are you sure you want to reject this proposal?')) {
                $.ajax({
                    url: 'reject_proposal.php',
                    type: 'POST',
                    data: {
                        client_id: clientId,
                        user_role: userRole,
                        reject_reason: reason
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showAlert('Proposal rejected successfully!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showAlert('Error: ' + result.message, 'error');
                        }
                    }
                });
            }
        }
        </script>
        <?php
    } else {
        echo '<div class="alert alert-danger">Client or proposal not found.</div>';
    }
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>