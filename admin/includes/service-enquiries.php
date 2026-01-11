<?php
// service-enquiries.php

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    $enquiry_id = intval($_POST['enquiry_id'] ?? 0);
    $new_status = mysqli_real_escape_string($connection, $_POST['status'] ?? '');
    
    $allowed_statuses = ['new', 'contacted', 'dropped', 'qualified'];
    
    if ($enquiry_id > 0 && in_array($new_status, $allowed_statuses)) {
        // Update status and mark as read
        $query = "UPDATE enquiries SET 
                  status = '$new_status',
                  is_read = 1
                  WHERE enquiry_id = $enquiry_id";
        
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($connection)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
    exit;
}

// Fetch all enquiries - ONLY USE COLUMNS THAT EXIST
$query = "SELECT 
            enquiry_id,
            name,
            email,
            contact,
            service,
            sub_service,
            message,
            submitted_at,
            is_read,
            status
          FROM enquiries 
          ORDER BY submitted_at DESC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>  
    <style>
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-new { background-color: #e3f2fd; color: #1976d2; }
        .status-contacted { background-color: #e8f5e9; color: #2e7d32; }
        .status-dropped { background-color: #ffebee; color: #c62828; }
        .status-qualified { background-color: #fff3e0; color: #ef6c00; }
        .enquiry-unread { background-color: #f8f9fa; font-weight: 500; }
        .enquiry-read { background-color: #ffffff; }
        .action-dropdown .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.02); }
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
        }
        .modal-message {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0d6efd;
        }
    </style>
</head>
<body>

    <div class="main-content" id="mainContent">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Enquiries Management</h1>
                <div>
                    <button class="btn btn-outline-secondary" onclick="refreshEnquiries()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-envelope-paper me-2"></i>All Enquiries</h5>
                    <p class="text-light mb-0 small">Total: <?php echo mysqli_num_rows($result); ?> enquiries</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="enquiriesTable">
                            <thead>
                                <tr class="table-dark">
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Service</th>
                                    <th>Message</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php $serial = 1; while ($enquiry = mysqli_fetch_assoc($result)): ?>
                                        <tr data-id="<?php echo $enquiry['enquiry_id']; ?>" 
                                            class="<?php echo $enquiry['is_read'] == 0 ? 'enquiry-unread' : 'enquiry-read'; ?>">
                                            <td class="fw-bold"><?php echo $serial++; ?></td>
                                            <td class="text-muted"><?php echo $enquiry['enquiry_id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($enquiry['name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($enquiry['email']); ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($enquiry['contact'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($enquiry['contact']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($enquiry['contact']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($enquiry['service']); ?></span>
                                                <?php if (!empty($enquiry['sub_service'])): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($enquiry['sub_service']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="message-preview" 
                                                     data-bs-toggle="tooltip" 
                                                     title="<?php echo htmlspecialchars($enquiry['message']); ?>">
                                                    <?php echo strlen($enquiry['message']) > 50 ? 
                                                        htmlspecialchars(substr($enquiry['message'], 0, 50)) . '...' : 
                                                        htmlspecialchars($enquiry['message']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($enquiry['submitted_at'])); ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($enquiry['submitted_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $enquiry['status']; ?>">
                                                    <?php echo ucfirst($enquiry['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown action-dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                            type="button" 
                                                            data-bs-toggle="dropdown"
                                                            data-enquiry-id="<?php echo $enquiry['enquiry_id']; ?>"
                                                            data-current-status="<?php echo $enquiry['status']; ?>">
                                                        <i class="bi bi-gear"></i> Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item view-enquiry" href="#" 
                                                               data-id="<?php echo $enquiry['enquiry_id']; ?>">
                                                                <i class="bi bi-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item update-status-btn" href="#" 
                                                               data-id="<?php echo $enquiry['enquiry_id']; ?>" 
                                                               data-current-status="<?php echo $enquiry['status']; ?>">
                                                                <i class="bi bi-pencil-square me-2"></i>Update Status
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item send-email" 
                                                               href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>?subject=Re: Your enquiry about <?php echo htmlspecialchars($enquiry['service']); ?>">
                                                                <i class="bi bi-envelope me-2"></i>Send Email
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-envelope display-4 d-block mb-3"></i>
                                                <h5>No enquiries found</h5>
                                                <p>Enquiries will appear here when users submit contact forms.</p>
                                            </div>
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

    <!-- View Enquiry Details Modal -->
    <div class="modal fade" id="viewEnquiryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enquiry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="enquiryDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Enquiry Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="updateEnquiryId" name="enquiry_id">
                        
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Status</label>
                            <select class="form-select" id="statusSelect" name="status" required>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="dropped">Dropped</option>
                                <option value="qualified">Qualified</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm d-none" id="statusSpinner"></span>
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>
                    <span id="toastMessage">Status updated successfully!</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#enquiriesTable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                responsive: true,
                language: {
                    search: "Search enquiries:",
                    lengthMenu: "Show _MENU_ enquiries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ enquiries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // View enquiry details
        document.querySelectorAll('.view-enquiry').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const enquiryId = this.dataset.id;
                
                // Direct database query for details
                const formData = new FormData();
                formData.append('action', 'get_enquiry_details');
                formData.append('enquiry_id', enquiryId);
                
                fetch('service-enquiries.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const enquiry = data.enquiry;
                        
                        let detailsHtml = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${enquiry.name}</p>
                                    <p><strong>Email:</strong> <a href="mailto:${enquiry.email}">${enquiry.email}</a></p>
                                    <p><strong>Contact:</strong> ${enquiry.contact || 'N/A'}</p>
                                    <p><strong>Service:</strong> <span class="badge bg-primary">${enquiry.service}</span></p>
                                    ${enquiry.sub_service ? `<p><strong>Sub Service:</strong> ${enquiry.sub_service}</p>` : ''}
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <span class="status-badge status-${enquiry.status}">${enquiry.status}</span></p>
                                    <p><strong>Submitted:</strong> ${new Date(enquiry.submitted_at).toLocaleString()}</p>
                                    <p><strong>Read Status:</strong> ${enquiry.is_read == 1 ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning">Unread</span>'}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Message:</h6>
                                    <div class="modal-message">
                                        ${enquiry.message.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                            </div>`;
                        
                        document.getElementById('enquiryDetailsContent').innerHTML = detailsHtml;
                        const modal = new bootstrap.Modal(document.getElementById('viewEnquiryModal'));
                        modal.show();
                        
                        // Mark as read after viewing
                        if (enquiry.is_read == 0) {
                            const markReadForm = new FormData();
                            markReadForm.append('action', 'mark_as_read');
                            markReadForm.append('enquiry_id', enquiryId);
                            
                            fetch('service-enquiries.php', {
                                method: 'POST',
                                body: markReadForm
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const row = document.querySelector(`tr[data-id="${enquiryId}"]`);
                                    if (row) {
                                        row.classList.remove('enquiry-unread');
                                        row.classList.add('enquiry-read');
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading enquiry details');
                });
            });
        });

        // Update status - show modal
        document.querySelectorAll('.update-status-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const enquiryId = this.dataset.id;
                const currentStatus = this.dataset.currentStatus;
                
                document.getElementById('updateEnquiryId').value = enquiryId;
                document.getElementById('statusSelect').value = currentStatus;
                
                const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
                modal.show();
            });
        });

        // Handle status form submission
        document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_status');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('statusSpinner');
            
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            
            fetch('service-enquiries.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal'));
                    modal.hide();
                    
                    // Update UI without reload
                    const enquiryId = formData.get('enquiry_id');
                    const newStatus = formData.get('status');
                    const row = document.querySelector(`tr[data-id="${enquiryId}"]`);
                    
                    if (row) {
                        // Update status badge
                        const statusCell = row.querySelector('.status-badge');
                        statusCell.className = `status-badge status-${newStatus}`;
                        statusCell.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        
                        // Update button data attributes
                        const dropdownBtn = row.querySelector('.dropdown-toggle');
                        const updateBtn = row.querySelector('.update-status-btn');
                        
                        if (dropdownBtn) {
                            dropdownBtn.dataset.currentStatus = newStatus;
                        }
                        if (updateBtn) {
                            updateBtn.dataset.currentStatus = newStatus;
                        }
                        
                        // Mark as read
                        row.classList.remove('enquiry-unread');
                        row.classList.add('enquiry-read');
                    }
                    
                    // Show success toast
                    const toast = new bootstrap.Toast(document.getElementById('successToast'));
                    document.getElementById('toastMessage').textContent = data.message;
                    toast.show();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                alert('Error updating status: ' + error.message);
            });
        });

        // Add AJAX handlers for get_enquiry_details and mark_as_read
        // This replaces the separate include files
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept form submissions for get_enquiry_details
            document.querySelectorAll('.view-enquiry').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // The main logic is already handled above
                });
            });
        });

        // Refresh enquiries
        function refreshEnquiries() {
            window.location.reload();
        }

        // Auto-hide toast after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('successToast');
            if (toastEl) {
                toastEl.addEventListener('shown.bs.toast', function () {
                    setTimeout(() => {
                        const toast = bootstrap.Toast.getInstance(toastEl);
                        if (toast) toast.hide();
                    }, 5000);
                });
            }
        });
    </script>
</body>
</html>

<?php
// Handle additional AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'get_enquiry_details') {
        $enquiry_id = intval($_POST['enquiry_id'] ?? 0);
        
        if ($enquiry_id > 0) {
            $query = "SELECT * FROM enquiries WHERE enquiry_id = $enquiry_id";
            $result = mysqli_query($connection, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $enquiry = mysqli_fetch_assoc($result);
                echo json_encode([
                    'success' => true,
                    'enquiry' => $enquiry
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Enquiry not found']);
            }
            exit;
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'mark_as_read') {
        $enquiry_id = intval($_POST['enquiry_id'] ?? 0);
        
        if ($enquiry_id > 0) {
            $query = "UPDATE enquiries SET is_read = 1 WHERE enquiry_id = $enquiry_id";
            $result = mysqli_query($connection, $query);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            exit;
        }
    }
}
?>