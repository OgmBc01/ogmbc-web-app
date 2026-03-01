<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

$is_admin = ($user_role == 'hr_admin' || $user_role == 'ceo_gm' || $user_role == 'admin_staff');

// Handle add/update/delete if admin
if ($is_admin && isset($_POST['add_band'])) {
    $dept_type = mysqli_real_escape_string($connection, $_POST['dept_type']);
    $min_perf = floatval($_POST['min_performance']);
    $max_perf = floatval($_POST['max_performance']);
    $increment = floatval($_POST['increment_percentage']);
    
    $insert_query = "INSERT INTO salary_increment_bands 
                    (department_type, min_performance, max_performance, increment_percentage)
                    VALUES ('$dept_type', $min_perf, $max_perf, $increment)";
    mysqli_query($connection, $insert_query);
    
    $_SESSION['success_message'] = "Salary band added successfully!";
    header("Location: cdp_annual.php?tab=bands");
    exit();
}

if ($is_admin && isset($_GET['delete_band'])) {
    $band_id = (int)$_GET['delete_band'];
    $delete_query = "DELETE FROM salary_increment_bands WHERE band_id = $band_id";
    mysqli_query($connection, $delete_query);
    
    $_SESSION['success_message'] = "Salary band deleted successfully!";
    header("Location: cdp_annual.php?tab=bands");
    exit();
}

// Fetch bands
$ops_bands_query = "SELECT * FROM salary_increment_bands WHERE department_type = 'OPERATIONS' ORDER BY min_performance";
$ops_bands_result = mysqli_query($connection, $ops_bands_query);

$sales_bands_query = "SELECT * FROM salary_increment_bands WHERE department_type = 'SALES' ORDER BY min_performance";
$sales_bands_result = mysqli_query($connection, $sales_bands_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Salary Increment Bands</h1>
    </div>

    <?php if ($is_admin): ?>
    <!-- Add New Band Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Salary Band</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="dept_type" class="form-label">Department Type</label>
                    <select id="dept_type" name="dept_type" class="form-control" required>
                        <option value="OPERATIONS">Operations</option>
                        <option value="SALES">Sales</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="min_performance" class="form-label">Min %</label>
                    <input type="number" step="0.1" id="min_performance" name="min_performance" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label for="max_performance" class="form-label">Max %</label>
                    <input type="number" step="0.1" id="max_performance" name="max_performance" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label for="increment_percentage" class="form-label">Increment %</label>
                    <input type="number" step="0.1" id="increment_percentage" name="increment_percentage" class="form-control" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="add_band" class="btn btn-primary">Add Band</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Operations Bands -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Operations Department</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Performance Range</th>
                                <th>Increment</th>
                                <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($band = mysqli_fetch_assoc($ops_bands_result)): ?>
                            <tr>
                                <td><?php echo $band['min_performance']; ?>% - <?php echo $band['max_performance']; ?>%</td>
                                <td><strong><?php echo $band['increment_percentage']; ?>%</strong></td>
                                <?php if ($is_admin): ?>
                                <td>
                                    <a href="cdp_annual.php?tab=bands&delete_band=<?php echo $band['band_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this band?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sales Bands -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Sales Department</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Performance Range</th>
                                <th>Increment</th>
                                <?php if ($is_admin): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($band = mysqli_fetch_assoc($sales_bands_result)): ?>
                            <tr>
                                <td><?php echo $band['min_performance']; ?>% - <?php echo $band['max_performance']; ?>%</td>
                                <td><strong><?php echo $band['increment_percentage']; ?>%</strong></td>
                                <?php if ($is_admin): ?>
                                <td>
                                    <a href="cdp_annual.php?tab=bands&delete_band=<?php echo $band['band_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete this band?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Default Bands Info -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Default Bands (from requirements)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Operations</h6>
                    <ul>
                        <li>65% - 74% → 20% increment</li>
                        <li>75% - 85% → 30% increment</li>
                        <li>86% - 100% → 35% increment</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Sales</h6>
                    <ul>
                        <li>65% - 80% → 25% increment</li>
                        <li>81% - 90% → 30% increment</li>
                        <li>91% - 100% → 35% increment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>