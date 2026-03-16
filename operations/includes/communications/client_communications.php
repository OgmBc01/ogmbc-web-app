<?php
// Check if client ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'communications.php';</script>";
    exit();
}

$client_id = (int)$_GET['id'];

// Verify client access
$check_query = "SELECT c.client_id, c.company_name 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                GROUP BY c.client_id";
$check_result = mysqli_query($connection, $check_query);
$client = mysqli_fetch_assoc($check_result);

if (!$client) {
    echo "<script>window.location.href = 'communications.php';</script>";
    exit();
}

// Get all communications for this client
$comms_query = "SELECT cc.*, 
                e.title as engagement_title
                FROM client_communications cc
                LEFT JOIN engagements e ON cc.engagement_id = e.engagement_id
                WHERE cc.client_id = $client_id
                ORDER BY cc.created_at DESC";
$comms_result = mysqli_query($connection, $comms_query);

// Get stats
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN direction = 'outgoing' THEN 1 ELSE 0 END) as outgoing,
                SUM(CASE WHEN direction = 'incoming' THEN 1 ELSE 0 END) as incoming
                FROM client_communications
                WHERE client_id = $client_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-chat-dots me-2"></i>Communications with <?php echo htmlspecialchars($client['company_name']); ?></h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="communications.php">Communications</a></li>
                    <li class="breadcrumb-item active">Client History</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="communications.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button class="btn btn-primary" onclick="quickComm(<?php echo $client_id; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')">
                <i class="bi bi-plus-circle me-1"></i>New Communication
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-chat-dots text-primary"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Total Communications</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-arrow-right-circle text-success"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['outgoing'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Outgoing</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-arrow-left-circle text-info"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['incoming'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Incoming</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Communications Timeline -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-clock-history me-2"></i>Communication History
            </h5>
        </div>
        <div class="card-body">
            <?php if ($comms_result && mysqli_num_rows($comms_result) > 0): ?>
                <div class="communications-timeline">
                    <?php while($comm = mysqli_fetch_assoc($comms_result)): 
                        $icon = 'chat-dots';
                        $color = 'primary';
                        switch($comm['comm_type']) {
                            case 'email': $icon = 'envelope'; $color = 'info'; break;
                            case 'whatsapp': $icon = 'whatsapp'; $color = 'success'; break;
                            case 'call': $icon = 'telephone'; $color = 'warning'; break;
                            case 'meeting': $icon = 'people'; $color = 'secondary'; break;
                            case 'note': $icon = 'sticky'; $color = 'dark'; break;
                        }
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?php echo $color; ?>">
                            <i class="bi bi-<?php echo $icon; ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="text-<?php echo $color; ?>"><?php echo ucfirst($comm['comm_type']); ?></strong>
                                    <span class="badge bg-<?php echo $comm['direction'] == 'outgoing' ? 'success' : 'info'; ?> ms-2">
                                        <i class="bi bi-arrow-<?php echo $comm['direction'] == 'outgoing' ? 'right' : 'left'; ?> me-1"></i>
                                        <?php echo ucfirst($comm['direction']); ?>
                                    </span>
                                    <?php if (!empty($comm['engagement_title'])): ?>
                                        <span class="badge bg-secondary ms-2">
                                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($comm['engagement_title']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($comm['created_at'])); ?></small>
                            </div>
                            <?php if (!empty($comm['subject'])): ?>
                                <p class="mt-2 mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($comm['subject']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($comm['message'])): ?>
                                <p class="mb-2 text-muted"><?php echo nl2br(htmlspecialchars($comm['message'])); ?></p>
                            <?php endif; ?>
                            <div class="mt-2">
                                <a href="communications.php?source=view&id=<?php echo $comm['comm_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots display-1 text-muted"></i>
                    <h5 class="mt-3">No Communications Yet</h5>
                    <p class="text-muted">Start logging interactions with this client.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.communications-timeline {
    position: relative;
    padding-left: 30px;
}
.communications-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    margin-bottom: 30px;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    z-index: 1;
}
.timeline-content {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-left: 10px;
}
.timeline-content:hover {
    background: #e9ecef;
}
</style>