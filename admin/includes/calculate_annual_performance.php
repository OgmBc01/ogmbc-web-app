<?php
// Start output buffering
ob_start();

// Check if user is logged in and is HR admin
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

if ($user_role != 'hr_admin' && $user_role != 'ceo_gm' && $user_role != 'admin_staff') {
    $_SESSION['error_message'] = "You don't have permission to calculate performance.";
    ob_end_clean();
    header("Location: cdp_annual.php?tab=annual");
    exit();
}

// Get parameters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_employee = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

// Initialize variables
$message = '';
$message_type = '';
$showSuccessModal = false;
$calculation_results = [];

// Get employees for dropdown
$employees_query = "SELECT u.user_id, u.first_name, u.last_name, 
                   CASE WHEN r.role_name IN ('operations_staff', 'operations') THEN 'OPERATIONS' ELSE 'SALES' END as dept_type
                   FROM users u
                   LEFT JOIN user_roles r ON u.role_id = r.role_id
                   WHERE u.user_status = 'active'
                   ORDER BY u.first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Handle calculation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate_performance'])) {
    
    $employee_id = (int)$_POST['employee_id'];
    $year = (int)$_POST['year'];
    
    // Get employee department type
    $dept_query = "SELECT CASE WHEN r.role_name IN ('operations_staff', 'operations') THEN 'OPERATIONS' ELSE 'SALES' END as dept_type
                   FROM users u
                   LEFT JOIN user_roles r ON u.role_id = r.role_id
                   WHERE u.user_id = $employee_id";
    $dept_result = mysqli_query($connection, $dept_query);
    $dept_row = mysqli_fetch_assoc($dept_result);
    $dept_type = $dept_row['dept_type'] ?? 'SALES';
    
    // Get total points for the year
    $points_query = "SELECT SUM(points) as total_points 
                    FROM points_ledger 
                    WHERE employee_id = $employee_id 
                    AND YEAR(created_at) = $year
                    AND points_type = 'EARNED'";
    $points_result = mysqli_query($connection, $points_query);
    $points_row = mysqli_fetch_assoc($points_result);
    $total_points = $points_row['total_points'] ?? 0;
    
    // Calculate base percentage
    $base_percentage = 0;
    if ($dept_type == 'OPERATIONS') {
        $base_percentage = min(70, ($total_points / 12000) * 70);
    } else {
        $base_percentage = min(75, ($total_points / 12000) * 75);
    }
    
    // Get CDP uplifts for the year
    $cdp_query = "SELECT 
                  SUM(CASE WHEN cdp_type = 'CERTIFICATE' THEN uplift_percentage ELSE 0 END) as certificate_uplift,
                  SUM(CASE WHEN cdp_type = 'COURSE' THEN uplift_percentage ELSE 0 END) as course_uplift,
                  SUM(CASE WHEN cdp_type = 'LOYALTY' THEN uplift_percentage ELSE 0 END) as loyalty_uplift,
                  SUM(CASE WHEN cdp_type = 'BEHAVIOR' THEN uplift_percentage ELSE 0 END) as behavior_uplift
                  FROM cdp_records 
                  WHERE employee_id = $employee_id 
                  AND YEAR(effective_date) = $year
                  AND status = 'APPROVED'";
    $cdp_result = mysqli_query($connection, $cdp_query);
    $cdp_row = mysqli_fetch_assoc($cdp_result);
    
    $certificate_uplift = $cdp_row['certificate_uplift'] ?? 0;
    $course_uplift = $cdp_row['course_uplift'] ?? 0;
    $loyalty_uplift = $cdp_row['loyalty_uplift'] ?? 0;
    $behavior_uplift = $cdp_row['behavior_uplift'] ?? 0;
    $total_uplift = $certificate_uplift + $course_uplift + $loyalty_uplift + $behavior_uplift;
    
    // Calculate final percentage (capped at 100)
    $final_percentage = min(100, $base_percentage + $total_uplift);
    
    // Determine recommended band
    $band_query = "SELECT band_id, increment_percentage 
                   FROM salary_increment_bands 
                   WHERE department_type = '$dept_type'
                   AND min_performance <= $final_percentage 
                   AND max_performance >= $final_percentage
                   AND is_active = 1";
    $band_result = mysqli_query($connection, $band_query);
    $band_row = mysqli_fetch_assoc($band_result);
    $recommended_band = $band_row ? $band_row['increment_percentage'] . '% increment' : 'No band found';
    
    // Check if performance record exists
    $check_query = "SELECT performance_id FROM annual_performance 
                    WHERE employee_id = $employee_id AND year = $year";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing
        $perf_row = mysqli_fetch_assoc($check_result);
        $update_query = "UPDATE annual_performance SET 
                        total_points = $total_points,
                        base_percentage = $base_percentage,
                        cdp_uplift = $certificate_uplift,
                        loyalty_uplift = $loyalty_uplift,
                        behavior_uplift = $behavior_uplift,
                        total_uplift = $total_uplift,
                        final_percentage = $final_percentage,
                        recommended_band = '$recommended_band',
                        status = 'DRAFT'
                        WHERE performance_id = {$perf_row['performance_id']}";
        mysqli_query($connection, $update_query);
        $perf_id = $perf_row['performance_id'];
    } else {
        // Insert new
        $insert_query = "INSERT INTO annual_performance 
                        (employee_id, year, total_points, base_percentage, cdp_uplift, 
                         loyalty_uplift, behavior_uplift, total_uplift, final_percentage, 
                         recommended_band, status, created_by)
                        VALUES 
                        ($employee_id, $year, $total_points, $base_percentage, $certificate_uplift,
                         $loyalty_uplift, $behavior_uplift, $total_uplift, $final_percentage,
                         '$recommended_band', 'DRAFT', $user_id)";
        mysqli_query($connection, $insert_query);
        $perf_id = mysqli_insert_id($connection);
    }
    
    $calculation_results = [
        'employee_id' => $employee_id,
        'year' => $year,
        'total_points' => $total_points,
        'base_percentage' => round($base_percentage, 1),
        'certificate_uplift' => $certificate_uplift,
        'course_uplift' => $course_uplift,
        'loyalty_uplift' => $loyalty_uplift,
        'behavior_uplift' => $behavior_uplift,
        'total_uplift' => $total_uplift,
        'final_percentage' => round($final_percentage, 1),
        'recommended_band' => $recommended_band,
        'perf_id' => $perf_id
    ];
    
    $showSuccessModal = true;
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Calculate Annual Performance</h5>
                    <a href="cdp_annual.php?tab=annual" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Performance
                    </a>
                </div>
                <div class="card-body">
                    
                    <form method="POST" action="" class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select id="employee_id" name="employee_id" class="form-control" required>
                                <option value="">Select Employee</option>
                                <?php while($emp = mysqli_fetch_assoc($employees_result)): ?>
                                    <option value="<?php echo $emp['user_id']; ?>" <?php echo ($selected_employee == $emp['user_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="year" class="form-label">Year</label>
                            <select id="year" name="year" class="form-control" required>
                                <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="calculate_performance" class="btn btn-primary w-100">
                                <i class="bi bi-calculator"></i> Calculate
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($calculation_results)): ?>
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Calculation Results</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Total Points:</th>
                                            <td><strong><?php echo number_format($calculation_results['total_points']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Base Percentage:</th>
                                            <td><?php echo $calculation_results['base_percentage']; ?>%</td>
                                        </tr>
                                        <tr>
                                            <th>Certificate Uplift:</th>
                                            <td>+<?php echo $calculation_results['certificate_uplift']; ?>%</td>
                                        </tr>
                                        <tr>
                                            <th>Course Uplift:</th>
                                            <td>+<?php echo $calculation_results['course_uplift']; ?>%</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Loyalty Uplift:</th>
                                            <td>+<?php echo $calculation_results['loyalty_uplift']; ?>%</td>
                                        </tr>
                                        <tr>
                                            <th>Behavior Uplift:</th>
                                            <td>+<?php echo $calculation_results['behavior_uplift']; ?>%</td>
                                        </tr>
                                        <tr>
                                            <th>Total Uplift:</th>
                                            <td class="text-success">+<?php echo $calculation_results['total_uplift']; ?>%</td>
                                        </tr>
                                        <tr>
                                            <th class="fs-5">Final Percentage:</th>
                                            <td class="fs-5"><strong><?php echo $calculation_results['final_percentage']; ?>%</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Recommended Band:</strong> <?php echo $calculation_results['recommended_band']; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="cdp_annual.php?tab=annual" class="btn btn-success">
                                    <i class="bi bi-list-ul"></i> View Performance List
                                </a>
                                <a href="cdp_annual.php?tab=annual&annual_source=calculate" class="btn btn-outline-primary">
                                    <i class="bi bi-calculator"></i> Calculate Another
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>