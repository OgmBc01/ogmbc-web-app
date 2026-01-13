<?php

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
                                    <tr>
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
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-info flex-fill" 
                                                        onclick="viewEmployee(<?php echo $employee['employee_id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                              
                                                <a href='employees.php?source=edit_employee&id=<?php echo $employee['employee_id']; ?>'
                                                   class="btn btn-sm btn-warning flex-fill" title="Edit Employee">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
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
                                            <a href="add_employee.php" class="btn btn-primary mt-2">
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
    .d-flex.gap-1 {
        gap: 0.25rem !important;
    }
    .flex-fill {
        flex: 1 1 auto;
    }
</style>

<script>
    function viewEmployee(employeeId) {
        // Fetch employee details via AJAX
        fetch('get_employee_details.php?id=' + employeeId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('employeeDetails').innerHTML = data;
                document.getElementById('editEmployeeBtn').href = 'edit_employee.php?id=' + employeeId;
                
                // Show the modal
                const viewModal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
                viewModal.show();
            })
            .catch(error => {
                document.getElementById('employeeDetails').innerHTML = 
                    '<div class="alert alert-danger">Error loading employee details.</div>';
            });
    }

    // Initialize DataTables if needed
    document.addEventListener('DOMContentLoaded', function() {
        // You can initialize DataTables here if you want advanced table features
        // $('#employeesTable').DataTable();
    });
</script>