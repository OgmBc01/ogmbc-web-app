<?php
// Suppress PHP errors for AJAX endpoints
error_reporting(0);
ini_set('display_errors', 0);

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all employees from the database
$sql = "SELECT * FROM employees ORDER BY first_name, last_name";
$result = $connection->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $connection->error);
}
?>
<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Employee Management</h1>
            <a href="employees.php?source=add_employee" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Add New Employee
            </a>
        </div>
        <!-- Employees Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>All Employees</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="employeesTable">
                        <thead>
                            <tr class="table-dark">
                                <th width="50">#</th>
                                <th width="80">ID</th>
                                <th width="70">Image</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Field of Study</th>
                                <th>Qualification</th>
                                <th>Highest Graduation</th>
                                <th width="80">Year</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php 
                                $serial = 1;
                                while ($employee = $result->fetch_assoc()): 
                                ?>
                                    <tr id="employee-row-<?php echo $employee['employee_id']; ?>">
                                        <td class="fw-bold"><?php echo $serial++; ?></td>
                                        <td class="text-muted"><?php echo $employee['employee_id']; ?></td>
                                        <td>
                                            <?php
                                            $image_url = "";
                                            if (!empty($employee['user_image']) && file_exists("../uploads/profiles/" . $employee['user_image'])) {
                                                $image_url = "../uploads/profiles/" . $employee['user_image'];
                                            } else {
                                                $name = urlencode(($employee['first_name'] ?? '') . '+' . ($employee['last_name'] ?? ''));
                                                $image_url = "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=40";
                                            }
                                            ?>
                                            <img src="<?php echo $image_url; ?>" 
                                                 alt="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>"
                                                 class="rounded-circle" width="40" height="40"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=40'">
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['user_email']); ?></td>
                                        <td><?php echo !empty($employee['field_of_study']) ? htmlspecialchars($employee['field_of_study']) : '<span class="text-muted">N/A</span>'; ?></td>
                                        <td><?php echo !empty($employee['qualification']) ? htmlspecialchars($employee['qualification']) : '<span class="text-muted">N/A</span>'; ?></td>
                                        <td><?php echo !empty($employee['highest_graduation']) ? htmlspecialchars($employee['highest_graduation']) : '<span class="text-muted">N/A</span>'; ?></td>
                                        <td><?php echo !empty($employee['year_of_graduation']) ? htmlspecialchars($employee['year_of_graduation']) : '<span class="text-muted">N/A</span>'; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-employee-btn" 
                                                        onclick="viewEmployee(<?php echo $employee['employee_id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='employees.php?source=edit_employee&id=<?php echo $employee['employee_id']; ?>'
                                                   class="btn btn-outline-warning"
                                                   title="Edit Employee">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-outline-danger delete-employee-btn" 
                                                        onclick="showDeleteConfirmation(<?php echo $employee['employee_id']; ?>, '<?php echo htmlspecialchars(addslashes($employee['first_name'] . ' ' . $employee['last_name'])); ?>')"
                                                        title="Delete Employee">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-4 d-block mb-2"></i>
                                            <h5>No employees found</h5>
                                            <p>Get started by adding your first employee.</p>
                                            <a href="employees.php?source=add_employee" class="btn btn-primary mt-2">
                                                <i class="bi bi-person-plus"></i> Add Employee
                                            </a>
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

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header card-header text-white">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="employeeDetails">
                <!-- Employee details will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editEmployeeBtn" class="btn btn-primary">Edit Employee</a>
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
                Are you sure you want to delete <strong id="deleteEmployeeName"></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteEmployee()">
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
    .table th {
        font-weight: 600;
        background-color: #2c3e50 !important;
        color: white;
        vertical-align: middle;
    }
    .table-dark {
        --bs-table-bg: #2c3e50;
        --bs-table-color: white;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    #employeesTable tbody tr:hover {
        background-color: rgba(241, 191, 112, 0.1);
    }
    .btn-group .btn {
        border-radius: 4px !important;
    }
    .btn-group .btn:not(:last-child) {
        margin-right: 2px;
    }
    .employee-details-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 3px solid #f1bf70;
    }
</style>

<script>
// Global variables
let currentDeleteEmployeeId = null;

// View Employee Details
function viewEmployee(employeeId) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }
    
    // Show loading in modal
    document.getElementById('employeeDetails').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading employee details...</p>
        </div>
    `;
    
    // Show modal
    const viewModal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
    viewModal.show();
    
    // Fetch employee details
    fetch('includes/get_employee_details.php?id=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.employee) {
                const employee = data.employee;
                
                // Get image URL
                let imageUrl = "";
                if (employee.user_image && employee.user_image !== 'null' && employee.user_image !== '') {
                    imageUrl = '../uploads/profiles/' + employee.user_image;
                } else {
                    const name = encodeURIComponent((employee.first_name || '') + '+' + (employee.last_name || ''));
                    imageUrl = 'https://ui-avatars.com/api/?name=' + name + '&background=f1bf70&color=0f172a&size=120';
                }
                
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <img src="${imageUrl}" 
                                 alt="${escapeHtml(employee.first_name + ' ' + employee.last_name)}"
                                 class="rounded-circle employee-details-img mb-3"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=120'">
                            <h4 class="mb-1">${escapeHtml(employee.first_name + ' ' + employee.last_name)}</h4>
                            <p class="text-muted">Employee ID: ${employee.employee_id}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Email:</strong><br>
                                    <a href="mailto:${escapeHtml(employee.user_email)}">${escapeHtml(employee.user_email)}</a></p>
                                    
                                    <p><strong>Phone:</strong><br>
                                    ${employee.contact_number ? escapeHtml(employee.contact_number) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Address:</strong><br>
                                    ${employee.address ? escapeHtml(employee.address) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Gender:</strong><br>
                                    ${employee.gender ? escapeHtml(employee.gender) : '<span class="text-muted">N/A</span>'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Field of Study:</strong><br>
                                    ${employee.field_of_study ? escapeHtml(employee.field_of_study) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Qualification:</strong><br>
                                    ${employee.qualification ? escapeHtml(employee.qualification) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Highest Graduation:</strong><br>
                                    ${employee.highest_graduation ? escapeHtml(employee.highest_graduation) : '<span class="text-muted">N/A</span>'}</p>
                                    
                                    <p><strong>Year of Graduation:</strong><br>
                                    ${employee.year_of_graduation ? escapeHtml(employee.year_of_graduation) : '<span class="text-muted">N/A</span>'}</p>
                                </div>
                            </div>
                            ${employee.bio ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Bio/Description:</h6>
                                    <div class="bg-light p-3 rounded">
                                        ${escapeHtml(employee.bio).replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                            </div>` : ''}
                        </div>
                    </div>`;
                
                document.getElementById('employeeDetails').innerHTML = detailsHtml;
                document.getElementById('editEmployeeBtn').href = 'employees.php?source=edit_employee&id=' + employeeId;
            } else {
                document.getElementById('employeeDetails').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Failed to load employee details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('employeeDetails').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading employee details. Please try again.
                </div>
            `;
            console.error('Error:', error);
        });
}

// Show Delete Confirmation
function showDeleteConfirmation(employeeId, employeeName) {
    if (!employeeId) {
        showError('Invalid employee ID');
        return;
    }
    
    currentDeleteEmployeeId = employeeId;
    document.getElementById('deleteEmployeeName').textContent = employeeName;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

// Delete Employee
function deleteEmployee() {
    if (!currentDeleteEmployeeId) {
        showError('No employee selected for deletion');
        return;
    }
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    fetch('includes/delete_employee.php?id=' + currentDeleteEmployeeId)
        .then(response => response.json())
        .then(data => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                modal.hide();
                
                // Remove row from table
                const row = document.getElementById('employee-row-' + currentDeleteEmployeeId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.4s';
                    setTimeout(() => {
                        row.remove();
                        // If using DataTables, you might need to redraw
                        if (typeof $.fn.DataTable !== 'undefined' && $('#employeesTable').DataTable()) {
                            $('#employeesTable').DataTable().clear().draw();
                        }
                    }, 400);
                }
                
                // Show success message
                showSuccess(data.message || 'Employee deleted successfully!');
                currentDeleteEmployeeId = null;
            } else {
                showError(data.message || 'Failed to delete employee');
            }
        })
        .catch(error => {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            showError('Error deleting employee: ' + error.message);
            console.error('Error:', error);
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

// Initialize DataTable when jQuery is available
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#employeesTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            responsive: true,
            language: {
                search: "Search employees:",
                lengthMenu: "Show _MENU_ employees per page",
                info: "Showing _START_ to _END_ of _TOTAL_ employees",
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