<?php
require_once 'database.php';

/**
 * Centralized Session & Authentication Functions
 * Include this file at the top of all pages that need session/authentication
 */

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Initialize session with security settings
 * Call this at the beginning of all pages that need session
 */
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
    
    // Set secure session cookie parameters
    if (PHP_SESSION_ACTIVE) {
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
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 7);
}

/**
 * Check if current user is operations employee (role_id 4, type_id 1)
 * @return bool
 */
function isOperations() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 1);
}

/**
 * Check if current user is client (role_id 4, type_id 2)
 * @return bool
 */
function isClient() {
    return (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4 && 
            isset($_SESSION['type_id']) && $_SESSION['type_id'] == 2);
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

// Try to load Composer's autoloader so PHPMailer (and other libs) are available.
// Use a safe path relative to this file and fail gracefully with a log message.
$composerAutoload = __DIR__ . '/./vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    error_log("Composer autoload not found at: $composerAutoload. PHPMailer classes may be unavailable.");
}

// PHPMailer Imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email Settings - Updated for Hostinger
// Hostinger SMTP Settings:
// SMTP Host: smtp.hostinger.com
// SMTP Port: 465 (SSL) or 587 (TLS)
// SMTP Authentication: Required
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // Use 587 for TLS or 465 for SSL
define('SMTP_USERNAME', 'info@ogmbc.ae');
define('SMTP_PASSWORD', 'Ogmbc@2212'); // <<< ENTER YOUR PASSWORD HERE

define('SMTP_FROM_EMAIL', 'info@ogmbc.ae');
define('SMTP_FROM_NAME', 'OGM Business Consultants');

define('ADMIN_EMAIL', 'info@ogmbc.ae'); // Admin email for receiving enquiries

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Sanitize user input
 * @param string $input User input
 * @return string Sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Show modal with message
 * @param string $modalId Modal ID
 * @param string $message Message to display (optional)
 */

// Alternative show_modal function (simpler)
function show_modal($modal_id, $message = '') {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalElement = document.getElementById("' . $modal_id . '");
        if (modalElement) {
            var modal = new bootstrap.Modal(modalElement);
            ';
            
    if ($modal_id === 'enquiryErrorModal' && !empty($message)) {
        echo 'var errorMessageElement = modalElement.querySelector(".error-message");
              if (errorMessageElement) {
                  errorMessageElement.textContent = "' . addslashes($message) . '";
              }';
    }
    
    echo 'modal.show();
        }
    });
    </script>';
}

// Handle enquiry form
function handle_enquiry_form() {
    global $connection;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // Sanitize form data
    $name        = sanitize_input(trim($_POST['name'] ?? ''));
    $email       = sanitize_input(trim($_POST['email'] ?? ''));
    $contact     = sanitize_input(trim($_POST['contact'] ?? ''));
    $service     = sanitize_input(trim($_POST['service'] ?? ''));
    $sub_service = sanitize_input(trim($_POST['sub_service'] ?? ''));
    $message     = nl2br(htmlspecialchars(sanitize_input(trim($_POST['message'] ?? ''))));

    // Required fields
    if (empty($name) || empty($email) || empty($service)) {
        show_modal("enquiryErrorModal", "Please fill in all required fields.");
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        show_modal("enquiryErrorModal", "Please enter a valid email address.");
        return;
    }

    try {
        // Insert DB record using MySQLi (prepare & bind)
        $query = "INSERT INTO enquiries 
                  (name, email, contact, service, sub_service, message)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $connection->error);
            show_modal("enquiryErrorModal", "A database error occurred. Please try again.");
            return;
        }

        // Bind parameters: types are: s (string), i (integer), d (double), b (blob)
        $stmt->bind_param("ssssss", $name, $email, $contact, $service, $sub_service, $message);

        if ($stmt->execute()) {

            // ==========================
            // ADMIN EMAIL BODY TEMPLATE
            // ==========================
            $adminEmailBody = <<<HTML
            <table width="100%" style="font-family:Arial;background:#f5f7fb;padding:20px;">
            <tr><td align="center">
                <table width="600" style="background:#fff;padding:30px;border-radius:10px;">
                    <tr><td style="text-align:center;">
                        <h2 style="color:#222;">New Enquiry Received</h2>
                        <p style="color:#555;">A new customer has submitted an enquiry.</p>
                    </td></tr>

                    <tr><td>
                        <h3 style="color:#222;">Client Information</h3>
                        <p><strong>Name:</strong> {$name}</p>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Contact:</strong> {$contact}</p>

                        <h3 style="color:#222;margin-top:20px;">Service Details</h3>
                        <p><strong>Service:</strong> {$service}</p>
                        <p><strong>Sub Service:</strong> {$sub_service}</p>

                        <h3 style="color:#222;margin-top:20px;">Message</h3>
                        <p style="color:#444;">{$message}</p>
                    </td></tr>

                    <tr><td style="text-align:center;padding-top:20px;">
                        <p style="color:#888;font-size:12px;">This message was generated from your website enquiry form.</p>
                    </td></tr>
                </table>
            </td></tr>
            </table>
            HTML;

            // ==========================
            // USER EMAIL BODY TEMPLATE
            // ==========================
            $userEmailBody = <<<HTML
            <table width="100%" style="font-family:Arial;background:#f4f6fa;padding:20px;">
            <tr><td align="center">
                <table width="600" style="background:#fff;padding:30px;border-radius:10px;">

                    <tr><td style="text-align:center;">
                        <h2 style="color:#222;">Thank You, {$name}</h2>
                        <p style="color:#555;">Your enquiry has been received.</p>
                    </td></tr>

                    <tr><td>
                        <p style="color:#444;">Thank you for contacting us. Our team will review your details and contact you soon.</p>

                        <h3 style="color:#222;margin-top:20px;">Your Submission</h3>
                        <p><strong>Service:</strong> {$service}</p>
                        <p><strong>Sub Service:</strong> {$sub_service}</p>

                        <h3 style="color:#222;margin-top:20px;">Your Message</h3>
                        <p style="color:#444;">{$message}</p>

                        <p style="color:#444;margin-top:20px;">If you need to update anything, simply reply to this email.</p>

                        <p style="margin-top:30px;color:#222;font-weight:bold;">— OGMBC Team</p>
                    </td></tr>

                    <tr><td style="text-align:center;padding-top:20px;">
                        <p style="color:#888;font-size:12px;">You received this email because you submitted an enquiry at our website.</p>
                    </td></tr>

                </table>
            </td></tr>
            </table>
            HTML;

            // ======================
            // SEND EMAIL TO ADMIN
            // ======================
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = 'info@ogmbc.ae';
                $mail->Password = 'Ogmbc@2212'; // <<< ENTER YOUR PASSWORD HERE
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS for port 465
                $mail->Port = SMTP_PORT;
                
                // Optional: Debug mode for troubleshooting
                // $mail->SMTPDebug = 2; // Uncomment for debugging
                // $mail->Debugoutput = 'error_log'; // Log debug output

                $mail->setFrom(SMTP_FROM_EMAIL, 'Website – Enquiry Alert');
                $mail->addAddress(ADMIN_EMAIL); // Send to admin email

                $mail->isHTML(true);
                $mail->Subject = "New Enquiry – {$name}";
                $mail->Body = $adminEmailBody;
                $mail->AltBody = strip_tags($adminEmailBody);

                $mail->send();
                error_log("Admin email sent successfully to: " . ADMIN_EMAIL);
            } catch (Exception $e) {
                error_log("Admin Email Error: " . $e->getMessage());
                // Don't show error to user - just log it
            }

            // ======================
            // SEND USER CONFIRMATION
            // ======================
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = 'info@ogmbc.ae';
                $mail->Password = 'Ogmbc@2212'; // <<< ENTER YOUR PASSWORD HERE
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS for port 465
                $mail->Port = SMTP_PORT;

                // IMPORTANT: Use info@ogmbc.ae as sender, but reply-to set to admin email
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($email, $name); // Send to user's email
                $mail->addReplyTo(ADMIN_EMAIL, SMTP_FROM_NAME); // Replies go to admin

                $mail->isHTML(true);
                $mail->Subject = "We Have Received Your Enquiry";
                $mail->Body = $userEmailBody;
                $mail->AltBody = strip_tags($userEmailBody);

                $mail->send();
                error_log("User confirmation email sent successfully to: " . $email);
            } catch (Exception $e) {
                error_log("User Email Error: " . $e->getMessage());
                // Don't show error to user - just log it
            }

            show_modal("enquirySuccessModal");

        } else {
            error_log("Database Error: " . $stmt->error);
            show_modal("enquiryErrorModal", "Unable to save your enquiry. Please try again.");
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Enquiry Error: " . $e->getMessage());
        show_modal("enquiryErrorModal", "A server error occurred.");
    }
}

/////////////////////////////////////////////////// FOR LOCAL HOST USAGE//////////////////////////////////////////////////////////////////////////////

// // Try to load Composer's autoloader so PHPMailer (and other libs) are available.
// // Use a safe path relative to this file and fail gracefully with a log message.
// $composerAutoload = __DIR__ . '/./vendor/autoload.php';
// if (file_exists($composerAutoload)) {
//     require_once $composerAutoload;
// } else {
//     error_log("Composer autoload not found at: $composerAutoload. PHPMailer classes may be unavailable.");
// }

// // PHPMailer Imports
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// // Email Settings
// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'otaksiconnect@gmail.com');
// define('SMTP_PASSWORD', 'vplf hstc rwda qbbh');

// define('SMTP_FROM_EMAIL', 'otaksiconnect@gmail.com');
// define('SMTP_FROM_NAME', 'OGM Business Consultants');

// define('ADMIN_EMAIL', 'otaksiconnect@gmail.com');

// // ========================================
// // UTILITY FUNCTIONS
// // ========================================

// /**
//  * Sanitize user input
//  * @param string $input User input
//  * @return string Sanitized input
//  */
// function sanitize_input($input) {
//     return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
// }

// /**
//  * Show modal with message
//  * @param string $modalId Modal ID
//  * @param string $message Message to display (optional)
//  */

// // Alternative show_modal function (simpler)
// function show_modal($modal_id, $message = '') {
//     echo '<script>
//     document.addEventListener("DOMContentLoaded", function() {
//         var modalElement = document.getElementById("' . $modal_id . '");
//         if (modalElement) {
//             var modal = new bootstrap.Modal(modalElement);
//             ';
            
//     if ($modal_id === 'enquiryErrorModal' && !empty($message)) {
//         echo 'var errorMessageElement = modalElement.querySelector(".error-message");
//               if (errorMessageElement) {
//                   errorMessageElement.textContent = "' . addslashes($message) . '";
//               }';
//     }
    
//     echo 'modal.show();
//         }
//     });
//     </script>';
// }

// // Handle enquiry form
// function handle_enquiry_form() {
//     global $connection;

//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         return;
//     }

//     // Sanitize form data
//     $name        = sanitize_input(trim($_POST['name'] ?? ''));
//     $email       = sanitize_input(trim($_POST['email'] ?? ''));
//     $contact     = sanitize_input(trim($_POST['contact'] ?? ''));
//     $service     = sanitize_input(trim($_POST['service'] ?? ''));
//     $sub_service = sanitize_input(trim($_POST['sub_service'] ?? ''));
//     $message     = nl2br(htmlspecialchars(sanitize_input(trim($_POST['message'] ?? ''))));

//     // Required fields
//     if (empty($name) || empty($email) || empty($service)) {
//         show_modal("enquiryErrorModal", "Please fill in all required fields.");
//         return;
//     }

//     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//         show_modal("enquiryErrorModal", "Please enter a valid email address.");
//         return;
//     }

//     try {
//         // Insert DB record using MySQLi (prepare & bind)
//         $query = "INSERT INTO enquiries 
//                   (name, email, contact, service, sub_service, message)
//                   VALUES (?, ?, ?, ?, ?, ?)";

//         $stmt = $connection->prepare($query);

//         if (!$stmt) {
//             error_log("Prepare failed: " . $connection->error);
//             show_modal("enquiryErrorModal", "A database error occurred. Please try again.");
//             return;
//         }

//         // Bind parameters: types are: s (string), i (integer), d (double), b (blob)
//         $stmt->bind_param("ssssss", $name, $email, $contact, $service, $sub_service, $message);

//         if ($stmt->execute()) {

//             // ==========================
//             // ADMIN EMAIL BODY TEMPLATE
//             // ==========================
//             $adminEmailBody = <<<HTML
//             <table width="100%" style="font-family:Arial;background:#f5f7fb;padding:20px;">
//             <tr><td align="center">
//                 <table width="600" style="background:#fff;padding:30px;border-radius:10px;">
//                     <tr><td style="text-align:center;">
//                         <h2 style="color:#222;">New Enquiry Received</h2>
//                         <p style="color:#555;">A new customer has submitted an enquiry.</p>
//                     </td></tr>

//                     <tr><td>
//                         <h3 style="color:#222;">Client Information</h3>
//                         <p><strong>Name:</strong> {$name}</p>
//                         <p><strong>Email:</strong> {$email}</p>
//                         <p><strong>Contact:</strong> {$contact}</p>

//                         <h3 style="color:#222;margin-top:20px;">Service Details</h3>
//                         <p><strong>Service:</strong> {$service}</p>
//                         <p><strong>Sub Service:</strong> {$sub_service}</p>

//                         <h3 style="color:#222;margin-top:20px;">Message</h3>
//                         <p style="color:#444;">{$message}</p>
//                     </td></tr>

//                     <tr><td style="text-align:center;padding-top:20px;">
//                         <p style="color:#888;font-size:12px;">This message was generated from your website enquiry form.</p>
//                     </td></tr>
//                 </table>
//             </td></tr>
//             </table>
//             HTML;

//             // ==========================
//             // USER EMAIL BODY TEMPLATE
//             // ==========================
//             $userEmailBody = <<<HTML
//             <table width="100%" style="font-family:Arial;background:#f4f6fa;padding:20px;">
//             <tr><td align="center">
//                 <table width="600" style="background:#fff;padding:30px;border-radius:10px;">

//                     <tr><td style="text-align:center;">
//                         <h2 style="color:#222;">Thank You, {$name}</h2>
//                         <p style="color:#555;">Your enquiry has been received.</p>
//                     </td></tr>

//                     <tr><td>
//                         <p style="color:#444;">Thank you for contacting us. Our team will review your details and contact you soon.</p>

//                         <h3 style="color:#222;margin-top:20px;">Your Submission</h3>
//                         <p><strong>Service:</strong> {$service}</p>
//                         <p><strong>Sub Service:</strong> {$sub_service}</p>

//                         <h3 style="color:#222;margin-top:20px;">Your Message</h3>
//                         <p style="color:#444;">{$message}</p>

//                         <p style="color:#444;margin-top:20px;">If you need to update anything, simply reply to this email.</p>

//                         <p style="margin-top:30px;color:#222;font-weight:bold;">— OGMBC Team</p>
//                     </td></tr>

//                     <tr><td style="text-align:center;padding-top:20px;">
//                         <p style="color:#888;font-size:12px;">You received this email because you submitted an enquiry at our website.</p>
//                     </td></tr>

//                 </table>
//             </td></tr>
//             </table>
//             HTML;

//             // ======================
//             // SEND EMAIL TO ADMIN
//             // ======================
//             try {
//                 $mail = new PHPMailer(true);
//                 $mail->isSMTP();
//                 $mail->Host = 'smtp.gmail.com';
//                 $mail->SMTPAuth = true;
//                 $mail->Username = 'otaksiconnect@gmail.com'; // Change this
//                 $mail->Password = 'vplf hstc rwda qbbh'; // Change this
//                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//                 $mail->Port = 587;

//                 $mail->setFrom('otaksiconnect@gmail.com', 'Website – Enquiry Alert'); // Change this
//                 $mail->addAddress('otaksiconnect@gmail.com'); // Change to your admin email

//                 $mail->isHTML(true);
//                 $mail->Subject = "New Enquiry – {$name}";
//                 $mail->Body = $adminEmailBody;
//                 $mail->AltBody = strip_tags($adminEmailBody);

//                 $mail->send();
//             } catch (Exception $e) {
//                 error_log("Admin Email Error: " . $e->getMessage());
//             }

//             // ======================
//             // SEND USER CONFIRMATION
//             // ======================
//             try {
//                 $mail = new PHPMailer(true);
//                 $mail->isSMTP();
//                 $mail->Host = 'smtp.gmail.com';
//                 $mail->SMTPAuth = true;
//                 $mail->Username = 'otaksiconnect@gmail.com'; // Change this
//                 $mail->Password = 'vplf hstc rwda qbbh'; // Change this
//                 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//                 $mail->Port = 587;

//                 $mail->setFrom('otaksiconnect@gmail.com', 'OGM Business Consultants'); // Change this
//                 $mail->addAddress($email, $name);

//                 $mail->isHTML(true);
//                 $mail->Subject = "We Have Received Your Enquiry";
//                 $mail->Body = $userEmailBody;
//                 $mail->AltBody = strip_tags($userEmailBody);

//                 $mail->send();
//             } catch (Exception $e) {
//                 error_log("User Email Error: " . $e->getMessage());
//             }

//             show_modal("enquirySuccessModal");

//         } else {
//             error_log("Database Error: " . $stmt->error);
//             show_modal("enquiryErrorModal", "Unable to save your enquiry. Please try again.");
//         }

//         $stmt->close();

//     } catch (Exception $e) {
//         error_log("Enquiry Error: " . $e->getMessage());
//         show_modal("enquiryErrorModal", "A server error occurred.");
//     }
// }


// Add to your existing functions.php file
    function save_lead_to_crm(): int|false {
    
    global $connection;
    
    // Get the JSON data from the request body
    $json_data = file_get_contents('php://input');
    $lead_data = json_decode($json_data, true);
    
    // If no JSON body, try form data
    if (!$lead_data) {
        $lead_data = $_POST;
    }
    
    if (!$lead_data) {
        return false;
    }
    
    // Check if action is 'save_lead'
    if (!isset($lead_data['action']) || $lead_data['action'] !== 'save_lead') {
        return false;
    }
    
    // Extract data (use null coalescing for safety)
    $full_name = mysqli_real_escape_string($connection, $lead_data['full_name'] ?? '');
    $email = mysqli_real_escape_string($connection, $lead_data['email'] ?? '');
    $phone = mysqli_real_escape_string($connection, $lead_data['phone'] ?? '');
    $company_name = mysqli_real_escape_string($connection, $lead_data['company_name'] ?? '');
    $industry = mysqli_real_escape_string($connection, $lead_data['industry'] ?? '');
    $consent_given = isset($lead_data['consent_given']) ? 1 : 0;
    $ratios_calculated = isset($lead_data['ratios_calculated']) ? 
        mysqli_real_escape_string($connection, json_encode($lead_data['ratios_calculated'])) : '[]';
    $timestamp = date('Y-m-d H:i:s');
    
    // Debug log - remove after testing if not needed
    error_log('Lead data received: full_name=' . $full_name . ', email=' . $email . ', phone=' . $phone);
    
    // Validate required fields
    if (empty($email) || empty($company_name) || empty($industry)) {
        return false;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if lead exists
    $check_query = "SELECT id FROM leads WHERE email = '{$email}'";
    $check_result = mysqli_query($connection, $check_query);
    
    if (!$check_result) {
        return false;
    }
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing lead
        $query = "UPDATE leads SET 
            full_name = '{$full_name}',
            phone = '{$phone}',
            company_name = '{$company_name}',
            industry = '{$industry}',
            consent_given = {$consent_given},
            ratios_calculated = '{$ratios_calculated}',
            last_interaction = '{$timestamp}',
            status = 'active'
            WHERE email = '{$email}'";
    } else {
        // Insert new lead
        $query = "INSERT INTO leads (
            full_name,
            email, 
            phone,
            company_name, 
            industry, 
            consent_given, 
            ratios_calculated, 
            first_interaction, 
            last_interaction, 
            status,
            source
        ) VALUES (
            '{$full_name}',
            '{$email}',
            '{$phone}',
            '{$company_name}',
            '{$industry}',
            {$consent_given},
            '{$ratios_calculated}',
            '{$timestamp}',
            '{$timestamp}',
            'new',
            'financial_ratio_calculator'
        )";
    }
    
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        return false;
    }
    
    // Return the lead_id
    $lead_id = mysqli_insert_id($connection);
    if ($lead_id == 0 && mysqli_num_rows($check_result) > 0) {
        // For updates, get the existing ID
        $id_result = mysqli_query($connection, "SELECT id FROM leads WHERE email = '{$email}'");
        if ($id_result && mysqli_num_rows($id_result) > 0) {
            $row = mysqli_fetch_assoc($id_result);
            return $row['id'];
        }
    }
    
    return $lead_id;
}


?>