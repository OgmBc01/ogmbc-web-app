<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get analytics data
$total_depts_query = "SELECT COUNT(*) as total FROM departments";
$total_depts_result = mysqli_query($connection, $total_depts_query);
$total_departments = mysqli_fetch_assoc($total_depts_result)['total'];

$total_employees_query = "SELECT COUNT(*) as total FROM employees";
$total_employees_result = mysqli_query($connection, $total_employees_query);
$total_employees = mysqli_fetch_assoc($total_employees_result)['total'];

$avg_salary_query = "SELECT COALESCE(AVG(salary), 0) as avg_salary FROM employees";
$avg_salary_result = mysqli_query($connection, $avg_salary_query);
$avg_salary = round(mysqli_fetch_assoc($avg_salary_result)['avg_salary'], 2);

$top_dept_query = "SELECT d.dept_name, COUNT(e.employee_id) as emp_count 
                   FROM departments d
                   LEFT JOIN employees e ON d.id = e.department_id
                   GROUP BY d.id
                   ORDER BY emp_count DESC
                   LIMIT 1";
$top_dept_result = mysqli_query($connection, $top_dept_query);
$top_dept = mysqli_fetch_assoc($top_dept_result);

// Get chart data
$chart_query = "SELECT 
                d.dept_name,
                COUNT(e.employee_id) as employee_count,
                COALESCE(AVG(e.salary), 0) as avg_salary
              FROM departments d
              LEFT JOIN employees e ON d.id = e.department_id
              GROUP BY d.id
              HAVING employee_count > 0
              ORDER BY employee_count DESC
              LIMIT 10";
$chart_result = mysqli_query($connection, $chart_query);
$chart_data = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    $chart_data[] = $row;
}
?>

<div class="container-fluid">
    <!-- Analytics Dashboard -->
    <div class="analytics-dashboard">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-title mb-0">Departments Analytics</h1>
            <a href="departments.php?source=add_department" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Department
            </a>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Departments</h3>
                    <p class="stat-number"><?php echo $total_departments; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-details">
                    <h3>Total Employees</h3>
                    <p class="stat-number"><?php echo $total_employees; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-details">
                    <h3>Avg Salary</h3>
                    <p class="stat-number">AED <?php echo number_format($avg_salary, 2); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-pie-chart"></i>
                </div>
                <div class="stat-details">
                    <h3>Dept with Most Staff</h3>
                    <p class="stat-number"><?php echo $top_dept ? $top_dept['dept_name'] : 'N/A'; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Department Performance Chart -->
        <?php if (!empty($chart_data)): ?>
        <div class="chart-container">
            <canvas id="deptChart"></canvas>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            No department data available for chart visualization. Add employees to departments to see analytics.
        </div>
        <?php endif; ?>
    </div>

    <!-- Departments Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Existing Departments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Department Name</th>
                            <th>Manager</th>
                            <th>Budget</th>
                            <th>Location</th>
                            <th>Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $query = "SELECT d.*, 
                                  COUNT(e.employee_id) as employee_count,
                                  COALESCE(AVG(e.salary), 0) as avg_salary,
                                  CONCAT(emp.first_name, ' ', emp.last_name) as manager_name
                                  FROM departments d
                                  LEFT JOIN employees e ON d.id = e.department_id
                                  LEFT JOIN employees emp ON d.manager = emp.employee_id
                                  GROUP BY d.id
                                  ORDER BY d.id DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='8' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='8' class='text-center'>No departments found. <a href='departments.php?source=add_department'>Add your first department</a></td></tr>";
                        } else {
                            while($row = mysqli_fetch_assoc($result)) {
                                $dept_id = $row['id'];
                                $dept_name = $row['dept_name'];
                                $dept_code = $row['dept_code'];
                                $manager_name = $row['manager_name'] ?: 'Not Assigned';
                                $budget = number_format($row['budget'], 2);
                                $location = $row['location'] ?: 'Not specified';
                                $employee_count = $row['employee_count'];
                                ?>
                                <tr>
                                    <td><?php echo $dept_id; ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($dept_code); ?></span></td>
                                    <td><?php echo htmlspecialchars($dept_name); ?></td>
                                    <td><?php echo htmlspecialchars($manager_name); ?></td>
                                    <td>AED <?php echo $budget; ?></td>
                                    <td><?php echo htmlspecialchars($location); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $employee_count; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewDepartment(<?php echo $dept_id; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="departments.php?source=edit_department&id=<?php echo $dept_id; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $dept_id; ?>, '<?php echo htmlspecialchars($dept_name, ENT_QUOTES); ?>')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($chart_data)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deptChart').getContext('2d');
    const chartLabels = <?php echo json_encode(array_column($chart_data, 'dept_name')); ?>;
    const chartEmployees = <?php echo json_encode(array_column($chart_data, 'employee_count')); ?>;
    const chartSalaries = <?php echo json_encode(array_column($chart_data, 'avg_salary')); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Number of Employees',
                data: chartEmployees,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y-employees'
            },
            {
                label: 'Average Salary (AED)',
                data: chartSalaries,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y-salary'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                'y-employees': {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    stepSize: 1,
                    title: {
                        display: true,
                        text: 'Number of Employees'
                    }
                },
                'y-salary': {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Average Salary (AED)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'AED ' + value;
                        }
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Department Performance Analysis'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label.includes('Salary')) {
                                label += 'AED ' + context.raw.toFixed(2);
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>