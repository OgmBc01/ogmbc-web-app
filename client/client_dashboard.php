<?php
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

// // Check authentication
// if (!isset($_SESSION['client_id'])) {
//     echo "<script>window.location.href = '../login.php';</script>";
//     exit();
// }

$client_id = $_SESSION['client_id'];

// Initialize variables with default values
$active_engagements = 0;
$unread_notifications = 0;
$files_count = 0;

// Get active engagements count with error handling
$engagements_query = "SELECT COUNT(*) as total FROM engagements WHERE client_id = " . intval($client_id) . " AND status NOT IN ('CLOSED', 'SUBMITTED')";
$engagements_result = mysqli_query($connection, $engagements_query);
if ($engagements_result) {
    $active_engagements = mysqli_fetch_assoc($engagements_result)['total'];
} else {
    // Table might not exist yet, silently use default value
    error_log("Engagements query failed: " . mysqli_error($connection));
}

// Get recent files - check if table exists first
$files_result = null;
$files_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_files'");
if (mysqli_num_rows($files_check) > 0) {
    $files_query = "SELECT * FROM client_files WHERE client_id = " . intval($client_id) . " ORDER BY uploaded_at DESC LIMIT 5";
    $files_result = mysqli_query($connection, $files_query);
    if ($files_result) {
        $files_count = mysqli_num_rows($files_result);
    }
} else {
    // Table doesn't exist yet, create empty result set
    $files_result = null;
}

// Get assigned staff
$staff_result = null;
$staff_query = "SELECT DISTINCT 
                u.user_id, u.first_name, u.last_name, u.user_email, r.role_name,
                e.engagement_id, e.title
                FROM engagements e
                JOIN users u ON e.assigned_to = u.user_id
                LEFT JOIN user_roles r ON u.role_id = r.role_id
                WHERE e.client_id = " . intval($client_id) . " AND e.status != 'CLOSED'
                LIMIT 2";
$staff_result = mysqli_query($connection, $staff_query);
if (!$staff_result) {
    $staff_result = null;
}

// Get unread notifications - check if table exists
$notif_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_notifications'");
if (mysqli_num_rows($notif_check) > 0) {
    $notif_query = "SELECT COUNT(*) as total FROM client_notifications WHERE client_id = " . intval($client_id) . " AND is_read = 0";
    $notif_result = mysqli_query($connection, $notif_query);
    if ($notif_result) {
        $unread_notifications = mysqli_fetch_assoc($notif_result)['total'];
    }
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Welcome Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['client_name'] ?? 'Client'); ?>!</h2>
                        <p class="mb-0">Here's what's happening with your engagements today.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="bi bi-briefcase" style="color: #f1bf70; font-size: 2rem;"></i>
                        </div>
                        <div class="stat-number"><?php echo $active_engagements; ?></div>
                        <div class="stat-title">Active Engagements</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="bi bi-file-earmark" style="color: #f1bf70; font-size: 2rem;"></i>
                        </div>
                        <div class="stat-number"><?php echo $files_count; ?></div>
                        <div class="stat-title">Files Shared</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="bi bi-bell" style="color: #f1bf70; font-size: 2rem;"></i>
                        </div>
                        <div class="stat-number"><?php echo $unread_notifications; ?></div>
                        <div class="stat-title">Notifications</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon mb-2">
                            <i class="bi bi-star" style="color: #f1bf70; font-size: 2rem;"></i>
                        </div>
                        <div class="stat-number"><?php echo date('Y'); ?></div>
                        <div class="stat-title">Year</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Your Team & Recent Activity -->
        <div class="row">
            <!-- Your Team Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Your Team
                    </div>
                    <div class="card-body">
                        <?php if ($staff_result && mysqli_num_rows($staff_result) > 0): ?>
                            <?php while($staff = mysqli_fetch_assoc($staff_result)): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo ucfirst($staff['role_name'] ?? 'Staff'); ?></small>
                                    </div>
                                    <div class="btn-group">
                                        <a href="https://wa.me/?text=Hi%20<?php echo urlencode($staff['first_name']); ?>%2C%20I%20have%20a%20question%20about%20engagement%20%23<?php echo $staff['engagement_id']; ?>" target="_blank" class="btn btn-sm btn-success" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                        <a href="mailto:<?php echo $staff['user_email']; ?>?subject=Question%20about%20engagement%20%23<?php echo $staff['engagement_id']; ?>" class="btn btn-sm btn-info" title="Email">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                        <button class="btn btn-sm btn-primary" title="Call" onclick="alert('Contact your admin for phone numbers')">
                                            <i class="bi bi-telephone"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No staff assigned yet.</p>
                        <?php endif; ?>
                        <a href="engagements.php" class="btn btn-outline-primary btn-sm w-100 mt-2">
                            View All Engagements
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Files Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-file-earmark me-2"></i>Recent Files
                    </div>
                    <div class="card-body">
                        <?php if ($files_result && mysqli_num_rows($files_result) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($file = mysqli_fetch_assoc($files_result)): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-earmark-text me-2"></i>
                                                <?php echo htmlspecialchars(substr($file['file_name'], 0, 20)) . '...'; ?>
                                                <br><small class="text-muted"><?php echo date('M d', strtotime($file['uploaded_at'])); ?></small>
                                            </div>
                                            <a href="includes/download_file.php?id=<?php echo $file['file_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No files yet.</p>
                        <?php endif; ?>
                        <a href="files.php" class="btn btn-outline-primary btn-sm w-100 mt-2">
                            View All Files
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="engagements.php" class="btn btn-outline-primary text-start">
                                <i class="bi bi-briefcase me-2"></i> Track My Engagements
                            </a>
                            <a href="files.php?source=upload" class="btn btn-outline-success text-start">
                                <i class="bi bi-cloud-upload me-2"></i> Upload Documents
                            </a>
                            <a href="support.php?source=new" class="btn btn-outline-info text-start">
                                <i class="bi bi-question-circle me-2"></i> Get Support
                            </a>
                            <a href="feedback.php" class="btn btn-outline-warning text-start">
                                <i class="bi bi-star me-2"></i> Leave Feedback
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary text-start">
                                <i class="bi bi-person me-2"></i> Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/client_footer.php'; ?>