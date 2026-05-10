<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="logo-container">
        <div class="logo">
            <span class="logo-text">Sales Portal</span>
        </div>
    </div>
    
    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" 
               href="sales_dashboard.php">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <!-- Activities / Timesheet -->
        <li class="nav-item">
            <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'activities.php') ? 'active' : ''; ?>" 
            href="#" data-menu="activities">
                <i class="bi bi-calendar-check nav-icon"></i>
                <span class="nav-text">My Activities</span>
                <i class="bi bi-chevron-right menu-toggle"></i>
            </a>
            <ul class="sub-menu" id="activities-menu">
                <li class="nav-item">
                    <a class="nav-link" href="activities.php#daily">
                        <i class="bi bi-calendar-day nav-icon"></i>
                        <span class="nav-text">Daily Log</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activities.php#tasks">
                        <i class="bi bi-list-check nav-icon"></i>
                        <span class="nav-text">My Tasks</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activities.php#schedule">
                        <i class="bi bi-calendar-week nav-icon"></i>
                        <span class="nav-text">Weekly Schedule</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activities.php#expenses">
                        <i class="bi bi-cash-stack nav-icon"></i>
                        <span class="nav-text">Expenses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activities.php#reports">
                        <i class="bi bi-file-earmark-spreadsheet nav-icon"></i>
                        <span class="nav-text">Reports</span>
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
