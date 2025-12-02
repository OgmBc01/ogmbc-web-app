<?php
// includes/review_proforma.php
include dirname(__DIR__) . '/includes/database.php';
session_start();

if(!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager', 'ceo', 'admin'])) {
    echo "<div class='alert alert-danger'>Unauthorized access.</div>";
    return;
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
$user_role = $_SESSION['user_role'];

// Get client and latest proforma details
$sql = "SELECT c.*, pf.*, cat.cat_title, u.first_name, u.last_name 
        FROM clients c 
        LEFT JOIN proforma_invoices pf ON c.client_id = pf.client_id 
        LEFT JOIN categories cat ON c.service_id = cat.cat_id 
        LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
        WHERE c.client_id = ? 
        ORDER BY pf.prepared_at DESC 
        LIMIT 1";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$data = $result->fetch_assoc()) {
    echo "<div class='alert alert-danger'>Client or proforma invoice not found.</div>";
    return;
}

// Check if user can review (similar logic as proposal)
$can_review = false;
if ($user_role === 'manager' && $data['client_status'] === 'Proforma Drafted') {
    $can_review = true;
} elseif ($user_role === 'ceo' && $data['client_status'] === 'Manager Approved Proforma') {
    $can_review = true;
} elseif ($user_role === 'admin') {
    $can_review = true;
}

if (!$can_review) {
    echo "<div class='alert alert-warning'>You cannot review this proforma at this stage. Current status: " . htmlspecialchars($data['client_status']) . "</div>";
    return;
}

$stmt->close();
?>

<!-- The structure would be similar to review_proposal.php but with proforma-specific checklist -->
<!-- Create this file similarly, adjusting the checklist items for proforma invoices -->