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

            <!-- Operations Menu -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="sales">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span class="nav-text">Operations</span>
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
                <a class="nav-link menu-toggle-btn" href="#" data-menu="users">
                    <i class="bi bi-file-earmark-text nav-icon"></i> <!-- Recommended -->
                    <span class="nav-text">Submissions</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="users-menu">
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
                <a class="nav-link menu-toggle-btn" href="#" data-menu="users">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">Employees</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="users-menu">
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
            
            <!-- Services Menu -->
            <li class="nav-item">
                <a class="nav-link" href="categories.php" data-menu="services">
                    <i class="bi bi-gear nav-icon"></i>
                    <span class="nav-text">Services</span>
                </a>
            </li>

            <!-- Bank Accounts Menu -->
            <li class="nav-item">
                <a class="nav-link" href="bank_accounts.php" data-menu="bank-accounts">
                    <i class="bi bi-bank nav-icon"></i>
                    <span class="nav-text">Bank Accounts</span>
                </a>
            </li>
            
            <!-- Sales/CRM Menu
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
            </li> -->
        </ul>
    </div>