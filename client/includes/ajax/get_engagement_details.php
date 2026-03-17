<?php
// client/includes/ajax/get_engagement_details.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../includes/database.php'; // Use correct path to DB connection

if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit();
}

$client_id = (int)$_SESSION['client_id'];
$engagement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$engagement_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid engagement ID.']);
    exit();
}

// Fetch engagement details for this client
$query = "SELECT e.*, s.service_name, CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name, u.user_email as assigned_email, DATEDIFF(COALESCE(e.approved_deadline, e.original_deadline), CURDATE()) as days_remaining FROM engagements e JOIN service_types s ON e.service_id = s.service_id LEFT JOIN users u ON e.assigned_to = u.user_id WHERE e.engagement_id = $engagement_id AND e.client_id = $client_id LIMIT 1";
$result = mysqli_query($connection, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Engagement not found.']);
    exit();
}
$eng = mysqli_fetch_assoc($result);

// Fetch evidence files (client uploads only)
$evidence = [];
$ev_query = "SELECT file_name, file_path, uploaded_at FROM client_files WHERE engagement_id = $engagement_id AND uploaded_by = 'client' ORDER BY uploaded_at DESC";
$ev_result = mysqli_query($connection, $ev_query);
if ($ev_result) {
    while ($row = mysqli_fetch_assoc($ev_result)) {
        $evidence[] = $row;
    }
}


// Render HTML for modal
$html = '<div class="mb-3">'
    . '<h5>' . htmlspecialchars($eng['title']) . '</h5>'
    . '<p class="mb-1"><strong>Service:</strong> ' . htmlspecialchars($eng['service_name']) . '</p>'
    . '<p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary">' . htmlspecialchars($eng['status']) . '</span></p>'
    . '<p class="mb-1"><strong>Assigned to:</strong> ' . htmlspecialchars($eng['assigned_to_name']) . ' <span class="text-muted">(' . htmlspecialchars($eng['assigned_email']) . ')</span></p>'
    . '<p class="mb-1"><strong>Deadline:</strong> ' . date('M d, Y', strtotime($eng['approved_deadline'] ?: $eng['original_deadline'])) . '</p>'
    . '<p class="mb-1"><strong>Days Remaining:</strong> ' . $eng['days_remaining'] . '</p>'
    . (!empty($eng['description']) ? '<div class="mt-3"><strong>Description:</strong><br>' . nl2br(htmlspecialchars($eng['description'])) . '</div>' : '')
    . '</div>';

// Evidence files
$html .= '<div class="mt-4"><strong>Your Uploaded Files:</strong>';
if (count($evidence) > 0) {
    $html .= '<ul class="list-group">';
    foreach ($evidence as $ev) {
        $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">'
            . htmlspecialchars($ev['file_name'])
            . '<a href="../../uploads/client_files/' . rawurlencode($ev['file_path']) . '" class="btn btn-sm btn-outline-primary ms-2" target="_blank">View</a>'
            . '</li>';
    }
    $html .= '</ul>';
} else {
    $html .= '<p class="text-muted mt-2 mb-0">No files uploaded yet.</p>';
}

$html .= '</div>';

// Add Full Details button (route through switch case)
$html .= '<div class="mt-4 text-end">'
    . '<a href="../../client/engagements.php?source=view_details&id=' . (int)$eng['engagement_id'] . '" class="btn btn-primary" target="_blank">'
    . '<i class="bi bi-list-ul me-1"></i> Full Details</a>'
    . '</div>';

echo json_encode([
    'success' => true,
    'html' => $html
]);