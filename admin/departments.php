<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle delete action
$delete_feedback = '';
$delete_feedback_type = '';
if (isset($_GET['delete'])) {
    $dept_id = (int)$_GET['delete'];
    // Check if department has employees
    $check_query = "SELECT COUNT(*) as emp_count FROM employees WHERE department_id = $dept_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    if ($row['emp_count'] > 0) {
        $delete_feedback = "Cannot delete department with existing employees. Please reassign or remove employees first.";
        $delete_feedback_type = "danger";
    } else {
        $delete_query = "DELETE FROM departments WHERE id = $dept_id";
        if (mysqli_query($connection, $delete_query)) {
            $delete_feedback = "Department deleted successfully!";
            $delete_feedback_type = "success";
        } else {
            $delete_feedback = "Error deleting department: " . mysqli_error($connection);
            $delete_feedback_type = "danger";
        }
    }
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php 
                    if (isset($_GET['source'])) {
                        switch($_GET['source']) {
                            case 'add_department':
                                echo 'Add Department';
                                break;
                            case 'edit_department':
                                echo 'Edit Department';
                                break;
                            default:
                                echo 'Departments';
                        }
                    } else {
                        echo 'Departments';
                    }
                    ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all_departments';
                }

                switch($source) {
                    case 'add_department';
                        include "includes/add_department.php";
                        break;

                    case 'edit_department';
                        include "includes/edit_department.php";
                        break;

                    case 'view_all_departments';
                    default:
                        include "includes/view_all_departments.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Department Details Modal -->
<div class="modal fade" id="departmentDetailsModal" tabindex="-1" aria-labelledby="departmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header card-header text-white">
                <h5 class="modal-title" id="departmentDetailsModalLabel">Department Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="departmentDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading department details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editDepartmentBtn" class="btn btn-primary">Edit Department</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteFeedbackArea"></div>
                <p>Are you sure you want to delete the department "<span id="deleteDepartmentName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone and may affect employee records.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// View department details
function viewDepartment(id) {
    const modal = new bootstrap.Modal(document.getElementById('departmentDetailsModal'));
    const contentDiv = document.getElementById('departmentDetailsContent');
    
    // Show loading state
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading department details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch department details
    fetch('includes/get_department_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dept = data.department;
                const employees = data.employees || [];
                
                let employeesHtml = '';
                if (employees.length > 0) {
                    employeesHtml = `
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2">Department Employees (${employees.length})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Field of Study</th>
                                            <th>Qualification</th>
                                            <th>Graduation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    employees.forEach(emp => {
                        employeesHtml += `
                            <tr>
                                <td>${emp.employee_id}</td>
                                <td>${emp.first_name} ${emp.last_name}</td>
                                <td>${emp.field_of_study || 'N/A'}</td>
                                <td>${emp.qualification || 'N/A'}</td>
                                <td>${emp.highest_graduation || 'N/A'} ${emp.year_of_graduation ? '(' + emp.year_of_graduation + ')' : ''}</td>
                            </tr>
                        `;
                    });
                    
                    employeesHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    employeesHtml = `
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            No employees assigned to this department.
                        </div>
                    `;
                }
                
                contentDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="bg-light rounded-circle mx-auto mb-3" style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-building text-secondary" style="font-size:4rem;"></i>
                            </div>
                            <h4 class="mb-1">${dept.dept_name}</h4>
                            <p class="text-muted">Department Code: ${dept.dept_code}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Manager:</strong><br>${dept.manager_name || dept.manager || '<span class="text-muted">Not Assigned</span>'}</p>
                                    <p><strong>Budget:</strong><br>$${parseFloat(dept.budget).toFixed(2)}</p>
                                    <p><strong>Location:</strong><br>${dept.location || '<span class="text-muted">Not specified</span>'}</p>
                                    <p><strong>Total Employees:</strong><br>${employees.length}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Description:</strong><br>${dept.description ? dept.description.replace(/\n/g, '<br>') : '<span class="text-muted">No description provided.</span>'}</p>
                                </div>
                            </div>
                            ${employeesHtml}
                        </div>
                    </div>
                `;
                document.getElementById('editDepartmentBtn').href = 'departments.php?source=edit_department&id=' + dept.dept_id;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Error loading department details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    Error loading department details: ${error.message}
                </div>
            `;
        });
}

// Show delete confirmation modal
function confirmDelete(id, name) {
    document.getElementById('deleteDepartmentName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = 'departments.php?delete=' + id;
    document.getElementById('deleteFeedbackArea').innerHTML = '';
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Show delete feedback modal if delete was attempted
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($delete_feedback)): ?>
    document.getElementById('deleteFeedbackArea').innerHTML = '<div class="alert alert-<?php echo $delete_feedback_type; ?>">' + <?php echo json_encode($delete_feedback); ?> + '</div>';
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    <?php endif; ?>
});

// Handle menu toggle state
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a departments page and expand the menu
    const currentPath = window.location.pathname;
    const urlParams = new URLSearchParams(window.location.search);
    const source = urlParams.get('source');
    
    if (currentPath.includes('departments.php')) {
        const departmentsMenu = document.getElementById('departments-menu');
        const menuToggle = document.querySelector('[data-menu="departments"]');
        
        if (departmentsMenu && menuToggle) {
            departmentsMenu.classList.add('show');
            menuToggle.classList.add('expanded');
            
            // Mark active state based on source
            const addDeptLink = document.querySelector('a[href*="source=add_department"]');
            const viewAllLink = document.querySelector('a[href="./departments.php"]');
            
            if (addDeptLink && viewAllLink) {
                if (source === 'add_department') {
                    addDeptLink.classList.add('active');
                    viewAllLink.classList.remove('active');
                } else {
                    addDeptLink.classList.remove('active');
                    viewAllLink.classList.add('active');
                }
            }
        }
    }
});
</script>

<?php include "includes/footer.php"; ?>