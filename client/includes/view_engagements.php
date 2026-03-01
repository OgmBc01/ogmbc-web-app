<?php
// Ensure client_id is defined
if (!isset($client_id)) {
    $client_id = $_SESSION['client_id'] ?? 0;
}

// Initialize result variable
$result = null;

// Check if engagements table exists
$table_check = mysqli_query($connection, "SHOW TABLES LIKE 'engagements'");
if (mysqli_num_rows($table_check) > 0) {
    // Get all engagements for this client - FIXED ORDER BY CASE SYNTAX
    $query = "SELECT e.*, 
              s.service_name,
              CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
              r.role_name as assigned_role,
              DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining,
              (SELECT COUNT(*) FROM client_files WHERE engagement_id = e.engagement_id AND uploaded_by = 'client') as my_files,
              (SELECT COUNT(*) FROM client_files WHERE engagement_id = e.engagement_id AND uploaded_by = 'staff') as staff_files
              FROM engagements e
              JOIN service_types s ON e.service_id = s.service_id
              LEFT JOIN users u ON e.assigned_to = u.user_id
              LEFT JOIN user_roles r ON u.role_id = r.role_id
              WHERE e.client_id = " . intval($client_id) . "
              ORDER BY 
                CASE 
                    WHEN e.status = 'IN_PROGRESS' THEN 1
                    WHEN e.status = 'AWAITING_REVIEW' THEN 2
                    WHEN e.status = 'ASSIGNED' THEN 3
                    WHEN e.status = 'SUBMITTED' THEN 4
                    WHEN e.status = 'CLOSED' THEN 5
                    ELSE 6
                END,
                e.created_at DESC";

    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        error_log("Engagements query failed: " . mysqli_error($connection));
        $result = null;
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">My Engagements</h1>
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
    <div class="row" id="engagementsGrid">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($eng = mysqli_fetch_assoc($result)): 
                $status_class = 'secondary';
                $status_text = $eng['status'];
                if ($eng['status'] == 'IN_PROGRESS') $status_class = 'primary';
                if ($eng['status'] == 'AWAITING_REVIEW') $status_class = 'warning';
                if ($eng['status'] == 'SUBMITTED') $status_class = 'info';
                if ($eng['status'] == 'CLOSED') $status_class = 'success';
                
                $deadline_class = 'success';
                $deadline_text = 'No deadline';
                
                if (isset($eng['days_remaining'])) {
                    if ($eng['days_remaining'] < 0) {
                        $deadline_class = 'danger';
                        $deadline_text = abs($eng['days_remaining']) . ' days overdue';
                    } elseif ($eng['days_remaining'] < 7) {
                        $deadline_class = 'warning';
                        $deadline_text = $eng['days_remaining'] . ' days left';
                    } else {
                        $deadline_class = 'success';
                        $deadline_text = $eng['days_remaining'] . ' days left';
                    }
                }
                
                // Determine if engagement is active (not closed)
                $is_active = ($eng['status'] != 'CLOSED' && $eng['status'] != 'SUBMITTED');
                
                // Safely get file counts
                $my_files = $eng['my_files'] ?? 0;
                $staff_files = $eng['staff_files'] ?? 0;
                $total_files = $my_files + $staff_files;
                $progress_width = $total_files > 0 ? min(100, $total_files * 10) : 0;
            ?>
            <div class="col-md-6 col-lg-4 mb-4 engagement-card" data-status="<?php echo $is_active ? 'active' : 'closed'; ?>">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        <small class="text-muted">#<?php echo $eng['engagement_id']; ?></small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($eng['title'] ?? 'Untitled'); ?></h5>
                        <p class="card-text small text-muted"><?php echo htmlspecialchars($eng['service_name'] ?? 'N/A'); ?></p>
                        
                        <div class="mb-2">
                            <strong>Assigned to:</strong><br>
                            <?php echo htmlspecialchars($eng['assigned_to_name'] ?? 'Unassigned'); ?>
                            <small class="text-muted">(<?php echo ucfirst($eng['assigned_role'] ?? 'Staff'); ?>)</small>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Deadline:</strong>
                            <span class="text-<?php echo $deadline_class; ?>"><?php echo $deadline_text; ?></span>
                        </div>
                        
                        <div class="progress mb-2" style="height: 5px;">
                            <div class="progress-bar bg-<?php echo $staff_files > 0 ? 'success' : 'secondary'; ?>" 
                                 style="width: <?php echo $progress_width; ?>%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between small">
                            <span><i class="bi bi-cloud-upload"></i> You: <?php echo $my_files; ?></span>
                            <span><i class="bi bi-cloud-download"></i> Staff: <?php echo $staff_files; ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewEngagement(<?php echo $eng['engagement_id']; ?>)">
                                <i class="bi bi-eye"></i> Details
                            </button>
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
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-briefcase display-1"></i>
                    <h4 class="mt-3">No engagements yet</h4>
                    <p>Your service requests will appear here once created.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

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
</script>