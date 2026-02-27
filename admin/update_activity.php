<?php
// update_activity.php
header('Content-Type: application/json');

// Verify access for edit page
verifyBankAccess();


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity'])) {
    if(isset($_SESSION['bank_accounts_access']) && $_SESSION['bank_accounts_access']) {
        $_SESSION['bank_accounts_last_activity'] = time();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No active session']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>