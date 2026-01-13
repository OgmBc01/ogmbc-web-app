<?php
session_start();

if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Redirect to login page with logout message
    header("Location: login.php?logout=success");
    exit();
}

// If not confirmed, show confirmation page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .logout-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            text-align: center;
            max-width: 450px;
            width: 100%;
        }
        .logout-icon {
            font-size: 4rem;
            color: #f1bf70;
            margin-bottom: 1.5rem;
        }
        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .btn-logout:hover {
            background: #bb2d3b;
            color: white;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .btn-cancel:hover {
            background: #5c636a;
            color: white;
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <i class="bi bi-box-arrow-right logout-icon"></i>
        <h2 class="mb-3">Logout Confirmation</h2>
        <p class="text-muted mb-4">Are you sure you want to logout from your account?</p>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="logout.php?confirm=true" class="btn btn-logout me-md-2">
                <i class="bi bi-box-arrow-right me-2"></i> Yes, Logout
            </a>
            <a href="javascript:history.back()" class="btn btn-cancel">
                <i class="bi bi-x-circle me-2"></i> Cancel
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>