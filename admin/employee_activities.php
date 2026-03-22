<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$current_year = date('Y');
$current_month = date('m');

// Handle expense approval/rejection
if (isset($_GET['approve_expense']) && is_numeric($_GET['approve_expense'])) {
    $expense_id = (int)$_GET['approve_expense'];
    $update_query = "UPDATE employee_expenses SET status = 'Approved', approved_by = {$_SESSION['user_id']}, approved_at = NOW() WHERE expense_id = $expense_id";
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "Expense approved successfully!";
    } else {
        $_SESSION['error_message'] = "Error approving expense: " . mysqli_error($connection);
    }
    echo '<script>window.location.href="employee_activities.php?tab=expenses";</script>';
    return;
}

if (isset($_GET['reject_expense']) && is_numeric($_GET['reject_expense'])) {
    $expense_id = (int)$_GET['reject_expense'];
    $update_query = "UPDATE employee_expenses SET status = 'Rejected', approved_by = {$_SESSION['user_id']}, approved_at = NOW() WHERE expense_id = $expense_id";
    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success_message'] = "Expense rejected.";
    } else {
        $_SESSION['error_message'] = "Error rejecting expense: " . mysqli_error($connection);
    }
    echo '<script>window.location.href="employee_activities.php?tab=expenses";</script>';
    return;
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Card -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">
                            <i class="bi bi-people me-2"></i>Employee Activities
                        </div>
                        <div class="welcome-subtitle">Monitor and manage all employee tasks, activities, and expenses.</div>
                    </div>
                    <div class="current-date mt-3 mt-md-0">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Employee Activities</li>
            </ol>
        </nav>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="employeeActivityTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'activities') ? 'active' : ''; ?>" 
                        id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button" role="tab">
                    <i class="bi bi-calendar-check me-2"></i>Daily Activities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'tasks') ? 'active' : ''; ?>" 
                        id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
                    <i class="bi bi-list-check me-2"></i>Tasks
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'schedule') ? 'active' : ''; ?>" 
                        id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="bi bi-calendar-week me-2"></i>Weekly Schedule
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'expenses') ? 'active' : ''; ?>" 
                        id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab">
                    <i class="bi bi-cash-stack me-2"></i>Expenses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'reports') ? 'active' : ''; ?>" 
                        id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Reports
                </button>
            </li>
        </ul>

        <div class="tab-content" id="employeeActivityTabsContent">
            <!-- Daily Activities Tab -->
            <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'activities') ? 'show active' : ''; ?>" id="activities" role="tabpanel">
                <?php include "includes/employee_activities/view_activities.php"; ?>
            </div>

            <!-- Tasks Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'tasks') ? 'show active' : ''; ?>" id="tasks" role="tabpanel">
                <?php include "includes/employee_activities/view_tasks.php"; ?>
            </div>

            <!-- Schedule Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'schedule') ? 'show active' : ''; ?>" id="schedule" role="tabpanel">
                <?php include "includes/employee_activities/view_schedule.php"; ?>
            </div>

            <!-- Expenses Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'expenses') ? 'show active' : ''; ?>" id="expenses" role="tabpanel">
                <?php include "includes/employee_activities/view_expenses.php"; ?>
            </div>

            <!-- Reports Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'reports') ? 'show active' : ''; ?>" id="reports" role="tabpanel">
                <?php include "includes/employee_activities/reports.php"; ?>
            </div>
        </div>
    </div>
</div>

<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Employee Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading employee details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewEmployeeDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('employeeDetailsModal'));
    const contentDiv = document.getElementById('employeeDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading employee details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/get_employee_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

function approveExpense(id) {
    if (confirm('Approve this expense claim?')) {
        window.location.href = 'employee_activities.php?approve_expense=' + id + '&tab=expenses';
    }
}

function rejectExpense(id) {
    if (confirm('Reject this expense claim?')) {
        window.location.href = 'employee_activities.php?reject_expense=' + id + '&tab=expenses';
    }
}

function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
        const container = document.querySelector('.container-fluid');
        const div = document.createElement('div');
        div.id = 'alertBox';
        div.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        container.prepend(div);
        setTimeout(() => div.remove(), 5000);
    }
}
</script>

<style>
.welcome-card {
    background: linear-gradient(135deg, #0a2342 0%, #19376d 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(10, 35, 66, 0.18);
    width: 100%;
}
.welcome-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 10px; }
.welcome-subtitle { font-size: 1rem; opacity: 0.9; margin-bottom: 0; }
.current-date {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
}
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 10px 20px;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #667eea;
    background: transparent;
    border-bottom: 3px solid #667eea;
}
@media (max-width: 768px) {
    .welcome-title { font-size: 1.4rem; }
    .welcome-card { padding: 18px; }
}
</style>

<?php include "includes/footer.php"; ?>