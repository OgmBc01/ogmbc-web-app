<?php
session_start();
include '../includes/database.php';

header('Content-Type: application/json');

// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_password = trim($_POST['user_password']);
    
    // Debug: Log session data
    error_log("=== BANK ACCOUNTS AUTH DEBUG ===");
    error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
    error_log("Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET'));
    error_log("Entered password length: " . strlen($entered_password));
    
    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        error_log("ERROR: User not logged in");
        echo json_encode(['success' => false, 'message' => 'Please login first.']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get hashed password from database
    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($row = mysqli_fetch_assoc($result)) {
            $hashed_password = $row['password'];
            
            error_log("Database hashed password: " . $hashed_password);
            error_log("Hashed password length: " . strlen($hashed_password));
            
            // Check if password is hashed or plain text
            if (strlen($hashed_password) < 60) {
                error_log("WARNING: Password appears to be plain text, not hashed");
                // Fallback to plain text comparison if not migrated yet
                if($entered_password === $hashed_password) {
                    error_log("SUCCESS: Plain text password match");
                    $_SESSION['bank_accounts_access'] = true;
                    $_SESSION['bank_accounts_access_time'] = time();
                    echo json_encode(['success' => true]);
                } else {
                    error_log("FAIL: Plain text password mismatch");
                    echo json_encode(['success' => false, 'message' => 'Invalid password. Please try again.']);
                }
            } else {
                // Password is hashed, use password_verify
                error_log("Using password_verify for hashed password");
                if(password_verify($entered_password, $hashed_password)) {
                    error_log("SUCCESS: Hashed password verification successful");
                    $_SESSION['bank_accounts_access'] = true;
                    $_SESSION['bank_accounts_access_time'] = time();
                    echo json_encode(['success' => true]);
                } else {
                    error_log("FAIL: Hashed password verification failed");
                    echo json_encode(['success' => false, 'message' => 'Invalid password. Please try again.']);
                }
            }
        } else {
            error_log("ERROR: User not found in database for user_id: " . $user_id);
            echo json_encode(['success' => false, 'message' => 'User not found in database.']);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        error_log("ERROR: Database statement preparation failed");
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    
    error_log("=== END DEBUG ===");
}
?>