<?php
$user_id = isset($user_id) ? (int)$user_id : (int)($_SESSION['user_id'] ?? 0);

// Get all clients this user has engagements with
$query = "SELECT DISTINCT 
    c.client_id,
    c.company_name,
    c.contact_name,
    c.contact_email,
    c.contact_mobile,
    c.country,
    c.created_at,
    COUNT(e.engagement_id) as total_engagements,
    SUM(CASE WHEN e.status NOT IN ('CLOSED', 'SUBMITTED') THEN 1 ELSE 0 END) as active_engagements,
    MAX(e.updated_at) as last_activity,
    (SELECT COUNT(*) FROM client_communications WHERE client_id = c.client_id) as total_communications
    FROM clients c
    JOIN engagements e ON c.client_id = e.client_id
    WHERE e.assigned_to = $user_id
    GROUP BY c.client_id
    ORDER BY last_activity DESC";

$result = mysqli_query($connection, $query);

// Get statistics
$stats_query = "SELECT 
    COUNT(DISTINCT c.client_id) as total_clients,
    COUNT(DISTINCT CASE WHEN e.status NOT IN ('CLOSED', 'SUBMITTED') THEN c.client_id END) as active_clients,
    COUNT(DISTINCT c.country) as countries
    FROM clients c
    JOIN engagements e ON c.client_id = e.client_id
    WHERE e.assigned_to = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent communications
$recent_comms_query = "SELECT 
    cc.*,
    c.company_name,
    c.client_id
    FROM client_communications cc
    JOIN clients c ON cc.client_id = c.client_id
    WHERE cc.user_id = $user_id
    ORDER BY cc.created_at DESC
    LIMIT 5";
$recent_comms = mysqli_query($connection, $recent_comms_query);
?>

<div class="container-fluid">

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-building text-primary"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['total_clients'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Total Clients</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-chat-dots text-success"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['active_clients'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Active Clients</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-globe text-info"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['countries'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Countries</p>
                    </div>
                </div>
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
    margin-bottom: 24px;
    transition: box-shadow 0.2s;
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-success { border-left-color: #38c172; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-body {
    padding: 24px 20px;
    display: flex;
    align-items: center;
}
.stat-icon {
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: #f5f6fa;
    border-radius: 50%;
}
.stat-value {
    font-size: 2.1rem;
    font-weight: 700;
    color: #222;
}
.stat-label {
    font-size: 1rem;
    color: #888;
    margin-top: 2px;
}
@media (max-width: 768px) {
    .stat-card-body { padding: 16px 10px; }
    .stat-icon { width: 40px; height: 40px; font-size: 1.3rem; }
    .stat-value { font-size: 1.3rem; }
}
</style>

    <div class="row">
        <!-- Main Clients List -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-list-ul me-2"></i>My Clients
                    </h5>
                    <div class="card-header-actions">
                        <input type="text" class="form-control form-control-sm" id="clientSearch" placeholder="Search clients..." style="width: 200px;">
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <div class="client-list" id="clientList">
                            <?php while($client = mysqli_fetch_assoc($result)): ?>
                            <div class="client-item">
                                <div class="client-avatar">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="client-info">
                                    <h6 class="client-name">
                                        <a href="clients.php?source=view&id=<?php echo $client['client_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($client['company_name']); ?>
                                        </a>
                                        <?php if ($client['active_engagements'] > 0): ?>
                                            <span class="badge bg-success ms-2"><?php echo $client['active_engagements']; ?> active</span>
                                        <?php endif; ?>
                                    </h6>
                                    <div class="client-contact">
                                        <span class="contact-item">
                                            <i class="bi bi-person"></i>
                                            <?php echo htmlspecialchars($client['contact_name'] ?: 'No contact'); ?>
                                        </span>
                                        <span class="contact-item">
                                            <i class="bi bi-envelope"></i>
                                            <?php echo htmlspecialchars($client['contact_email']); ?>
                                        </span>
                                        <span class="contact-item">
                                            <i class="bi bi-telephone"></i>
                                            <?php echo htmlspecialchars($client['contact_mobile']); ?>
                                        </span>
                                    </div>
                                    <div class="client-meta">
                                        <small class="text-muted">
                                            <i class="bi bi-briefcase me-1"></i><?php echo $client['total_engagements']; ?> engagements
                                        </small>
                                        <small class="text-muted ms-3">
                                            <i class="bi bi-clock me-1"></i>Last: <?php echo $client['last_activity'] ? date('M d', strtotime($client['last_activity'])) : 'No activity'; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="client-actions">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewClient(<?php echo $client['client_id']; ?>)" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="quickComm(<?php echo $client['client_id']; ?>, '<?php echo htmlspecialchars($client['company_name'], ENT_QUOTES); ?>')" title="Quick Communication">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>
                                    <a href="clients.php?source=communications&id=<?php echo $client['client_id']; ?>" class="btn btn-sm btn-outline-success" title="Communications">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="clients.php?source=engagements&id=<?php echo $client['client_id']; ?>">
                                                    <i class="bi bi-briefcase me-2"></i>View Engagements
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="clients.php?source=files&id=<?php echo $client['client_id']; ?>">
                                                    <i class="bi bi-files me-2"></i>File Exchange
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="mailto:<?php echo $client['contact_email']; ?>">
                                                    <i class="bi bi-envelope me-2"></i>Send Email
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $client['contact_mobile']); ?>" target="_blank">
                                                    <i class="bi bi-whatsapp me-2"></i>WhatsApp
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-people display-1 text-muted"></i>
                            <h5 class="mt-3">No Clients Found</h5>
                            <p class="text-muted">You don't have any assigned clients yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Recent Communications & Tips -->
        <div class="col-lg-4">
            <!-- Recent Communications -->
            <div class="card shadow-sm mb-4">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-clock-history me-2"></i>Recent Communications
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-light" onclick="quickComm(0, '')">New</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($recent_comms && mysqli_num_rows($recent_comms) > 0): ?>
                        <div class="communication-feed">
                            <?php while($comm = mysqli_fetch_assoc($recent_comms)): 
                                $icon = 'chat-dots';
                                $color = 'primary';
                                if ($comm['comm_type'] == 'email') { $icon = 'envelope'; $color = 'info'; }
                                if ($comm['comm_type'] == 'whatsapp') { $icon = 'whatsapp'; $color = 'success'; }
                                if ($comm['comm_type'] == 'call') { $icon = 'telephone'; $color = 'warning'; }
                                if ($comm['comm_type'] == 'meeting') { $icon = 'people'; $color = 'secondary'; }
                            ?>
                            <div class="communication-item">
                                <div class="comm-icon bg-<?php echo $color; ?>-soft">
                                    <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                </div>
                                <div class="comm-details">
                                    <strong><?php echo htmlspecialchars($comm['company_name']); ?></strong>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($comm['subject'] ?: $comm['comm_type']); ?></p>
                                    <small class="text-muted"><?php echo date('M d, H:i', strtotime($comm['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <p class="text-muted mb-0">No recent communications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pro Tip Card -->
            <div class="pro-tip-card clients-tip gradient-bg">
                <h6 class="text-white mb-3">
                    <i class="bi bi-lightbulb me-2"></i>
                    Client Relationship Tips
                </h6>
                <ul class="pro-tip-list small">
                    <li class="mb-2">✅ Log every client interaction for future reference</li>
                    <li class="mb-2">✅ Use WhatsApp for quick updates, email for formal communication</li>
                    <li class="mb-2">✅ Check client's active engagements before contacting</li>
                    <li class="mb-2">✅ Keep notes of important client preferences</li>
                    <li>✅ Follow up within 24 hours of receiving client queries</li>
                </ul>
                <hr class="border-white-50">
                <p class="pro-tip-footer small mb-0">
                    <i class="bi bi-star me-1"></i>
                    Happy clients lead to better feedback and more points!
                </p>
            </div>

<style>
.gradient-bg {
    background: linear-gradient(90deg, #0a2240 0%, #003366 100%) !important;
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.18);
    padding: 28px 24px;
    margin-bottom: 24px;
}
.pro-tip-list,
.pro-tip-footer {
    color: #fff !important;
    opacity: 0.97;
    text-shadow: 0 1px 2px rgba(60,60,60,0.10);
}
</style>
        </div>
    </div>
</div>

<style>
.clients-welcome {
    background: linear-gradient(135deg, #11998e 0%, #0a7e6b 100%);
}

.client-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.client-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s ease;
    flex-wrap: wrap;
}

.client-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.client-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #f1bf70 0%, #e5b465 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.client-info {
    flex: 1;
    min-width: 200px;
}

.client-name {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.client-name a {
    color: #2c3e50;
}

.client-name a:hover {
    color: #f1bf70;
}

.client-contact {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
}

.contact-item i {
    font-size: 0.8rem;
    color: #f1bf70;
}

.client-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.client-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    align-items: center;
}

/* Communication Feed */
.communication-feed {
    max-height: 300px;
    overflow-y: auto;
}

.communication-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.communication-item:hover {
    background: #f8f9fa;
}

.comm-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.comm-details {
    flex: 1;
    min-width: 0;
}

.comm-details p {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Pro Tip Card */
.pro-tip-card.clients-tip {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .client-item {
        flex-direction: column;
        align-items: stretch;
    }
    
    .client-avatar {
        align-self: center;
    }
    
    .client-actions {
        justify-content: flex-end;
    }
    
    .client-contact {
        flex-direction: column;
        gap: 5px;
    }
}

/* Search highlighting */
.highlight {
    background-color: #fff3cd;
    transition: background-color 0.3s ease;
}
</style>

<script>
// Client search functionality
document.getElementById('clientSearch')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const clientItems = document.querySelectorAll('.client-item');
    
    clientItems.forEach(item => {
        const clientName = item.querySelector('.client-name').textContent.toLowerCase();
        const clientContact = item.querySelector('.client-contact').textContent.toLowerCase();
        
        if (clientName.includes(searchTerm) || clientContact.includes(searchTerm)) {
            item.style.display = '';
            // Highlight matching text
            if (searchTerm.length > 0) {
                item.classList.add('highlight');
                setTimeout(() => item.classList.remove('highlight'), 2000);
            }
        } else {
            item.style.display = 'none';
        }
    });
});
</script>