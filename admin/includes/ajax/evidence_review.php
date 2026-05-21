<?php
// admin/includes/ajax/evidence_review.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$evidence_id = isset($_POST['evidence_id']) ? (int)$_POST['evidence_id'] : 0;
$action = $_POST['action'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if (!$evidence_id || !in_array($action, ['APPROVED','REJECTED'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Get evidence and engagement info
$evq = "SELECT ev.*, e.reviewer_id FROM evidence ev JOIN engagements e ON ev.engagement_id = e.engagement_id WHERE ev.evidence_id = $evidence_id";
$evr = mysqli_query($connection, $evq);
$evidence = mysqli_fetch_assoc($evr);
if (!$evidence) {
    echo json_encode(['success' => false, 'message' => 'Evidence not found']);
    exit;
}

// Only reviewer or admin/ceo_gm can approve/reject
// if (!($evidence['reviewer_id'] == $user_id || in_array($role, ['CEO_GM','ADMIN_STAFF']))) {
//     echo json_encode(['success' => false, 'message' => 'Permission denied']);
//     exit;
// }

// Update evidence status
$status = $action;
$now = date('Y-m-d H:i:s');
$update = "UPDATE evidence SET status='$status', last_reviewed_by=$user_id, last_reviewed_at='$now' WHERE evidence_id=$evidence_id";
$ok = mysqli_query($connection, $update);
if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Failed to update evidence']);
    exit;
}

// Insert into history
$reason_sql = $reason ? ("'" . mysqli_real_escape_string($connection, $reason) . "'") : 'NULL';
$hist = "INSERT INTO evidence_approval_history (evidence_id, action, reviewed_by, reviewed_at, reason) VALUES ($evidence_id, '$action', $user_id, '$now', $reason_sql)";
mysqli_query($connection, $hist);


$msg = $action === 'APPROVED' ? 'Evidence approved successfully!' : 'Evidence sent back.';
$engagement_id = (int)$evidence['engagement_id'];

// Update engagement status to EVIDENCE_APPROVED or EVIDENCE_REJECTED if not already CLOSED or SUBMITTED
$get_status_q = "SELECT status FROM engagements WHERE engagement_id = $engagement_id";
$get_status_r = mysqli_query($connection, $get_status_q);
$eng = mysqli_fetch_assoc($get_status_r);
if ($eng && !in_array($eng['status'], ['CLOSED','SUBMITTED'])) {
    $old_status = $eng['status'];
    $new_status = ($action === 'APPROVED') ? 'EVIDENCE_APPROVED' : 'EVIDENCE_REJECTED';
    $update_eng = "UPDATE engagements SET status = '$new_status' WHERE engagement_id = $engagement_id";
    mysqli_query($connection, $update_eng);
    $history_query = "INSERT INTO engagement_status_history (engagement_id, old_status, new_status, changed_by, notes) VALUES ($engagement_id, '" . mysqli_real_escape_string($connection, $old_status) . "', '$new_status', $user_id, 'Evidence $action by reviewer.')";
    mysqli_query($connection, $history_query);
}

// If evidence was approved, check if all required evidence for this engagement is now approved
if ($action === 'APPROVED') {
    // Check if all evidence for this engagement is approved
    $all_approved_q = "SELECT COUNT(*) as total, SUM(status='APPROVED') as approved FROM evidence WHERE engagement_id = $engagement_id";
    $all_approved_r = mysqli_query($connection, $all_approved_q);
    $row = mysqli_fetch_assoc($all_approved_r);
    if ($row && $row['total'] > 0 && $row['total'] == $row['approved']) {
        // All evidence approved, update engagement status if not already SUBMITTED
        $get_status_q2 = "SELECT status FROM engagements WHERE engagement_id = $engagement_id";
        $get_status_r2 = mysqli_query($connection, $get_status_q2);
        $eng2 = mysqli_fetch_assoc($get_status_r2);
        if ($eng2 && $eng2['status'] != 'SUBMITTED') {
            $old_status2 = $eng2['status'];
            $update_eng2 = "UPDATE engagements SET status = 'SUBMITTED' WHERE engagement_id = $engagement_id";
            mysqli_query($connection, $update_eng2);
            $history_query2 = "INSERT INTO engagement_status_history (engagement_id, old_status, new_status, changed_by, notes) VALUES ($engagement_id, '" . mysqli_real_escape_string($connection, $old_status2) . "', 'SUBMITTED', $user_id, 'All evidence approved, engagement auto-submitted.')";
            mysqli_query($connection, $history_query2);
        }
    }
}

echo json_encode(['success' => true, 'message' => $msg]);
