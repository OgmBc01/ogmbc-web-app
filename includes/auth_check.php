<?php
/**
 * Authentication Helper
 * Handles user authentication and role-based redirection
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

function authenticateUser($username, $password, $connection) {
        $sql = "SELECT u.user_id, u.username, u.user_email, u.user_status, 
                 u.password, u.role_id, u.type_id,
                 r.role_name, t.type_name
             FROM users u
             LEFT JOIN user_roles r ON u.role_id = r.role_id
             LEFT JOIN user_types t ON u.type_id = t.type_id
             WHERE u.username = ? OR u.user_email = ?";

    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param("ss", $username, $username);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $user_id = $db_username = $db_email = $db_status = $db_password = $role_id = $type_id = $role_name = $type_name = null;
                $stmt->bind_result(
                    $user_id, $db_username, $db_email, $db_status,
                    $db_password, $role_id, $type_id, $role_name, $type_name
                );
                if ($stmt->fetch()) {
                    // Check account status
                    if ($db_status != 'active') {
                        return ['success' => false, 'message' => 'Your account is not active. Please contact administrator.'];
                    }
                    // Check password exists
                    if (!isset($db_password) || empty($db_password)) {
                        return ['success' => false, 'message' => 'Password not set for this user. Please contact administrator.'];
                    }
                    // Verify password
                    if (password_verify($password, $db_password)) {
                        // Initialize session with security settings
                        initSession();
                        // Set session variables
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $db_username;
                        $_SESSION['user_email'] = $db_email;
                        $_SESSION['role_id'] = $role_id;
                        $_SESSION['type_id'] = $type_id;
                        $_SESSION['role_name'] = $role_name;
                        $_SESSION['type_name'] = $type_name;
                        return ['success' => true, 'user' => [
                            'user_id' => $user_id,
                            'role_id' => $role_id,
                            'type_id' => $type_id,
                            'role_name' => $role_name,
                            'type_name' => $type_name
                        ]];
                    } else {
                        return ['success' => false, 'message' => 'Invalid password.'];
                    }
                }
            } else {
                return ['success' => false, 'message' => 'No account found with that username/email.'];
            }
        } else {
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }
        $stmt->close();
    } else {
        return ['success' => false, 'message' => 'Database error. Please try again.'];
    }
}

function getRedirectUrl($user) {
    $role_id = $user['role_id'];
    $type_id = $user['type_id'];
    $role_name = strtolower($user['role_name'] ?? '');
    $type_name = strtolower($user['type_name'] ?? '');
    
    // Case 1: Admin (role_id 1) with System (type_id 7)
    if ($role_id == 1 && $type_id == 7) {
        return 'admin/dashboard.php';
    }
    
    // Case 2: Editor (role_id 4) with Operations (type_id 1)
    if ($role_id == 4 && $type_id == 1) {
        return 'operations/operations_dashboard.php';
    }
    
    // Case 3: Editor (role_id 4) with Client (type_id 2)
    if ($role_id == 4 && $type_id == 2) {
        return 'client/client_dashboard.php';
    }
    
    // Fallback: Check role names
    if (in_array($role_name, ['admin', 'ceo_gm', 'hr_admin'])) {
        return 'admin/dashboard.php';
    }
    
    if (in_array($type_name, ['operations', 'employee'])) {
        return 'operations/operations_dashboard.php';
    }
    
    if ($type_name == 'client') {
        return 'client/client_dashboard.php';
    }
    
    // Default fallback
    return 'index.php';
}