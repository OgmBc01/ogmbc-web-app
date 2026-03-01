<?php
/**
 * Audit Log Helper Functions
 * Include this file in other modules to log actions
 */

/**
 * Log an action to the audit log
 * 
 * @param mysqli $connection Database connection
 * @param int $user_id User performing the action
 * @param string $username Username of the user
 * @param string $action Action type (INSERT, UPDATE, DELETE, LOGIN, LOGOUT, EXPORT)
 * @param string $table_name Table being affected
 * @param int|null $record_id ID of the record affected
 * @param array|null $old_data Old data before change
 * @param array|null $new_data New data after change
 * @param string|null $description Human-readable description
 * @return bool Success or failure
 */
function log_action($connection, $user_id, $username, $action, $table_name, $record_id = null, $old_data = null, $new_data = null, $description = null) {
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    
    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Get request URL
    $request_url = $_SERVER['REQUEST_URI'] ?? null;
    
    // Calculate changes if both old and new data are provided
    $changes = null;
    if ($old_data && $new_data) {
        $changes = [];
        foreach ($new_data as $key => $value) {
            if (isset($old_data[$key]) && $old_data[$key] != $value) {
                $changes[$key] = [
                    'old' => $old_data[$key],
                    'new' => $value
                ];
            }
        }
        $changes = json_encode($changes);
    }
    
    // Prepare JSON data
    $old_json = $old_data ? json_encode($old_data) : null;
    $new_json = $new_data ? json_encode($new_data) : null;
    
    // Escape strings
    $username = mysqli_real_escape_string($connection, $username);
    $action = mysqli_real_escape_string($connection, $action);
    $table_name = mysqli_real_escape_string($connection, $table_name);
    $description = $description ? "'" . mysqli_real_escape_string($connection, $description) . "'" : "NULL";
    $ip_address = $ip_address ? "'" . mysqli_real_escape_string($connection, $ip_address) . "'" : "NULL";
    $user_agent = $user_agent ? "'" . mysqli_real_escape_string($connection, $user_agent) . "'" : "NULL";
    $request_url = $request_url ? "'" . mysqli_real_escape_string($connection, $request_url) . "'" : "NULL";
    $old_json = $old_json ? "'" . mysqli_real_escape_string($connection, $old_json) . "'" : "NULL";
    $new_json = $new_json ? "'" . mysqli_real_escape_string($connection, $new_json) . "'" : "NULL";
    $changes = $changes ? "'" . mysqli_real_escape_string($connection, $changes) . "'" : "NULL";
    $record_id = $record_id ?: 'NULL';
    
    $query = "INSERT INTO audit_log 
              (user_id, username, action, table_name, record_id, old_data, new_data, changes, description, ip_address, user_agent, request_url)
              VALUES 
              ($user_id, '$username', '$action', '$table_name', $record_id, $old_json, $new_json, $changes, $description, $ip_address, $user_agent, $request_url)";
    
    return mysqli_query($connection, $query);
}

/**
 * Log a login action
 */
function log_login($connection, $user_id, $username) {
    return log_action($connection, $user_id, $username, 'LOGIN', 'users', $user_id, null, null, 'User logged in');
}

/**
 * Log a logout action
 */
function log_logout($connection, $user_id, $username) {
    return log_action($connection, $user_id, $username, 'LOGOUT', 'users', $user_id, null, null, 'User logged out');
}

/**
 * Log an export action
 */
function log_export($connection, $user_id, $username, $table_name, $description) {
    return log_action($connection, $user_id, $username, 'EXPORT', $table_name, null, null, null, $description);
}