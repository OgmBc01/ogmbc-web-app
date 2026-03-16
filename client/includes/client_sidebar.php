    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <span class="logo-text">Client Portal</span>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="client_dashboard.php" data-menu="services">
                    <i class="bi bi-speedometer nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <!--  -->
            <!-- Engagements -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'engagements.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="engagements">
                    <i class="bi bi-briefcase nav-icon"></i>
                    <span class="nav-text">My Engagements</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="engagements-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'engagements.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                        href="engagements.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="engagements.php?filter=active">
                            <i class="bi bi-play-circle nav-icon"></i>
                            <span class="nav-text">Active</span>
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

            <!-- File Exchange -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'files.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="files">
                    <i class="bi bi-folder2 nav-icon"></i>
                    <span class="nav-text">File Exchange</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="files-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'files.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                        href="files.php">
                            <i class="bi bi-files nav-icon"></i>
                            <span class="nav-text">All Files</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="files.php?source=upload">
                            <i class="bi bi-cloud-upload nav-icon"></i>
                            <span class="nav-text">Upload File</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Support Tickets -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'support.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="support">
                    <i class="bi bi-question-circle nav-icon"></i>
                    <span class="nav-text">Support</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="support-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'support.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                        href="support.php">
                            <i class="bi bi-ticket nav-icon"></i>
                            <span class="nav-text">My Tickets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php?source=new">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">New Ticket</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Feedback -->
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'feedback.php') ? 'active' : ''; ?>" 
                href="feedback.php">
                    <i class="bi bi-star nav-icon"></i>
                    <span class="nav-text">Feedback</span>
                </a>
            </li>

            <!-- Invoices (Optional)
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'invoices.php') ? 'active' : ''; ?>" 
                href="invoices.php">
                    <i class="bi bi-receipt nav-icon"></i>
                    <span class="nav-text">Invoices</span>
                </a>
            </li> -->

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
        </ul>
    </div>