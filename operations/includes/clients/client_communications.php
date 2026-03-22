<?php
// Check if client ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

$client_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify client access
$check_query = "SELECT c.* 
                FROM clients c
                JOIN engagements e ON c.client_id = e.client_id
                WHERE c.client_id = $client_id AND e.assigned_to = $user_id
                GROUP BY c.client_id";
$check_result = mysqli_query($connection, $check_query);
$client = mysqli_fetch_assoc($check_result);

if (!$client) {
    echo "<script>window.location.href = 'clients.php';</script>";
    exit();
}

// Get all communications
$comms_query = "SELECT cc.*, 
                CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM client_communications cc
                JOIN users u ON cc.user_id = u.user_id
                WHERE cc.client_id = $client_id
                ORDER BY cc.created_at DESC";
$comms_result = mysqli_query($connection, $comms_query);

// Get communication stats
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN comm_type = 'email' THEN 1 ELSE 0 END) as emails,
                SUM(CASE WHEN comm_type = 'call' THEN 1 ELSE 0 END) as calls,
                SUM(CASE WHEN comm_type = 'whatsapp' THEN 1 ELSE 0 END) as whatsapp,
                SUM(CASE WHEN comm_type = 'meeting' THEN 1 ELSE 0 END) as meetings,
                MAX(created_at) as last_contact
                FROM client_communications
                WHERE client_id = $client_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<div class="container-fluid">
    <!-- Header with Client Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="client-header-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-2"><?php echo htmlspecialchars($client['company_name']); ?></h4>
                        <p class="mb-0 text-muted">
                            <i class="bi bi-person me-2"></i><?php echo htmlspecialchars($client['contact_name']); ?>
                            <span class="mx-3">|</span>
                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($client['contact_email']); ?>
                            <span class="mx-3">|</span>
                            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($client['contact_mobile']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="clients.php?source=view&id=<?php echo $client_id; ?>" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>Back to Client
                        </a>
                        <button class="btn btn-primary" onclick="quickComm(<?php echo $client_id; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')">
                            <i class="bi bi-plus-circle me-1"></i>New Communication
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-chat-dots text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3>
                    <p class="stat-label">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-envelope text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['emails'] ?? 0; ?></h3>
                    <p class="stat-label">Emails</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-whatsapp text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['whatsapp'] ?? 0; ?></h3>
                    <p class="stat-label">WhatsApp</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-telephone text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['calls'] ?? 0; ?></h3>
                    <p class="stat-label">Calls</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-secondary-soft">
                    <i class="bi bi-people text-secondary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['meetings'] ?? 0; ?></h3>
                    <p class="stat-label">Meetings</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card-small">
                <div class="stat-icon bg-danger-soft">
                    <i class="bi bi-clock-history text-danger"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo $stats['last_contact'] ? date('M d', strtotime($stats['last_contact'])) : 'Never'; ?></h3>
                    <p class="stat-label">Last Contact</p>
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
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-light" onclick="filterCommunications('all')">All</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterCommunications('email')">Email</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterCommunications('whatsapp')">WhatsApp</button>
                <button class="btn btn-sm btn-outline-light" onclick="filterCommunications('call')">Calls</button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($comms_result && mysqli_num_rows($comms_result) > 0): ?>
                <div class="communications-timeline" id="communicationsList">
                    <?php while($comm = mysqli_fetch_assoc($comms_result)): 
                        $icon = 'chat-dots';
                        $color = 'primary';
                        $bgClass = 'bg-primary-soft';
                        
                        switch($comm['comm_type']) {
                            case 'email':
                                $icon = 'envelope';
                                $color = 'info';
                                $bgClass = 'bg-info-soft';
                                break;
                            case 'whatsapp':
                                $icon = 'whatsapp';
                                $color = 'success';
                                $bgClass = 'bg-success-soft';
                                break;
                            case 'call':
                                $icon = 'telephone';
                                $color = 'warning';
                                $bgClass = 'bg-warning-soft';
                                break;
                            case 'meeting':
                                $icon = 'people';
                                $color = 'secondary';
                                $bgClass = 'bg-secondary-soft';
                                break;
                        }
                    ?>
                    <div class="communication-timeline-item" data-type="<?php echo $comm['comm_type']; ?>">
                        <div class="timeline-marker <?php echo $bgClass; ?>">
                            <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                        </div>
                        <div class="timeline-content-wrapper">
                            <div class="communication-bubble <?php echo $comm['direction'] == 'incoming' ? 'incoming' : 'outgoing'; ?>">
                                <div class="bubble-header">
                                    <span class="badge bg-<?php echo $color; ?>">
                                        <i class="bi bi-<?php echo $icon; ?> me-1"></i>
                                        <?php echo ucfirst($comm['comm_type']); ?>
                                    </span>
                                    <span class="bubble-direction">
                                        <i class="bi bi-arrow-<?php echo $comm['direction'] == 'incoming' ? 'left' : 'right'; ?> me-1"></i>
                                        <?php echo $comm['direction'] == 'incoming' ? 'From Client' : 'To Client'; ?>
                                    </span>
                                    <small class="bubble-time">
                                        <?php echo date('M d, Y H:i', strtotime($comm['created_at'])); ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($comm['subject'])): ?>
                                    <div class="bubble-subject">
                                        <strong>Subject:</strong> <?php echo htmlspecialchars($comm['subject']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($comm['message'])): ?>
                                    <div class="bubble-message">
                                        <?php echo nl2br(htmlspecialchars($comm['message'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="bubble-footer">
                                    <small class="text-muted">
                                        Logged by: <?php echo htmlspecialchars($comm['user_name']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots display-1 text-muted"></i>
                    <h5 class="mt-3">No Communications Yet</h5>
                    <p class="text-muted">Start logging your interactions with this client.</p>
                    <button class="btn btn-primary mt-3" onclick="quickComm(<?php echo $client_id; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')">
                        <i class="bi bi-plus-circle me-2"></i>Log First Communication
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pro Tip Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="pro-tip-card comm-tip">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6 class="text-white mb-2">
                        <i class="bi bi-lightbulb me-2"></i>
                        Communication Best Practices
                    </h6>
                    <ul class="text-white-50 small mb-md-0">
                        <li>📝 Always log client communications for future reference</li>
                        <li>⚡ Use WhatsApp for urgent matters, email for formal documentation</li>
                        <li>📞 Follow up calls with a summary email when appropriate</li>
                        <li>📅 Schedule regular check-ins with key clients</li>
                    </ul>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-chat-dots display-4 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.client-header-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.stat-card-small {
    background: white;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 12px;
    height: 100%;
}

.stat-card-small .stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.stat-card-small .stat-content {
    flex: 1;
}

.stat-card-small .stat-value {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 2px;
}

.stat-card-small .stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    margin: 0;
}

.communications-timeline {
    display: flex;
    flex-direction: column;
    gap: 25px;
    position: relative;
    padding-left: 30px;
}

.communications-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.communication-timeline-item {
    position: relative;
    display: flex;
    gap: 20px;
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
    z-index: 1;
    border: 2px solid white;
}

.timeline-content-wrapper {
    flex: 1;
}

.communication-bubble {
    background: white;
    border-radius: 16px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    max-width: 80%;
}

.communication-bubble.incoming {
    border-left: 4px solid #f1bf70;
}

.communication-bubble.outgoing {
    border-right: 4px solid #0d6efd;
    margin-left: auto;
}

.bubble-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.bubble-direction {
    font-size: 0.8rem;
    color: #6c757d;
}

.bubble-time {
    margin-left: auto;
    font-size: 0.75rem;
    color: #6c757d;
}

.bubble-subject {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.bubble-message {
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 10px;
    white-space: pre-wrap;
}

.bubble-footer {
    border-top: 1px solid #eee;
    padding-top: 8px;
    margin-top: 8px;
}

.pro-tip-card.comm-tip {
    background: linear-gradient(90deg, #0a2240 0%, #003366 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .client-header-card .d-flex {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .communication-bubble {
        max-width: 100%;
    }
    
    .bubble-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .bubble-time {
        margin-left: 0;
    }
}
</style>

<script>
function filterCommunications(type) {
    const items = document.querySelectorAll('.communication-timeline-item');
    items.forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>