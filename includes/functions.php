<?php
require_once 'database.php';

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

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'otaksiconnect@gmail.com');
define('SMTP_PASSWORD', 'vplf hstc rwda qbbh');

define('SMTP_FROM_EMAIL', 'otaksiconnect@gmail.com');
define('SMTP_FROM_NAME', 'OGM Business Consultants');

define('ADMIN_EMAIL', 'otaksiconnect@gmail.com');

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
function show_modal($modalId, $message = '') {
    $_SESSION['modal_id'] = $modalId;
    $_SESSION['modal_message'] = $message;
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

                        <p style="margin-top:30px;color:#222;font-weight:bold;">— Your Company Name Team</p>
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
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'otaksiconnect@gmail.com'; // Change this
                $mail->Password = 'vplf hstc rwda qbbh'; // Change this
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('otaksiconnect@gmail.com', 'Website – Enquiry Alert'); // Change this
                $mail->addAddress('otaksiconnect@gmail.com'); // Change to your admin email

                $mail->isHTML(true);
                $mail->Subject = "New Enquiry – {$name}";
                $mail->Body = $adminEmailBody;
                $mail->AltBody = strip_tags($adminEmailBody);

                $mail->send();
            } catch (Exception $e) {
                error_log("Admin Email Error: " . $e->getMessage());
            }

            // ======================
            // SEND USER CONFIRMATION
            // ======================
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'otaksiconnect@gmail.com'; // Change this
                $mail->Password = 'vplf hstc rwda qbbh'; // Change this
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('otaksiconnect@gmail.com', 'Your Company Name'); // Change this
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = "We Have Received Your Enquiry";
                $mail->Body = $userEmailBody;
                $mail->AltBody = strip_tags($userEmailBody);

                $mail->send();
            } catch (Exception $e) {
                error_log("User Email Error: " . $e->getMessage());
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

?>