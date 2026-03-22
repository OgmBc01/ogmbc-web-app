<?php
$user_id = $_SESSION['user_id'];
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
$week_end = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));

// Get activities for the week
$activities_query = "SELECT * FROM employee_activities 
                     WHERE employee_id = $user_id 
                     AND activity_date BETWEEN '$week_start' AND '$week_end'
                     ORDER BY activity_date DESC";
$activities_result = mysqli_query($connection, $activities_query);

// Get total hours for the week
$hours_query = "SELECT SUM(hours_worked) as total_hours FROM employee_activities 
                WHERE employee_id = $user_id 
                AND activity_date BETWEEN '$week_start' AND '$week_end'";
$hours_result = mysqli_query($connection, $hours_query);
$total_hours = mysqli_fetch_assoc($hours_result)['total_hours'] ?? 0;
?>

<div class="container-fluid">
    <!-- Header with Date Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Daily Activity Log</h4>
        <div class="d-flex gap-2">
            <a href="?date=<?php echo date('Y-m-d', strtotime('-7 days', strtotime($selected_date))); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-left"></i> Previous Week
            </a>
            <span class="px-3 py-2 bg-light rounded"><?php echo date('M d, Y', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?></span>
            <a href="?date=<?php echo date('Y-m-d', strtotime('+7 days', strtotime($selected_date))); ?>" class="btn btn-outline-secondary btn-sm">
                Next Week <i class="bi bi-chevron-right"></i>
            </a>
            <button onclick="showAddActivityModal()" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Log Activity
            </button>
        </div>
    </div>

    <!-- Weekly Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history text-primary"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $total_hours; ?> hrs</h3>
                        <p class="stat-label mb-0">Total Hours This Week</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check text-success"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo mysqli_num_rows($activities_result); ?></h3>
                        <p class="stat-label mb-0">Days Logged</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-briefcase text-info"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo round($total_hours / 8, 1); ?></h3>
                        <p class="stat-label mb-0">Working Days Equivalent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title"><i class="bi bi-list-ul me-2"></i>Daily Activities</h5>
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($activities_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Hours</th>
                                <th>Clients</th>
                                <th>Location</th>
                                <th>Nature of Work</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($activity = mysqli_fetch_assoc($activities_result)): 
                                $day_name = date('l', strtotime($activity['activity_date']));
                            ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($activity['activity_date'])); ?></td>
                                <td><?php echo $day_name; ?></td>
                                <td><span class="badge bg-primary"><?php echo $activity['hours_worked']; ?> hrs</span></td>
                                <td><?php echo htmlspecialchars($activity['clients_attended'] ?: '-'); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $activity['work_location']; ?></span></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($activity['nature_of_work'], 0, 100))); ?><?php echo strlen($activity['nature_of_work']) > 100 ? '...' : ''; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewActivity(<?php echo $activity['activity_id']; ?>)">
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
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h5 class="mt-3">No activities logged this week</h5>
                    <p class="text-muted">Start logging your daily activities.</p>
                    <button class="btn btn-primary mt-2" onclick="showAddActivityModal()">
                        <i class="bi bi-plus-circle me-2"></i>Log Today's Activity
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border-left: 6px solid #e0e0e0;
    padding: 0;
    height: 100%;
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-success { border-left-color: #38c172; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-body {
    padding: 18px 20px;
    display: flex;
    align-items: center;
}
.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    background: #f5f6fa;
    border-radius: 50%;
}
.stat-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: #222;
}
.stat-label {
    font-size: 0.85rem;
    color: #666;
}
.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
</style>

<!-- Activity Detail Modal -->
<div class="modal fade" id="activityDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Activity Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activityDetailBody">
                <div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
function viewActivity(id) {
        const modal = new bootstrap.Modal(document.getElementById('activityDetailModal'));
        const body = document.getElementById('activityDetailBody');
        body.innerHTML = '<div class="text-center text-muted"><i class="bi bi-arrow-repeat"></i> Loading...</div>';
        modal.show();
        fetch('includes/ajax/get_activity_detail.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                        if (data.success) {
                                const a = data.activity;
                                body.innerHTML = `
                                    <div class="mb-2"><strong>Date:</strong> ${a.activity_date}</div>
                                    <div class="mb-2"><strong>Day:</strong> ${a.day_name || ''}</div>
                                    <div class="mb-2"><strong>Hours Worked:</strong> ${a.hours_worked} hrs</div>
                                    <div class="mb-2"><strong>Clients Attended:</strong> ${a.clients_attended || '-'} </div>
                                    <div class="mb-2"><strong>Work Location:</strong> ${a.work_location}</div>
                                    <div class="mb-2"><strong>Nature of Work:</strong><br><span class="text-break">${a.nature_of_work.replace(/\n/g, '<br>')}</span></div>
                                `;
                        } else {
                                body.innerHTML = '<div class="text-danger">' + (data.message || 'Failed to load activity details') + '</div>';
                        }
                })
                .catch(() => {
                        body.innerHTML = '<div class="text-danger">Server error. Please try again.</div>';
                });
}
</script>