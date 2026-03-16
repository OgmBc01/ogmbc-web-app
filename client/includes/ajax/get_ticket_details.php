<?php
// Endpoint: get_ticket_details.php
// Returns JSON details for a specific support ticket (and its replies) for the logged-in client

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/database.php';

$user_id = $_SESSION['user_id'] ?? 0;
$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if ($user_id <= 0 || $ticket_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request.']);
    exit;
}

// Fetch the correct client_id for this user
$client_id = 0;
$result_client = mysqli_query($connection, "SELECT client_id FROM clients WHERE user_id = $user_id");
if ($result_client && mysqli_num_rows($result_client) > 0) {
    $row = mysqli_fetch_assoc($result_client);
    $client_id = $row['client_id'];
}
if ($client_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Client not found.']);
    exit;
}

// Fetch the ticket details (ensure it belongs to this client)
$ticket_query = "SELECT * FROM support_tickets WHERE ticket_id = $ticket_id AND client_id = $client_id LIMIT 1";
$ticket_result = mysqli_query($connection, $ticket_query);
if (!$ticket_result || mysqli_num_rows($ticket_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket not found or not owned by you.']);
    exit;
}
$ticket = mysqli_fetch_assoc($ticket_result);

// Fetch all replies for this ticket
$replies = [];
$replies_query = "SELECT r.*, c.user_id as client_user_id FROM ticket_replies r LEFT JOIN clients c ON r.user_id = c.client_id WHERE r.ticket_id = $ticket_id ORDER BY r.created_at ASC";
$replies_result = mysqli_query($connection, $replies_query);
if ($replies_result) {
    while ($reply = mysqli_fetch_assoc($replies_result)) {
        $replies[] = $reply;
    }
}

// Return ticket and replies
$response = [
    'success' => true,
    'ticket' => $ticket,
    'replies' => $replies
];
echo json_encode($response);
