<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role and ID
$user_id = $_SESSION['user_id'];
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $user_id";
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

$can_validate = ($user_role == 'ceo_gm' || $user_role == 'hr_admin' || $user_role == 'admin_staff');

// Get selected filters
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : '';
$selected_client = isset($_GET['client_id']) ? (int)$_GET['client_id'] : '';

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_feedback,
                SUM(CASE WHEN is_validated = 1 THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN is_validated = 0 THEN 1 ELSE 0 END) as pending,
                COALESCE(SUM(CASE WHEN is_validated = 1 THEN 50 ELSE 0 END), 0) as total_points
                FROM client_feedback
                WHERE YEAR(created_at) = $selected_year";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get clients for filter
$clients_query = "SELECT client_id, company_name FROM clients ORDER BY company_name";
$clients_result = mysqli_query($connection, $clients_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Client Feedback</h1>
        <div>
            <a href="client_feedback.php?source=add_feedback" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Log New Feedback
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Feedback</h5>
                    <h2><?php echo $stats['total_feedback'] ?? 0; ?></h2>
                    <small>Year <?php echo $selected_year; ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Validated</h5>
                    <h2><?php echo $stats['validated'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2><?php echo $stats['pending'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Points Awarded</h5>
                    <h2><?php echo number_format($stats['total_points'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-control">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select id="month" name="month" class="form-control">
                        <option value="">All Months</option>
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($selected_month == $m) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="client_id" class="form-label">Client</label>
                    <select id="client_id" name="client_id" class="form-control">
                        <option value="">All Clients</option>
                        <?php while($client = mysqli_fetch_assoc($clients_result)): ?>
                            <option value="<?php echo $client['client_id']; ?>" <?php echo ($selected_client == $client['client_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($client['company_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-chat-quote me-2"></i>Client Feedback Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Date</th>
                            <th>Client</th>
                            <th>Employee</th>
                            <th>Feedback</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build query
                        $where = ["YEAR(cf.created_at) = $selected_year"];
                        if (!empty($selected_month)) {
                            $where[] = "MONTH(cf.created_at) = $selected_month";
                        }
                        if (!empty($selected_client)) {
                            $where[] = "cf.client_id = $selected_client";
                        }
                        
                        $where_clause = implode(' AND ', $where);
                        
                        $query = "SELECT cf.*, 
                                 c.company_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                                 CONCAT(v.first_name, ' ', v.last_name) as validated_by_name
                                 FROM client_feedback cf
                                 JOIN clients c ON cf.client_id = c.client_id
                                 LEFT JOIN users u ON cf.employee_id = u.user_id
                                 LEFT JOIN users v ON cf.validated_by = v.user_id
                                 WHERE $where_clause
                                 ORDER BY cf.created_at DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='7' class='text-center'>No feedback records found.</td></tr>";
                        } else {
                            while($feedback = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($feedback['company_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($feedback['employee_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($feedback['feedback_text'], 0, 50)) . '...'; ?>
                                        <?php if ($feedback['engagement_id']): ?>
                                            <span class="badge bg-info" title="Linked to engagement">#<?php echo $feedback['engagement_id']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($feedback['is_validated']): ?>
                                            <span class="badge bg-success">+50 pts</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($feedback['is_validated']): ?>
                                            <span class="badge bg-success">Validated</span>
                                            <br><small><?php echo htmlspecialchars($feedback['validated_by_name']); ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewFeedback(<?php echo $feedback['feedback_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        
                                        <?php if (!$feedback['is_validated'] && $can_validate): ?>
                                            <a href="client_feedback.php?validate=<?php echo $feedback['feedback_id']; ?>" class="btn btn-sm btn-success" title="Validate & Award Points" onclick="return confirm('Validate this feedback and award 50 points?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($can_validate && !$feedback['is_validated']): ?>
                                            <a href="client_feedback.php?delete=<?php echo $feedback['feedback_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>