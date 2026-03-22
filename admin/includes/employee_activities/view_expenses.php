<?php
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$where = ["e.expense_date BETWEEN '$date_from' AND '$date_to'"];
if (!empty($employee_filter)) {
    $where[] = "e.employee_id = $employee_filter";
}
if (!empty($status_filter)) {
    $where[] = "e.status = '" . mysqli_real_escape_string($connection, $status_filter) . "'";
}
$where_clause = implode(' AND ', $where);

// Get employees for filter
$employees_query = "SELECT user_id, first_name, last_name FROM users WHERE user_status = 'active' AND type_id = 1 ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Get expenses
$query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name,
          c.company_name
          FROM employee_expenses e
          JOIN users u ON e.employee_id = u.user_id
          LEFT JOIN clients c ON e.client_id = c.client_id
          WHERE $where_clause
          ORDER BY e.created_at DESC";
$result = mysqli_query($connection, $query);

// Get summary stats
$stats_query = "SELECT 
                COUNT(*) as total_expenses,
                SUM(e.amount) as total_amount,
                SUM(CASE WHEN e.status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN e.status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN e.status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                FROM employee_expenses e
                WHERE $where_clause";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Employee Expenses</h4>
        <div class="btn-group">
            <a href="?tab=expenses&report_type=excel&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&employee_id=<?php echo $employee_filter; ?>" 
               class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card-small bg-primary text-white"><div class="stat-icon"><i class="bi bi-receipt"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['total_expenses'] ?? 0; ?></h3><p class="stat-label">Total</p></div></div></div>
        <div class="col-md-3"><div class="stat-card-small bg-warning text-white"><div class="stat-icon"><i class="bi bi-clock-history"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['pending'] ?? 0; ?></h3><p class="stat-label">Pending</p></div></div></div>
        <div class="col-md-3"><div class="stat-card-small bg-success text-white"><div class="stat-icon"><i class="bi bi-check-circle"></i></div><div class="stat-content"><h3 class="stat-value"><?php echo $stats['approved'] ?? 0; ?></h3><p class="stat-label">Approved</p></div></div></div>
        <div class="col-md-3"><div class="stat-card-small bg-danger text-white"><div class="stat-icon"><i class="bi bi-x-circle"></i></div><div class="stat-content"><h3 class="stat-value">AED <?php echo number_format($stats['total_amount'] ?? 0, 2); ?></h3><p class="stat-label">Total Amount</p></div></div></div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="expenses">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['user_id']; ?>" <?php echo $employee_filter == $emp['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
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
            <h5 class="card-title"><i class="bi bi-list-ul me-2"></i>Expense Records</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Client</th>
                                <th>Type</th>
                                <th>Mode</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Receipt</th>
                                <th>Actions</th>
                            比
                        </thead>
                        <tbody>
                            <?php while($exp = mysqli_fetch_assoc($result)):
                                $status_color = $exp['status'] == 'Approved' ? 'success' : ($exp['status'] == 'Rejected' ? 'danger' : 'warning');
                                $client_name = $exp['company_name'] ?: ($exp['client_name'] ?: '-');
                            ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($exp['employee_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($client_name); ?></td>
                                <td><?php echo htmlspecialchars($exp['expense_type']); ?></td>
                                <td><?php echo htmlspecialchars($exp['mode_of_transport'] ?: '-'); ?></td>
                                <td class="fw-bold text-success">AED <?php echo number_format($exp['amount'], 2); ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $exp['status']; ?></span></td>
                                <td>
                                    <?php if($exp['receipt_file']): ?>
                                        <a href="../../uploads/expenses/<?php echo $exp['receipt_file']; ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($exp['status'] == 'Pending'): ?>
                                        <button class="btn btn-sm btn-success" onclick="approveExpense(<?php echo $exp['expense_id']; ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectExpense(<?php echo $exp['expense_id']; ?>)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewEmployeeDetails(<?php echo $exp['employee_id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-receipt display-1 text-muted"></i>
                    <h5 class="mt-3">No expenses found</h5>
                    <p class="text-muted">No employee expenses match your criteria.</p>
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