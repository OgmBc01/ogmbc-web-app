<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role and ID
$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// $can_validate = ($user_role == 'ceo_gm' || $user_role == 'hr_admin' || $user_role == 'admin_staff');
$can_validate = true; // Temporarily allow all users to approve/reject

// Get selected filters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : '';
$selected_client = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';

// Get statistics
// Updated statistics query with rejection column
$stats_query = "SELECT 
                COUNT(*) as total_feedback,
                SUM(CASE WHEN is_validated = 1 THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN is_validated = 0 AND is_rejected = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN is_rejected = 1 THEN 1 ELSE 0 END) as rejected,
                COALESCE(SUM(CASE WHEN is_validated = 1 THEN points_awarded ELSE 0 END), 0) as total_points
                FROM client_feedback
                WHERE YEAR(created_at) = $selected_year";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Client Feedback</h1>
        <div>
            <a href="client_feedback.php?source=add_feedback" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Log New Feedback
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Feedback</h5>
                    <h2><?php echo $stats['total_feedback'] ?? 0; ?></h2>
                    <small>Year <?php echo $selected_year; ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Validated</h5>
                    <h2><?php echo $stats['validated'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2><?php echo $stats['pending'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Points Awarded</h5>
                    <h2><?php echo number_format($stats['total_points'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4" style="border: 1px solid rgba(10, 34, 64, 0.1);">
        <div class="card-header" style="background: #0a2240; color: #f1bf70;">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Feedback</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="source" value="view_all">
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">Year</label>
                    <select id="year" name="year" class="form-control">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Month</label>
                    <select id="month" name="month" class="form-control">
                        <option value="">All Months</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($selected_month == $m) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Client</label>
                    <select id="client_id" name="client_id" class="form-control">
                        <option value="">All Clients</option>
                        <?php 
                        if ($clients_result) {
                            mysqli_data_seek($clients_result, 0);
                            while($client = mysqli_fetch_assoc($clients_result)): 
                        ?>
                            <option value="<?php echo $client['client_id']; ?>" <?php echo ($selected_client == $client['client_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['company_name']); ?>
                            </option>
                        <?php 
                            endwhile;
                        } 
                        ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" style="background: #f1bf70; border-color: #f1bf70; color: #0a2240; font-weight: 600;">
                        <i class="bi bi-funnel me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="card shadow-sm">
        <div class="card-header" style="background: #0a2240; color: #f1bf70;">
            <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i>Client Feedback Records 
                <span class="badge bg-light text-dark ms-2"><?php echo mysqli_num_rows(mysqli_query($connection, "SELECT 1 FROM client_feedback WHERE YEAR(created_at) = $selected_year")); ?> records</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Date</th>
                            <th>Client</th>
                            <th>Employee</th>
                            <th>Feedback</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build query
                        $where = ["YEAR(cf.created_at) = $selected_year"];
                        if (!empty($selected_month)) {
                            $where[] = "MONTH(cf.created_at) = $selected_month";
                        }
                        if (!empty($selected_client)) {
                            $where[] = "cf.client_id = $selected_client";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT cf.*, 
                                 c.company_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 CONCAT(v.first_name, ' ', v.last_name) as validated_by_name
                                 FROM client_feedback cf
                                 JOIN clients c ON cf.client_id = c.client_id
                                 LEFT JOIN users u ON cf.employee_id = u.user_id
                                 LEFT JOIN users v ON cf.validated_by = v.user_id
                                 WHERE $where_clause
                                 ORDER BY cf.created_at DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center py-5'>";
                            echo "<div class='text-muted'>";
                            echo "<i class='bi bi-chat-quote display-4 d-block mb-3'></i>";
                            echo "<h5>No feedback records found</h5>";
                            if (isset($_GET['year']) || isset($_GET['month']) || isset($_GET['client_id'])) {
                                echo "<p>No results match your filter criteria. Try adjusting your filters.</p>";
                                echo "<a href='client_feedback.php' class='btn btn-outline-primary mt-2'>";
                                echo "<i class='bi bi-arrow-counterclockwise me-2'></i>Clear All Filters";
                                echo "</a>";
                            } else {
                                echo "<p>Get started by logging your first client feedback.</p>";
                                echo "<a href='client_feedback.php?source=add_feedback' class='btn btn-primary mt-2'>";
                                echo "<i class='bi bi-plus-circle'></i> Log New Feedback";
                                echo "</a>";
                            }
                            echo "</div>";
                            echo "</td></tr>";
                        } else {
                            while($feedback = mysqli_fetch_assoc($result)):
                                $points = $feedback['points_awarded'] ?? 50;
                                ?>
                                <tr id="feedback-row-<?php echo $feedback['feedback_id']; ?>">
                                    <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($feedback['company_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($feedback['employee_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="feedback-preview">
                                            <?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 50)) . (strlen($feedback['feedback_text']) > 50 ? '...' : ''); ?>
                                        </div>
                                        <?php if ($feedback['engagement_id']): ?>
                                            <span class="badge bg-info" title="Linked to engagement">#<?php echo $feedback['engagement_id']; ?></span>
                                        <?php endif; ?>
                                        <?php if ($feedback['rating']): ?>
                                            <span class="badge bg-secondary">Rating: <?php echo $feedback['rating']; ?>/5</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($feedback['is_validated']): ?>
                                            <span class="badge bg-success">+<?php echo $points; ?> pts</span>
                                        <?php elseif ($feedback['is_rejected']): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($feedback['is_validated']): ?>
                                            <span class="badge bg-success">Validated</span>
                                            <br><small><?php echo htmlspecialchars($feedback['validated_by_name']); ?></small>
                                        <?php elseif ($feedback['is_rejected']): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                            <?php if ($feedback['rejection_reason']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($feedback['rejection_reason'], 0, 20)); ?>...</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-info" onclick="viewFeedback(<?php echo $feedback['feedback_id']; ?>)" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            

                                            <?php if (!$feedback['is_validated'] && !$feedback['is_rejected'] && $can_validate): ?>
                                                <button class="btn btn-outline-success" onclick="showApproveModal(<?php echo $feedback['feedback_id']; ?>, '<?php echo htmlspecialchars(addslashes($feedback['company_name'])); ?>', '<?php echo htmlspecialchars(addslashes($feedback['employee_name'] ?? 'N/A')); ?>')" title="Approve & Award Points">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="showRejectModal(<?php echo $feedback['feedback_id']; ?>, '<?php echo htmlspecialchars(addslashes($feedback['company_name'])); ?>', '<?php echo htmlspecialchars(addslashes($feedback['employee_name'] ?? 'N/A')); ?>')" title="Reject Feedback">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($can_validate && !$feedback['is_validated'] && !$feedback['is_rejected']): ?>
                                                <button class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $feedback['feedback_id']; ?>, 'feedback from <?php echo htmlspecialchars(addslashes($feedback['company_name'])); ?>')" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Approve Feedback Modal -->
<div class="modal fade" id="approveFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Feedback</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveFeedbackForm">
                <div class="modal-body">
                    <input type="hidden" id="approve_feedback_id" name="feedback_id">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        Approving this feedback will award <strong>50 points</strong> to the employee.
                    </div>
                    
                    <p>Are you sure you want to approve feedback from <strong id="approve_client_name"></strong> for employee <strong id="approve_employee_name"></strong>?</p>
                    
                    <div class="mb-3">
                        <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                        <textarea class="form-control" id="approval_notes" name="notes" rows="2" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="approveBtn">
                        <i class="bi bi-check-lg me-1"></i>Approve & Award Points
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Feedback Modal -->
<div class="modal fade" id="rejectFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Feedback</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectFeedbackForm">
                <div class="modal-body">
                    <input type="hidden" id="reject_feedback_id" name="feedback_id">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Rejecting this feedback will NOT award any points.
                    </div>
                    
                    <p>Are you sure you want to reject feedback from <strong id="reject_client_name"></strong> for employee <strong id="reject_employee_name"></strong>?</p>
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="reason" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="rejectBtn">
                        <i class="bi bi-x-lg me-1"></i>Reject Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toasts -->
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

<script>
// View feedback details
// View feedback details
function viewFeedback(id) {
    // Check if the modal exists in the current page
    const modalElement = document.getElementById('viewFeedbackModal');
    if (!modalElement) {
        console.error('Modal not found');
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement);
    const contentDiv = document.getElementById('feedbackDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading feedback details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_feedback_details.php?id=' + id)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

// Show approve modal
function showApproveModal(feedbackId, clientName, employeeName) {
    document.getElementById('approve_feedback_id').value = feedbackId;
    document.getElementById('approve_client_name').textContent = clientName;
    document.getElementById('approve_employee_name').textContent = employeeName;
    document.getElementById('approval_notes').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('approveFeedbackModal'));
    modal.show();
}

// Show reject modal
function showRejectModal(feedbackId, clientName, employeeName) {
    document.getElementById('reject_feedback_id').value = feedbackId;
    document.getElementById('reject_client_name').textContent = clientName;
    document.getElementById('reject_employee_name').textContent = employeeName;
    document.getElementById('rejection_reason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('rejectFeedbackModal'));
    modal.show();
}

// Handle approve form submission
document.getElementById('approveFeedbackForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const feedbackId = document.getElementById('approve_feedback_id').value;
    const notes = document.getElementById('approval_notes').value;
    const approveBtn = document.getElementById('approveBtn');
    
    approveBtn.disabled = true;
    approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    fetch('includes/ajax/process_feedback_review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'approve',
            feedback_id: feedbackId,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('approveFeedbackModal'));
            modal.hide();
            showSuccess('Feedback approved successfully! 50 points awarded.');
            
            // Reload the page after 1.5 seconds
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showError(data.message || 'Failed to approve feedback');
            approveBtn.disabled = false;
            approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve & Award Points';
        }
    })
    .catch(error => {
        showError('Error: ' + error.message);
        approveBtn.disabled = false;
        approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Approve & Award Points';
    });
});

// Handle reject form submission
document.getElementById('rejectFeedbackForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const feedbackId = document.getElementById('reject_feedback_id').value;
    const reason = document.getElementById('rejection_reason').value;
    const rejectBtn = document.getElementById('rejectBtn');
    
    if (!reason.trim()) {
        showError('Please provide a rejection reason');
        return;
    }
    
    rejectBtn.disabled = true;
    rejectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    fetch('includes/ajax/process_feedback_review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'reject',
            feedback_id: feedbackId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('rejectFeedbackModal'));
            modal.hide();
            showSuccess('Feedback rejected successfully.');
            
            // Reload the page after 1.5 seconds
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showError(data.message || 'Failed to reject feedback');
            rejectBtn.disabled = false;
            rejectBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Reject Feedback';
        }
    })
    .catch(error => {
        showError('Error: ' + error.message);
        rejectBtn.disabled = false;
        rejectBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i>Reject Feedback';
    });
});

// Confirm delete
function confirmDelete(feedbackId, description) {
    document.getElementById('deleteItemName').textContent = description;
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Remove any existing event listeners by cloning
    const newDeleteBtn = deleteBtn.cloneNode(true);
    deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);
    
    // Add new click event
    newDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        executeDelete(feedbackId);
    });
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Execute delete via AJAX
function executeDelete(feedbackId) {
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    
    fetch('includes/ajax/delete_feedback.php?id=' + feedbackId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                showSuccess(data.message);
                
                // Remove the row from table
                const row = document.getElementById('feedback-row-' + feedbackId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                        // Update the record count badge
                        const badge = document.querySelector('.card-header .badge');
                        if (badge) {
                            let count = parseInt(badge.textContent);
                            badge.textContent = (count - 1) + ' records';
                        }
                    }, 400);
                }
            } else {
                showError(data.message || 'Failed to delete feedback');
            }
        })
        .catch(error => {
            showError('Error: ' + error.message);
        })
        .finally(() => {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
}

// Show success message
function showSuccess(message) {
    document.getElementById('toastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
    
    setTimeout(() => {
        toast.hide();
    }, 5000);
}

// Show error message
function showError(message) {
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
    toast.show();
    
    setTimeout(() => {
        toast.hide();
    }, 5000);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
    /* Theme Colors */
    :root {
        --dark-blue: #0a2240;
        --gold: #f1bf70;
    }

    /* Card Headers */
    .card-header {
        background: var(--dark-blue) !important;
        color: var(--gold) !important;
        font-weight: 600;
    }

    /* Filter Section */
    .form-control:focus, .form-select:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 0.2rem rgba(241, 191, 112, 0.25);
    }

    /* Table Styles */
    .table-dark {
        --bs-table-bg: var(--dark-blue) !important;
        --bs-table-color: white !important;
    }

    .table tbody tr:hover {
        background-color: rgba(241, 191, 112, 0.1);
    }

    /* Badge Styles */
    .badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.6rem;
        font-weight: 500;
    }

    /* Button Group */
    .btn-group .btn {
        border-radius: 6px !important;
        margin: 0 2px;
        padding: 0.25rem 0.5rem;
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-2px);
    }

    /* Modal Styles */
    .modal-header {
        background: var(--dark-blue) !important;
        color: var(--gold) !important;
        border-bottom: 1px solid var(--gold) !important;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    /* Primary Button */
    .btn-primary {
        background: var(--gold);
        border-color: var(--gold);
        color: var(--dark-blue);
        font-weight: 600;
    }

    .btn-primary:hover {
        background: #e5b465;
        border-color: #e5b465;
        color: var(--dark-blue);
    }

    /* Feedback Preview */
    .feedback-preview {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Action Buttons */
    .btn-outline-info { color: #17a2b8; border-color: #17a2b8; }
    .btn-outline-info:hover { background: #17a2b8; color: white; }
    .btn-outline-success { color: #28a745; border-color: #28a745; }
    .btn-outline-success:hover { background: #28a745; color: white; }
    .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover { background: #dc3545; color: white; }
</style>