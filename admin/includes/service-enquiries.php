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
        $enquiry_id = intval($_POST['enquiry_id'] ?? 0);
        $new_status = mysqli_real_escape_string($connection, $_POST['status'] ?? '');
        $notes = mysqli_real_escape_string($connection, $_POST['notes'] ?? '');
        
        $allowed_statuses = ['new', 'contacted', 'dropped', 'qualified'];
        
        if ($enquiry_id > 0 && in_array($new_status, $allowed_statuses)) {
            // Update status and mark as read
            $query = "UPDATE enquiries SET 
                      status = '$new_status',
                      is_read = 1,
                      updated_at = NOW() 
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
        ob_end_flush();
        exit;
    }
}

// Fetch all enquiries
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

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Service Enquiries Management</h1>
            <div>
                <button class="btn btn-outline-secondary" onclick="exportToCSV()">
                    <i class="bi bi-download me-2"></i>Export CSV
                </button>
                <button class="btn btn-primary" onclick="refreshEnquiries()">
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
                                <th>Email</th>
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
                                    <tr id="enquiry-row-<?php echo $enquiry['enquiry_id']; ?>" 
                                        class="<?php echo $enquiry['is_read'] == 0 ? 'enquiry-unread' : 'enquiry-read'; ?>">
                                        <td class="fw-bold"><?php echo $serial++; ?></td>
                                        <td class="text-muted"><?php echo $enquiry['enquiry_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enquiry['name'] ?: 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" class="text-primary">
                                                <?php echo htmlspecialchars($enquiry['email']); ?>
                                            </a>
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
                                            <span class="status-badge status-<?php echo $enquiry['status']; ?>" id="status-badge-<?php echo $enquiry['enquiry_id']; ?>">
                                                <?php echo ucfirst($enquiry['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-enquiry-btn" 
                                                        onclick="viewEnquiryDetails(<?php echo $enquiry['enquiry_id']; ?>)"
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning update-status-btn" 
                                                        onclick="showUpdateStatusModal(<?php echo $enquiry['enquiry_id']; ?>, '<?php echo $enquiry['status']; ?>')"
                                                        title="Update Status">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger delete-enquiry-btn" 
                                                        onclick="showDeleteConfirmation(<?php echo $enquiry['enquiry_id']; ?>, '<?php echo htmlspecialchars(addslashes($enquiry['name'])); ?>')"
                                                        title="Delete Enquiry">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
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
<div class="modal fade" id="viewEnquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enquiry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="enquiryDetailsContent">
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
                <h5 class="modal-title">Update Enquiry Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" onsubmit="updateEnquiryStatus(event)">
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
                    
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3" 
                                  placeholder="Add any notes about this enquiry..."></textarea>
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
                Are you sure you want to delete <strong id="deleteEnquiryName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteEnquiry()">
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

.status-new { background-color: #0d6efd; color: white; }
.status-contacted { background-color: #fd7e14; color: white; }
.status-dropped { background-color: #dc3545; color: white; }
.status-qualified { background-color: #198754; color: white; }

.enquiry-unread { background-color: #f8f9fa; font-weight: 500; }
.enquiry-read { background-color: #ffffff; }

.btn-group .btn {
    border-radius: 4px !important;
}

.btn-group .btn:not(:last-child) {
    margin-right: 2px;
}

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
    max-height: 300px;
    overflow-y: auto;
}
</style>

<script>
// Global variables
let currentDeleteEnquiryId = null;

// View Enquiry Details
function viewEnquiryDetails(enquiryId) {
    if (!enquiryId) {
        showError('Invalid enquiry ID');
        return;
    }
    
    // Show loading in modal
    document.getElementById('enquiryDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading enquiry details...</p>
        </div>
    `;
    
    // Show modal
    const viewModal = new bootstrap.Modal(document.getElementById('viewEnquiryModal'));
    viewModal.show();
    
    // Fetch enquiry details
    fetch('includes/get_enquiry_details.php?id=' + enquiryId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.enquiry) {
                const enquiry = data.enquiry;
                
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Enquiry ID:</strong> ${enquiry.enquiry_id}</p>
                            <p><strong>Name:</strong> ${escapeHtml(enquiry.name || 'N/A')}</p>
                            <p><strong>Email:</strong> <a href="mailto:${escapeHtml(enquiry.email)}">${escapeHtml(enquiry.email)}</a></p>
                            <p><strong>Contact:</strong> ${escapeHtml(enquiry.contact || 'N/A')}</p>
                            <p><strong>Service:</strong> <span class="badge bg-primary">${escapeHtml(enquiry.service || 'N/A')}</span></p>
                            ${enquiry.sub_service ? `<p><strong>Sub Service:</strong> ${escapeHtml(enquiry.sub_service)}</p>` : ''}
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="status-badge status-${enquiry.status}">${capitalizeFirst(enquiry.status)}</span></p>
                            <p><strong>Submitted:</strong> ${formatDateTime(enquiry.submitted_at)}</p>
                            <p><strong>Read Status:</strong> ${enquiry.is_read == 1 ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning">Unread</span>'}</p>
                            ${enquiry.updated_at ? `<p><strong>Last Updated:</strong> ${formatDateTime(enquiry.updated_at)}</p>` : ''}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6>Message:</h6>
                            <div class="modal-message">
                                ${escapeHtml(enquiry.message || 'No message').replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>`;
                
                document.getElementById('enquiryDetailsContent').innerHTML = detailsHtml;
                
                // Mark as read after viewing if it's unread
                if (enquiry.is_read == 0) {
                    const row = document.getElementById('enquiry-row-' + enquiryId);
                    if (row) {
                        row.classList.remove('enquiry-unread');
                        row.classList.add('enquiry-read');
                    }
                }
            } else {
                document.getElementById('enquiryDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load enquiry details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('enquiryDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading enquiry details. Please try again.
                </div>
            `;
            console.error('Error:', error);
        });
}

// Show Update Status Modal
function showUpdateStatusModal(enquiryId, currentStatus) {
    if (!enquiryId) {
        showError('Invalid enquiry ID');
        return;
    }
    
    document.getElementById('updateEnquiryId').value = enquiryId;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('statusNotes').value = '';
    
    const updateModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    updateModal.show();
}

// Update Enquiry Status
function updateEnquiryStatus(event) {
    event.preventDefault();
    
    const enquiryId = document.getElementById('updateEnquiryId').value;
    const form = document.getElementById('updateStatusForm');
    const formData = new FormData(form);
    formData.append('action', 'update_status');
    
    if (!enquiryId) {
        showError('Invalid enquiry ID');
        return;
    }
    
    const submitBtn = document.getElementById('updateStatusBtn');
    const spinner = document.getElementById('statusSpinner');
    
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    fetch('includes/service-enquiries.php', {
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
            updateEnquiryStatusInUI(enquiryId, newStatus);
            
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
function showDeleteConfirmation(enquiryId, enquiryName) {
    if (!enquiryId) {
        showError('Invalid enquiry ID');
        return;
    }
    
    currentDeleteEnquiryId = enquiryId;
    document.getElementById('deleteEnquiryName').textContent = enquiryName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Delete Enquiry
function deleteEnquiry() {
    if (!currentDeleteEnquiryId) {
        showError('No enquiry selected for deletion');
        return;
    }
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    fetch('includes/delete_enquiry.php?id=' + currentDeleteEnquiryId)
        .then(response => response.json())
        .then(data => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                // Remove row from table
                const row = document.getElementById('enquiry-row-' + currentDeleteEnquiryId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                        // If using DataTables, you might need to redraw
                        if (typeof $.fn.DataTable !== 'undefined' && $('#enquiriesTable').DataTable()) {
                            $('#enquiriesTable').DataTable().clear().draw();
                        }
                    }, 400);
                }
                
                // Show success message
                showSuccess(data.message || 'Enquiry deleted successfully!');
                currentDeleteEnquiryId = null;
            } else {
                showError(data.message || 'Failed to delete enquiry');
            }
        })
        .catch(error => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            showError('Error deleting enquiry: ' + error.message);
            console.error('Error:', error);
        });
}

// Helper function to update enquiry status in UI
function updateEnquiryStatusInUI(enquiryId, newStatus) {
    // Update status badge
    const statusBadge = document.getElementById('status-badge-' + enquiryId);
    if (statusBadge) {
        statusBadge.className = 'status-badge status-' + newStatus;
        statusBadge.textContent = capitalizeFirst(newStatus);
    }
    
    // Update button onclick attribute
    const updateBtn = document.querySelector('#enquiry-row-' + enquiryId + ' .update-status-btn');
    if (updateBtn) {
        updateBtn.setAttribute('onclick', `showUpdateStatusModal(${enquiryId}, '${newStatus}')`);
    }
    
    // Mark as read
    const row = document.getElementById('enquiry-row-' + enquiryId);
    if (row) {
        row.classList.remove('enquiry-unread');
        row.classList.add('enquiry-read');
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
    window.location.href = 'includes/export_enquiries.php';
}

// Refresh enquiries
function refreshEnquiries() {
    window.location.reload();
}

// Initialize DataTable when jQuery is available
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
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
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>