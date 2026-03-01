<?php
// Get activity logs
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$logs_query = "SELECT * FROM client_activity_log 
               WHERE client_id = $client_id 
               ORDER BY created_at DESC 
               LIMIT $offset, $limit";
$logs_result = mysqli_query($connection, $logs_query);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM client_activity_log WHERE client_id = $client_id";
$count_result = mysqli_query($connection, $count_query);
$total_logs = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_logs / $limit);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Account Activity</h4>
        <a href="profile.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($logs_result && mysqli_num_rows($logs_result) > 0): ?>
                <div class="timeline">
                    <?php while($log = mysqli_fetch_assoc($logs_result)): 
                        $icon = 'bi-info-circle';
                        $color = 'secondary';
                        
                        switch($log['activity_type']) {
                            case 'login':
                                $icon = 'bi-box-arrow-in-right';
                                $color = 'success';
                                break;
                            case 'logout':
                                $icon = 'bi-box-arrow-left';
                                $color = 'secondary';
                                break;
                            case 'profile_update':
                                $icon = 'bi-pencil';
                                $color = 'primary';
                                break;
                            case 'password_change':
                                $icon = 'bi-key';
                                $color = 'warning';
                                break;
                            case 'ticket_created':
                                $icon = 'bi-ticket';
                                $color = 'info';
                                break;
                            case 'ticket_reply':
                                $icon = 'bi-reply';
                                $color = 'info';
                                break;
                            case 'file_upload':
                                $icon = 'bi-cloud-upload';
                                $color = 'success';
                                break;
                            case 'file_download':
                                $icon = 'bi-cloud-download';
                                $color = 'primary';
                                break;
                            case 'feedback_submitted':
                                $icon = 'bi-star';
                                $color = 'warning';
                                break;
                        }
                    ?>
                    <div class="d-flex mb-3 pb-2 border-bottom">
                        <div class="me-3">
                            <span class="badge bg-<?php echo $color; ?> p-2">
                                <i class="bi <?php echo $icon; ?>"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo ucwords(str_replace('_', ' ', $log['activity_type'])); ?></strong>
                                <small class="text-muted"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></small>
                            </div>
                            <p class="mb-0"><?php echo htmlspecialchars($log['description']); ?></p>
                            <?php if ($log['ip_address']): ?>
                                <small class="text-muted">IP: <?php echo $log['ip_address']; ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?source=activity&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-clock-history display-1 text-muted"></i>
                    <h5 class="mt-3">No Activity Logs Yet</h5>
                    <p class="text-muted">Your account activities will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>