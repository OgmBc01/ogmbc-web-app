<?php
include '../includes/database.php';
session_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    
    // Get client details with service information
    $sql = "SELECT c.*, cat.cat_title, cat.cat_price, u.first_name, u.last_name, u.user_email 
            FROM clients c 
            LEFT JOIN categories cat ON c.service_id = cat.cat_id 
            LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
            WHERE c.client_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($client = $result->fetch_assoc()) {
        // Calculate payment breakdown
        $payment_breakdown = calculatePaymentBreakdown($client['service_total_fee'], $client['payment_term']);
        
        // Generate unique proposal reference
        $proposal_ref = 'PROP-' . date('Ymd') . '-' . sprintf('%04d', $client_id) . '-V1';
        
        // Check for existing proposals to determine version
        $version_sql = "SELECT MAX(version) as max_version FROM proposals WHERE client_id = ?";
        $version_stmt = $connection->prepare($version_sql);
        $version_stmt->bind_param("i", $client_id);
        $version_stmt->execute();
        $version_result = $version_stmt->get_result();
        $max_version = $version_result->fetch_assoc()['max_version'] ?? 0;
        $version = $max_version + 1;
        
        $proposal_ref = 'PROP-' . date('Ymd') . '-' . sprintf('%04d', $client_id) . '-V' . $version;
        
        // Create proposals directory if it doesn't exist
        $upload_dir = "../uploads/proposals/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate PDF content
        $pdf_content = generateProposalPDFContent($client, $payment_breakdown, $proposal_ref);
        $filename = "proposal_{$proposal_ref}.pdf";
        $file_path = $upload_dir . $filename;
        
        // Use DomPDF or similar PDF library
        require_once '../vendor/autoload.php'; // If using Composer
        
        // For this example, we'll create a simple HTML file
        // In production, replace this with actual PDF generation
        file_put_contents($file_path, $pdf_content);
        
        // Save proposal to database
        $insert_sql = "INSERT INTO proposals (
                      client_id, proposal_ref, version, proposal_content, 
                      total_amount, payment_breakdown, prepared_by, file_path
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $connection->prepare($insert_sql);
        $payment_breakdown_json = json_encode($payment_breakdown);
        $insert_stmt->bind_param("isisdsis", 
            $client_id, $proposal_ref, $version, $pdf_content,
            $client['service_total_fee'], $payment_breakdown_json, 
            $_SESSION['user_id'], $file_path
        );
        
        if ($insert_stmt->execute()) {
            // Update client status
            $update_sql = "UPDATE clients SET client_status = 'Proposal Drafted' WHERE client_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("i", $client_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Proposal generated successfully',
                'file_path' => $file_path,
                'proposal_ref' => $proposal_ref
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save proposal to database']);
        }
        
        $insert_stmt->close();
        $stmt->close();
        $version_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

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

function getDueDescription($payment_term, $installment) {
    $descriptions = [
        'Monthly' => "Month $installment",
        'Quarterly' => "Quarter $installment",
        'Bi-yearly' => "Half $installment",
        'One-time' => "One-time payment"
    ];
    
    return $descriptions[$payment_term] ?? "Payment $installment";
}

function generateProposalPDFContent($client, $payment_breakdown, $proposal_ref) {
    // This is a simplified version - in production, use a proper PDF library
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .section { margin-bottom: 20px; }
            .section-title { background: #f8f9fa; padding: 10px; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; font-size: 1.2em; }
            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>OGM BUSINESS CONSULTANCY</h1>
            <h2>PROPOSAL</h2>
            <p>Reference: {$proposal_ref}</p>
            <p>Date: " . date('F j, Y') . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>Client Information</div>
            <p><strong>Company:</strong> {$client['company_name']}</p>
            <p><strong>Contact Person:</strong> {$client['contact_name']}</p>
            <p><strong>Email:</strong> {$client['contact_email']}</p>
            <p><strong>Phone:</strong> {$client['contact_mobile']}</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>Service Details</div>
            <p><strong>Service Type:</strong> {$client['cat_title']}</p>
            <p><strong>Service Description:</strong> {$client['service_description']}</p>
            <p><strong>Expected Start Date:</strong> " . ($client['expected_start_date'] ? date('F j, Y', strtotime($client['expected_start_date'])) : 'To be determined') . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>Financial Proposal</div>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount ({$client['payment_currency']})</th>
                </tr>
                <tr>
                    <td>{$client['cat_title']} Service</td>
                    <td>" . number_format($client['service_total_fee'], 2) . "</td>
                </tr>
                <tr class='total'>
                    <td>Total Amount</td>
                    <td>" . number_format($client['service_total_fee'], 2) . "</td>
                </tr>
            </table>
        </div>
        
        <div class='section'>
            <div class='section-title'>Payment Schedule</div>
            <table>
                <tr>
                    <th>Installment</th>
                    <th>Due</th>
                    <th>Amount ({$client['payment_currency']})</th>
                </tr>";
    
    foreach ($payment_breakdown as $payment) {
        $html .= "<tr>
                    <td>{$payment['installment']}</td>
                    <td>{$payment['due_description']}</td>
                    <td>{$payment['amount']}</td>
                  </tr>";
    }
    
    $html .= "</table>
            <p><strong>Payment Term:</strong> {$client['payment_term']}</p>
        </div>
        
        <div class='footer'>
            <p><strong>Prepared by:</strong> {$client['first_name']} {$client['last_name']}</p>
            <p><strong>Position:</strong> Sales Consultant</p>
            <p><strong>Date:</strong> " . date('F j, Y') . "</p>
            <br><br>
            <p>_________________________</p>
            <p>Signature</p>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>