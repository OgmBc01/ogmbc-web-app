<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
require_once 'includes/database.php';

$error = '';
$success = '';
$username = $user_email = $first_name = $last_name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $user_email = trim($_POST['user_email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($first_name)) {
        $error = "Please enter your first name.";
    } elseif (empty($last_name)) {
        $error = "Please enter your last name.";
    } elseif (empty($username)) {
        $error = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } elseif (empty($user_email)) {
        $error = "Please enter an email address.";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $error = "Password must have at least 6 characters.";
    } elseif ($password != $confirm_password) {
        $error = "Passwords did not match.";
    } else {
        // Check if username already exists
        $sql = "SELECT user_id FROM users WHERE username = ?";
        
        if ($stmt = $connection->prepare($sql)) {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $error = "This username is already taken.";
                } else {
                    // Check if email already exists
                    $stmt->close();
                    $sql = "SELECT user_id FROM users WHERE user_email = ?";
                    
                    if ($stmt = $connection->prepare($sql)) {
                        $stmt->bind_param("s", $user_email);
                        
                        if ($stmt->execute()) {
                            $stmt->store_result();
                            
                            if ($stmt->num_rows > 0) {
                                $error = "This email is already registered.";
                            } else {
                                $stmt->close();
                                
                                // Prepare INSERT query with all required fields based on your table structure
                                $sql = "INSERT INTO users (first_name, last_name, username, user_email, password, user_image, user_role, user_type, user_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                
                                if ($stmt = $connection->prepare($sql)) {
                                    // Hash the password before storing
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    
                                    // Default values for new users based on your table
                                    $default_image = 'default.jpg';
                                    $user_role = 'subscriber';  // From your table default
                                    $user_type = 'client';      // From your table default
                                    $user_status = 'active';    // From your table default
                                    
                                    $stmt->bind_param("sssssssss", 
                                        $first_name, 
                                        $last_name, 
                                        $username, 
                                        $user_email, 
                                        $hashed_password,
                                        $default_image,
                                        $user_role,
                                        $user_type,
                                        $user_status
                                    );
                                    
                                    if ($stmt->execute()) {
                                        // Success - redirect to login page
                                        header("Location: login.php?registered=true");
                                        exit();
                                    } else {
                                        $error = "Registration failed. Error: " . $stmt->error;
                                    }
                                } else {
                                    $error = "Database preparation failed: " . $connection->error;
                                }
                            }
                        } else {
                            $error = "Database error. Please try again.";
                        }
                    }
                }
            } else {
                $error = "Database error. Please try again.";
            }
            
            if ($stmt) {
                $stmt->close();
            }
        } else {
            $error = "Database preparation failed.";
        }
    }
    
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - OGMBC</title>
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
            max-width: 500px;
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
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 576px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
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
        
        .password-hint {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="resources/img/logo.png" alt="OGMBC Logo" style="height:60px;">
                <h1>Sign Up</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" 
                               value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($username); ?>" required>
                    <div class="password-hint">Only letters, numbers, and underscores</div>
                </div>
                
                <div class="form-group">
                    <label for="user_email">Email *</label>
                    <input type="email" id="user_email" name="user_email" class="form-control" 
                           value="<?php echo htmlspecialchars($user_email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="password-hint">Minimum 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
                
                <div class="links">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>