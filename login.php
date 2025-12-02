<?php
session_start();

// At the top of login.php, after session_start()
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logout_message = "You have been successfully logged out. Go to <a href='index.php' class='alert-link'>Home</a>";
}

include 'includes/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        // Include user_role in the SELECT query
        $sql = "SELECT user_id, username, user_email, user_role, password FROM users WHERE username = ?";
        
        if ($stmt = $connection->prepare($sql)) {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    // Bind user_id, username, email, user_role, and password
                    $stmt->bind_result($user_id, $db_username, $db_email, $db_role, $db_password);
                    
                    if ($stmt->fetch()) {
                        // Use password_verify for hashed passwords
                        if (password_verify($password, $db_password)) { 
                            // Regenerate session ID for security
                            session_regenerate_id(true);
                            
                            // Save into session (don't store password in session)
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $db_username;
                            $_SESSION['user_email'] = $db_email;
                            $_SESSION['user_role'] = $db_role;
                            // Do NOT store password in session for security
                            
                            // Update last login time (optional)
                            $update_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                            if ($update_stmt = $connection->prepare($update_sql)) {
                                $update_stmt->bind_param("i", $user_id);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            header("Location: admin/dashboard.php");
                            exit();
                        } else {
                            $error = "Invalid password.";
                        }
                    }
                } else {
                    $error = "No account found with that username.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <i class="bi bi-shield-shaded"></i>
                <h1>Login</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="success">Registration successful! Please login.</div>
            <?php endif; ?>
            
            <?php if (isset($logout_message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i> <?php echo $logout_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
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