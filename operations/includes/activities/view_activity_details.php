<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$activity_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$activity_id) {
    $_SESSION['error_message'] = 'Invalid activity ID';
    header('Location: activities.php');
    exit();
}

$query = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
          FROM employee_activities a
          JOIN users u ON a.employee_id = u.user_id
          WHERE a.activity_id = $activity_id AND a.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = 'Activity not found';
    header('Location: activities.php');
    exit();
}

$activity = mysqli_fetch_assoc($result);
?>

<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">Activity Details</h5>
            <a href="activities.php" class="btn btn-sm btn-outline-light">Back</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h6 class="info-title">Basic Information</h6>
                        <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($activity['activity_date'])); ?></p>
                        <p><strong>Day:</strong> <?php echo date('l', strtotime($activity['activity_date'])); ?></p>
                        <p><strong>Hours Worked:</strong> <?php echo $activity['hours_worked']; ?> hours</p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($activity['work_location']); ?></p>
                        <p><strong>Clients Attended:</strong> <?php echo htmlspecialchars($activity['clients_attended'] ?: 'None'); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card">
                        <h6 class="info-title">Work Details</h6>
                        <div class="work-details">
                            <?php echo nl2br(htmlspecialchars($activity['nature_of_work'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
}
.info-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
}
.work-details {
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>