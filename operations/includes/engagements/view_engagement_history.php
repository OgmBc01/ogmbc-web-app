<?php
// This would be included as a tab in view_engagement_details.php
// But we'll create it as a separate file for modularity

if (!isset($engagement_id)) {
    exit;
}

// Get status history
$history_query = "SELECT h.*, CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                  FROM engagement_status_history h
                  JOIN users u ON h.changed_by = u.user_id
                  WHERE h.engagement_id = $engagement_id
                  ORDER BY h.changed_at DESC";
$history_result = mysqli_query($connection, $history_query);

// Get deadline change requests
$requests_query = "SELECT r.*, 
                  CONCAT(ru.first_name, ' ', ru.last_name) as requested_by_name,
                  CONCAT(ru2.first_name, ' ', ru2.last_name) as reviewed_by_name
                  FROM deadline_change_requests r
                  LEFT JOIN users ru ON r.requested_by = ru.user_id
                  LEFT JOIN users ru2 ON r.reviewed_by = ru2.user_id
                  WHERE r.engagement_id = $engagement_id
                  ORDER BY r.created_at DESC";
$requests_result = mysqli_query($connection, $requests_query);
?>

<div class="history-timeline">
    <!-- Status History -->
    <div class="timeline-section mb-4">
        <h6 class="timeline-title">
            <i class="bi bi-arrow-repeat me-2"></i>Status History
        </h6>
        <div class="timeline-items">
            <?php if ($history_result && mysqli_num_rows($history_result) > 0): ?>
                <?php while($item = mysqli_fetch_assoc($history_result)): ?>
                <div class="timeline-item">
                    <div class="timeline-badge">
                        <i class="bi bi-record-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                            <strong>
                                <?php echo $item['old_status'] ?: 'New'; ?> 
                                <i class="bi bi-arrow-right mx-2"></i> 
                                <?php echo $item['new_status']; ?>
                            </strong>
                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($item['changed_at'])); ?></small>
                        </div>
                        <p class="mb-0 small text-muted">
                            by <?php echo htmlspecialchars($item['changed_by_name']); ?>
                            <?php if (!empty($item['notes'])): ?>
                                <br><i class="bi bi-chat me-1"></i><?php echo htmlspecialchars($item['notes']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No status history available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Deadline Change Requests -->
    <div class="timeline-section">
        <h6 class="timeline-title">
            <i class="bi bi-calendar-plus me-2"></i>Deadline Change Requests
        </h6>
        <div class="timeline-items">
            <?php if ($requests_result && mysqli_num_rows($requests_result) > 0): ?>
                <?php while($req = mysqli_fetch_assoc($requests_result)): 
                    $status_class = 'secondary';
                    $status_icon = 'clock';
                    if ($req['status'] == 'APPROVED') {
                        $status_class = 'success';
                        $status_icon = 'check-circle';
                    } elseif ($req['status'] == 'REJECTED') {
                        $status_class = 'danger';
                        $status_icon = 'x-circle';
                    }
                ?>
                <div class="timeline-item">
                    <div class="timeline-badge">
                        <i class="bi bi-calendar"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Requested: <?php echo date('M d, Y', strtotime($req['requested_date'])); ?></strong>
                                <span class="badge bg-<?php echo $status_class; ?> ms-2">
                                    <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo $req['status']; ?>
                                </span>
                            </div>
                            <small class="text-muted"><?php echo date('M d', strtotime($req['created_at'])); ?></small>
                        </div>
                        <p class="mb-1 small">
                            <strong>Reason:</strong> <?php echo ucfirst($req['reason_code']); ?>
                        </p>
                        <?php if (!empty($req['reason_notes'])): ?>
                            <p class="mb-1 small text-muted">
                                <i class="bi bi-chat me-1"></i><?php echo htmlspecialchars($req['reason_notes']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($req['status'] != 'PENDING' && $req['reviewed_by_name']): ?>
                            <p class="mb-0 small text-muted">
                                Reviewed by <?php echo htmlspecialchars($req['reviewed_by_name']); ?>
                                on <?php echo date('M d, Y', strtotime($req['reviewed_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No deadline change requests.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.history-timeline {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
}

.timeline-section {
    margin-bottom: 25px;
}

.timeline-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    padding-bottom: 8px;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 15px;
}

.timeline-items {
    position: relative;
    padding-left: 20px;
}

.timeline-items::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-badge {
    position: absolute;
    left: -20px;
    width: 24px;
    height: 24px;
    background: white;
    border: 2px solid #f1bf70;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f1bf70;
    font-size: 0.8rem;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px;
    margin-left: 10px;
}

.timeline-content:hover {
    background: #e9ecef;
}
</style>