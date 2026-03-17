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
$bg_gradient = "linear-gradient(135deg, #11998e 0%, #0a7e6b 100%)";
$portal_name = "Client Portal";
$icon = "bi-person-badge";
$icon_color = "#11998e";

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
            --primary: #11998e;
            --primary-dark: #0a7e6b;
            --secondary: #1e293b;
            --dark: #0f172a;
            --light: #e2e8f0;
            --gold: #f1bf70;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: <?php echo $bg_gradient; ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        .background-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0 0 L100 100 M100 0 L0 100" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></svg>');
            background-size: 30px 30px;
            pointer-events: none;
            z-index: 0;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo i {
            font-size: 3rem;
            color: <?php echo $icon_color; ?>;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 50%;
            margin-bottom: 1rem;
        }
        
        .logo h1 {
            color: white;
            font-size: 1.8rem;
            margin-top: 0.5rem;
        }
        
        .portal-badge {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .portal-badge span {
            background: rgba(17, 153, 142, 0.2);
            color: <?php echo $primary; ?>;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(17, 153, 142, 0.3);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e2e8f0;
            font-weight: 500;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: <?php echo $primary; ?>;
            font-size: 1.2rem;
            z-index: 1;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: <?php echo $primary; ?>;
            box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.2);
        }
        
        .form-control::placeholder {
            color: rgba(255,255,255,0.3);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            background: <?php echo $primary; ?>;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            background: <?php echo $primary; ?>;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(17, 153, 142, 0.4);
        }
        
        .btn i {
            font-size: 1.2rem;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links a {
            color: <?php echo $primary; ?>;
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .links a:hover {
            color: white;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #f87171;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #4ade80;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-link a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="resources/img/logo.png" alt="OGMBC Logo" style="height:50px;">
                <h1>Welcome Back</h1>
            </div>
            
            <div class="portal-badge">
                <span><i class="bi <?php echo $icon; ?> me-2"></i><?php echo $portal_name; ?></span>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Enter your username or email" 
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
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
</body>
</html>