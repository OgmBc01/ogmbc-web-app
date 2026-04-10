<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Check if user is logged in as client (using user_id)
if (!isset($_SESSION['user_id'])) {
    // Debug: Check what's in session
    echo "<!-- Debug: Session content: " . print_r($_SESSION, true) . " -->";
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
// Debug: Output user ID
echo "<!-- Debug: User ID from session: " . $user_id . " -->";

// Get all client_ids for this user
$client_ids = [];
$client_result = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = $user_id");
while ($row = mysqli_fetch_assoc($client_result)) {
    $client_ids[] = $row['client_id'];
}

if (!empty($client_ids)) {
    $client_ids_str = implode(',', array_map('intval', $client_ids));
    // Get all engagements for these client_ids
    $query = "SELECT e.*, 
                    s.service_name,
                    c.company_name,
                    CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
                    r.role_name as assigned_role,
                    DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining,
                    (SELECT COUNT(*) FROM client_files WHERE engagement_id = e.engagement_id AND uploaded_by = 'client') as my_files,
                    (SELECT COUNT(*) FROM client_files WHERE engagement_id = e.engagement_id AND uploaded_by = 'staff') as staff_files
                    FROM engagements e
                    JOIN service_types s ON e.service_id = s.service_id
                    JOIN clients c ON e.client_id = c.client_id
                    LEFT JOIN users u ON e.assigned_to = u.user_id
                    LEFT JOIN user_roles r ON u.role_id = r.role_id
                    WHERE e.client_id IN ($client_ids_str)
                    ORDER BY 
                        CASE e.status 
                                WHEN 'IN_PROGRESS' THEN 1
                                WHEN 'AWAITING_REVIEW' THEN 2
                                WHEN 'ASSIGNED' THEN 3
                                WHEN 'SUBMITTED' THEN 4
                                WHEN 'CLOSED' THEN 5
                                ELSE 6
                        END,
                        e.created_at DESC";
    // Debug: Output query
    echo "<!-- Debug: Query: " . $query . " -->";
    $result = mysqli_query($connection, $query);
    // Debug: Check if query executed
    if (!$result) {
        echo "<!-- Debug: Query failed: " . mysqli_error($connection) . " -->";
    } else {
        echo "<!-- Debug: Query returned " . mysqli_num_rows($result) . " rows -->";
    }
    // Get statistics
    $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'AWAITING_REVIEW' THEN 1 ELSE 0 END) as awaiting_review,
                    SUM(CASE WHEN status = 'ASSIGNED' THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted
                    FROM engagements 
                    WHERE client_id IN ($client_ids_str)";
    $stats_result = mysqli_query($connection, $stats_query);
    $stats = mysqli_fetch_assoc($stats_result);
    // Get overdue count
    $overdue_query = "SELECT COUNT(*) as overdue FROM engagements 
                      WHERE client_id IN ($client_ids_str)
                      AND status NOT IN ('CLOSED', 'SUBMITTED')
                      AND COALESCE(approved_deadline, original_deadline) < CURDATE()";
    $overdue_result = mysqli_query($connection, $overdue_query);
    $overdue = mysqli_fetch_assoc($overdue_result);
} else {
    // No companies for this user
    $result = false;
    $stats = [ 'total' => 0, 'in_progress' => 0, 'awaiting_review' => 0, 'assigned' => 0, 'closed' => 0, 'submitted' => 0 ];
    $overdue = [ 'overdue' => 0 ];
}
?>

<!-- Debug output (remove in production) -->
<?php if (isset($_GET['debug'])): ?>
<div class="alert alert-info">
    <strong>Debug Info:</strong><br>
    Session client_id: <?php echo $client_id; ?><br>
    Query: <?php echo htmlspecialchars($query); ?><br>
    Rows found: <?php echo $result ? mysqli_num_rows($result) : 0; ?><br>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        First row: <pre><?php print_r(mysqli_fetch_assoc($result)); ?></pre>
        <?php mysqli_data_seek($result, 0); // Reset pointer ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">My Engagements</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-briefcase text-primary"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Total Engagements</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-play-circle text-warning"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo ($stats['in_progress'] + $stats['assigned']) ?? 0; ?></h3>
                        <p class="stat-label mb-0">Active</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-info">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history text-info"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $stats['awaiting_review'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Awaiting Review</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-danger">
                <div class="stat-card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                    </div>
                    <div class="stat-content ms-3">
                        <h3 class="stat-value mb-0"><?php echo $overdue['overdue'] ?? 0; ?></h3>
                        <p class="stat-label mb-0">Overdue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" id="engagementTabs">
        <li class="nav-item">
            <a class="nav-link active" data-status="all" href="#">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="active" href="#">Active</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-status="closed" href="#">Completed</a>
        </li>
    </ul>

    <!-- Engagements Grid -->
    <div class="row g-4" id="engagementsGrid">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($eng = mysqli_fetch_assoc($result)): 
                $status_class = 'secondary';
                $status_text = $eng['status'];
                if ($eng['status'] == 'IN_PROGRESS') $status_class = 'primary';
                if ($eng['status'] == 'AWAITING_REVIEW') $status_class = 'warning';
                if ($eng['status'] == 'SUBMITTED') $status_class = 'info';
                if ($eng['status'] == 'CLOSED') $status_class = 'success';
                
                $deadline_class = $eng['days_remaining'] < 0 ? 'danger' : ($eng['days_remaining'] < 7 ? 'warning' : 'success');
                $deadline_text = $eng['days_remaining'] < 0 ? abs($eng['days_remaining']) . ' days overdue' : $eng['days_remaining'] . ' days left';
                
                // Determine if engagement is active (not closed)
                $is_active = ($eng['status'] != 'CLOSED' && $eng['status'] != 'SUBMITTED');
            ?>
            <div class="col-md-6 col-lg-4 mb-4 engagement-card" data-status="<?php echo $is_active ? 'active' : 'closed'; ?>">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        <a href="engagements.php?source=view_details&id=<?php echo $eng['engagement_id']; ?>" class="text-decoration-none fw-bold" style="color: #0a2240;">
                            ENG-<?php echo date('dmy', strtotime($eng['created_at'])); ?>-<?php echo $eng['engagement_id']; ?>
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($eng['title']); ?></h5>
                        <p class="card-text small text-muted mb-1"><?php echo htmlspecialchars($eng['service_name']); ?></p>
                        <div class="company-pill mb-2">
                            <i class="bi bi-building me-1"></i>
                            <a href="engagements.php?source=view_details&id=<?php echo $eng['engagement_id']; ?>" 
                               class="fw-bold company-link" 
                               style="font-size:1.1rem;letter-spacing:0.5px;color:#f1bf70;text-decoration:none;">
                                <?php echo htmlspecialchars($eng['company_name']); ?>
                            </a>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Assigned to:</strong><br>
                            <?php echo htmlspecialchars($eng['assigned_to_name']); ?>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Deadline:</strong>
                            <span class="text-<?php echo $deadline_class; ?>"><?php echo $deadline_text; ?></span>
                        </div>
                        
                        <div class="progress mb-2" style="height: 5px;">
                            <div class="progress-bar bg-<?php echo $eng['staff_files'] > 0 ? 'success' : 'secondary'; ?>" 
                                 style="width: <?php echo min(100, ($eng['staff_files'] + $eng['my_files']) * 10); ?>%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between small">
                            <span><i class="bi bi-cloud-upload"></i> You: <?php echo $eng['my_files']; ?></span>
                            <span><i class="bi bi-cloud-download"></i> Staff: <?php echo $eng['staff_files']; ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100">
                            <a href="engagements.php?source=view_details&id=<?php echo $eng['engagement_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Details
                            </a>
                            <?php if ($is_active): ?>
                            <a href="engagements.php?source=upload_file&id=<?php echo $eng['engagement_id']; ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cloud-upload"></i> Upload
                            </a>
                            <?php endif; ?>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-chat"></i> Contact
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="contactStaff(<?php echo $eng['assigned_to']; ?>, 'whatsapp', <?php echo $eng['engagement_id']; ?>)">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="contactStaff(<?php echo $eng['assigned_to']; ?>, 'email', <?php echo $eng['engagement_id']; ?>)">
                                        <i class="bi bi-envelope text-primary"></i> Email
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-briefcase display-1 text-muted"></i>
                    <h5 class="mt-3">No engagements yet</h5>
                    <p class="text-muted">Your service requests will appear here once created.</p>
                    <?php if (isset($_GET['debug'])): ?>
                        <p class="text-info">Debug: No engagements found for client ID <?php echo $client_id; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Stat Cards - Matching client portal theme */
.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border-left: 6px solid #e0e0e0;
    padding: 0;
    margin-bottom: 0;
    transition: box-shadow 0.2s;
    height: 100%;
}
.stat-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}
.stat-card-primary { border-left-color: #667eea; }
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-info { border-left-color: #17a2b8; }
.stat-card-danger { border-left-color: #dc3545; }

.stat-card-body {
    padding: 20px;
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
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #222;
    line-height: 1.2;
}
.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-top: 2px;
}

/* Engagement Cards */
.engagement-card .card {
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    transition: all 0.3s ease;
    overflow: hidden;
}
.engagement-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.engagement-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    padding: 12px 16px;
}
.engagement-card .card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 12px;
}
.engagement-card .btn-group {
    display: flex;
    gap: 4px;
}
.engagement-card .btn {
    border-radius: 8px;
}
.company-pill {
    display: inline-flex;
    align-items: center;
    background: #eef4fb;
    border-radius: 20px;
    padding: 4px 14px 4px 10px;
    font-size: 1rem;
    margin-bottom: 2px;
    box-shadow: 0 1px 4px rgba(102,126,234,0.07);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

/* Nav Tabs */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    padding: 10px 20px;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #667eea;
    background: transparent;
    border-bottom: 3px solid #667eea;
}

/* Responsive */
@media (max-width: 768px) {
    .stat-card-body {
        padding: 15px;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
    }
    .stat-value {
        font-size: 1.4rem;
    }
}

/* Gold company link hover */
.company-link:hover {
    color: #c89a3c;
    text-decoration: underline;
}
</style>

<script>
// Filter engagements by status
document.querySelectorAll('#engagementTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#engagementTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const status = this.dataset.status;
        document.querySelectorAll('.engagement-card').forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Contact staff function
function contactStaff(staffId, method, engagementId) {
    if (method === 'whatsapp') {
        window.open(`https://wa.me/?text=Hi%2C%20I%20have%20a%20question%20about%20engagement%20%23${engagementId}`, '_blank');
    } else if (method === 'email') {
        window.location.href = `mailto:?subject=Question about engagement #${engagementId}`;
    }
}

// View engagement details (to be implemented)
function viewEngagement(id) {
    window.location.href = 'engagements.php?source=view&id=' + id;
}
</script>