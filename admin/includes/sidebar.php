    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <span class="logo-text">AdminPanel</span>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php" data-menu="services">
                    <i class="bi bi-speedometer nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Sales/CRM Menu -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="sales">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span class="nav-text">Sales/CRM</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="sales-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="clients.php?source=add_client">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add Client</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./clients.php">
                            <i class="bi bi-card-checklist nav-icon"></i>
                            <span class="nav-text">View All Clients</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Engagements Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'engagements.php' && (!isset($_GET['source']) || $_GET['source'] == 'view_all')) ? 'active' : ''; ?>" 
                href="#" data-menu="engagements">
                    <i class="bi bi-tools nav-icon"></i>
                    <span class="nav-text">Operations</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="engagements-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'operations_dashboard') ? 'active' : ''; ?>" 
                        href="engagements.php?source=operations_dashboard">
                            <i class="bi bi-bar-chart-line nav-icon"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_engagement') ? 'active' : ''; ?>" 
                        href="engagements.php?source=add_engagement">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Create Engagement</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./engagements.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="engagements.php?filter=overdue">
                            <i class="bi bi-exclamation-triangle nav-icon"></i>
                            <span class="nav-text">Overdue</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Users Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="users">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">Users</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="users-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_user') ? 'active' : ''; ?>" 
                        href="users.php?source=add_user">
                            <i class="bi bi-person-plus nav-icon"></i>
                            <span class="nav-text">Add User</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./users.php">
                            <i class="bi bi-person-lines-fill nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_roles.php') ? 'active' : ''; ?>" 
                        href="user_roles.php">
                            <i class="bi bi-shield-lock nav-icon"></i>
                            <span class="nav-text">Roles & Types</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sales Targets Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'sales_targets.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="sales-targets">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span class="nav-text">Sales Targets</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="sales-targets-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'set_target') ? 'active' : ''; ?>" 
                        href="sales_targets.php?source=set_target">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Set Target</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./sales_targets.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales_targets.php?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>">
                            <i class="bi bi-calendar-check nav-icon"></i>
                            <span class="nav-text">Current Month</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Client Feedback Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'client_feedback.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="client-feedback">
                    <i class="bi bi-chat-quote nav-icon"></i>
                    <span class="nav-text">Client Feedback</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="client-feedback-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_feedback') ? 'active' : ''; ?>" 
                        href="client_feedback.php?source=add_feedback">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Log Feedback</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./client_feedback.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="client_feedback.php?filter=pending">
                            <i class="bi bi-clock-history nav-icon"></i>
                            <span class="nav-text">Pending Validation</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Posts Menu -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="posts">
                    <i class="bi bi-file-earmark-post nav-icon"></i>
                    <span class="nav-text">Posts</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="posts-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="posts.php?source=add_post">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add Post</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./posts.php">
                            <i class="bi bi-card-checklist nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                </ul>
            </li>
            

            <!-- Submissions -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="submissions">
                    <i class="bi bi-file-earmark-text nav-icon"></i> <!-- Recommended -->
                    <span class="nav-text">Submissions</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="submissions-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="leads.php?source=ratio-calc-leads">
                            <i class="bi bi-calculator nav-icon"></i>
                            <span class="nav-text">Ratio Calculator Leads</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="leads.php?source=service-enquiries">
                            <i class="bi bi-chat-left-text nav-icon"></i> <!-- Changed from calculator to chat -->
                            <span class="nav-text">Service Enquiries</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="lead.php">
                            <i class="bi bi-envelope-paper nav-icon"></i> <!-- Changed from envelope to envelope-paper>
                            <span class="nav-text">Newsletter Subs</span>
                        </a>
                    </li> -->
                </ul>
            </li>

            <!-- Users/Employees Menu -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="employees">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">Employees</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="employees-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="employees.php?source=add_employee">
                            <i class="bi bi-person-plus nav-icon"></i>
                            <span class="nav-text">Add Employee</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./employees.php">
                            <i class="bi bi-person-lines-fill nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Points Ledger Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'points_ledger.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="points-ledger">
                    <i class="bi bi-cash-coin nav-icon"></i>
                    <span class="nav-text">Points Ledger</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="points-ledger-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'view_ledger') ? 'active' : ''; ?>" 
                        href="points_ledger.php?source=view_ledger">
                            <i class="bi bi-journal-bookmark-fill nav-icon"></i>
                            <span class="nav-text">Transaction Ledger</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'monthly_summary') ? 'active' : ''; ?>" 
                        href="points_ledger.php?source=monthly_summary">
                            <i class="bi bi-calendar-month nav-icon"></i>
                            <span class="nav-text">Monthly Summary</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'quarterly_payout') ? 'active' : ''; ?>" 
                        href="points_ledger.php?source=quarterly_payout">
                            <i class="bi bi-cash-stack nav-icon"></i>
                            <span class="nav-text">Quarterly Payout</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'manual_adjustment') ? 'active' : ''; ?>" 
                        href="points_ledger.php?source=manual_adjustment">
                            <i class="bi bi-pencil-square nav-icon"></i>
                            <span class="nav-text">Manual Adjustment</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'employee_wallet') ? 'active' : ''; ?>" href="points_ledger.php?source=employee_wallet">
                            <i class="bi bi-wallet2 nav-icon"></i>
                            <span class="nav-text">My Wallet</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- CDP & Annual Performance Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'cdp_annual.php' && !isset($_GET['tab'])) ? 'active' : ''; ?>" 
                href="#" data-menu="cdp-annual">
                    <i class="bi bi-bar-chart-steps nav-icon"></i>
                    <span class="nav-text">CDP & Annual</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="cdp-annual-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'cdp' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                        href="cdp_annual.php?tab=cdp">
                            <i class="bi bi-mortarboard nav-icon"></i>
                            <span class="nav-text">CDP Records</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'annual') ? 'active' : ''; ?>" 
                        href="cdp_annual.php?tab=annual">
                            <i class="bi bi-graph-up nav-icon"></i>
                            <span class="nav-text">Annual Performance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'bands') ? 'active' : ''; ?>" 
                        href="cdp_annual.php?tab=bands">
                            <i class="bi bi-bar-chart nav-icon"></i>
                            <span class="nav-text">Salary Bands</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cdp_annual.php?source=add_cdp">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add CDP Record</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Services Menu -->
            <li class="nav-item">
                <a class="nav-link" href="categories.php" data-menu="services">
                    <i class="bi bi-gear nav-icon"></i>
                    <span class="nav-text">Services</span>
                </a>
            </li>

            <!-- Services Config Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="services-config">
                    <i class="bi bi-gear-wide-connected nav-icon"></i>
                    <span class="nav-text">Services Config</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="services-config-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_service') ? 'active' : ''; ?>" 
                        href="services.php?source=add_service">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add Service</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./services.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Departments Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'departments.php' && !isset($_GET['source'])) ? 'active-sub' : ''; ?>" 
                href="#" data-menu="departments">
                    <i class="bi bi-building nav-icon"></i>
                    <span class="nav-text">Departments</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="departments-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_department') ? 'active-sub' : ''; ?>" 
                        href="departments.php?source=add_department">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add Department</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all_departments') ? 'active-sub' : ''; ?>" 
                        href="./departments.php">
                            <i class="bi bi-list-ul nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Audit Log Menu -->
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'audit_log.php') ? 'active' : ''; ?>" 
                href="audit_log.php">
                    <i class="bi bi-journal-text nav-icon"></i>
                    <span class="nav-text">Audit Log</span>
                </a>
            </li>

            <!-- Bank Accounts Menu -->
            <li class="nav-item">
                <a class="nav-link" href="bank_accounts.php" data-menu="bank-accounts">
                    <i class="bi bi-bank nav-icon"></i>
                    <span class="nav-text">Bank Accounts</span>
                </a>
            </li>
        </ul>
    </div>