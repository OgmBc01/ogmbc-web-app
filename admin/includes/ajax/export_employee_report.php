<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'monthly';
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

if($report_type == 'monthly') {
    $start_date = date('Y-m-01', strtotime("$selected_year-$selected_month-01"));
    $end_date = date('Y-m-t', strtotime("$selected_year-$selected_month-01"));
} elseif($report_type == 'weekly') {
    $week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));
    $start_date = $week_start;
    $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($week_start)));
}

// Get employee name if filtered
$employee_name = 'All Employees';
if($employee_filter) {
    $emp_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = $employee_filter";
    $emp_result = mysqli_query($connection, $emp_query);
    if($emp_result && mysqli_num_rows($emp_result) > 0) {
        $employee_name = mysqli_fetch_assoc($emp_result)['name'];
    }
}

// Get activities
$where = ["activity_date BETWEEN '$start_date' AND '$end_date'"];
if(!empty($employee_filter)) { $where[] = "employee_id = $employee_filter"; }
$activities_query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
                     FROM employee_activities a
                     JOIN users u ON a.employee_id = u.user_id
                     WHERE " . implode(' AND ', $where) . "
                     ORDER BY a.employee_id, a.activity_date";
$activities_result = mysqli_query($connection, $activities_query);

// Get tasks
$tasks_query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name, c.company_name
                FROM employee_tasks t
                JOIN users u ON t.employee_id = u.user_id
                JOIN clients c ON t.client_id = c.client_id
                WHERE (t.date_started BETWEEN '$start_date' AND '$end_date' OR t.updated_at BETWEEN '$start_date' AND '$end_date')
                " . (!empty($employee_filter) ? "AND t.employee_id = $employee_filter" : "") . "
                ORDER BY t.employee_id, t.updated_at DESC";
$tasks_result = mysqli_query($connection, $tasks_query);

// Get expenses
$expenses_query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name, c.company_name
                   FROM employee_expenses e
                   JOIN users u ON e.employee_id = u.user_id
                   LEFT JOIN clients c ON e.client_id = c.client_id
                   WHERE e.expense_date BETWEEN '$start_date' AND '$end_date'
                   " . (!empty($employee_filter) ? "AND e.employee_id = $employee_filter" : "") . "
                   ORDER BY e.employee_id, e.expense_date DESC";
$expenses_result = mysqli_query($connection, $expenses_query);

$filename = "Employee_Report_" . str_replace(' ', '_', $employee_name) . "_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

echo "<html><body>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";

// Header
echo "<tr style='background:#0a2240; color:#f1bf70;'><td colspan='6'><h2 style='margin:0;'>Employee Activity Report</h2></td></tr>";
echo "<tr><td colspan='6'><b>Employee:</b> $employee_name</td></tr>";
echo "<tr><td colspan='6'><b>Period:</b> " . date('M d, Y', strtotime($start_date)) . " - " . date('M d, Y', strtotime($end_date)) . "</td></tr>";
echo "<tr><td colspan='6'>&nbsp;</td></tr>";

// Activities Section
echo "<tr style='background:#667eea; color:white;'><th colspan='6'>DAILY ACTIVITIES</th></tr>";
echo "<tr style='background:#e9ecef;'><th>Date</th><th>Employee</th><th>Hours</th><th>Clients</th><th>Location</th><th>Nature of Work</th></tr>";
$total_hours = 0;
while($act = mysqli_fetch_assoc($activities_result)) {
    $total_hours += $act['hours_worked'];
    echo "<tr>";
    echo "<td>" . date('M d, Y', strtotime($act['activity_date'])) . "</td>";
    echo "<td>" . htmlspecialchars($act['employee_name']) . "</td>";
    echo "<td>" . $act['hours_worked'] . "</td>";
    echo "<td>" . htmlspecialchars($act['clients_attended'] ?: '-') . "</td>";
    echo "<td>" . htmlspecialchars($act['work_location']) . "</td>";
    echo "<td>" . nl2br(htmlspecialchars($act['nature_of_work'])) . "</td>";
    echo "</tr>";
}
if(mysqli_num_rows($activities_result) == 0) {
    echo "<tr><td colspan='6' align='center'>No activities recorded</td></tr>";
}
echo "<tr><td colspan='3'><b>Total Hours:</b> $total_hours hrs</td><td colspan='3'>&nbsp;</td></tr>";
echo "<tr><td colspan='6'>&nbsp;</td></tr>";

// Tasks Section
echo "<tr style='background:#667eea; color:white;'><th colspan='6'>TASKS</th></tr>";
echo "<tr style='background:#e9ecef;'><th>Employee</th><th>Client</th><th>Job Type</th><th>Status</th><th>Date Started</th><th>Remarks</th></tr>";
$task_count = 0;
while($task = mysqli_fetch_assoc($tasks_result)) {
    $task_count++;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($task['employee_name']) . "</td>";
    echo "<td>" . htmlspecialchars($task['company_name']) . "</td>";
    echo "<td>" . htmlspecialchars($task['job_type']) . "</td>";
    echo "<td>" . htmlspecialchars($task['status']) . "</td>";
    echo "<td>" . ($task['date_started'] ? date('M d, Y', strtotime($task['date_started'])) : '-') . "</td>";
    echo "<td>" . htmlspecialchars(substr($task['remarks'] ?? '', 0, 100)) . "</td>";
    echo "</tr>";
}
if($task_count == 0) { echo "<tr><td colspan='6' align='center'>No tasks recorded</td></tr>"; }
echo "<tr><td colspan='6'>&nbsp;</td></tr>";

// Expenses Section
echo "<tr style='background:#667eea; color:white;'><th colspan='6'>EXPENSES</th></tr>";
echo "<tr style='background:#e9ecef;'><th>Date</th><th>Employee</th><th>Client</th><th>Type</th><th>Amount</th><th>Status</th></tr>";
$total_amount = 0;
while($exp = mysqli_fetch_assoc($expenses_result)) {
    $total_amount += $exp['amount'];
    echo "<tr>";
    echo "<td>" . date('M d, Y', strtotime($exp['expense_date'])) . "</td>";
    echo "<td>" . htmlspecialchars($exp['employee_name']) . "</td>";
    echo "<td>" . htmlspecialchars($exp['company_name'] ?: '-') . "</td>";
    echo "<td>" . htmlspecialchars($exp['expense_type']) . "</td>";
    echo "<td>AED " . number_format($exp['amount'], 2) . "</td>";
    echo "<td>" . $exp['status'] . "</td>";
    echo "</tr>";
}
if(mysqli_num_rows($expenses_result) == 0) { echo "<tr><td colspan='6' align='center'>No expenses recorded</td></tr>"; }
echo "<tr><td colspan='3'><b>Total Expenses:</b> AED " . number_format($total_amount, 2) . "</td><td colspan='3'>&nbsp;</td></tr>";
echo "<tr><td colspan='6'>&nbsp;</td></tr>";

// Summary
echo "<tr style='background:#e9ecef;'><td colspan='6'><b>Summary:</b> Total Hours: $total_hours hrs | Total Tasks: $task_count | Total Expenses: AED " . number_format($total_amount, 2) . "</td></tr>";
echo "<tr><td colspan='6' align='center'><i>Report generated on " . date('M d, Y H:i:s') . "</i></td></tr>";
echo "</table></body></html>";

ob_end_flush();
?>