<?php
session_start();
include 'includes/database.php';

$error = '';
$username = $user_email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $user_email = trim($_POST['user_email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($username)) {
        $error = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } elseif (empty($user_email)) {
        $error = "Please enter an email address.";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) { // Fixed variable name from $email to $user_email
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
                
                if ($stmt->num_rows == 1) {
                    $error = "This username is already taken.";
                } else {
                    // Check if email already exists
                    $sql = "SELECT user_id FROM users WHERE user_email = ?";
                    
                    if ($stmt = $connection->prepare($sql)) {
                        $stmt->bind_param("s", $user_email);
                        
                        if ($stmt->execute()) {
                            $stmt->store_result();
                            
                            if ($stmt->num_rows == 1) {
                                $error = "This email is already registered.";
                            } else {
                                // Insert new user
                                $sql = "INSERT INTO users (username, user_email, password) VALUES (?, ?, ?)";
                                
                                if ($stmt = $connection->prepare($sql)) {
                                    // Note: In a real application, you should hash the password
                                    $stmt->bind_param("sss", $username, $user_email, $password);
                                    
                                    if ($stmt->execute()) {
                                        header("Location: login.php?registered=true");
                                        exit();
                                    } else {
                                        $error = "Something went wrong. Please try again later.";
                                    }
                                    
                                    $stmt->close();
                                }
                            }
                        } else {
                            $error = "Oops! Something went wrong. Please try again later.";
                        }
                    }
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
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
    <title>Sign Up - Admin Panel</title>
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
            max-width: 450px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <i class="bi bi-shield-shaded"></i>
                <h1>Create Account</h1>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
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