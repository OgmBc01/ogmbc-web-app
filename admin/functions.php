<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function initSession() {
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['CREATED'])) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
    // Set secure session cookie parameters BEFORE any output
    if (PHP_SESSION_ACTIVE && !headers_sent()) {
        $cookieParams = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires' => time() + 7200, // 2 hours
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user's role ID
 * @return int|null
 */
function getCurrentRoleId() {
    return $_SESSION['role_id'] ?? null;
}

/**
 * Get current user's type ID
 * @return int|null
 */
function getCurrentTypeId() {
    return $_SESSION['type_id'] ?? null;
}

/**
 * Check if current user is admin (role_id 1, type_id 7)
 * @return bool
 */
function isAdmin() {
    return (
        (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1 && isset($_SESSION['type_id']) && $_SESSION['type_id'] == 7)
        ||
        (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2 && isset($_SESSION['type_id']) && $_SESSION['type_id'] == 1)
    );
}

/**
 * Require authentication - redirect to login if not authenticated
 * @param string $loginPage
 */
function requireAuth($loginPage = 'index.php') {
    if (!isLoggedIn()) {
        header("Location: $loginPage");
        exit();
    }
}

/**
 * Require specific role/type combination
 * @param int $requiredRoleId
 * @param int $requiredTypeId
 * @param string $redirectPage
 */
function requireRole($requiredRoleId, $requiredTypeId, $redirectPage = 'index.php') {
    if (!isLoggedIn() || 
        getCurrentRoleId() != $requiredRoleId || 
        getCurrentTypeId() != $requiredTypeId) {
        header("Location: $redirectPage");
        exit();
    }
}

/**
 * Clear all session data and destroy session
 */
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Set success message in session
 * @param string $message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Set error message in session
 * @param string $message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Get and clear success message
 * @return string|null
 */
function getSuccessMessage() {
    $message = $_SESSION['success_message'] ?? null;
    unset($_SESSION['success_message']);
    return $message;
}

/**
 * Get and clear error message
 * @return string|null
 */
function getErrorMessage() {
    $message = $_SESSION['error_message'] ?? null;
    unset($_SESSION['error_message']);
    return $message;
}

// Reusable session inactivity timeout checker
if (!function_exists('enforce_session_timeout')) {
function enforce_session_timeout($timeout_seconds = 1800, $redirect = '../index.php?error=session&reason=inactivity') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Only enforce for logged-in users
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $now = time();
    if (isset($_SESSION['last_activity'])) {
        $elapsed = $now - intval($_SESSION['last_activity']);
        if ($elapsed > intval($timeout_seconds)) {
            // expire session and redirect
            session_unset();
            session_destroy();
            header("Location: {$redirect}");
            exit();
        }
    }

    // update last activity timestamp
    $_SESSION['last_activity'] = $now;
}
} // Close function_exists block for enforce_session_timeout

// Function to sanitize HTML content //
if (!function_exists('sanitizeHTML')) {
function sanitizeHTML($html) {
    // Use built-in PHP filter for basic sanitization
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}
} // Close function_exists block for sanitizeHTML

// Function to insert/update categories
if (!function_exists('insert_categories')) {
function insert_categories() {
    global $connection;

    if(isset($_POST['submit'])) {
        $cat_title = mysqli_real_escape_string($connection, $_POST['cat_title']);
        $cat_price = isset($_POST['cat_price']) ? floatval($_POST['cat_price']) : 0.00;
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

        if($cat_title == "" || empty($cat_title)) {
            echo "<script>showAlert('Category title should not be empty', 'error');</script>";
        } else if($cat_price < 0) {
            echo "<script>showAlert('Price cannot be negative', 'error');</script>";
        } else {
            if ($cat_id > 0) {
                // Update existing category
                $query = "UPDATE categories SET cat_title = '{$cat_title}', cat_price = {$cat_price} WHERE cat_id = {$cat_id}";
                $success_message = "Category updated successfully!";
                $redirect_param = "updated=true";
            } else {
                // Insert new category
                $query = "INSERT INTO categories(cat_title, cat_price) VALUES('{$cat_title}', {$cat_price})";
                $success_message = "Category added successfully!";
                $redirect_param = "added=true";
            }

            $category_query = mysqli_query($connection, $query);

            if(!$category_query) {
                die('Query Failed' . mysqli_error($connection));
            } else {
                // Use JavaScript redirect instead of header redirect
                echo "<script>window.location.href = 'categories.php?{$redirect_param}';</script>";
                exit;
            }
        }
    }
}
} // Close function_exists block for insert_categories

//////////////// FIND CATEGORY BY ID //////////////////
if (!function_exists('findCategoryById')) {
function findCategoryById($cat_id) {
    global $connection;
    
    $query = "SELECT * FROM categories WHERE cat_id = " . intval($cat_id);
    $result = mysqli_query($connection, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
    
    return null;
}
} // Close function_exists block for findCategoryById

//////////////// GET CATEGORY PRICE //////////////////
if (!function_exists('getCategoryPrice')) {
function getCategoryPrice($cat_id) {
    global $connection;
    
    $query = "SELECT cat_price FROM categories WHERE cat_id = " . intval($cat_id);
    $result = mysqli_query($connection, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return floatval($row['cat_price']);
    }
    
    return 0.00;
}
} // Close function_exists block for getCategoryPrice


//////////////// FIND ALL CATEGORIES //////////////////
if (!function_exists('findAllCategories')) {
function findAllCategories() {
    global $connection;

    $query = "SELECT * FROM categories ORDER BY cat_id DESC";
    $select_all_categories_query = mysqli_query($connection, $query);

    while($row = mysqli_fetch_assoc($select_all_categories_query)) {
        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];
        $cat_price = isset($row['cat_price']) ? number_format($row['cat_price'], 2) : '0.00';

        echo "<tr>";
        echo "<td>{$cat_id}</td>";
        echo "<td>{$cat_title}</td>";
        echo "<td>AED " . $cat_price . "</td>";
        echo "<td class='action-links'>";
        echo "<a href='categories.php?edit={$cat_id}'><i class='bi bi-pencil'></i> Edit</a>";
        echo "<a href='' data-bs-toggle='modal' data-bs-target='#confirmDeleteModalCategory' data-id='{$cat_id}' data-name='{$cat_title}' onclick='setDeleteId({$cat_id}, \"{$cat_title}\")'><i class='bi bi-trash'></i> Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}
} // Close function_exists block for findAllCategories

// Function to delete categories
if (!function_exists('deleteCategory')) {
function deleteCategory() {
    global $connection;

    if (isset($_GET['delete_category'])) {
        $cat_id = intval($_GET['delete_category']); // Sanitize the input

        // Delete query
        $query = "DELETE FROM categories WHERE cat_id = {$cat_id}";
        $delete_query = mysqli_query($connection, $query);

        if ($delete_query) {
            // Use JavaScript redirect instead of header redirect
            echo "<script>window.location.href = 'categories.php?deleted=true';</script>";
            exit;
        } else {
            // Handle deletion error
            echo "<script>window.location.href = 'categories.php?error=true';</script>";
            exit;
        }
    }
}
} // Close function_exists block for deleteCategory


////////////////////////

//BANK ACCOUNTS LOGIC 

//////////////////

if (!function_exists('checkBankAccountsSession')) {
function checkBankAccountsSession() {
    if(!isset($_SESSION['bank_accounts_access']) || !$_SESSION['bank_accounts_access']) {
        header('Location: dashboard.php?session_expired=true');
        exit;
    }
    
    // Check inactivity (30 seconds)
    $inactive_time = 30;
    if(isset($_SESSION['bank_accounts_last_activity']) && 
       (time() - $_SESSION['bank_accounts_last_activity']) > $inactive_time) {
        
        unset($_SESSION['bank_accounts_access']);
        unset($_SESSION['bank_accounts_last_activity']);
        
        header('Location: dashboard.php?session_expired=true');
        exit;
    }
    
    // Update last activity
    $_SESSION['bank_accounts_last_activity'] = time();
}

    function insert_bank_account() {
    global $connection;
    
    // Check session before proceeding
    checkBankAccountsSession();

    if(isset($_POST['submit_bank_account'])) {
        $bank_name = mysqli_real_escape_string($connection, $_POST['bank_name']);
        $account_name = mysqli_real_escape_string($connection, $_POST['account_name']);
        $account_number = mysqli_real_escape_string($connection, $_POST['account_number']);
        $iban_number = mysqli_real_escape_string($connection, $_POST['iban_number']);
        $swift_code = mysqli_real_escape_string($connection, $_POST['swift_code']);
        $bank_country = mysqli_real_escape_string($connection, $_POST['bank_country']);
        $bank_address = mysqli_real_escape_string($connection, $_POST['bank_address']);
        $currency = mysqli_real_escape_string($connection, $_POST['currency']);
        $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;

        if(empty($bank_name) || empty($account_name) || empty($account_number) || empty($bank_country)) {
            echo "<script>showAlert('All required fields must be filled', 'error');</script>";
        } else {
            if ($account_id > 0) {
                // Update existing account
                $query = "UPDATE bank_accounts SET 
                         bank_name = '{$bank_name}', 
                         account_name = '{$account_name}', 
                         account_number = '{$account_number}', 
                         iban_number = '{$iban_number}', 
                         swift_code = '{$swift_code}', 
                         bank_country = '{$bank_country}', 
                         bank_address = '{$bank_address}', 
                         currency = '{$currency}' 
                         WHERE account_id = {$account_id}";
                $success_message = "Bank account updated successfully!";
                $redirect_param = "updated=true";
            } else {
                // Insert new account
                $query = "INSERT INTO bank_accounts(
                         bank_name, account_name, account_number, iban_number, 
                         swift_code, bank_country, bank_address, currency) 
                         VALUES(
                         '{$bank_name}', '{$account_name}', '{$account_number}', '{$iban_number}', 
                         '{$swift_code}', '{$bank_country}', '{$bank_address}', '{$currency}')";
                $success_message = "Bank account added successfully!";
                $redirect_param = "added=true";
            }

            $bank_account_query = mysqli_query($connection, $query);

            if(!$bank_account_query) {
                die('Query Failed: ' . mysqli_error($connection));
            } else {
                echo "<script>showAlert('{$success_message}', 'success');</script>";
                echo "<script>setTimeout(() => { window.location.href = 'bank_accounts.php?{$redirect_param}'; }, 1000);</script>";
                exit;
            }
        }
    }
    }
} // Close function_exists block for insert_bank_account


if (!function_exists('findAllBankAccounts')) {
    function findAllBankAccounts() {
    global $connection;

    $query = "SELECT * FROM bank_accounts ORDER BY account_id DESC";
    $select_all_accounts_query = mysqli_query($connection, $query);

    while($row = mysqli_fetch_assoc($select_all_accounts_query)) {
        $account_id = $row['account_id'];
        $bank_name = $row['bank_name'];
        $account_name = $row['account_name'];
        $account_number = $row['account_number'];
        $iban_number = $row['iban_number'];
        $swift_code = $row['swift_code'];
        $bank_country = $row['bank_country'];
        $currency = $row['currency'];
        $is_active = $row['is_active'];

        echo "<tr>";
        echo "<td>{$account_id}</td>";
        echo "<td>{$bank_name}</td>";
        echo "<td>{$account_name}</td>";
        echo "<td>{$account_number}</td>";
        echo "<td>{$iban_number}</td>";
        echo "<td>{$swift_code}</td>";
        echo "<td>{$bank_country}</td>";
        echo "<td>{$currency}</td>";
        echo "<td>" . ($is_active ? 'Active' : 'Inactive') . "</td>";
        echo "<td class='action-links'>";
        echo "<a href='includes/edit_bank_account.php?edit={$account_id}' onclick='return verifyAccess()'><i class='bi bi-pencil'></i> Edit</a>";
        echo "<a href='' data-bs-toggle='modal' data-bs-target='#confirmDeleteModalBankAccount' data-id='{$account_id}' data-name='{$bank_name} - {$account_name}' onclick='setDeleteBankAccountId({$account_id}, \"{$bank_name} - {$account_name}\")'><i class='bi bi-trash'></i> Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    }
} // Close function_exists block for findAllBankAccounts

if (!function_exists('deleteBankAccount')) {
    function deleteBankAccount() {
    global $connection;
    
    // Check session before proceeding
    checkBankAccountsSession();
    
    if(isset($_GET['delete'])) {
        $account_id = $_GET['delete'];
        
        $query = "DELETE FROM bank_accounts WHERE account_id = {$account_id}";
        $delete_query = mysqli_query($connection, $query);
        
        if(!$delete_query) {
            die('Query Failed: ' . mysqli_error($connection));
        } else {
            echo "<script>showAlert('Bank account deleted successfully!', 'success');</script>";
            echo "<script>setTimeout(() => { window.location.href = 'bank_accounts.php?deleted=true'; }, 1000);</script>";
            exit;
        }
    }
    }
} // Close function_exists block for deleteBankAccount

if (!function_exists('verifyBankAccess')) {
function verifyBankAccess() {
    // Don't call session_start() here if it's already called in your pages
    
    // Check if user has access
    if(!isset($_SESSION['bank_accounts_access']) || !$_SESSION['bank_accounts_access']) {
        echo "<script>
            alert('Session expired or access denied. Please authenticate again.');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
    
    // Check inactivity (30 seconds)
    $inactive_time = 30;
    if(isset($_SESSION['bank_accounts_last_activity']) && 
       (time() - $_SESSION['bank_accounts_last_activity']) > $inactive_time) {
        
        unset($_SESSION['bank_accounts_access']);
        unset($_SESSION['bank_accounts_last_activity']);
        
        echo "<script>
            alert('Session expired due to inactivity. Please authenticate again.');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
    
    // Update last activity
    $_SESSION['bank_accounts_last_activity'] = time();
}


//////////////// CLIENT MANAGEMENT FUNCTIONS ////////////////
// Function to generate a unique but readable password
function generate_client_password($company_name, $contact_name) {
    // Get first 3 letters of company name (uppercase)
    $company_prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $company_name), 0, 3));
    if (strlen($company_prefix) < 3) {
        $company_prefix = str_pad($company_prefix, 3, 'X');
    }
    
    // Get first 2 letters of contact name (lowercase)
    $contact_prefix = strtolower(substr(preg_replace('/[^A-Za-z]/', '', $contact_name), 0, 2));
    if (strlen($contact_prefix) < 2) {
        $contact_prefix = str_pad($contact_prefix, 2, 'x');
    }
    
    // Add random numbers and special character
    $random_numbers = rand(100, 999);
    $special_chars = ['!', '@', '#', '$', '%', '&', '*'];
    $special_char = $special_chars[array_rand($special_chars)];
    
    // Combine to create password
    $password = $company_prefix . $contact_prefix . $random_numbers . $special_char;
    
    return $password;
}

// Function to check if password is unique
function is_password_unique($connection, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Note: We can't search by hashed password directly, so we'll generate a few times if needed
    // For simplicity, we'll assume passwords are unique enough with our generation method
    return true;
}


// Function to insert new client (without service_id)
function insert_client() {
    global $connection;

    if (isset($_POST['submit_client'])) {
        $client_id = intval($_POST['client_id'] ?? 0);
        
        // Only proceed for new client (client_id = 0)
        if ($client_id > 0) {
            return;
        }
        
        // Sanitize and validate inputs
        $company_name = mysqli_real_escape_string($connection, trim($_POST['company_name']));
        $trade_license_no = mysqli_real_escape_string($connection, trim($_POST['trade_license_no'] ?? ''));
        $country = mysqli_real_escape_string($connection, trim($_POST['country']));
        $jurisdiction = mysqli_real_escape_string($connection, trim($_POST['jurisdiction'] ?? ''));
        $emirate_zone = mysqli_real_escape_string($connection, trim($_POST['emirate_zone'] ?? ''));
        $business_activity = mysqli_real_escape_string($connection, trim($_POST['business_activity'] ?? ''));
        $industry = mysqli_real_escape_string($connection, trim($_POST['industry'] ?? ''));
        $address = mysqli_real_escape_string($connection, trim($_POST['address'] ?? ''));
        $contact_title = mysqli_real_escape_string($connection, trim($_POST['contact_title'] ?? ''));
        $contact_name = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
        $contact_designation = mysqli_real_escape_string($connection, trim($_POST['contact_designation'] ?? ''));
        $contact_mobile = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
        $contact_email = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
        $service_description = mysqli_real_escape_string($connection, trim($_POST['service_description'] ?? ''));
        $contract_start_date = mysqli_real_escape_string($connection, trim($_POST['contract_start_date'] ?? ''));
        $contract_end_date = mysqli_real_escape_string($connection, trim($_POST['contract_end_date'] ?? ''));
        $payment_currency = mysqli_real_escape_string($connection, trim($_POST['payment_currency'] ?? 'AED'));
        $payment_term = mysqli_real_escape_string($connection, trim($_POST['payment_term'] ?? 'Monthly'));
        $service_total_fee = floatval($_POST['service_total_fee'] ?? 0.00);
        $lead_source = mysqli_real_escape_string($connection, trim($_POST['lead_source'] ?? 'website'));
        $client_status = mysqli_real_escape_string($connection, trim($_POST['client_status'] ?? 'New Lead'));

        // Validation
        if (empty($company_name) || empty($contact_name) || empty($contact_mobile) || empty($contact_email)) {
            $_SESSION['error_message'] = 'Please fill in all required fields';
            return;
        }

        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Please enter a valid email address';
            return;
        }

        // Generate a unique password for new client
        $plain_password = generate_client_password($company_name, $contact_name);
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
        
        // Insert new client - service_id set to NULL by default
        $query = "INSERT INTO clients (
                    company_name, trade_license_no, country, jurisdiction, emirate_zone, business_activity, industry, address,
                    contact_title, contact_name, contact_designation, contact_mobile, contact_email, client_password,
                    service_description, contract_start_date, contract_end_date, payment_currency, payment_term, 
                    service_total_fee, lead_source, client_status, service_id
                ) VALUES (
                    '$company_name', '$trade_license_no', '$country', '$jurisdiction', '$emirate_zone', '$business_activity', '$industry', '$address',
                    '$contact_title', '$contact_name', '$contact_designation', '$contact_mobile', '$contact_email', '$hashed_password',
                    '$service_description', " . ($contract_start_date ? "'$contract_start_date'" : "NULL") . ", " . ($contract_end_date ? "'$contract_end_date'" : "NULL") . ",
                    '$payment_currency', '$payment_term', $service_total_fee, '$lead_source', '$client_status', NULL
                )";

        if (mysqli_query($connection, $query)) {
            $new_client_id = mysqli_insert_id($connection);
            send_client_welcome_email($contact_email, $company_name, $plain_password);
            $_SESSION['new_client_password'] = $plain_password;
            $_SESSION['new_client_email'] = $contact_email;
            $_SESSION['new_client_name'] = $company_name;
            $_SESSION['client_add_success'] = true;
        } else {
            $_SESSION['error_message'] = 'Query Failed: ' . mysqli_error($connection);
        }
    }
}


// Function to update existing client (without service_id)
function update_client() {
    global $connection;

    if (isset($_POST['update_client'])) {
        $client_id = intval($_POST['client_id'] ?? 0);
        
        // Only proceed for existing client
        if ($client_id <= 0) {
            return;
        }
        
        // Sanitize and validate inputs
        $company_name = mysqli_real_escape_string($connection, trim($_POST['company_name']));
        $trade_license_no = mysqli_real_escape_string($connection, trim($_POST['trade_license_no'] ?? ''));
        $country = mysqli_real_escape_string($connection, trim($_POST['country']));
        $jurisdiction = mysqli_real_escape_string($connection, trim($_POST['jurisdiction'] ?? ''));
        $emirate_zone = mysqli_real_escape_string($connection, trim($_POST['emirate_zone'] ?? ''));
        $business_activity = mysqli_real_escape_string($connection, trim($_POST['business_activity'] ?? ''));
        $industry = mysqli_real_escape_string($connection, trim($_POST['industry'] ?? ''));
        $address = mysqli_real_escape_string($connection, trim($_POST['address'] ?? ''));
        $contact_title = mysqli_real_escape_string($connection, trim($_POST['contact_title'] ?? ''));
        $contact_name = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
        $contact_designation = mysqli_real_escape_string($connection, trim($_POST['contact_designation'] ?? ''));
        $contact_mobile = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
        $contact_email = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
        $service_description = mysqli_real_escape_string($connection, trim($_POST['service_description'] ?? ''));
        $contract_start_date = mysqli_real_escape_string($connection, trim($_POST['contract_start_date'] ?? ''));
        $contract_end_date = mysqli_real_escape_string($connection, trim($_POST['contract_end_date'] ?? ''));
        $payment_currency = mysqli_real_escape_string($connection, trim($_POST['payment_currency'] ?? 'AED'));
        $payment_term = mysqli_real_escape_string($connection, trim($_POST['payment_term'] ?? 'Monthly'));
        $service_total_fee = floatval($_POST['service_total_fee'] ?? 0.00);
        $lead_source = mysqli_real_escape_string($connection, trim($_POST['lead_source'] ?? 'website'));
        $client_status = mysqli_real_escape_string($connection, trim($_POST['client_status'] ?? 'New Lead'));

        // Validation
        if (empty($company_name) || empty($contact_name) || empty($contact_mobile) || empty($contact_email)) {
            $_SESSION['error_message'] = 'Please fill in all required fields';
            return;
        }

        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Please enter a valid email address';
            return;
        }
        
        // Update existing client - service_id set to NULL
        $query = "UPDATE clients SET 
             company_name = '{$company_name}', 
             trade_license_no = '{$trade_license_no}', 
             country = '{$country}', 
             jurisdiction = '{$jurisdiction}', 
             emirate_zone = '{$emirate_zone}', 
             business_activity = '{$business_activity}', 
             industry = '{$industry}', 
             address = '{$address}', 
             contact_title = '{$contact_title}', 
             contact_name = '{$contact_name}', 
             contact_designation = '{$contact_designation}', 
             contact_mobile = '{$contact_mobile}', 
             contact_email = '{$contact_email}', 
             service_description = '{$service_description}', 
             contract_start_date = " . ($contract_start_date ? "'{$contract_start_date}'" : "NULL") . ", 
             contract_end_date = " . ($contract_end_date ? "'{$contract_end_date}'" : "NULL") . ", 
             payment_currency = '{$payment_currency}', 
             payment_term = '{$payment_term}', 
             service_total_fee = {$service_total_fee}, 
             lead_source = '{$lead_source}',
             client_status = '{$client_status}',
             service_id = NULL
             WHERE client_id = {$client_id}";

        if (mysqli_query($connection, $query)) {
            $_SESSION['client_update_success'] = true;
        } else {
            $_SESSION['error_message'] = 'Query Failed: ' . mysqli_error($connection);
        }
    }
}


// Function to send welcome email with credentials
function send_client_welcome_email($email, $company_name, $plain_password) {
    $subject = "Welcome to Our Platform - Your Login Credentials";
    
    $message = "
    <html>
    <head>
        <title>Welcome to Our Platform</title>
    </head>
    <body>
        <h2>Welcome, {$company_name}!</h2>
        <p>Thank you for registering with us. Your account has been created successfully.</p>
        
        <h3>Your Login Credentials:</h3>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Password:</strong> {$plain_password}</p>
        
        <p>You can log in to your account using the link below:</p>
        <p><a href='https://yourdomain.com/client-login.php'>Login to Client Portal</a></p>
        
        <p>For security reasons, we recommend changing your password after first login.</p>
        
        <p>Best regards,<br>OGM Business Consultants</p>
    </body>
    </html>
    ";
    
    // Set content-type headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@ogmbc.ae" . "\r\n";
    
    // Send email
    return mail($email, $subject, $message, $headers);
}

// Function to get all clients with jurisdiction and industry fields
if (!function_exists('findAllClients')) {
function findAllClients() {
    global $connection;

    // Updated query to include jurisdiction and industry
    $query = "SELECT c.*, cat.cat_title as service_name, u.first_name, u.last_name 
              FROM clients c 
              LEFT JOIN categories cat ON c.service_id = cat.cat_id 
              LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
              ORDER BY c.created_at DESC";
    
    $result = mysqli_query($connection, $query);

    if(!$result) {
        die("Query Failed: " . mysqli_error($connection));
    }

    while($row = mysqli_fetch_assoc($result)) {
        $client_id = $row['client_id'];
        $company_name = htmlspecialchars($row['company_name']);
        
        // Combine title and name for contact person
        $contact_title = isset($row['contact_title']) ? trim($row['contact_title']) : '';
        $contact_name = htmlspecialchars($row['contact_name']);
        
        // Format contact name with title
        if (!empty($contact_title)) {
            $formatted_contact = $contact_title . ' ' . $contact_name;
        } else {
            $formatted_contact = $contact_name;
        }
        
        $contact_email = htmlspecialchars($row['contact_email']);
        $contact_mobile = htmlspecialchars($row['contact_mobile']);
        $country = htmlspecialchars($row['country'] ?? 'N/A');
        $jurisdiction = htmlspecialchars($row['jurisdiction'] ?? 'N/A');
        $industry = htmlspecialchars($row['industry'] ?? 'N/A');
        $service_name = $row['service_name'] ? htmlspecialchars($row['service_name']) : 'N/A';
        $client_status = $row['client_status'];
        $created_at = date('M j, Y', strtotime($row['created_at']));
        $sales_person = $row['first_name'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : 'N/A';

        // Status badge color
        $status_class = getStatusBadgeClass($client_status);

        echo "<tr id='client-row-{$client_id}'>";
        echo "<td>{$client_id}</td>";
        echo "<td><strong>{$company_name}</strong></td>";
        echo "<td>{$formatted_contact}</td>"; // Now shows "Mr. John Smith" format
        echo "<td>{$contact_email}</td>";
        echo "<td>{$contact_mobile}</td>";
        echo "<td>{$country}</td>";
        echo "<td>{$jurisdiction}</td>";
        echo "<td>{$industry}</td>";
        echo "<td>{$service_name}</td>";
        echo "<td><span class='badge {$status_class}'>{$client_status}</span></td>";
        echo "<td>{$sales_person}</td>";
        echo "<td>{$created_at}</td>";
        echo "<td class='action-links'>";
        
        // Action buttons container - icon only version
        echo "<div class='d-flex gap-1'>";
        
        // View button
        echo "<button onclick='loadClientDetails({$client_id})' class='btn btn-light btn-sm rounded-circle p-2' title='View Details' data-bs-toggle='tooltip'>
            <i class='bi bi-eye text-primary'></i>
              </button>";
        
        // Edit button
        echo "<a href='clients.php?source=edit_client&id={$client_id}' class='btn btn-light btn-sm rounded-circle p-2' title='Edit' data-bs-toggle='tooltip'>
            <i class='bi bi-pencil text-info'></i>
              </a>";

        // Delete button with data attributes
        echo "<button onclick='confirmDelete({$client_id}, \"" . addslashes($company_name) . "\")' class='btn btn-light btn-sm rounded-circle p-2' title='Delete' data-bs-toggle='tooltip' data-client-id='{$client_id}' data-client-name='" . htmlspecialchars($company_name, ENT_QUOTES) . "'>
            <i class='bi bi-trash text-danger'></i>
              </button>";

        // Review button (for Manager/CEO)
        if (function_exists('shouldShowReviewButton') && shouldShowReviewButton($row)) {
            echo "<button onclick='loadReviewDetails({$client_id})' class='btn btn-light btn-sm rounded-circle p-2' title='Review' data-bs-toggle='tooltip'>
                <i class='bi bi-clipboard-check text-warning'></i>
              </button>";
        }

        echo "</div>";
        
        echo "</td>";
        echo "</tr>";
    }
}
}

// Helper function for status badge colors (if not already defined)
if (!function_exists('getStatusBadgeClass')) {
function getStatusBadgeClass($status) {
    $badge_classes = [
        'New Lead' => 'bg-primary',
        'Contacted' => 'bg-info',
        'Qualified' => 'bg-success',
        'Proposal Drafted' => 'bg-secondary',
        'Under Manager Review' => 'bg-warning text-dark',
        'Rejected by Manager' => 'bg-danger',
        'Approved by Manager' => 'bg-success',
        'Under CEO Review' => 'bg-warning text-dark',
        'Rejected by CEO' => 'bg-danger',
        'Final Proposal Ready' => 'bg-success',
        'Proposal Sent to Client' => 'bg-info',
        'Awaiting Client Action' => 'bg-warning',
        'Signed – Move to Finance' => 'bg-success'
    ];
    
    return $badge_classes[$status] ?? 'bg-secondary';
}
}

// Helper function for status badge colors (if not already defined)
if (!function_exists('getStatusBadgeClass')) {
function getStatusBadgeClass($status) {
    $badge_classes = [
        'New Lead' => 'bg-primary',
        'Contacted' => 'bg-info',
        'Qualified' => 'bg-success',
        'Proposal Drafted' => 'bg-secondary',
        'Under Manager Review' => 'bg-warning text-dark',
        'Rejected by Manager' => 'bg-danger',
        'Approved by Manager' => 'bg-success',
        'Under CEO Review' => 'bg-warning text-dark',
        'Rejected by CEO' => 'bg-danger',
        'Final Proposal Ready' => 'bg-success',
        'Proposal Sent to Client' => 'bg-info',
        'Awaiting Client Action' => 'bg-warning',
        'Signed – Move to Finance' => 'bg-success'
    ];
    
    return $badge_classes[$status] ?? 'bg-secondary';
}
}

// Helper function for status badge classes
if (!function_exists('getStatusBadgeClass')) {
function getStatusBadgeClass($status) {
    $classes = [
        'New Lead' => 'bg-secondary',
        'Contacted' => 'bg-info',
        'Qualified' => 'bg-primary',
        'Proposal Drafted' => 'bg-warning',
        'Under Manager Review' => 'bg-warning text-dark',
        'Rejected by Manager' => 'bg-danger',
        'Approved by Manager' => 'bg-success',
        'Under CEO Review' => 'bg-warning text-dark',
        'Rejected by CEO' => 'bg-danger',
        'Final Proposal Ready' => 'bg-success',
        'Proposal Sent to Client' => 'bg-info',
        'Awaiting Client Action' => 'bg-warning',
        'Signed – Move to Finance' => 'bg-success'
    ];
    
    return $classes[$status] ?? 'bg-secondary';
}
}

// Helper function to check if review button should be shown
function shouldShowReviewButton($client) {
    $user_role = $_SESSION['user_role'] ?? '';
    $status = $client['client_status'];
    
    if ($user_role === 'manager' && $status === 'Under Manager Review') {
        return true;
    }
    
    if ($user_role === 'ceo' && $status === 'Under CEO Review') {
        return true;
    }
    
    return false;
}

// Function to calculate payment breakdown
function calculatePaymentBreakdown($total_amount, $payment_term) {
    $breakdown = [];
    
    switch($payment_term) {
        case 'Monthly':
            $installments = 12;
            break;
        case 'Quarterly':
            $installments = 4;
            break;
        case 'Bi-yearly':
            $installments = 2;
            break;
        case 'One-time':
            $installments = 1;
            break;
        default:
            $installments = 1;
    }
    
    $installment_amount = $total_amount / $installments;
    
    for($i = 1; $i <= $installments; $i++) {
        $breakdown[] = [
            'installment' => $i,
            'amount' => number_format($installment_amount, 2),
            'due_description' => getDueDescription($payment_term, $i)
        ];
    }
    
    return $breakdown;
}

// Helper function for due descriptions
function getDueDescription($payment_term, $installment) {
    $descriptions = [
        'Monthly' => "Month $installment",
        'Quarterly' => "Quarter $installment",
        'Bi-yearly' => "Half $installment",
        'One-time' => "One-time payment"
    ];
    
    return $descriptions[$payment_term] ?? "Payment $installment";
    }
}
?>
