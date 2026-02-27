<?php
session_start();
include dirname(__DIR__) . '/includes/database.php';

// Start output buffering to prevent any stray output
ob_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check for required POST data
if (!isset($_POST['client_id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Client ID is required']);
    exit();
}

$client_id = intval($_POST['client_id']);

if ($client_id <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    exit();
}

try {
    // Get client details with service information
    $sql = "SELECT c.*, cat.cat_title, cat.cat_price, u.first_name, u.last_name 
            FROM clients c 
            LEFT JOIN categories cat ON c.service_id = cat.cat_id 
            LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
            WHERE c.client_id = ?";
    
    $stmt = $connection->prepare($sql);
    if(!$stmt) {
        throw new Exception('Database prepare error: ' . $connection->error);
    }
    
    $stmt->bind_param("i", $client_id);
    
    if(!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$client = $result->fetch_assoc()) {
        throw new Exception('Client not found');
    }
    
    // Calculate payment breakdown
    $payment_breakdown = calculatePaymentBreakdown($client['service_total_fee'], $client['payment_term']);
    
    // Check for existing proformas to determine version
    $version_sql = "SELECT MAX(version) as max_version FROM proforma_invoices WHERE client_id = ?";
    $version_stmt = $connection->prepare($version_sql);
    if(!$version_stmt) {
        throw new Exception('Version prepare error: ' . $connection->error);
    }
    
    $version_stmt->bind_param("i", $client_id);
    $version_stmt->execute();
    $version_result = $version_stmt->get_result();
    $version_data = $version_result->fetch_assoc();
    $max_version = $version_data['max_version'] ?? 0;
    $version = $max_version + 1;
    
    // Generate unique proforma reference
    $invoice_ref = 'PROF-' . date('Ymd') . '-' . sprintf('%04d', $client_id) . '-V' . $version;
    
    // Get latest proposal for reference (optional)
    $proposal_id = null;
    $proposal_sql = "SELECT proposal_id FROM proposals WHERE client_id = ? ORDER BY prepared_at DESC LIMIT 1";
    if($proposal_stmt = $connection->prepare($proposal_sql)) {
        $proposal_stmt->bind_param("i", $client_id);
        $proposal_stmt->execute();
        $proposal_result = $proposal_stmt->get_result();
        if($proposal_data = $proposal_result->fetch_assoc()) {
            $proposal_id = $proposal_data['proposal_id'];
        }
        $proposal_stmt->close();
    }
    
    // Create proforma directory if it doesn't exist
    $upload_dir = "uploads/proformas/";
    if (!file_exists($upload_dir)) {
        if(!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create directory: ' . $upload_dir);
        }
    }
    
    // Generate HTML content
    $html_content = generateProformaHTMLContent($client, $payment_breakdown, $invoice_ref);
    $filename = "proforma_{$invoice_ref}.html";
    $file_path = $upload_dir . $filename;
    
    // Create the file
    if(!file_put_contents($file_path, $html_content)) {
        throw new Exception('Failed to create proforma file');
    }
    
    // First, verify the table structure
    $check_column = $connection->query("SHOW COLUMNS FROM proforma_invoices LIKE 'validity_period'");
    if ($check_column->num_rows == 0) {
        // Column doesn't exist, add it
        $alter_sql = "ALTER TABLE proforma_invoices ADD COLUMN validity_period DATE NOT NULL AFTER payment_breakdown";
        if (!$connection->query($alter_sql)) {
            throw new Exception('Failed to add validity_period column: ' . $connection->error);
        }
    }
    
    // Prepare the date
    $validity_period = date('Y-m-d', strtotime('+30 days'));
    $payment_breakdown_json = json_encode($payment_breakdown);
    $user_id = (int)$_SESSION['user_id']; // Ensure user_id is integer
    $total_amount = (float)$client['service_total_fee']; // Cast to float

    // Prepare the insert statement
    $insert_sql = "INSERT INTO proforma_invoices (
                client_id, proposal_id, invoice_ref, version, invoice_content, 
                total_amount, payment_breakdown, validity_period, prepared_by, file_path, prepared_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $insert_stmt = $connection->prepare($insert_sql);
    if(!$insert_stmt) {
        throw new Exception('Insert prepare error: ' . $connection->error);
    }

    // CORRECT bind_param with proper type matching
    $insert_stmt->bind_param(
        "iisisdssis", // 10 parameters: i,i,s,i,s,d,s,s,i,s
        $client_id,           // i
        $proposal_id,         // i
        $invoice_ref,         // s
        $version,             // i
        $html_content,        // s
        $total_amount,        // d (double/decimal)
        $payment_breakdown_json, // s
        $validity_period,     // s
        $user_id,             // i
        $file_path            // s
    );
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to save proforma to database: ' . $insert_stmt->error . ' (Date: ' . $validity_period . ')');
    }
    
    // Get the last inserted ID
    $last_id = $connection->insert_id;
    
    // Clean output buffer and return success response
    ob_end_clean();
    echo json_encode([
        'success' => true, 
        'message' => 'Proforma invoice generated successfully',
        'file_path' => $file_path,
        'invoice_ref' => $invoice_ref,
        'version' => $version,
        'valid_until' => date('F j, Y', strtotime($validity_period)),
        'invoice_id' => $last_id
    ]);
    
    // Clean up
    $insert_stmt->close();
    $stmt->close();
    $version_stmt->close();
    
} catch (Exception $e) {
    // Clean buffer and return error response
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    exit();
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

function generateProformaHTMLContent($client, $payment_breakdown, $invoice_ref) {
    $validity_date = date('F j, Y', strtotime('+30 days'));
    $issue_date = date('F j, Y');
    
    $html = "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Proforma Invoice: {$invoice_ref}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .company-info { float: right; text-align: right; margin-bottom: 30px; }
            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }
            .section { margin-bottom: 25px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .total-row { background-color: #f8f9fa; font-weight: bold; }
            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }
            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }
            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }
            .company-logo { text-align: center; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class='company-logo'>
            <h1>OGM BUSINESS CONSULTANCY</h1>
            <h2>PROFORMA INVOICE</h2>
        </div>
        
        <div class='header'>
            <h3>Invoice Reference: {$invoice_ref}</h3>
            <p><strong>Date Issued:</strong> {$issue_date} | <strong>Valid Until:</strong> {$validity_date}</p>
        </div>
        
        <div class='company-info'>
            <h4>From:</h4>
            <p><strong>OGM Business Consultancy</strong></p>
            <p>Business Bay, Dubai</p>
            <p>United Arab Emirates</p>
            <p>Email: info@ogmbusiness.com</p>
            <p>Phone: +971 4 123 4567</p>
            <p>VAT: TRN 123456789012345</p>
        </div>
        
        <div class='client-info'>
            <h4>Bill To:</h4>
            <p><strong>" . htmlspecialchars($client['company_name']) . "</strong></p>
            <p>Attn: " . htmlspecialchars($client['contact_name']) . "</p>
            <p>" . htmlspecialchars($client['contact_designation']) . "</p>
            <p>" . htmlspecialchars($client['address']) . "</p>
            <p>" . htmlspecialchars($client['country']) . "</p>
            <p>Email: " . htmlspecialchars($client['contact_email']) . "</p>
            <p>Phone: " . htmlspecialchars($client['contact_mobile']) . "</p>
        </div>
        
        <div class='section'>
            <h4>Service Description</h4>
            <p><strong>" . htmlspecialchars($client['cat_title']) . "</strong> - " . htmlspecialchars($client['service_description']) . "</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price (" . htmlspecialchars($client['payment_currency']) . ")</th>
                    <th>Amount (" . htmlspecialchars($client['payment_currency']) . ")</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>" . htmlspecialchars($client['cat_title']) . " Service</td>
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
            <h4>Payment Terms: " . htmlspecialchars($client['payment_term']) . "</h4>
            <table>
                <tr>
                    <th>Installment</th>
                    <th>Due</th>
                    <th>Amount (" . htmlspecialchars($client['payment_currency']) . ")</th>
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
        
        <div class='bank-details'>
            <h4>Bank Transfer Details</h4>
            <p><strong>Bank Name:</strong> Emirates NBD</p>
            <p><strong>Account Name:</strong> OGM Business Consultancy</p>
            <p><strong>Account Number:</strong> 1234 5678 9012</p>
            <p><strong>IBAN:</strong> AE123456789012345678901</p>
            <p><strong>Swift Code:</strong> EBILAEAD</p>
            <p><strong>Branch:</strong> Business Bay, Dubai</p>
        </div>
        
        <div class='section'>
            <h4>Notes</h4>
            <ol>
                <li>This is a proforma invoice and should not be considered as a demand for payment</li>
                <li>Prices are valid for 30 days from the date of issue</li>
                <li>Payment should be made in full before service commencement</li>
                <li>All bank charges are to be borne by the client</li>
                <li>Services will commence upon receipt of payment</li>
            </ol>
        </div>
        
        <div class='footer'>
            <div style='float: left; width: 45%;'>
                <p><strong>Prepared by:</strong></p>
                <br><br>
                <p>_________________________</p>
                <p>" . htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) . "</p>
                <p>Sales Consultant</p>
                <p>OGM Business Consultancy</p>
            </div>
            
            <div style='float: right; width: 45%; text-align: center;'>
                <p><strong>For OGM Business Consultancy:</strong></p>
                <br><br>
                <p>_________________________</p>
                <p>Authorized Signature</p>
                <p>Date: {$issue_date}</p>
            </div>
            <div style='clear: both;'></div>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>