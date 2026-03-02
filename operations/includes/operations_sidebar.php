<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-container">
        <div class="logo">
            <span class="logo-text">Operations Portal</span>
        </div>
    </div>
    
    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" 
               href="dashboard.php">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        
        <!-- Engagements -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'engagements.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="engagements">
                <i class="bi bi-briefcase nav-icon"></i>
                <span class="nav-text">Engagements</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="engagements-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'engagements.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="engagements.php">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text">All Engagements</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="engagements.php?filter=active">
                        <i class="bi bi-play-circle nav-icon"></i>
                        <span class="nav-text">Active</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="engagements.php?filter=overdue">
                        <i class="bi bi-exclamation-triangle nav-icon"></i>
                        <span class="nav-text">Overdue</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="engagements.php?filter=completed">
                        <i class="bi bi-check-circle nav-icon"></i>
                        <span class="nav-text">Completed</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Tasks (Kanban/Board View) -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tasks.php') ? 'active' : ''; ?>" 
               href="tasks.php">
                <i class="bi bi-kanban nav-icon"></i>
                <span class="nav-text">Task Board</span>
            </a>
        </li>

        <!-- Clients -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'clients.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="clients">
                <i class="bi bi-people nav-icon"></i>
                <span class="nav-text">My Clients</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="clients-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'clients.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="clients.php">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text">All Clients</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clients.php?filter=active">
                        <i class="bi bi-chat-dots nav-icon"></i>
                        <span class="nav-text">Recent Activity</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- CDP (Career Development) -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'cdp.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="cdp">
                <i class="bi bi-mortarboard nav-icon"></i>
                <span class="nav-text">Career Development</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="cdp-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'cdp.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="cdp.php">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text">My Records</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cdp.php?source=add">
                        <i class="bi bi-plus-circle nav-icon"></i>
                        <span class="nav-text">Add New</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cdp.php?filter=pending">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Pending Approval</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Wallet / Points -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'wallet.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="wallet">
                <i class="bi bi-wallet2 nav-icon"></i>
                <span class="nav-text">Points Wallet</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="wallet-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'wallet.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="wallet.php">
                        <i class="bi bi-cash-stack nav-icon"></i>
                        <span class="nav-text">Summary</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="wallet.php?source=history">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Transaction History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="wallet.php?source=monthly">
                        <i class="bi bi-calendar-month nav-icon"></i>
                        <span class="nav-text">Monthly Breakdown</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Performance -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'performance.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="performance">
                <i class="bi bi-graph-up nav-icon"></i>
                <span class="nav-text">Performance</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="performance-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'performance.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="performance.php">
                        <i class="bi bi-bar-chart nav-icon"></i>
                        <span class="nav-text">Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="performance.php?source=monthly">
                        <i class="bi bi-calendar-check nav-icon"></i>
                        <span class="nav-text">Monthly Targets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="performance.php?source=rankings">
                        <i class="bi bi-trophy nav-icon"></i>
                        <span class="nav-text">Rankings</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Communications -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'communications.php') ? 'active' : ''; ?>" 
               href="communications.php">
                <i class="bi bi-chat-dots nav-icon"></i>
                <span class="nav-text">Communications</span>
            </a>
        </li>

        <!-- Notifications -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>" 
               href="notifications.php">
                <i class="bi bi-bell nav-icon"></i>
                <span class="nav-text">Notifications</span>
                <?php
                // Show unread count badge
                if (isset($_SESSION['user_id'])) {
                    $notif_query = "SELECT COUNT(*) as unread FROM user_notifications WHERE user_id = " . $_SESSION['user_id'] . " AND is_read = 0";
                    $notif_result = mysqli_query($connection, $notif_query);
                    $notif_count = mysqli_fetch_assoc($notif_result)['unread'];
                    if ($notif_count > 0) {
                        echo '<span class="badge bg-danger rounded-pill ms-2">' . $notif_count . '</span>';
                    }
                }
                ?>
            </a>
        </li>

        <!-- Profile -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
               href="#" data-menu="profile">
                <i class="bi bi-person nav-icon"></i>
                <span class="nav-text">My Profile</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="profile-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                       href="profile.php">
                        <i class="bi bi-person-badge nav-icon"></i>
                        <span class="nav-text">View Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php?source=edit">
                        <i class="bi bi-pencil nav-icon"></i>
                        <span class="nav-text">Edit Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php?source=password">
                        <i class="bi bi-key nav-icon"></i>
                        <span class="nav-text">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php?source=activity">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Activity Log</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>
