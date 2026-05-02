<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Get performer user_id from query string
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo '<div class="alert alert-danger">Invalid performer selected.</div>';
    include 'includes/footer.php';
    exit();
}
$performer_id = (int)$_GET['user_id'];

// Fetch performer details
$sql = "
    SELECT u.*, e.field_of_study, e.qualification, e.highest_graduation, e.year_of_graduation 
    FROM users u
    LEFT JOIN employees e ON u.user_id = e.user_id
    WHERE u.user_id = ?
";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $performer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo '<div class="alert alert-danger">Performer not found.</div>';
    include 'includes/footer.php';
    exit();
}
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="container">
                    <div class="card shadow-sm p-4">
                        <!-- Profile Header with Image -->
                        <div class="text-center mb-4">
                          <div class="user-image-container mx-auto mb-3">
                                <img src="<?php 
                                    if (!empty($user['user_image']) && file_exists('../uploads/profiles/' . $user['user_image'])) {
                                        echo '../uploads/profiles/' . htmlspecialchars($user['user_image']);
                                    } else {
                                        $name = urlencode(($user['first_name'] ?? '') . '+' . ($user['last_name'] ?? ''));
                                        echo "https://ui-avatars.com/api/?name=$name&background=f1bf70&color=0f172a&size=128";
                                    }
                                ?>" 
                                alt="User Profile" class="user-profile-image"
                                onerror="this.src='https://ui-avatars.com/api/?name=User&background=f1bf70&color=0f172a&size=128'">
                            </div>
                            <h2 class="mb-1"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                            <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        </div>

                        <!-- Two-column layout for profile information -->
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="info-card mb-4">
                                    <h5 class="info-card-header">
                                        <i class="bi bi-person-circle me-2"></i>Basic Information
                                    </h5>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-person me-2"></i>Username</span>
                                        <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-envelope me-2"></i>Email</span>
                                        <span class="info-value"><?php echo htmlspecialchars($user['user_email']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-person-badge me-2"></i>First Name</span>
                                        <span class="info-value"><?php echo !empty($user['first_name']) ? htmlspecialchars($user['first_name']) : '<span class="text-muted">Not set</span>'; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-person-badge me-2"></i>Last Name</span>
                                        <span class="info-value"><?php echo !empty($user['last_name']) ? htmlspecialchars($user['last_name']) : '<span class="text-muted">Not set</span>'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="info-card mb-4">
                                    <h5 class="info-card-header">
                                        <i class="bi bi-gear me-2"></i>Account Information
                                    </h5>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-shield me-2"></i>User Role</span>
                                        <span class="info-value badge bg-primary"><?php echo htmlspecialchars($user['user_role'] ?? ''); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-person-workspace me-2"></i>User Type</span>
                                        <span class="info-value badge bg-info"><?php echo htmlspecialchars($user['user_type'] ?? ''); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="bi bi-circle-fill me-2"></i>Account Status</span>
                                        <span class="info-value badge bg-<?php echo ($user['user_status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($user['user_status'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Information (if applicable) -->
                        <?php if (isset($user['user_type']) && $user['user_type'] === 'employee'): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="info-card employee-info">
                                    <h5 class="info-card-header">
                                        <i class="bi bi-briefcase me-2"></i>Employee Information
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span class="info-label"><i class="bi bi-book me-2"></i>Field of Study</span>
                                                <span class="info-value"><?php echo !empty($user['field_of_study']) ? htmlspecialchars($user['field_of_study']) : '<span class="text-muted">Not set</span>'; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label"><i class="bi bi-award me-2"></i>Qualification</span>
                                                <span class="info-value"><?php echo !empty($user['qualification']) ? htmlspecialchars($user['qualification']) : '<span class="text-muted">Not set</span>'; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span class="info-label"><i class="bi bi-mortarboard me-2"></i>Highest Graduation</span>
                                                <span class="info-value"><?php echo !empty($user['highest_graduation']) ? htmlspecialchars($user['highest_graduation']) : '<span class="text-muted">Not set</span>'; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label"><i class="bi bi-calendar-event me-2"></i>Year of Graduation</span>
                                                <span class="info-value"><?php echo !empty($user['year_of_graduation']) ? htmlspecialchars($user['year_of_graduation']) : '<span class="text-muted">Not set</span>'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Back Button -->
                        <div class="text-center mt-4">
                            <a href="performers.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i> Back to Performers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

<?php
include 'includes/footer.php';
?>
