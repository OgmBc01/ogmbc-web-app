<?php
include 'includes/sales_header.php';
include 'includes/sales_nav.php';
include 'includes/sales_sidebar.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$current_year = date('Y');
$current_week = date('W');
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Card -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">
                            <i class="bi bi-calendar-check me-2"></i>My Activities
                        </div>
                        <div class="welcome-subtitle">Track your daily tasks, activities, and generate reports.</div>
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
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Activities</li>
            </ol>
        </nav>

        <!-- Alert Messages Container -->
        <div id="alertBox"></div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="activityTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab">
                    <i class="bi bi-calendar-day me-2"></i>Daily Log
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
                    <i class="bi bi-list-check me-2"></i>My Tasks
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                    <i class="bi bi-calendar-week me-2"></i>Weekly Schedule
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab">
                    <i class="bi bi-cash-stack me-2"></i>Expenses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Reports
                </button>
            </li>
        </ul>

        <div class="tab-content" id="activityTabsContent">
            <!-- Daily Log Tab -->
            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                <?php include "includes/activities/view_activities.php"; ?>
            </div>

            <!-- Tasks Tab -->
            <div class="tab-pane fade" id="tasks" role="tabpanel">
                <?php include "includes/activities/view_tasks.php"; ?>
            </div>

            <!-- Weekly Schedule Tab -->
            <div class="tab-pane fade" id="schedule" role="tabpanel">
                <?php include "includes/activities/view_schedule.php"; ?>
            </div>

            <!-- Expenses Tab -->
            <div class="tab-pane fade" id="expenses" role="tabpanel">
                <?php include "includes/activities/view_expenses.php"; ?>
            </div>

            <!-- Reports Tab -->
            <div class="tab-pane fade" id="reports" role="tabpanel">
                <?php include "includes/activities/reports.php"; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Log Daily Activity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addActivityForm" method="POST" action="includes/activities/add_activity.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Activity Date *</label>
                            <input type="date" name="activity_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hours Worked *</label>
                            <input type="number" step="0.5" name="hours_worked" class="form-control" value="9" min="0" max="24" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Clients Attended</label>
                        <input type="text" name="clients_attended" class="form-control" placeholder="e.g., Abusalim, Jamespro, Rentacrane">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Work Location</label>
                        <select name="work_location" class="form-select">
                            <option value="OGMBC">OGMBC Office</option>
                            <option value="Client Place">Client Place</option>
                            <option value="Work from Home">Work from Home</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nature of Work / Details *</label>
                        <textarea name="nature_of_work" class="form-control" rows="4" 
                                  placeholder="Describe your work in detail..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add/Edit Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm" method="POST" action="includes/activities/add_task.php">
                <div class="modal-body">
                    <input type="hidden" name="task_id" id="task_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client *</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">Select Client</option>
                                <?php
                                // Only show clients related to engagements assigned to the current user
                                $clients_query = "SELECT DISTINCT c.client_id, c.company_name
                                    FROM clients c
                                    INNER JOIN engagements e ON c.client_id = e.client_id
                                    WHERE e.assigned_to = $user_id
                                    ORDER BY c.company_name";
                                $clients_result = mysqli_query($connection, $clients_query);
                                while($client = mysqli_fetch_assoc($clients_result)) {
                                    echo "<option value='{$client['client_id']}'>" . htmlspecialchars($client['company_name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Type</label>
                            <input type="text" name="job_type" class="form-control" placeholder="e.g., Bookkeeping, VAT Filing">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="Work in progress">Work in progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Pending">Pending</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Started</label>
                            <input type="date" name="date_started" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" 
                                  placeholder="Describe the work progress..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Given for Review</label>
                            <input type="date" name="date_given_for_review" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimated Completion Date</label>
                            <input type="date" name="estimated_completion_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Pending</label>
                        <textarea name="reason_for_pending" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoicing Status</label>
                            <select name="invoicing_status" class="form-select">
                                <option value="Not Invoiced">Not Invoiced</option>
                                <option value="Invoiced">Invoiced</option>
                                <option value="Partially Paid">Partially Paid</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="Pending">Pending</option>
                                <option value="Partial">Partial</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Add Expense</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm" method="POST" action="includes/activities/add_expense.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Date *</label>
                        <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select">
                            <option value="">Select Client</option>
                            <?php
                            $clients_result = mysqli_query($connection, $clients_query);
                            mysqli_data_seek($clients_result, 0);
                            while($client = mysqli_fetch_assoc($clients_result)) {
                                echo "<option value='{$client['client_id']}'>" . htmlspecialchars($client['company_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expense Type</label>
                            <select name="expense_type" class="form-select">
                                <option value="Transport">Transport</option>
                                <option value="Meals">Meals</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mode of Transport</label>
                            <input type="text" name="mode_of_transport" class="form-control" placeholder="e.g., Bus, Metro, Taxi">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receipt (Optional)</label>
                        <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editExpenseForm" method="POST" action="includes/activities/edit_expense.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="expense_id" id="edit_expense_id">
                    <div class="mb-3">
                        <label class="form-label">Expense Date *</label>
                        <input type="date" name="expense_date" id="edit_expense_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select name="client_id" id="edit_client_id" class="form-select">
                            <option value="">Select Client</option>
                            <?php
                            $clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
                            $clients_result = mysqli_query($connection, $clients_query);
                            mysqli_data_seek($clients_result, 0);
                            while($client = mysqli_fetch_assoc($clients_result)) {
                                echo "<option value='{$client['client_id']}'>" . htmlspecialchars($client['company_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expense Type</label>
                            <select name="expense_type" id="edit_expense_type" class="form-select">
                                <option value="Transport">Transport</option>
                                <option value="Meals">Meals</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mode of Transport</label>
                            <input type="text" name="mode_of_transport" id="edit_mode_of_transport" class="form-control" placeholder="e.g., Bus, Metro, Taxi">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receipt (Optional)</label>
                        <input type="file" name="receipt_file" class="form-control" accept="image/*,.pdf">
                        <div class="form-text" id="current_receipt_info"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Weekly Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-week me-2"></i>Weekly Schedule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST" action="includes/activities/save_schedule.php">
                <div class="modal-body">
                    <input type="hidden" name="week_start" id="schedule_week_start">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Hours</th>
                                    <th>Place</th>
                                    <th>Clients</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleRows">
                                <?php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                foreach($days as $day) {
                                    echo "
                                    <tr>
                                        <td><strong>$day</strong></td>
                                        <td><input type='number' step='0.5' name='hours[$day]' class='form-control' value='9' style='width:80px'></td>
                                        <td>
                                            <select name='place[$day]' class='form-select'>
                                                <option value='OGMBC'>OGMBC</option>
                                                <option value='Client Place'>Client Place</option>
                                                <option value='Work from Home'>Work from Home</option>
                                            </select>
                                        </td>
                                        <td><input type='text' name='clients[$day]' class='form-control' placeholder='e.g., Jamespro, Abusalim'></td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
// Persist selected tab using localStorage
document.addEventListener('DOMContentLoaded', function() {
    // Restore last selected tab
    const lastTab = localStorage.getItem('activityTab');
    if (lastTab) {
        const trigger = document.querySelector(`[data-bs-target="${lastTab}"]`);
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }
    // Listen for tab changes
    document.querySelectorAll('#activityTabs button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activityTab', e.target.getAttribute('data-bs-target'));
        });
    });
});
</script>

<script>
function showAddActivityModal() {
    document.getElementById('addActivityForm').reset();
    document.getElementById('addActivityForm').querySelector('input[name="activity_date"]').value = new Date().toISOString().split('T')[0];
    new bootstrap.Modal(document.getElementById('addActivityModal')).show();
}

function showAddTaskModal(taskId = null) {
    const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    if (taskId) {
        fetch('includes/ajax/get_task_details.php?id=' + taskId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const task = data.task;
                    document.getElementById('task_id').value = task.task_id;
                    document.querySelector('#addTaskForm select[name="client_id"]').value = task.client_id;
                    document.querySelector('#addTaskForm input[name="job_type"]').value = task.job_type;
                    document.querySelector('#addTaskForm select[name="status"]').value = task.status;
                    document.querySelector('#addTaskForm input[name="date_started"]').value = task.date_started;
                    document.querySelector('#addTaskForm textarea[name="remarks"]').value = task.remarks;
                    document.querySelector('#addTaskForm input[name="date_given_for_review"]').value = task.date_given_for_review;
                    document.querySelector('#addTaskForm input[name="estimated_completion_date"]').value = task.estimated_completion_date;
                    document.querySelector('#addTaskForm textarea[name="reason_for_pending"]').value = task.reason_for_pending;
                    document.querySelector('#addTaskForm select[name="invoicing_status"]').value = task.invoicing_status;
                    document.querySelector('#addTaskForm select[name="payment_status"]').value = task.payment_status;
                }
            });
    } else {
        document.getElementById('addTaskForm').reset();
        document.getElementById('task_id').value = '';
    }
    modal.show();
}

function showAddExpenseModal() {
    document.getElementById('addExpenseForm').reset();
    new bootstrap.Modal(document.getElementById('addExpenseModal')).show();
}

// Edit expense function
function editExpense(id) {
    fetch('includes/ajax/get_expense_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const expense = data.expense;
                document.getElementById('edit_expense_id').value = expense.expense_id;
                document.getElementById('edit_expense_date').value = expense.expense_date;
                document.getElementById('edit_client_id').value = expense.client_id;
                document.getElementById('edit_expense_type').value = expense.expense_type;
                document.getElementById('edit_mode_of_transport').value = expense.mode_of_transport;
                document.getElementById('edit_amount').value = expense.amount;
                document.getElementById('edit_description').value = expense.description;
                // Show current receipt info
                const receiptInfo = document.getElementById('current_receipt_info');
                if (expense.receipt_file) {
                    receiptInfo.innerHTML = '<i class="bi bi-receipt me-1"></i> Current file: ' + expense.receipt_file;
                } else {
                    receiptInfo.innerHTML = '<i class="bi bi-info-circle me-1"></i> No receipt uploaded';
                }
                const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                modal.show();
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            showAlert('Error loading expense details', 'danger');
        });
}

// Delete expense function
function deleteExpense(id) {
    if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        fetch('includes/ajax/delete_expense.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'expense_id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            showAlert('Error deleting expense', 'danger');
        });
    }
}

function showScheduleModal(weekStart) {
    document.getElementById('schedule_week_start').value = weekStart;
    fetch('includes/ajax/get_weekly_schedule.php?week_start=' + weekStart)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.schedule) {
                const schedule = data.schedule;
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                days.forEach(day => {
                    if (schedule[day]) {
                        document.querySelector(`input[name="hours[${day}]"]`).value = schedule[day].hours || 9;
                        document.querySelector(`select[name="place[${day}]"]`).value = schedule[day].place || 'OGMBC';
                        document.querySelector(`input[name="clients[${day}]"]`).value = schedule[day].clients || '';
                    }
                });
            }
        });
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}

function exportReport(type, startDate, endDate) {
    let url = 'includes/ajax/export_report.php?type=' + type;
    if (startDate) url += '&start_date=' + startDate;
    if (endDate) url += '&end_date=' + endDate;
    window.location.href = url;
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

// AJAX submit for Add/Edit Task form
document.addEventListener('DOMContentLoaded', function() {
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(addTaskForm);
            fetch('includes/ajax/add_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
                    if (modal) modal.hide();
                    // Redirect to tasks tab
                    window.location.href = 'activities.php#tasks';
                } else {
                    showAlert(data.message || 'Failed to save task', 'danger');
                }
            })
            .catch(() => {
                showAlert('Server error. Please try again.', 'danger');
            });
        });
    }
});
</script>
</script>

<style>
.welcome-card {
    background: linear-gradient(135deg, #0a2342 0%, #19376d 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    width: 100%;
}
.welcome-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}
.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0;
}
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

<?php include 'includes/sales_footer.php'; ?>