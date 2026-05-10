<?php
// Fetch overdue engagements for the dashboard card
$overdue_query = "SELECT engagement_id, title, client_id, COALESCE(approved_deadline, original_deadline) as deadline
    FROM engagements
    WHERE status NOT IN ('CLOSED', 'SUBMITTED')
      AND COALESCE(approved_deadline, original_deadline) < CURDATE()
    ORDER BY deadline ASC
    LIMIT 5";
$overdue_result = mysqli_query($connection, $overdue_query);
?>
<div class="overdue-list mt-2">
    <?php if ($overdue_result && mysqli_num_rows($overdue_result) > 0): ?>
        <?php while($overdue = mysqli_fetch_assoc($overdue_result)): ?>
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                <div>
                    <a href="engagements.php?source=view_engagement&id=<?php echo $overdue['engagement_id']; ?>" class="fw-bold text-danger">
                        <?php echo htmlspecialchars($overdue['title']); ?>
                    </a>
                    <small class="text-muted ms-2">Client ID: <?php echo $overdue['client_id']; ?></small>
                </div>
                <div>
                    <span class="badge bg-danger-soft text-danger">
                        <i class="bi bi-clock"></i> Due: <?php echo date('M d, Y', strtotime($overdue['deadline'])); ?>
                    </span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center text-muted">No overdue engagements</div>
    <?php endif; ?>
</div>
