<?php
include '../includes/database.php';

// Check if user is logged in and has permission (Manager or CEO)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'ceo', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    $user_id = $_SESSION['user_id'];
    
    // Get client details and latest documents
    $sql = "SELECT c.*, p.file_path as proposal_path, pi.file_path as proforma_path, 
                   p.proposal_ref, pi.invoice_ref
            FROM clients c 
            LEFT JOIN proposals p ON c.client_id = p.client_id 
            LEFT JOIN proforma_invoices pi ON c.client_id = pi.client_id 
            WHERE c.client_id = ? 
            ORDER BY p.created_at DESC, pi.created_at DESC 
            LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($data = $result->fetch_assoc()) {
        $client_email = $data['contact_email'];
        $company_name = $data['company_name'];
        $contact_name = $data['contact_name'];
        $proposal_path = $data['proposal_path'];
        $proforma_path = $data['proforma_path'];
        $proposal_ref = $data['proposal_ref'];
        $invoice_ref = $data['invoice_ref'];
        
        if (empty($proposal_path) || empty($proforma_path)) {
            echo json_encode(['success' => false, 'message' => 'Proposal or proforma invoice not found']);
            exit();
        }
        
        // Email configuration for Hostinger
        $to = $client_email;
        $subject = "Proposal and Proforma Invoice - OGM Business Consultancy";
        
        // Email body
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #0f172a; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 0.9em; color: #666; }
                .button { background: #f1bf70; color: #0f172a; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>OGM Business Consultancy</h1>
                <p>Your Trusted Business Partner</p>
            </div>
            
            <div class='content'>
                <h2>Dear {$contact_name},</h2>
                
                <p>Thank you for your interest in OGM Business Consultancy. We are pleased to present our proposal and proforma invoice for your review.</p>
                
                <p><strong>Company:</strong> {$company_name}</p>
                <p><strong>Proposal Reference:</strong> {$proposal_ref}</p>
                <p><strong>Proforma Invoice Reference:</strong> {$invoice_ref}</p>
                
                <p>Please find the attached documents:</p>
                <ul>
                    <li>Service Proposal - Detailed overview of our services and terms</li>
                    <li>Proforma Invoice - Payment details and schedule</li>
                </ul>
                
                <p>We would appreciate it if you could review the documents and let us know if you have any questions or require any clarifications.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <p>To proceed, please review the documents and sign both the proposal and proforma invoice.</p>
                </div>
                
                <p>If you have any questions or need further information, please don't hesitate to contact us.</p>
                
                <p>Best regards,<br>
                <strong>OGM Business Consultancy Team</strong><br>
                Email: info@ogmauditing.com<br>
                Phone: +971 4 123 4567</p>
            </div>
            
            <div class='footer'>
                <p>This email was sent from OGM Business Consultancy. Please do not reply to this email.</p>
                <p>Business Bay, Dubai, United Arab Emirates</p>
            </div>
        </body>
        </html>
        ";
        
        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: OGM Business Consultancy <info@ogmauditing.com>" . "\r\n";
        $headers .= "Reply-To: info@ogmauditing.com" . "\r\n";
        
        // Boundary for attachments
        $boundary = md5(time());
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        
        // Message body with attachments
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $message . "\r\n\r\n";
        
        // Attach proposal
        if (file_exists($proposal_path)) {
            $proposal_content = file_get_contents($proposal_path);
            $proposal_base64 = base64_encode($proposal_content);
            $proposal_filename = basename($proposal_path);
            
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/pdf; name=\"$proposal_filename\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$proposal_filename\"\r\n\r\n";
            $body .= chunk_split($proposal_base64) . "\r\n";
        }
        
        // Attach proforma invoice
        if (file_exists($proforma_path)) {
            $proforma_content = file_get_contents($proforma_path);
            $proforma_base64 = base64_encode($proforma_content);
            $proforma_filename = basename($proforma_path);
            
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/pdf; name=\"$proforma_filename\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$proforma_filename\"\r\n\r\n";
            $body .= chunk_split($proforma_base64) . "\r\n";
        }
        
        $body .= "--$boundary--";
        
        // Send email
        if (mail($to, $subject, $body, $headers)) {
            // Log email in database
            $log_sql = "INSERT INTO email_logs (client_id, proposal_id, email_type, recipient_email, subject, sent_by) 
                       VALUES (?, (SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 1), 'proposal_sent', ?, ?, ?)";
            $log_stmt = $connection->prepare($log_sql);
            $log_stmt->bind_param("iissi", $client_id, $client_id, $client_email, $subject, $user_id);
            $log_stmt->execute();
            $log_stmt->close();
            
            // Update client status
            $update_sql = "UPDATE clients SET client_status = 'Proposal Sent to Client' WHERE client_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("i", $client_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Proposal sent successfully to ' . $client_email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>