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
               href="operations_dashboard.php">
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
                <li class="nav-item">
                    <a class="nav-link" href="cdp_annual.php">
                        <i class="bi bi-calendar-check nav-icon"></i>
                        <span class="nav-text">Annual CDP Summary</span>
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

        <!-- Support Tickets -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tickets.php') ? 'active' : ''; ?>" 
            href="tickets.php">
                <i class="bi bi-ticket nav-icon"></i>
                <span class="nav-text">Support Tickets</span>
                <?php
                // Show count of open/high priority tickets
                $ticket_count_query = "SELECT COUNT(*) as open_tickets 
                                    FROM support_tickets 
                                    WHERE assigned_to = " . $_SESSION['user_id'] . " 
                                    AND status IN ('open', 'in_progress')";
                $ticket_count_result = mysqli_query($connection, $ticket_count_query);
                $open_tickets = mysqli_fetch_assoc($ticket_count_result)['open_tickets'];
                if ($open_tickets > 0) {
                    echo '<span class="badge bg-danger rounded-pill ms-2">' . $open_tickets . '</span>';
                }
                
                // Show urgent tickets count (optional)
                /*
                $urgent_query = "SELECT COUNT(*) as urgent 
                                FROM support_tickets 
                                WHERE assigned_to = " . $_SESSION['user_id'] . " 
                                AND priority = 'urgent' 
                                AND status != 'closed'";
                $urgent_result = mysqli_query($connection, $urgent_query);
                $urgent_count = mysqli_fetch_assoc($urgent_result)['urgent'];
                if ($urgent_count > 0) {
                    echo '<span class="badge bg-warning rounded-pill ms-2">!</span>';
                }
                */
                ?>
            </a>
        </li>

        <!-- Performance
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
        </li> -->

        <!-- Communications (NEW) -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'communications.php') ? 'active' : ''; ?>" 
               href="communications.php">
                <i class="bi bi-chat-dots nav-icon"></i>
                <span class="nav-text">Communications</span>
                <?php
                // Show unread communications count (optional - you can add a read/unread field later)
                /*
                $unread_comms_query = "SELECT COUNT(*) as unread FROM client_communications 
                                       WHERE user_id = " . $_SESSION['user_id'] . " 
                                       AND is_read = 0";
                $unread_comms_result = mysqli_query($connection, $unread_comms_query);
                $unread_comms = mysqli_fetch_assoc($unread_comms_result)['unread'];
                if ($unread_comms > 0) {
                    echo '<span class="badge bg-danger rounded-pill ms-2">' . $unread_comms . '</span>';
                }
                */
                ?>
            </a>
        </li>

        <!-- Client Feedback with Dropdown -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'feedback.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
            href="#" data-menu="feedback">
                <i class="bi bi-star nav-icon"></i>
                <span class="nav-text">Client Feedback</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
                <?php
                $new_feedback_query = "SELECT COUNT(*) as new_feedback 
                                    FROM client_feedback 
                                    WHERE employee_id = " . $_SESSION['user_id'] . " 
                                    AND is_validated = 1 
                                    AND is_rejected = 0
                                    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                $new_feedback_result = mysqli_query($connection, $new_feedback_query);
                $new_feedback = mysqli_fetch_assoc($new_feedback_result)['new_feedback'];
                if ($new_feedback > 0) {
                    echo '<span class="badge bg-success rounded-pill ms-2">' . $new_feedback . '</span>';
                }
                ?>
            </a>
            <ul class="sub-menu" id="feedback-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'feedback.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                    href="feedback.php">
                        <i class="bi bi-list-ul nav-icon"></i>
                        <span class="nav-text">All Feedback</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php?filter=positive">
                        <i class="bi bi-emoji-smile nav-icon"></i>
                        <span class="nav-text">Positive</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php?filter=recent">
                        <i class="bi bi-clock-history nav-icon"></i>
                        <span class="nav-text">Recent</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php?filter=high_rating">
                        <i class="bi bi-star-fill nav-icon"></i>
                        <span class="nav-text">High Rating (4-5⭐)</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Notifications Menu -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>" 
            href="notifications.php">
                <i class="bi bi-bell nav-icon"></i>
                <span class="nav-text">Notifications</span>
                <?php
                // Show unread count badge
                $unread_query = "SELECT COUNT(*) as unread FROM user_notifications WHERE user_id = " . $_SESSION['user_id'] . " AND is_read = 0";
                $unread_result = mysqli_query($connection, $unread_query);
                $unread_count = mysqli_fetch_assoc($unread_result)['unread'];
                if ($unread_count > 0) {
                    echo '<span class="badge bg-danger rounded-pill ms-2">' . $unread_count . '</span>';
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
