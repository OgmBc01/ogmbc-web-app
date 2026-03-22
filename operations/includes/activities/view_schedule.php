<?php
$user_id = $_SESSION['user_id'];
$current_week = isset($_GET['schedule_week']) ? $_GET['schedule_week'] : date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week', strtotime($current_week)));

// Get saved schedule if exists
$schedule_query = "SELECT * FROM employee_weekly_schedule 
                   WHERE employee_id = $user_id AND week_start_date = '$current_week'";
$schedule_result = mysqli_query($connection, $schedule_query);
$saved_schedule = null;
if (mysqli_num_rows($schedule_result) > 0) {
    $schedule_data = mysqli_fetch_assoc($schedule_result);
    $saved_schedule = json_decode($schedule_data['schedule_data'], true);
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Weekly Schedule</h4>
        <div class="d-flex gap-2">
            <a href="?schedule_week=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($current_week))); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-left"></i> Prev Week
            </a>
            <span class="px-3 py-2 bg-light rounded"><?php echo date('M d', strtotime($current_week)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?></span>
            <a href="?schedule_week=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($current_week))); ?>" class="btn btn-outline-secondary btn-sm">
                Next Week <i class="bi bi-chevron-right"></i>
            </a>
            <button onclick="showScheduleModal('<?php echo $current_week; ?>')" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil"></i> Edit Schedule
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-calendar-week me-2"></i>Weekly Plan</h5>
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
                            $hours = $saved_schedule[$day]['hours'] ?? 9;
                            $place = $saved_schedule[$day]['place'] ?? 'OGMBC';
                            $clients = $saved_schedule[$day]['clients'] ?? '';
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
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                Use the "Edit Schedule" button to plan your week ahead.
            </div>
        </div>
    </div>
</div>

<style>
.dark-header { background: #1e293b; color: white; padding: 12px 20px; border-radius: 12px 12px 0 0; }
</style>