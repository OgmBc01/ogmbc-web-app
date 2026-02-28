<?php
include '../includes/database.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);
    
    // Updated query to join with users, departments, user_roles, and user_types tables
    $sql = "SELECT 
                e.*, 
                u.username,
                u.user_status,
                u.user_type as user_account_type,
                u.created_at as user_created_at,
                u.role_id,
                u.type_id,
                r.role_name,
                r.role_level,
                t.type_name,
                d.id as department_id,
                d.dept_name as department_name,
                d.dept_code as department_code
            FROM employees e
            INNER JOIN users u ON e.user_id = u.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN user_roles r ON u.role_id = r.role_id
            LEFT JOIN user_types t ON u.type_id = t.type_id
            WHERE e.employee_id = ?";
            
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($employee = $result->fetch_assoc()) {
        
        // Determine status badge color
        $status_class = 'secondary';
        $status_text = 'Unknown';
        if (isset($employee['user_status'])) {
            switch(strtolower($employee['user_status'])) {
                case 'active':
                    $status_class = 'success';
                    $status_text = 'Active';
                    break;
                case 'inactive':
                    $status_class = 'warning';
                    $status_text = 'Inactive';
                    break;
                case 'suspended':
                    $status_class = 'danger';
                    $status_text = 'Suspended';
                    break;
                default:
                    $status_text = ucfirst($employee['user_status']);
            }
        }
        
        // Determine role badge color based on level
        $role_class = 'secondary';
        if (!empty($employee['role_level'])) {
            if ($employee['role_level'] >= 90) {
                $role_class = 'danger';
            } elseif ($employee['role_level'] >= 70) {
                $role_class = 'warning';
            } elseif ($employee['role_level'] >= 50) {
                $role_class = 'info';
            }
        }
        
        ?>
        <div class="row">
            <div class="col-md-4 text-center">
                <?php
                $image_url = "";
                if (!empty($employee['user_image']) && file_exists("../uploads/profiles/" . $employee['user_image'])) {
                    $image_url = "../uploads/profiles/" . $employee['user_image'];
                } else {
                    $name = urlencode(($employee['first_name'] ?? '') . '+' . ($employee['last_name'] ?? ''));
                    $image_url = "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=150";
                }
                ?>
                <img src="<?php echo $image_url; ?>" 
                     alt="<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>"
                     class="img-fluid rounded-circle mb-3 border border-3 border-primary" 
                     width="150" height="150"
                     style="object-fit: cover;"
                     onerror="this.src='https://ui-avatars.com/api/?name=Employee&background=f1bf70&color=0f172a&size=150'">
                
                <h4><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h4>
                <p class="text-muted mb-1">Employee ID: <?php echo $employee['employee_id']; ?></p>
                <p class="text-muted mb-1">User ID: <?php echo $employee['user_id']; ?></p>
                <p class="text-muted mb-1">Username: <code><?php echo htmlspecialchars($employee['username'] ?? 'N/A'); ?></code></p>
                
                <!-- Status Badge -->
                <div class="mt-2">
                    <span class="badge bg-<?php echo $status_class; ?> px-3 py-2">
                        <i class="bi bi-person-circle me-1"></i>Status: <?php echo $status_text; ?>
                    </span>
                </div>
                
                <!-- Role Badge -->
                <?php if (!empty($employee['role_name'])): ?>
                <div class="mt-2">
                    <span class="badge bg-<?php echo $role_class; ?> px-3 py-2">
                        <i class="bi bi-shield-lock me-1"></i>Role: <?php echo htmlspecialchars($employee['role_name']); ?>
                        <?php if (!empty($employee['role_level'])): ?>
                            <span class="badge bg-light text-dark ms-1">Level <?php echo $employee['role_level']; ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Type Badge -->
                <?php if (!empty($employee['type_name'])): ?>
                <div class="mt-2">
                    <span class="badge bg-info px-3 py-2">
                        <i class="bi bi-tag me-1"></i>Type: <?php echo htmlspecialchars($employee['type_name']); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- Department Badge -->
                <?php if (!empty($employee['department_name'])): ?>
                <div class="mt-2">
                    <span class="badge bg-secondary px-3 py-2">
                        <i class="bi bi-building me-1"></i>Dept: <?php echo htmlspecialchars($employee['department_name']); ?>
                        <?php if (!empty($employee['department_code'])): ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo $employee['department_code']; ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Employee Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-envelope me-1"></i>Email:
                                </strong>
                                <a href="mailto:<?php echo htmlspecialchars($employee['user_email']); ?>">
                                    <?php echo htmlspecialchars($employee['user_email']); ?>
                                </a>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-cash me-1"></i>Salary:
                                </strong>
                                $<?php echo number_format($employee['salary'] ?? 0, 2); ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-book me-1"></i>Field of Study:
                                </strong>
                                <?php echo !empty($employee['field_of_study']) ? htmlspecialchars($employee['field_of_study']) : '<span class="text-muted">N/A</span>'; ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-award me-1"></i>Qualification:
                                </strong>
                                <?php echo !empty($employee['qualification']) ? htmlspecialchars($employee['qualification']) : '<span class="text-muted">N/A</span>'; ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-mortarboard me-1"></i>Highest Graduation:
                                </strong>
                                <?php echo !empty($employee['highest_graduation']) ? htmlspecialchars($employee['highest_graduation']) : '<span class="text-muted">N/A</span>'; ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-calendar me-1"></i>Year of Graduation:
                                </strong>
                                <?php echo !empty($employee['year_of_graduation']) ? htmlspecialchars($employee['year_of_graduation']) : '<span class="text-muted">N/A</span>'; ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-clock me-1"></i>Created:
                                </strong>
                                <?php 
                                if (!empty($employee['created_at'])) {
                                    echo date('F j, Y', strtotime($employee['created_at']));
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </div>
                            
                            <div class="col-6 mb-3">
                                <strong class="d-block text-primary">
                                    <i class="bi bi-person-badge me-1"></i>User Type:
                                </strong>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($employee['user_account_type'] ?? 'employee'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Additional Information if needed -->
                        <?php if (!empty($employee['department_code']) || !empty($employee['role_id'])): ?>
                        <hr>
                        <div class="row mt-2">
                            <div class="col-12">
                                <h6><i class="bi bi-gear me-2"></i>System Information</h6>
                            </div>
                            <?php if (!empty($employee['department_code'])): ?>
                            <div class="col-4">
                                <small class="text-muted d-block">Department Code</small>
                                <code><?php echo htmlspecialchars($employee['department_code']); ?></code>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($employee['role_id'])): ?>
                            <div class="col-4">
                                <small class="text-muted d-block">Role ID</small>
                                <code><?php echo $employee['role_id']; ?></code>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($employee['type_id'])): ?>
                            <div class="col-4">
                                <small class="text-muted d-block">Type ID</small>
                                <code><?php echo $employee['type_id']; ?></code>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Employee not found.</div>';
    }
    
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}

$connection->close();
?>