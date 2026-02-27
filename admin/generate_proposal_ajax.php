<?php
include dirname(__DIR__) . '/includes/database.php';
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (isset($_POST['client_id'])) {
    $client_id = intval($_POST['client_id']);
    
    // Debug: Log the request
    error_log("Proposal generation request for client ID: $client_id");
    
    // Get client details with service information
    $sql = "SELECT c.*, cat.cat_title, cat.cat_price, u.first_name, u.last_name, u.user_email 
            FROM clients c 
            LEFT JOIN categories cat ON c.service_id = cat.cat_id 
            LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
            WHERE c.client_id = ?";
    $stmt = $connection->prepare($sql);
    
    if(!$stmt) {
        error_log("Prepare failed: " . $connection->error);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
        exit();
    }
    
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
        
        if(!$version_stmt) {
            error_log("Version prepare failed: " . $connection->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
            $stmt->close();
            exit();
        }
        
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
        $filename = "proposal_{$proposal_ref}.html"; // Changed to .html for now
        $file_path = $upload_dir . $filename;
        
        // For this example, we'll create an HTML file
        // In production, replace this with actual PDF generation
        if(file_put_contents($file_path, $pdf_content)) {
            // Save proposal to database
            $insert_sql = "INSERT INTO proposals (
                          client_id, proposal_ref, version, proposal_content, 
                          total_amount, payment_breakdown, prepared_by, file_path, prepared_at
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $connection->prepare($insert_sql);
            
            if(!$insert_stmt) {
                error_log("Insert prepare failed: " . $connection->error);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
                $stmt->close();
                $version_stmt->close();
                exit();
            }
            
            $payment_breakdown_json = json_encode($payment_breakdown);
            $user_id = $_SESSION['user_id'];
            $insert_stmt->bind_param("isisdsis", 
                $client_id, $proposal_ref, $version, $pdf_content,
                $client['service_total_fee'], $payment_breakdown_json, 
                $user_id, $file_path
            );
            
            if ($insert_stmt->execute()) {
                // Update client status
                $update_sql = "UPDATE clients SET client_status = 'Proposal Drafted' WHERE client_id = ?";
                $update_stmt = $connection->prepare($update_sql);
                
                if($update_stmt) {
                    $update_stmt->bind_param("i", $client_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Proposal generated successfully',
                    'file_path' => $file_path,
                    'proposal_ref' => $proposal_ref,
                    'version' => $version
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save proposal to database: ' . $insert_stmt->error]);
            }
            
            $insert_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create proposal file']);
        }
        
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
        <meta charset='UTF-8'>
        <title>Proposal: {$proposal_ref}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .section { margin-bottom: 30px; }
            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }
            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }
            .signature-box { margin-top: 50px; }
            .company-logo { text-align: center; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class='company-logo'>
            <h1>OGM BUSINESS CONSULTANCY</h1>
            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>
        </div>
        
        <div class='header'>
            <h3>Proposal Reference: {$proposal_ref}</h3>
            <p><strong>Date:</strong> " . date('F j, Y') . " | <strong>Valid Until:</strong> " . date('F j, Y', strtotime('+30 days')) . "</p>
        </div>
        
        <div class='section'>
            <div class='section-title'>Client Information</div>
            <table>
                <tr>
                    <td width='30%'><strong>Company Name:</strong></td>
                    <td>" . htmlspecialchars($client['company_name']) . "</td>
                </tr>
                <tr>
                    <td><strong>Contact Person:</strong></td>
                    <td>" . htmlspecialchars($client['contact_name']) . "</td>
                </tr>
                <tr>
                    <td><strong>Email Address:</strong></td>
                    <td>" . htmlspecialchars($client['contact_email']) . "</td>
                </tr>
                <tr>
                    <td><strong>Phone Number:</strong></td>
                    <td>" . htmlspecialchars($client['contact_mobile']) . "</td>
                </tr>
                <tr>
                    <td><strong>Address:</strong></td>
                    <td>" . htmlspecialchars($client['address']) . ", " . htmlspecialchars($client['country']) . "</td>
                </tr>
            </table>
        </div>
        
        <div class='section'>
            <div class='section-title'>Service Details</div>
            <table>
                <tr>
                    <td width='30%'><strong>Service Type:</strong></td>
                    <td>" . htmlspecialchars($client['cat_title']) . "</td>
                </tr>
                <tr>
                    <td><strong>Service Description:</strong></td>
                    <td>" . htmlspecialchars($client['service_description']) . "</td>
                </tr>
                <tr>
                    <td><strong>Expected Start Date:</strong></td>
                    <td>" . ($client['expected_start_date'] ? date('F j, Y', strtotime($client['expected_start_date'])) : 'To be determined') . "</td>
                </tr>
            </table>
        </div>
        
        <div class='section'>
            <div class='section-title'>Financial Proposal</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount ({$client['payment_currency']})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>" . htmlspecialchars($client['cat_title']) . " Service</td>
                        <td>" . number_format($client['service_total_fee'], 2) . "</td>
                    </tr>
                    <tr class='total'>
                        <td><strong>Total Amount</strong></td>
                        <td><strong>" . number_format($client['service_total_fee'], 2) . "</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class='section'>
            <div class='section-title'>Payment Schedule</div>
            <p><strong>Payment Term:</strong> {$client['payment_term']}</p>
            <table>
                <thead>
                    <tr>
                        <th>Installment</th>
                        <th>Due</th>
                        <th>Amount ({$client['payment_currency']})</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($payment_breakdown as $payment) {
        $html .= "<tr>
                    <td>{$payment['installment']}</td>
                    <td>{$payment['due_description']}</td>
                    <td>{$payment['amount']}</td>
                  </tr>";
    }
    
    $html .= "</tbody>
            </table>
        </div>
        
        <div class='section'>
            <div class='section-title'>Terms & Conditions</div>
            <ol>
                <li>This proposal is valid for 30 days from the date of issue.</li>
                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>
                <li>All payments are to be made in {$client['payment_currency']}.</li>
                <li>Any additional services requested will be billed separately.</li>
                <li>Either party may terminate this agreement with 30 days written notice.</li>
            </ol>
        </div>
        
        <div class='footer'>
            <div class='signature-box'>
                <div style='float: left; width: 45%;'>
                    <p><strong>For OGM Business Consultancy:</strong></p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Authorized Signature</p>
                    <p>Name: " . htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) . "</p>
                    <p>Position: Sales Consultant</p>
                    <p>Date: " . date('F j, Y') . "</p>
                </div>
                
                <div style='float: right; width: 45%;'>
                    <p><strong>Accepted by Client:</strong></p>
                    <br><br><br>
                    <p>_________________________</p>
                    <p>Authorized Signature</p>
                    <p>Name: ___________________</p>
                    <p>Position: ________________</p>
                    <p>Date: ___________________</p>
                </div>
                <div style='clear: both;'></div>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

?>