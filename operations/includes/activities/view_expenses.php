<?php
$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['expense_status']) ? $_GET['expense_status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$where = ["employee_id = $user_id", "expense_date BETWEEN '$date_from' AND '$date_to'"];
if (!empty($status_filter)) {
    $where[] = "status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
$where_clause = implode(' AND ', $where);

$expenses_query = "SELECT * FROM employee_expenses 
                   WHERE $where_clause 
                   ORDER BY expense_date DESC";
$expenses_result = mysqli_query($connection, $expenses_query);

$total_amount = 0;
mysqli_data_seek($expenses_result, 0);
while($exp = mysqli_fetch_assoc($expenses_result)) {
    $total_amount += $exp['amount'];
}
mysqli_data_seek($expenses_result, 0);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Expenses Tracker</h4>
        <button class="btn btn-primary btn-sm" onclick="showAddExpenseModal()">
            <i class="bi bi-plus-circle"></i> Add Expense
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card-small bg-primary text-white">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-content"><h3 class="stat-value">AED <?php echo number_format($total_amount, 2); ?></h3><p class="stat-label">Total This Month</p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-small bg-success text-white">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-content"><h3 class="stat-value"><?php echo mysqli_num_rows($expenses_result); ?></h3><p class="stat-label">Total Expenses</p></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-small bg-info text-white">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-content"><h3 class="stat-value">Pending: <?php 
                    $pending_query = "SELECT COUNT(*) as cnt FROM employee_expenses WHERE employee_id = $user_id AND status = 'Pending'";
                    $pending_result = mysqli_query($connection, $pending_query);
                    $pending = mysqli_fetch_assoc($pending_result);
                    echo $pending['cnt'];
                ?></h3><p class="stat-label">Awaiting Approval</p></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="expense_status" class="form-select">
                        <option value="">All</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-receipt me-2"></i>Expense Records</h5>
        </div>
        <div class="card-body p-0">
            <?php if(mysqli_num_rows($expenses_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php while($exp = mysqli_fetch_assoc($expenses_result)):
                                $status_color = $exp['status'] == 'Approved' ? 'success' : ($exp['status'] == 'Rejected' ? 'danger' : 'warning');
                                $client_name = '';
                                if($exp['client_id']) {
                                    $client_q = "SELECT company_name FROM clients WHERE client_id = " . $exp['client_id'];
                                    $client_r = mysqli_query($connection, $client_q);
                                    if($client_r && mysqli_num_rows($client_r) > 0) {
                                        $client_name = mysqli_fetch_assoc($client_r)['company_name'];
                                    }
                                } else {
                                    $client_name = $exp['client_name'] ?: '-';
                                }
                            ?>
                             <tr>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><?php echo htmlspecialchars($client_name); ?></td>
                                <td><?php echo htmlspecialchars($exp['expense_type']); ?></td>
                                <td><?php echo htmlspecialchars($exp['mode_of_transport'] ?: '-'); ?></td>
                                <td class="fw-bold text-success">AED <?php echo number_format($exp['amount'], 2); ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $exp['status']; ?></span></td>
                                <td>
                                    <?php if($exp['receipt_file']): ?>
                                        <a href="../../uploads/expenses/<?php echo $exp['receipt_file']; ?>" class="btn btn-sm btn-outline-info" target="_blank" title="View Receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if($exp['status'] == 'Pending'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editExpense(<?php echo $exp['expense_id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-receipt display-1 text-muted"></i>
                    <h5 class="mt-3">No expenses recorded</h5>
                    <p class="text-muted">Add your first expense to track your spending.</p>
                    <button class="btn btn-primary mt-2" onclick="showAddExpenseModal()"><i class="bi bi-plus-circle me-2"></i>Add Expense</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card-small {
    border-radius: 12px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
}
.stat-card-small .stat-icon { width: 40px; height: 40px; border-radius: 8px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.stat-card-small .stat-value { font-size: 1.2rem; font-weight: 600; margin-bottom: 2px; }
.stat-card-small .stat-label { font-size: 0.7rem; opacity: 0.9; margin: 0; }
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 60px 20px; }
</style>