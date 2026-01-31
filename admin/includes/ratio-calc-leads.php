<?php
// Suppress PHP errors for AJAX endpoints
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/database.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    // If AJAX request, return JSON
    if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest' || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false || $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// Handle status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        // Robust error handling for AJAX: always return JSON, catch fatal errors
        ob_start();
        error_reporting(0);
        ini_set('display_errors', 0);

        set_exception_handler(function($e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            exit;
        });
        set_error_handler(function($errno, $errstr) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
            exit;
        });
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
                exit;
            }
        });

        header('Content-Type: application/json');
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $new_status = mysqli_real_escape_string($connection, $_POST['status'] ?? '');
        $notes = mysqli_real_escape_string($connection, $_POST['notes'] ?? '');
        $allowed_statuses = ['new', 'contacted', 'qualified', 'converted', 'unresponsive'];
        if ($lead_id > 0 && in_array($new_status, $allowed_statuses)) {
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
        ob_end_flush();
        exit;
    }
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
            notes,
            created_at,
            updated_at
          FROM leads 
          ORDER BY created_at DESC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!-- Main Content -->
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
                                    <tr id="lead-row-<?php echo $lead['id']; ?>">
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
                                            <span class="badge bg-secondary">
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
                                            <span class="status-badge status-<?php echo $lead['status']; ?>" id="status-badge-<?php echo $lead['id']; ?>">
                                                <?php echo ucfirst($lead['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-lead-btn" 
                                                        onclick="viewLeadDetails(<?php echo $lead['id']; ?>)"
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning update-status-btn" 
                                                        onclick="showUpdateStatusModal(<?php echo $lead['id']; ?>, '<?php echo $lead['status']; ?>')"
                                                        title="Update Status">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger delete-lead-btn" 
                                                        onclick="showDeleteConfirmation(<?php echo $lead['id']; ?>, '<?php echo htmlspecialchars(addslashes($lead['full_name'])); ?>')"
                                                        title="Delete Lead">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" onsubmit="updateLeadStatus(event)">
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
                        <button type="submit" class="btn btn-primary" id="updateStatusBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="statusSpinner"></span>
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteLeadName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteLead()">
                    Delete
                </button>
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
                <span id="toastMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span id="errorToastMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 500;
    display: inline-block;
}

.status-new {
    background-color: #0d6efd;
    color: white;
}

.status-contacted {
    background-color: #fd7e14;
    color: white;
}

.status-qualified {
    background-color: #20c997;
    color: white;
}

.status-converted {
    background-color: #198754;
    color: white;
}

.status-unresponsive {
    background-color: #6c757d;
    color: white;
}

.btn-group .btn {
    border-radius: 4px !important;
}

.btn-group .btn:not(:last-child) {
    margin-right: 2px;
}
</style>

<script>
// Global variables
let currentDeleteLeadId = null;

// View Lead Details
function viewLeadDetails(leadId) {
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    
    // Show loading in modal
    document.getElementById('leadDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading lead details...</p>
        </div>
    `;
    
    // Show modal
    const viewModal = new bootstrap.Modal(document.getElementById('viewLeadModal'));
    viewModal.show();
    
    // Fetch lead details
    fetch('includes/get_lead_details.php?id=' + leadId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.lead) {
                const lead = data.lead;
                let ratios = [];
                try {
                    ratios = JSON.parse(lead.ratios_calculated || '[]');
                } catch (e) {
                    ratios = [];
                }
                
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Lead ID:</strong> ${lead.id}</p>
                            <p><strong>Full Name:</strong> ${escapeHtml(lead.full_name || 'N/A')}</p>
                            <p><strong>Email:</strong> <a href="mailto:${escapeHtml(lead.email)}">${escapeHtml(lead.email)}</a></p>
                            <p><strong>Phone:</strong> ${escapeHtml(lead.phone || 'N/A')}</p>
                            <p><strong>Company:</strong> ${escapeHtml(lead.company_name || 'N/A')}</p>
                            <p><strong>Industry:</strong> <span class="badge bg-info">${escapeHtml(lead.industry || 'N/A')}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="status-badge status-${lead.status}">${capitalizeFirst(lead.status)}</span></p>
                            <p><strong>Created:</strong> ${formatDateTime(lead.created_at)}</p>
                            <p><strong>First Interaction:</strong> ${formatDateTime(lead.first_interaction)}</p>
                            <p><strong>Last Interaction:</strong> ${formatDateTime(lead.last_interaction)}</p>
                            <p><strong>Last Updated:</strong> ${formatDateTime(lead.updated_at)}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Ratios Calculated (${ratios.length}):</h6>
                            <div class="bg-light p-3 rounded">
                                ${ratios.length > 0 ? 
                                    '<div class="row g-2">' + ratios.map(ratio => 
                                        `<div class="col-auto"><span class="badge bg-secondary">${escapeHtml(ratio)}</span></div>`
                                    ).join('') + '</div>' : 
                                    '<p class="text-muted mb-0">No ratios calculated</p>'}
                            </div>
                        </div>
                    </div>
                    ${lead.notes ? `
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Notes:</h6>
                            <div class="bg-light p-3 rounded">
                                ${escapeHtml(lead.notes)}
                            </div>
                        </div>
                    </div>` : ''}
                `;
                
                document.getElementById('leadDetailsContent').innerHTML = detailsHtml;
            } else {
                document.getElementById('leadDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load lead details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('leadDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading lead details. Please try again.
                </div>
            `;
            console.error('Error:', error);
        });
}

// Show Update Status Modal
function showUpdateStatusModal(leadId, currentStatus) {
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    
    document.getElementById('updateLeadId').value = leadId;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('statusNotes').value = '';
    
    const updateModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    updateModal.show();
}

// Update Lead Status
function updateLeadStatus(event) {
    event.preventDefault();
    
    const leadId = document.getElementById('updateLeadId').value;
    const form = document.getElementById('updateStatusForm');
    const formData = new FormData(form);
    formData.append('action', 'update_status');
    
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    
    const submitBtn = document.getElementById('updateStatusBtn');
    const spinner = document.getElementById('statusSpinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    fetch('includes/ratio-calc-leads.php', {
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
            
            // Update UI
            const newStatus = document.getElementById('statusSelect').value;
            updateLeadStatusInUI(leadId, newStatus);
            
            // Show success message
            showSuccess(data.message || 'Status updated successfully!');
        } else {
            showError(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        showError('Error updating status: ' + error.message);
        console.error('Error:', error);
    });
}

// Show Delete Confirmation
function showDeleteConfirmation(leadId, leadName) {
    if (!leadId) {
        showError('Invalid lead ID');
        return;
    }
    
    currentDeleteLeadId = leadId;
    document.getElementById('deleteLeadName').textContent = leadName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Delete Lead
function deleteLead() {
    if (!currentDeleteLeadId) {
        showError('No lead selected for deletion');
        return;
    }
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    fetch('includes/delete_lead.php?id=' + currentDeleteLeadId)
        .then(response => response.json())
        .then(data => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                // Remove row from table
                const row = document.getElementById('lead-row-' + currentDeleteLeadId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                        // If using DataTables, you might need to redraw
                        if (typeof $.fn.DataTable !== 'undefined' && $('#leadsTable').DataTable()) {
                            $('#leadsTable').DataTable().clear().draw();
                        }
                    }, 400);
                }
                
                // Show success message
                showSuccess(data.message || 'Lead deleted successfully!');
                currentDeleteLeadId = null;
            } else {
                showError(data.message || 'Failed to delete lead');
            }
        })
        .catch(error => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            showError('Error deleting lead: ' + error.message);
            console.error('Error:', error);
        });
}

// Helper function to update lead status in UI
function updateLeadStatusInUI(leadId, newStatus) {
    // Update status badge
    const statusBadge = document.getElementById('status-badge-' + leadId);
    if (statusBadge) {
        statusBadge.className = 'status-badge status-' + newStatus;
        statusBadge.textContent = capitalizeFirst(newStatus);
    }
    
    // Update button onclick attribute
    const updateBtn = document.querySelector('#lead-row-' + leadId + ' .update-status-btn');
    if (updateBtn) {
        updateBtn.setAttribute('onclick', `showUpdateStatusModal(${leadId}, '${newStatus}')`);
    }
}

// Helper function to capitalize first letter
function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Helper function to format date/time
function formatDateTime(dateTimeStr) {
    if (!dateTimeStr || dateTimeStr === '0000-00-00 00:00:00') return 'N/A';
    
    const date = new Date(dateTimeStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show success message
function showSuccess(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        toast.hide();
    }, 5000);
}

// Show error message
function showError(message) {
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
    toast.show();
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        toast.hide();
    }, 5000);
}

// Export to CSV
function exportToCSV() {
    window.location.href = 'includes/export_leads.php';
}

// Refresh leads
function refreshLeads() {
    window.location.reload();
}

// Initialize DataTable when jQuery is available
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
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
    }
});
</script>