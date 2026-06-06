<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Resolve the logged-in user and any linked client records
$session_client_id = (int) ($_SESSION['client_id'] ?? 0);
$session_user_id = (int) ($_SESSION['user_id'] ?? 0);

if ($session_client_id <= 0 && $session_user_id <= 0) {
    echo '<div class="alert alert-danger">Invalid or missing client session. Please log in again.</div>';
    return;
}

$clients = [];

if ($session_user_id > 0) {
    $query = "SELECT * FROM clients WHERE user_id = " . $session_user_id . " ORDER BY company_name ASC, client_id ASC";
    $result = mysqli_query($connection, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clients[] = $row;
        }
    }
}

if (empty($clients) && $session_client_id > 0) {
    $query = "SELECT * FROM clients WHERE user_id = " . $session_client_id . " ORDER BY company_name ASC, client_id ASC";
    $result = mysqli_query($connection, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clients[] = $row;
        }
    }
}

if (empty($clients) && $session_client_id > 0) {
    $query = "SELECT * FROM clients WHERE client_id = " . $session_client_id . " LIMIT 1";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $clients[] = mysqli_fetch_assoc($result);
    }
}

if (empty($clients)) {
    echo '<div class="alert alert-danger">Client not found</div>';
    return;
}

$client_ids = [];
$client_since_year = null;
foreach ($clients as $client_record) {
    $client_ids[] = (int) $client_record['client_id'];
    if (!empty($client_record['created_at'])) {
        $record_year = date('Y', strtotime($client_record['created_at']));
        if ($client_since_year === null || $record_year < $client_since_year) {
            $client_since_year = $record_year;
        }
    }
}

$client_ids = array_values(array_unique(array_filter($client_ids)));
$client_ids_str = !empty($client_ids) ? implode(',', $client_ids) : '0';
$primary_client = $clients[0];
$company_count = count($clients);

// Get activity summary
$activity = [
    'total_logins' => 0,
    'last_login' => null
];

// Check if client_activity_log table exists
$table_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_activity_log'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $activity_query = "SELECT 
                       COUNT(*) as total_logins,
                       MAX(created_at) as last_login
                       FROM client_activity_log 
                       WHERE client_id IN ($client_ids_str) AND activity_type = 'login'";
    $activity_result = mysqli_query($connection, $activity_query);
    if ($activity_result) {
        $activity = mysqli_fetch_assoc($activity_result);
    }
}

// Get engagement stats
$stats = [
    'total_engagements' => 0,
    'completed' => 0
];

// Check if engagements table exists
$table_check = mysqli_query($connection, "SHOW TABLES LIKE 'engagements'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $stats_query = "SELECT 
                    COUNT(*) as total_engagements,
                    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as completed
                    FROM engagements 
                    WHERE client_id IN ($client_ids_str)";
    $stats_result = mysqli_query($connection, $stats_query);
    if ($stats_result) {
        $stats = mysqli_fetch_assoc($stats_result);
    }
}

// Ensure all values are set
$stats['total_engagements'] = $stats['total_engagements'] ?? 0;
$stats['completed'] = $stats['completed'] ?? 0;
$activity['total_logins'] = $activity['total_logins'] ?? 0;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Profile Card -->
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-building" style="font-size: 4rem; color: #f1bf70;"></i>
                        </div>
                    </div>
                    <h4>
                        <?php if ($company_count === 1): ?>
                            <?php echo htmlspecialchars($primary_client['company_name'] ?? 'N/A'); ?>
                        <?php else: ?>
                            <?php echo $company_count; ?> Companies Linked
                        <?php endif; ?>
                    </h4>
                    <p class="text-muted">
                        Client since <?php echo $client_since_year ?? 'N/A'; ?>
                    </p>

                    <?php if ($company_count > 1): ?>
                    <p class="small text-muted mb-0">
                        This account is linked to multiple company records. All attached profiles are shown below.
                    </p>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="profile.php?source=edit" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="profile.php?source=password" class="btn btn-outline-secondary">
                            <i class="bi bi-key"></i> Change Password
                        </a>
                        <a href="profile.php?source=activity" class="btn btn-outline-info">
                            <i class="bi bi-clock-history"></i> View Activity
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Quick Stats
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Companies Linked:</span>
                        <strong><?php echo $company_count; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Engagements:</span>
                        <strong><?php echo $stats['total_engagements']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completed:</span>
                        <strong><?php echo $stats['completed']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completion Rate:</span>
                        <strong>
                            <?php 
                            if ($stats['total_engagements'] > 0) {
                                echo round(($stats['completed'] / $stats['total_engagements']) * 100, 1) . '%';
                            } else {
                                echo '0%';
                            }
                            ?>
                        </strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Last Login:</span>
                        <small>
                            <?php 
                            if ($activity['last_login']) {
                                echo date('M d, Y', strtotime($activity['last_login']));
                            } else {
                                echo 'First time';
                            }
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <?php foreach ($clients as $index => $client): ?>
            <div class="card shadow-sm <?php echo $index > 0 ? 'mt-4' : ''; ?>">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-building me-2"></i><?php echo htmlspecialchars($client['company_name'] ?? 'N/A'); ?></span>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if ($company_count > 1): ?>
                        <span class="badge bg-light text-dark border">Company <?php echo $index + 1; ?> of <?php echo $company_count; ?></span>
                        <?php endif; ?>
                        <a href="profile.php?source=edit&amp;client_record_id=<?php echo (int) $client['client_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit This Company
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h6 class="text-uppercase text-muted mb-3">Company Information</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="150">Company Name:</th>
                                    <td><strong><?php echo htmlspecialchars($client['company_name'] ?? 'N/A'); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Trade License No:</th>
                                    <td><?php echo htmlspecialchars($client['trade_license_no'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <td><?php echo htmlspecialchars($client['country'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Emirate/Zone:</th>
                                    <td><?php echo htmlspecialchars($client['emirate_zone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Business Activity:</th>
                                    <td><?php echo nl2br(htmlspecialchars($client['business_activity'] ?? 'N/A')); ?></td>
                                </tr>
                                <tr>
                                    <th>Address:</th>
                                    <td><?php echo nl2br(htmlspecialchars($client['address'] ?? 'N/A')); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-lg-6">
                            <h6 class="text-uppercase text-muted mb-3">Contact Person</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="150">Full Name:</th>
                                    <td><strong><?php echo htmlspecialchars($client['contact_name'] ?? 'N/A'); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Designation:</th>
                                    <td><?php echo htmlspecialchars($client['contact_designation'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Mobile Number:</th>
                                    <td>
                                        <?php echo htmlspecialchars($client['contact_mobile'] ?? 'N/A'); ?>
                                        <?php if (!empty($client['contact_mobile'])): ?>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $client['contact_mobile']); ?>" target="_blank" class="btn btn-sm btn-success ms-2">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>
                                        <?php if (!empty($client['contact_email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($client['contact_email']); ?>">
                                            <?php echo htmlspecialchars($client['contact_email']); ?>
                                        </a>
                                        <?php else: ?>
                                        N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Client Since:</th>
                                    <td>
                                        <?php if (!empty($client['created_at'])): ?>
                                            <?php echo date('M d, Y', strtotime($client['created_at'])); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>