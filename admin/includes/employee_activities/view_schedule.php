<?php
$employee_filter = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : '';
$week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));

$where = ["1=1"];
if (!empty($employee_filter)) {
    $where[] = "s.employee_id = $employee_filter";
}
$where_clause = implode(' AND ', $where);

// Get employees for filter
$employees_query = "SELECT user_id, first_name, last_name FROM users WHERE user_status = 'active' AND type_id = 1 ORDER BY first_name";
$employees_result = mysqli_query($connection, $employees_query);

// Get schedules
$query = "SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
          FROM employee_weekly_schedule s
          JOIN users u ON s.employee_id = u.user_id
          WHERE s.week_start_date = '$week_start' AND $where_clause
          ORDER BY u.first_name";
$result = mysqli_query($connection, $query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Weekly Schedules</h4>
        <div class="d-flex gap-2">
            <a href="?tab=schedule&week_start=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($week_start))); ?>&employee_id=<?php echo $employee_filter; ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-left"></i> Prev Week
            </a>
            <span class="px-3 py-2 bg-light rounded"><?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime('sunday this week', strtotime($week_start))); ?></span>
            <a href="?tab=schedule&week_start=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($week_start))); ?>&employee_id=<?php echo $employee_filter; ?>" class="btn btn-outline-secondary btn-sm">
                Next Week <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="tab" value="schedule">
                <input type="hidden" name="week_start" value="<?php echo $week_start; ?>">
                <div class="col-md-4">
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Display -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while($schedule = mysqli_fetch_assoc($result)):
            $schedule_data = json_decode($schedule['schedule_data'], true);
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header dark-header">
                <h5 class="card-title"><i class="bi bi-person me-2"></i><?php echo htmlspecialchars($schedule['employee_name']); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Day</th>
                                <th>Hours</th>
                                <th>Location</th>
                                <th>Clients / Tasks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            foreach($days as $day):
                                $hours = $schedule_data[$day]['hours'] ?? 9;
                                $place = $schedule_data[$day]['place'] ?? 'OGMBC';
                                $clients = $schedule_data[$day]['clients'] ?? '';
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo $day; ?></td>
                                <td class="text-center"><?php echo $hours; ?> hrs</td>
                                <td><?php echo htmlspecialchars($place); ?></td>
                                <td><?php echo htmlspecialchars($clients); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-calendar-week display-1 text-muted"></i>
            <h5 class="mt-3">No schedules found</h5>
            <p class="text-muted">No weekly schedules for the selected period.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
.empty-state { text-align: center; padding: 60px 20px; }
</style>