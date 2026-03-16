<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logout_message = "You have been successfully logged out. Go to <a href='index.php' class='alert-link'>Home</a>";
}

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Include database connection
require_once 'includes/database.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        // Check user status as well
        $sql = "SELECT user_id, username, user_email, user_role, user_status, password FROM users WHERE username = ? OR user_email = ?";
        
        if ($stmt = $connection->prepare($sql)) {
            $stmt->bind_param("ss", $username, $username);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    // Bind result in correct order
                    $stmt->bind_result($user_id, $db_username, $db_email, $db_role, $db_status, $db_password);
                    if ($stmt->fetch()) {
                        // Check if password column is not null
                        if (!isset($db_password)) {
                            $error = "Password column missing or null.";
                        }
                        // Check if user is active
                        elseif ($db_status != 'active') {
                            $error = "Your account is not active. Please contact administrator.";
                        }
                        // Use password_verify for hashed passwords
                        elseif (password_verify($password, $db_password)) { 
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $db_username;
                            $_SESSION['user_email'] = $db_email;
                            $_SESSION['user_role'] = $db_role;
                            header("Location: admin/dashboard.php");
                            exit();
                        } else {
                            $error = "Invalid password.";
                        }
                    }
                } else {
                    $error = "No account found with that username/email.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        } else {
            $error = "Database error. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
    
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --dark-blue: #0f172a;
            --medium-blue: #1e293b;
            --light-blue: #334155;
            --gold: #f1bf70;
            --light-gold: #f8d7a4;
            --text: #e2e8f0;
            --muted: #94a3b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-blue) 0%, var(--medium-blue) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 400px;
        }
        
        .card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(241, 191, 112, 0.2);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: var(--gold);
        }
        
        .logo h1 {
            color: var(--gold);
            font-size: 1.8rem;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--light-blue);
            border-radius: 6px;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(241, 191, 112, 0.2);
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--gold);
            color: var(--dark-blue);
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: var(--light-gold);
            transform: translateY(-2px);
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links a {
            color: var(--gold);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: var(--light-gold);
            text-decoration: underline;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #f87171;
        }
        
        .success {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #4ade80;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
            border-left: 4px solid #60a5fa;
        }

        /* Themed logout alert */
        .logout-alert {
            background: rgba(241, 191, 112, 0.06);
            color: var(--text);
            border-left: 4px solid var(--gold);
            padding: 0.75rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .logout-alert .message {
            color: var(--text);
            font-weight: 500;
        }

        .home-btn {
            display: inline-block;
            padding: 0.5rem 0.9rem;
            background: var(--gold);
            color: var(--dark-blue);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 6px 18px rgba(15,23,42,0.25);
        }
        
        .btn-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="resources/img/logo.png" alt="OGMBC Logo" style="height:60px;">
                <h1>Login</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="success">Registration successful! Please login.</div>
            <?php endif; ?>
            
            <?php if (isset($logout_message)): ?>
                <div class="logout-alert">
                    <div class="message"><i class="bi bi-check-circle-fill me-2" style="color:var(--gold);"></i> You have been successfully logged out.</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <a href="index.php" class="home-btn">Home</a>
                        <button type="button" class="btn-close" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
                    </div>
                </div>
                <script>
                (function(){
                    if (window.history && history.replaceState) {
                        try {
                            const url = new URL(window.location.href);
                            // Remove flash query params so the message doesn't reappear on refresh
                            ['logout','registered','error','reason'].forEach(p => url.searchParams.delete(p));
                            const newPath = url.pathname + (url.search ? ('?' + url.searchParams.toString()) : '');
                            history.replaceState(null, document.title, newPath);
                        } catch (e) {
                            // fallback: try manual replace without search params
                            const p = window.location.pathname;
                            history.replaceState(null, document.title, p);
                        }
                    }
                })();
                </script>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
                
                <div class="links">
                    Don't have an account? <a href="sign_up.php">Sign up here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>