<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_engagements,
                SUM(CASE WHEN status = 'ASSIGNED' THEN 1 ELSE 0 END) as assigned,
                SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review,
                SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected
                FROM engagements";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get overdue count
$overdue_query = "SELECT COUNT(*) as overdue FROM engagements 
                  WHERE status NOT IN ('CLOSED', 'SUBMITTED')
                  AND COALESCE(approved_deadline, original_deadline) < CURDATE()";
$overdue_result = mysqli_query($connection, $overdue_query);
$overdue = mysqli_fetch_assoc($overdue_result);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Engagement Management</h1>
        <div>
            <a href="engagements.php?source=add_engagement" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Engagement
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Engagements</h5>
                    <h2><?php echo $stats['total_engagements'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">In Progress</h5>
                    <h2><?php echo ($stats['assigned'] + $stats['in_progress']) ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Awaiting Review</h5>
                    <h2><?php echo $stats['awaiting_review'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Overdue</h5>
                    <h2><?php echo $overdue['overdue'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="source" value="view_all">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select id="status_filter" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="ASSIGNED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ASSIGNED') ? 'selected' : ''; ?>>Assigned</option>
                        <option value="IN_PROGRESS" <?php echo (isset($_GET['status']) && $_GET['status'] == 'IN_PROGRESS') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="AWAITING_REVIEW" <?php echo (isset($_GET['status']) && $_GET['status'] == 'AWAITING_REVIEW') ? 'selected' : ''; ?>>Awaiting Review</option>
                        <option value="SUBMITTED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'SUBMITTED') ? 'selected' : ''; ?>>Submitted</option>
                        <option value="CLOSED" <?php echo (isset($_GET['status']) && $_GET['status'] == 'CLOSED') ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="employee_filter" class="form-label">Assigned To</label>
                    <select id="employee_filter" name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        <?php
                        $emp_query = "SELECT user_id, first_name, last_name FROM users 
                                     WHERE user_id IN (SELECT DISTINCT assigned_to FROM engagements)
                                     ORDER BY first_name";
                        $emp_result = mysqli_query($connection, $emp_query);
                        while ($emp = mysqli_fetch_assoc($emp_result)) {
                            $selected = (isset($_GET['employee_id']) && $_GET['employee_id'] == $emp['user_id']) ? 'selected' : '';
                            echo "<option value='{$emp['user_id']}' $selected>" . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Start Date From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Start Date To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="engagements.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Engagements Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>All Engagements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>Engagement ID</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Assigned To</th>
                            <th>Start Date</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Overdue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        // Build query with filters
                        $where_conditions = [];
                        if (!empty($_GET['status'])) {
                            $status = mysqli_real_escape_string($connection, $_GET['status']);
                            $where_conditions[] = "e.status = '$status'";
                        }
                        if (!empty($_GET['employee_id'])) {
                            $employee_id = (int)$_GET['employee_id'];
                            $where_conditions[] = "e.assigned_to = $employee_id";
                        }
                        if (!empty($_GET['date_from'])) {
                            $date_from = mysqli_real_escape_string($connection, $_GET['date_from']);
                            $where_conditions[] = "e.start_date >= '$date_from'";
                        }
                        if (!empty($_GET['date_to'])) {
                            $date_to = mysqli_real_escape_string($connection, $_GET['date_to']);
                            $where_conditions[] = "e.start_date <= '$date_to'";
                        }
                        
                        $where_clause = '';
                        if (!empty($where_conditions)) {
                            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
                        }
                        
                        $query = "SELECT e.*, 
                                 c.company_name,
                                 s.service_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                                 DATEDIFF(CURDATE(), COALESCE(e.approved_deadline, e.original_deadline)) as days_overdue
                                 FROM engagements e
                                 JOIN clients c ON e.client_id = c.client_id
                                 JOIN service_types s ON e.service_id = s.service_id
                                 LEFT JOIN users u ON e.assigned_to = u.user_id
                                 $where_clause
                                 ORDER BY e.created_at DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='10' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='10' class='text-center'>No engagements found. <a href='engagements.php?source=add_engagement'>Create your first engagement</a></td></tr>";
                        } else {
                            while($engagement = mysqli_fetch_assoc($result)) {
                                // Determine status badge color
                                $status_class = 'secondary';
                                switch($engagement['status']) {
                                    case 'ASSIGNED':
                                        $status_class = 'info';
                                        break;
                                    case 'IN_PROGRESS':
                                        $status_class = 'primary';
                                        break;
                                    case 'AWAITING_REVIEW':
                                        $status_class = 'warning';
                                        break;
                                    case 'SUBMITTED':
                                        $status_class = 'success';
                                        break;
                                    case 'CLOSED':
                                        $status_class = 'dark';
                                        break;
                                    case 'REJECTED':
                                        $status_class = 'danger';
                                        break;
                                }
                                
                                $deadline = $engagement['approved_deadline'] ?? $engagement['original_deadline'];
                                $is_overdue = ($engagement['status'] != 'CLOSED' && $engagement['status'] != 'SUBMITTED' && strtotime($deadline) < time());
                                $overdue_days = $engagement['days_overdue'] > 0 ? $engagement['days_overdue'] : 0;
                                ?>
                                <tr id="engagement-row-<?php echo $engagement['engagement_id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($engagement['title']); ?></strong>
                                        <?php if (!empty($engagement['is_recurring'])): ?>
                                            <span class="badge bg-info ms-2" title="Recurring engagement (Sequence <?php echo $engagement['recurrence_sequence']; ?>)">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($engagement['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($engagement['service_name']); ?></td>
                                    <td><?php echo htmlspecialchars($engagement['assigned_to_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($engagement['start_date'])); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($deadline)); ?>
                                        <?php if ($engagement['approved_deadline']): ?>
                                            <span class="badge bg-warning" title="Approved deadline change">*</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $engagement['status']; ?></span></td>
                                    <td>
                                        <?php if ($is_overdue): ?>
                                            <span class="badge bg-danger"><?php echo $overdue_days; ?> days</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewEngagement(<?php echo $engagement['engagement_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($engagement['status'] != 'CLOSED'): ?>
                                            <a href="engagements.php?source=edit_engagement&id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="engagements.php?source=upload_evidence&id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-success" title="Upload Evidence">
                                                <i class="bi bi-upload"></i>
                                            </a>
                                            <a href="engagements.php?source=request_deadline_change&id=<?php echo $engagement['engagement_id']; ?>" class="btn btn-sm btn-primary" title="Request Deadline Change">
                                                <i class="bi bi-calendar-plus"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $engagement['engagement_id']; ?>, '<?php echo htmlspecialchars($engagement['title'], ENT_QUOTES); ?>')" title="Delete">
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