<?php
// ratio-calc-lead.php

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    $lead_id = intval($_POST['lead_id'] ?? 0);
    $new_status = mysqli_real_escape_string($connection, $_POST['status'] ?? '');
    $notes = mysqli_real_escape_string($connection, $_POST['notes'] ?? '');
    
    $allowed_statuses = ['new', 'contacted', 'qualified', 'converted', 'unresponsive'];
    
    if ($lead_id > 0 && in_array($new_status, $allowed_statuses)) {
        // Update status and notes
        $query = "UPDATE leads SET 
                  status = '$new_status',
                  notes = '$notes',
                  updated_at = NOW() 
                  WHERE id = $lead_id";
        
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

// Fetch all leads
$query = "SELECT 
            id,
            full_name,
            email,
            phone,
            company_name,
            industry,
            ratios_calculated,
            report_generated,
            first_interaction,
            last_interaction,
            status,
            created_at,
            updated_at
          FROM leads 
          ORDER BY created_at DESC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

    <style>
    </style>

<body>

    <div class="main-content" id="mainContent">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Ratio Calculator Leads</h1>
                <div>
                    <button class="btn btn-outline-secondary" onclick="exportToCSV()">
                        <i class="bi bi-download me-2"></i>Export CSV
                    </button>
                    <button class="btn btn-primary" onclick="refreshLeads()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Leads</h5>
                    <p class="text-light mb-0 small">Total: <?php echo mysqli_num_rows($result); ?> leads</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="leadsTable">
                            <thead>
                                <tr class="table-dark">
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Industry</th>
                                    <th>Ratios</th>
                                    <th>First Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php $serial = 1; while ($lead = mysqli_fetch_assoc($result)): ?>
                                        <tr data-id="<?php echo $lead['id']; ?>">
                                            <td class="fw-bold"><?php echo $serial++; ?></td>
                                            <td class="text-muted"><?php echo $lead['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($lead['full_name'] ?: 'N/A'); ?></strong>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" class="text-primary">
                                                    <?php echo htmlspecialchars($lead['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (!empty($lead['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($lead['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($lead['company_name']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars(ucfirst($lead['industry'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $ratios = json_decode($lead['ratios_calculated'], true);
                                                $ratio_count = is_array($ratios) ? count($ratios) : 0;
                                                ?>
                                                <span class="badge bg-secondary" title="<?php echo htmlspecialchars($lead['ratios_calculated']); ?>">
                                                    <?php echo $ratio_count; ?> ratios
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($lead['first_interaction'])); ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($lead['first_interaction'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $lead['status']; ?>">
                                                    <?php echo ucfirst($lead['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown actions-dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-gear"></i> Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item view-lead" href="#" data-id="<?php echo $lead['id']; ?>">
                                                                <i class="bi bi-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item update-status-btn" href="#" data-id="<?php echo $lead['id']; ?>" data-current-status="<?php echo $lead['status']; ?>">
                                                                <i class="bi bi-pencil-square me-2"></i>Update Status
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item send-email" href="mailto:<?php echo htmlspecialchars($lead['email']); ?>?subject=Follow-up%20from%20OGMBC%20Financial%20Analysis">
                                                                <i class="bi bi-envelope me-2"></i>Send Email
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger delete-lead" href="#" data-id="<?php echo $lead['id']; ?>">
                                                                <i class="bi bi-trash me-2"></i>Delete Lead
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-people display-4 d-block mb-3"></i>
                                                <h5>No leads found</h5>
                                                <p>Leads will appear here when users download financial ratio reports.</p>
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

    <!-- View Lead Details Modal -->
    <div class="modal fade" id="viewLeadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lead Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="leadDetailsContent">
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
                    <h5 class="modal-title">Update Lead Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="updateLeadId" name="lead_id">
                        
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Status</label>
                            <select class="form-select" id="statusSelect" name="status" required>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="converted">Converted</option>
                                <option value="unresponsive">Unresponsive</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="statusNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="statusNotes" name="notes" rows="3" 
                                      placeholder="Add any notes about this lead..."></textarea>
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


    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#leadsTable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                responsive: true,
                language: {
                    search: "Search leads:",
                    lengthMenu: "Show _MENU_ leads per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ leads",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });

        // View lead details
        document.querySelectorAll('.view-lead').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const leadId = this.dataset.id;
                
                fetch('includes/get_lead_details.php?id=' + leadId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const lead = data.lead;
                            const ratios = JSON.parse(lead.ratios_calculated || '[]');
                            
                            let detailsHtml = `
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Full Name:</strong> ${lead.full_name || 'N/A'}</p>
                                        <p><strong>Email:</strong> <a href="mailto:${lead.email}">${lead.email}</a></p>
                                        <p><strong>Phone:</strong> ${lead.phone || 'N/A'}</p>
                                        <p><strong>Company:</strong> ${lead.company_name}</p>
                                        <p><strong>Industry:</strong> <span class="badge bg-info">${lead.industry}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> <span class="status-badge status-${lead.status}">${lead.status}</span></p>
                                        <p><strong>First Interaction:</strong> ${new Date(lead.first_interaction).toLocaleString()}</p>
                                        <p><strong>Last Interaction:</strong> ${new Date(lead.last_interaction).toLocaleString()}</p>
                                        <p><strong>Report Generated:</strong> ${lead.report_generated ? new Date(lead.report_generated).toLocaleDateString() : 'N/A'}</p>
                                        <p><strong>Consent Given:</strong> ${lead.consent_given ? 'Yes' : 'No'}</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Ratios Calculated (${ratios.length}):</h6>
                                        <div class="bg-light p-3 rounded">
                                            ${ratios.length > 0 ? 
                                                '<div class="row g-2">' + ratios.map(ratio => 
                                                    `<div class="col-auto"><span class="badge bg-secondary">${ratio}</span></div>`
                                                ).join('') + '</div>' : 
                                                'No ratios calculated'}
                                        </div>
                                    </div>
                                </div>`;
                            
                            if (lead.notes) {
                                detailsHtml += `
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <h6>Notes:</h6>
                                            <div class="bg-light p-3 rounded">
                                                ${lead.notes}
                                            </div>
                                        </div>
                                    </div>`;
                            }
                            
                            document.getElementById('leadDetailsContent').innerHTML = detailsHtml;
                            const modal = new bootstrap.Modal(document.getElementById('viewLeadModal'));
                            modal.show();
                        }
                    });
            });
        });

        // Update status
        document.querySelectorAll('.update-status-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const leadId = this.dataset.id;
                const currentStatus = this.dataset.currentStatus;
                
                document.getElementById('updateLeadId').value = leadId;
                document.getElementById('statusSelect').value = currentStatus;
                document.getElementById('statusNotes').value = '';
                
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
            
            fetch('ratio-calc-lead.php', {
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
                    const leadId = formData.get('lead_id');
                    const newStatus = formData.get('status');
                    const row = document.querySelector(`tr[data-id="${leadId}"]`);
                    
                    if (row) {
                        // Update status badge
                        const statusCell = row.querySelector('.status-badge');
                        statusCell.className = `status-badge status-${newStatus}`;
                        statusCell.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        
                        // Update button data attribute
                        const updateBtn = row.querySelector('.update-status-btn');
                        if (updateBtn) {
                            updateBtn.dataset.currentStatus = newStatus;
                        }
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

        // Delete lead
        document.querySelectorAll('.delete-lead').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const leadId = this.dataset.id;
                
                if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                    fetch('includes/delete_lead.php?id=' + leadId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove row from table
                                const row = document.querySelector(`tr[data-id="${leadId}"]`);
                                if (row) {
                                    row.remove();
                                    // Show success message
                                    alert('Lead deleted successfully');
                                }
                            } else {
                                alert('Error deleting lead: ' + data.message);
                            }
                        });
                }
            });
        });

        // Export to CSV
        function exportToCSV() {
            window.location.href = 'includes/export_leads.php';
        }

        // Refresh leads
        function refreshLeads() {
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