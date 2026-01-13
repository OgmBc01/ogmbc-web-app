<!-- Top Navigation Bar -->
<nav class="navbar top-navbar fixed-top">
    <div class="container-fluid">
        <a class="brand d-flex align-items-center" href="../index.php">
            <img src="../resources/img/logo.png" alt="OGM Consultants Logo" class="logo-img me-2" />
            <span style="text-decoration: none;">OGMBC Consultants</span>
        </a>
        <div class="d-flex">
            <!-- Empty for alignment -->
        </div>
        
        <!-- Right-aligned user profile -->
        <div class="user-profile">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php 
                        // Check if user has a profile image
                        if (!empty($_SESSION['user_image']) && file_exists('./uploads/profiles/' . $_SESSION['user_image'])) {
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
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>