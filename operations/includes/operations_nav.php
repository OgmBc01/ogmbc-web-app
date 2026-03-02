<!-- Top Navigation Bar -->
<nav class="navbar top-navbar fixed-top">
    <div class="container-fluid">
        <a class="brand d-flex align-items-center" href="../index.php">
            <img src="../resources/img/logo.png" alt="OGM Consultants Logo" class="logo-img me-2" />
            <span style="text-decoration: none;">OGMBC Consultants</span>
        </a>
        <div class="d-flex">
            <!-- Notification Bell (Optional) -->
            <div class="dropdown me-3">
                <a href="#" class="notification-bell" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <?php
                    // Get unread notifications count
                    if (isset($_SESSION['user_id'])) {
                        $notif_query = "SELECT COUNT(*) as unread FROM user_notifications WHERE user_id = " . $_SESSION['user_id'] . " AND is_read = 0";
                        $notif_result = mysqli_query($connection, $notif_query);
                        $notif_count = mysqli_fetch_assoc($notif_result)['unread'];
                        if ($notif_count > 0) {
                            echo '<span class="notification-badge">' . $notif_count . '</span>';
                        }
                    }
                    ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <?php if ($notif_count > 0): ?>
                            <a href="notifications.php?mark_all=1" class="text-muted small">Mark all as read</a>
                        <?php endif; ?>
                    </div>
                    <div class="notification-list">
                        <?php
                        // Get recent notifications
                        $notif_list_query = "SELECT * FROM user_notifications WHERE user_id = " . $_SESSION['user_id'] . " ORDER BY created_at DESC LIMIT 5";
                        $notif_list_result = mysqli_query($connection, $notif_list_query);
                        
                        if (mysqli_num_rows($notif_list_result) > 0) {
                            while($notif = mysqli_fetch_assoc($notif_list_result)) {
                                $notif_class = $notif['is_read'] ? 'notification-item' : 'notification-item unread';
                                echo '<a class="dropdown-item ' . $notif_class . '" href="' . ($notif['link'] ?? '#') . '">';
                                echo '<div class="notification-title">' . htmlspecialchars($notif['title']) . '</div>';
                                echo '<small class="text-muted">' . date('M d, H:i', strtotime($notif['created_at'])) . '</small>';
                                echo '</a>';
                            }
                        } else {
                            echo '<div class="dropdown-item text-muted text-center">No notifications</div>';
                        }
                        ?>
                    </div>
                    <div class="dropdown-footer text-center">
                        <a href="notifications.php" class="small">View All</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right-aligned user profile -->
        <div class="user-profile">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php 
                        // Check if user has a profile image
                        if (!empty($_SESSION['user_image']) && file_exists('../uploads/profiles/' . $_SESSION['user_image'])) {
                            echo '../uploads/profiles/' . htmlspecialchars($_SESSION['user_image']);
                        } else {
                            // Fallback to default avatar using first name or username
                            $name = !empty($_SESSION['first_name']) ? $_SESSION['first_name'] : $_SESSION['username'];
                            echo 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=f1bf70&color=0f172a&bold=true';
                        }
                    ?>" 
                    alt="User" class="user-img"
                    onerror="this.src='https://ui-avatars.com/api/?name=User&background=f1bf70&color=0f172a&bold=true'">
                    <span class="user-name">
                        <?php 
                        // Display first name if available, otherwise username
                        echo !empty($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : htmlspecialchars($_SESSION['username']);
                        ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="../index.php"><i class="bi bi-house me-2"></i> Home</a></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="profile.php?source=settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>