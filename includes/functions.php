<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

// === CHANGE THIS BASED ON ENVIRONMENT ===
// Options: "local" or "production"
$environment = "local"; 

// Form Data
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$contact  = $_POST['contact'] ?? '';
$subject  = $_POST['subject'] ?? 'Website Quote Request';
$message  = $_POST['message'] ?? '';

// Setup PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    if ($environment === "local") {
        // === Local Testing with Gmail ===
        $mail->Host       = 'smtp.gmail.com';
        $mail->Username   = 'yourgmail@gmail.com'; // your Gmail
        $mail->Password   = 'your_app_password';   // Gmail App Password
        $mail->setFrom('yourgmail@gmail.com', 'Website Form');
        $mail->addAddress('yourgmail@gmail.com'); // Receive form data in Gmail
    } else {
        // === Production (Hostinger SMTP) ===
        $mail->Host       = 'smtp.hostinger.com';
        $mail->Username   = 'yourname@yourdomain.com'; // your Hostinger email
        $mail->Password   = 'your_hostinger_password'; // your Hostinger email password
        $mail->setFrom('yourname@yourdomain.com', 'Website Form');
        $mail->addAddress('yourname@yourdomain.com'); // receive emails
    }

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = "New Quote Request: " . htmlspecialchars($subject);
    $mail->Body    = "
        <h3>New Quote Request</h3>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Contact:</strong> {$contact}</p>
        <p><strong>Subject:</strong> {$subject}</p>
        <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
    ";

    $mail->send();
    echo "success"; // You can handle this in JS to show a success message/modal
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
