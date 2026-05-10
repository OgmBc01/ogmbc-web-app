<?php
// Ensure $user_id is set from session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
// Fetch user details
$query = "SELECT u.*, 
          r.role_name, r.role_level,
          t.type_name,
          d.dept_name, d.dept_code,
          e.employee_id, e.field_of_study, e.qualification, e.highest_graduation, 
          e.year_of_graduation, e.salary, e.department_id
          FROM users u
          LEFT JOIN user_roles r ON u.role_id = r.role_id
          LEFT JOIN user_types t ON u.type_id = t.type_id
          LEFT JOIN employees e ON u.user_id = e.user_id
          LEFT JOIN departments d ON e.department_id = d.id
          WHERE u.user_id = $user_id";
$result = mysqli_query($connection, $query);
$user = mysqli_fetch_assoc($result);

// Get statistics
$stats_query = "SELECT 
                (SELECT COUNT(*) FROM engagements WHERE assigned_to = $user_id) as total_engagements,
                (SELECT COUNT(*) FROM engagements WHERE assigned_to = $user_id AND status = 'CLOSED') as completed_engagements,
                (SELECT COUNT(*) FROM points_ledger WHERE employee_id = $user_id) as total_transactions,
                (SELECT COALESCE(SUM(points), 0) FROM points_ledger WHERE employee_id = $user_id AND points_type = 'EARNED') as total_points,
                (SELECT COUNT(*) FROM cdp_records WHERE employee_id = $user_id AND status = 'APPROVED') as approved_cdp
                FROM dual";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get recent activity
$activity_query = "SELECT 
                   'engagement' as type, 
                   CONCAT('Engagement: ', title) as description, 
                   updated_at as created_at 
                   FROM engagements WHERE assigned_to = $user_id 
                   UNION ALL
                   SELECT 'point', CONCAT(points, ' points earned'), created_at 
                   FROM points_ledger WHERE employee_id = $user_id 
                   UNION ALL
                   SELECT 'cdp', CONCAT('CDP: ', title), created_at 
                   FROM cdp_records WHERE employee_id = $user_id 
                   ORDER BY created_at DESC LIMIT 5";
$activity_result = mysqli_query($connection, $activity_query);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <h2 class="fw-bold text-dark text-center" style="font-size:2rem; letter-spacing:1px; border-bottom:2px solid #f1bf70; padding-bottom:0.5rem;">User Profile Overview</h2>
        </div>
        <!-- Left Column - Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="profile-card mb-4 shadow-sm" style="background: #fff; border-radius: 12px; padding: 1.5rem;">
                <div class="profile-header text-center" style="padding: 0 0 20px 0; display: flex; flex-direction: column; align-items: center;">
                    <div class="profile-avatar" style="width: 130px; height: 130px; position: relative; margin-bottom: 10px;">
                        <img src="<?php 
                            if (!empty($user['user_image']) && file_exists('../uploads/profiles/' . $user['user_image'])) {
                                echo '../uploads/profiles/' . $user['user_image'];
                            } else {
                                $name = urlencode($user['first_name'] . ' ' . $user['last_name']);
                                echo "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=150";
                            }
                        ?>" alt="Profile" class="avatar-img" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #f1bf70; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <a href="profile.php?source=edit" class="avatar-edit" title="Edit Profile" style="position: absolute; bottom: 5px; right: 5px; width: 35px; height: 35px; background: #f1bf70; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; border: 2px solid white;">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    <h3 class="profile-name fw-bold mt-3 mb-2" style="font-size: 1.6rem; color: #2c3e50; word-break: break-word;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <span class="badge bg-<?php 
                        echo $user['role_level'] >= 90 ? 'danger' : 
                            ($user['role_level'] >= 70 ? 'warning' : 
                            ($user['role_level'] >= 50 ? 'info' : 'secondary')); 
                    ?>" style="font-size:1rem; padding:0.5em 1em; font-weight:600; margin-bottom: 8px; display: inline-block;">
                        <?php echo ucfirst($user['role_name'] ?? 'Staff'); ?>
                    </span>
                    <span class="profile-dept d-block" style="color: #6c757d; font-size: 1rem; margin-bottom: 2px;">
                        <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($user['dept_name'] ?? 'No Department'); ?>
                        <?php if ($user['dept_code']): ?>
                            <span class="text-muted">(<?php echo $user['dept_code']; ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total_engagements'] ?? 0; ?></span>
                        <span class="stat-label">Engagements</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total_points'] ?? 0; ?></span>
                        <span class="stat-label">Points</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['approved_cdp'] ?? 0; ?></span>
                        <span class="stat-label">CDP Records</span>
                    </div>
                </div>
                
                <div class="profile-contact">
                    <h6 class="section-title">Contact Information</h6>
                    <div class="contact-item">
                        <i class="bi bi-envelope"></i>
                        <span><?php echo htmlspecialchars($user['user_email']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-person-badge"></i>
                        <span>Username: <?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-calendar"></i>
                        <span>Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="profile.php?source=edit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-pencil me-2"></i>Edit Profile
                    </a>
                    <a href="profile.php?source=password" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-key me-2"></i>Change Password
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Details & Activity -->
        <div class="col-lg-8">
            <!-- Personal Information Card -->
            <div class="details-card mb-4 shadow-sm" style="background: #fff; border-radius: 12px; padding: 1.5rem;">
                <div class="card-header" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; font-weight: 600; font-size: 1rem; padding: 0.6rem 1rem;">
                    <i class="bi bi-person-badge me-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">First Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['first_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Last Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['last_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email Address</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['user_email']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">User Type</span>
                                <span class="info-value"><?php echo ucfirst($user['type_name'] ?? 'Staff'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">User Role</span>
                                <span class="info-value"><?php echo ucfirst($user['role_name'] ?? 'Not Assigned'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Employee ID</span>
                                <span class="info-value"><?php echo $user['employee_id'] ? 'EMP-' . str_pad($user['employee_id'], 4, '0', STR_PAD_LEFT) : 'Not linked'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Information Card -->
            <?php if ($user['employee_id']): ?>
            <div class="details-card mb-4 shadow-sm" style="background: #fff; border-radius: 12px; padding: 1.5rem;">
                <div class="card-header" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; font-weight: 600; font-size: 1rem; padding: 0.6rem 1rem;">
                    <i class="bi bi-briefcase me-2"></i>Professional Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Department</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['dept_name'] ?? 'Not Assigned'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Field of Study</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['field_of_study'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Qualification</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['qualification'] ?? 'Not specified'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Highest Graduation</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['highest_graduation'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Year of Graduation</span>
                                <span class="info-value"><?php echo htmlspecialchars($user['year_of_graduation'] ?? 'Not specified'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Salary</span>
                                <span class="info-value">AED <?php echo number_format($user['salary'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Activity Card -->
            <div class="details-card shadow-sm" style="background: #fff; border-radius: 12px; padding: 1.5rem;">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #1e293b; color: #fff; border-radius: 12px 12px 0 0; font-weight: 600; font-size: 1rem; padding: 0.6rem 1rem;">
                    <span><i class="bi bi-clock-history me-2"></i>Recent Activity</span>
                    <a href="profile.php?source=activity" class="btn btn-sm btn-light">
                        View All
                    </a>
                </div>
                <div class="card-body" style="padding: 1.25rem 1.5rem 1.25rem 1.5rem;">
                    <?php if ($activity_result && mysqli_num_rows($activity_result) > 0): ?>
                        <div class="activity-feed">
                            <?php while($activity = mysqli_fetch_assoc($activity_result)): 
                                $icon = $activity['type'] == 'engagement' ? 'briefcase' : ($activity['type'] == 'point' ? 'trophy' : 'mortarboard');
                                $color = $activity['type'] == 'engagement' ? 'primary' : ($activity['type'] == 'point' ? 'success' : 'info');
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon bg-<?php echo $color; ?>-soft">
                                    <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="activity-details text-muted"><?php echo ucfirst($activity['type']); ?></small>
                                        </div>
                                        <small class="activity-time text-muted">
                                            <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-activity display-4"></i>
                            <h6>No recent activity</h6>
                            <p class="text-muted">Your recent actions will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Page Styles */
.profile-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.profile-header {
    padding: 30px;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    position: relative;
}

.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.avatar-edit {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 35px;
    height: 35px;
    background: #f1bf70;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid white;
}

.avatar-edit:hover {
    background: #e5b465;
    color: white;
    transform: scale(1.1);
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: #2c3e50;
}

.profile-role {
    margin-bottom: 5px;
}

.profile-dept {
    color: #6c757d;
    font-size: 0.9rem;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
}

.profile-contact {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2c3e50;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #6c757d;
    font-size: 0.9rem;
    word-break: break-all;
}

.contact-item i {
    width: 20px;
    color: #f1bf70;
    font-size: 1rem;
}

.profile-actions {
    padding: 20px;
}

/* Details Card */
.details-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.info-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    width: 140px;
    font-size: 0.9rem;
    color: #6c757d;
}

.info-value {
    flex: 1;
    font-weight: 500;
    color: #2c3e50;
}

/* Activity Feed (reused from dashboard) */
.activity-feed {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 10px;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.bg-primary-soft { background: rgba(102, 126, 234, 0.1); }
.bg-success-soft { background: rgba(40, 167, 69, 0.1); }
.bg-info-soft { background: rgba(23, 162, 184, 0.1); }

.activity-content {
    flex: 1;
}

.activity-text {
    margin-bottom: 3px;
    font-weight: 500;
}

.activity-details {
    font-size: 0.8rem;
}

.activity-time {
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state i {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 15px;
}

.empty-state h6 {
    margin-bottom: 5px;
}

/* Responsive */
@media (max-width: 768px) {
    .info-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .info-label {
        width: 100%;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .profile-stats {
        flex-wrap: wrap;
        gap: 15px;
    }
}
</style>