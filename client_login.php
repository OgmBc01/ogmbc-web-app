<?php
// Initialize at the very top
require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/auth_check.php';

// If already logged in as client, redirect to client dashboard
if (isLoggedIn()) {
    if (isClient()) {
        header("Location: client/client_dashboard.php");
        exit();
    } else {
        // If logged in but not client, logout and show client login
        logout();
    }
}

$error = '';
$username = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $auth = authenticateUser($username, $password, $connection);
        
        if ($auth['success']) {
            // Check if this is a client (type_id 2)
            if ($auth['user']['type_id'] == 2) {
                // Fetch the real client_id for this user
                $q = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = " . intval($auth['user']['user_id']));
                if ($q && mysqli_num_rows($q) > 0) {
                    $row = mysqli_fetch_assoc($q);
                    $_SESSION['client_id'] = $row['client_id'];
                } else {
                    // fallback: use user_id (legacy, but not recommended)
                    $_SESSION['client_id'] = $auth['user']['user_id'];
                }
                $redirect = getRedirectUrl($auth['user']);
                header("Location: $redirect");
                exit();
            } else {
                // Clear session if wrong portal
                logout();
                $error = "This portal is for clients only. Please use the employee login.";
            }
        } else {
            $error = $auth['message'];
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - OGMBC Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #f1bf70;
            --primary-dark: #e5b465;
            --primary-light: #fce6c0;
            --secondary: #1e293b;
            --dark: #0f172a;
            --light: #e2e8f0;
            --gold: #f1bf70;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --input-bg: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Subtle Mesh Gradient Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(241, 191, 112, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(236, 72, 153, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 60% 70%, rgba(6, 182, 212, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* Subtle grid pattern overlay */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }
        
        .container {
            width: 100%;
            max-width: 350px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Form Container with Shadow */
        .card {
            background: white;
            border-radius: 18px;
            padding: 1.3rem 1.1rem 1.1rem 1.1rem;
            box-shadow: 0 10px 24px -8px rgba(241, 191, 112, 0.08);
            border: 1px solid rgba(241, 191, 112, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 48px -16px rgba(0, 0, 0, 0.16);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo img {
            height: 36px;
            margin-bottom: 0.5rem;
        }
        
        .logo h1 {
            color: var(--text-dark);
            font-size: 1.15rem;
            font-weight: 600;
            margin-top: 0.3rem;
            letter-spacing: -0.5px;
        }
        
        .portal-badge {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .portal-badge span {
            background: linear-gradient(135deg, rgba(241, 191, 112, 0.12) 0%, rgba(241, 191, 112, 0.05) 100%);
            color: var(--primary-dark);
            padding: 0.32rem 0.8rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 600;
            border: 1px solid rgba(241, 191, 112, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .portal-badge span i {
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 0.85rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.3rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.82rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.1rem;
            z-index: 1;
            transition: color 0.2s ease;
        }
        
        .form-control {
            width: 100%;
            padding: 0.55rem 0.7rem 0.55rem 2.1rem;
            background: var(--input-bg);
            border: 1.2px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-dark);
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(241, 191, 112, 0.15);
        }
        
        .form-control::placeholder {
            color: #cbd5e1;
        }
        
        /* Password Toggle Button */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 1;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .password-toggle i {
            font-size: 1.1rem;
        }
        
        /* Button Styling */
        .btn {
            width: 100%;
            padding: 0.6rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #0f172a;
            border: none;
            border-radius: 10px;
            font-size: 0.93rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 0.4rem;
            box-shadow: 0 2px 8px rgba(241, 191, 112, 0.18);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(241, 191, 112, 0.35);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn i {
            font-size: 1.1rem;
        }
        
        .links {
            text-align: center;
            margin-top: 1.1rem;
        }
        
        .links a {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        
        .links a:hover {
            color: var(--primary);
            gap: 8px;
        }
        
        /* Error Message Styling */
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc2626;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }
        
        .error i {
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        /* Success Message Styling (if needed) */
        .success {
            background: #f0fdf4;
            color: #16a34a;
            padding: 1rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #16a34a;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Optional: Add subtle divider */
        .divider {
            text-align: center;
            margin: 1.5rem 0 1rem;
            position: relative;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 60px);
            height: 1px;
            background: var(--border-light);
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 520px) {
            .card {
                padding: 1.75rem;
            }
            
            .logo h1 {
                font-size: 1.5rem;
            }
            
            .logo img {
                height: 45px;
            }
        }
        
        /* Remove autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            box-shadow: 0 0 0 30px white inset !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="resources/img/logo.png" alt="OGMBC Logo">
                <h1>Welcome Back</h1>
            </div>
            
            <div class="portal-badge">
                <span>
                    <i class="bi bi-person-badge"></i>
                    Client Portal
                </span>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Enter your username or email" 
                               value="<?php echo htmlspecialchars($username); ?>" 
                               autocomplete="username"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" 
                               autocomplete="current-password"
                               required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login to Client Portal
                </button>
                
                <div class="links">
                    <a href="index.php">
                        <i class="bi bi-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Password visibility toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    // Toggle the type attribute
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle the eye icon
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-eye');
                        icon.classList.toggle('bi-eye-slash');
                    }
                });
            }
        });
    </script>
</body>
</html>