<?php
include '../includes/database.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'sales') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    
    // Get client details with service information
    $sql = "SELECT c.*, cat.cat_title, cat.cat_price, u.first_name, u.last_name 
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
        
        // Generate unique proforma reference
        $invoice_ref = 'PROF-' . date('Ymd') . '-' . sprintf('%04d', $client_id) . '-V1';
        
        // Check for existing proformas to determine version
        $version_sql = "SELECT MAX(version) as max_version FROM proforma_invoices WHERE client_id = ?";
        $version_stmt = $connection->prepare($version_sql);
        $version_stmt->bind_param("i", $client_id);
        $version_stmt->execute();
        $version_result = $version_stmt->get_result();
        $max_version = $version_result->fetch_assoc()['max_version'] ?? 0;
        $version = $max_version + 1;
        
        $invoice_ref = 'PROF-' . date('Ymd') . '-' . sprintf('%04d', $client_id) . '-V' . $version;
        
        // Get latest proposal for reference
        $proposal_sql = "SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY created_at DESC LIMIT 1";
        $proposal_stmt = $connection->prepare($proposal_sql);
        $proposal_stmt->bind_param("i", $client_id);
        $proposal_stmt->execute();
        $proposal_result = $proposal_stmt->get_result();
        $proposal = $proposal_result->fetch_assoc();
        $proposal_id = $proposal['proposal_id'] ?? null;
        
        // Create proforma directory if it doesn't exist
        $upload_dir = "../uploads/proformas/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate PDF content
        $pdf_content = generateProformaPDFContent($client, $payment_breakdown, $invoice_ref);
        $filename = "proforma_{$invoice_ref}.pdf";
        $file_path = $upload_dir . $filename;
        
        // For this example, we'll create a simple HTML file
        // In production, replace this with actual PDF generation
        file_put_contents($file_path, $pdf_content);
        
        // Save proforma to database
        $insert_sql = "INSERT INTO proforma_invoices (
                      client_id, proposal_id, invoice_ref, version, invoice_content, 
                      total_amount, payment_breakdown, validity_period, prepared_by, file_path
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?)";
        $insert_stmt = $connection->prepare($insert_sql);
        $payment_breakdown_json = json_encode($payment_breakdown);
        $insert_stmt->bind_param("iisisdsis", 
            $client_id, $proposal_id, $invoice_ref, $version, $pdf_content,
            $client['service_total_fee'], $payment_breakdown_json, 
            $_SESSION['user_id'], $file_path
        );
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Proforma invoice generated successfully',
                'file_path' => $file_path,
                'invoice_ref' => $invoice_ref
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save proforma to database']);
        }
        
        $insert_stmt->close();
        $stmt->close();
        $version_stmt->close();
        $proposal_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

function generateProformaPDFContent($client, $payment_breakdown, $invoice_ref) {
    $validity_date = date('F j, Y', strtotime('+30 days'));
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .company-info { float: right; text-align: right; }
            .client-info { margin-bottom: 30px; }
            .section { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .total-row { background-color: #f8f9fa; font-weight: bold; }
            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }
            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>PROFORMA INVOICE</h1>
            <p><strong>Invoice Reference:</strong> {$invoice_ref}</p>
            <p><strong>Date Issued:</strong> " . date('F j, Y') . "</p>
            <p><strong>Valid Until:</strong> {$validity_date}</p>
        </div>
        
        <div class='company-info'>
            <h3>OGM BUSINESS CONSULTANCY</h3>
            <p>Business Bay, Dubai</p>
            <p>United Arab Emirates</p>
            <p>Email: info@ogmauditing.com</p>
            <p>Phone: +971 4 123 4567</p>
        </div>
        
        <div class='client-info'>
            <h3>Bill To:</h3>
            <p><strong>{$client['company_name']}</strong></p>
            <p>Attn: {$client['contact_name']}</p>
            <p>{$client['contact_designation']}</p>
            <p>{$client['address']}</p>
            <p>{$client['country']}</p>
            <p>Email: {$client['contact_email']}</p>
            <p>Phone: {$client['contact_mobile']}</p>
        </div>
        
        <div class='section'>
            <h3>Service Description</h3>
            <p>{$client['cat_title']} - {$client['service_description']}</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price ({$client['payment_currency']})</th>
                    <th>Amount ({$client['payment_currency']})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{$client['cat_title']} Service</td>
                    <td>1</td>
                    <td>" . number_format($client['service_total_fee'], 2) . "</td>
                    <td>" . number_format($client['service_total_fee'], 2) . "</td>
                </tr>
                <tr class='total-row'>
                    <td colspan='3' style='text-align: right;'><strong>Total Amount:</strong></td>
                    <td><strong>" . number_format($client['service_total_fee'], 2) . "</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class='payment-terms'>
            <h4>Payment Terms: {$client['payment_term']}</h4>
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
        </div>
        
        <div class='section'>
            <h4>Bank Transfer Details</h4>
            <p><strong>Bank Name:</strong> Emirates NBD</p>
            <p><strong>Account Name:</strong> OGM Business Consultancy</p>
            <p><strong>Account Number:</strong> 1234 5678 9012</p>
            <p><strong>IBAN:</strong> AE123456789012345678901</p>
            <p><strong>Swift Code:</strong> EBILAEAD</p>
        </div>
        
        <div class='footer'>
            <p><strong>Prepared by:</strong> {$client['first_name']} {$client['last_name']}</p>
            <p><strong>Sales Consultant</strong></p>
            <p>OGM Business Consultancy</p>
            <br>
            <p><strong>Terms & Conditions:</strong></p>
            <ul>
                <li>This is a proforma invoice and should not be considered as a demand for payment</li>
                <li>Prices are valid for 30 days from the date of issue</li>
                <li>Part payment should be made in full before service commencement</li>
                <li>All bank charges are to be borne by the client</li>
            </ul>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>