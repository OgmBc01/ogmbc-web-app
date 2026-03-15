<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON header
header('Content-Type: application/json');

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Include database connection
    require_once __DIR__ . '/../../../includes/database.php';

    if (!$connection) {
        throw new Exception('Database connection failed');
    }

    $user_id = (int)$_SESSION['user_id'];

    // Get POST data
    $action = $_POST['action'] ?? '';
    $feedback_id = isset($_POST['feedback_id']) ? (int)$_POST['feedback_id'] : 0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if (!$feedback_id) {
        throw new Exception('Invalid feedback ID');
    }

    if ($action === 'approve') {
        // Check if feedback exists and is pending
        $check_query = "SELECT feedback_id, is_validated, is_rejected, employee_id FROM client_feedback WHERE feedback_id = ?";
        $check_stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $feedback_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $feedback = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);

        if (!$feedback) {
            throw new Exception('Feedback not found');
        }

        if ($feedback['is_validated'] == 1) {
            throw new Exception('Feedback is already validated');
        }

        if ($feedback['is_rejected'] == 1) {
            throw new Exception('Feedback is already rejected');
        }

        // Start transaction
        mysqli_begin_transaction($connection);

        try {
            // Update feedback
            $update_query = "UPDATE client_feedback SET 
                            is_validated = 1,
                            validated_by = ?,
                            validated_at = NOW(),
                            reviewed_at = NOW(),
                            review_notes = ?,
                            points_awarded = 50
                            WHERE feedback_id = ?";
            
            $update_stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($update_stmt, "isi", $user_id, $notes, $feedback_id);
            mysqli_stmt_execute($update_stmt);
            
            if (mysqli_stmt_affected_rows($update_stmt) > 0) {
                // Add points to ledger - ALIGNED WITH YOUR TABLE STRUCTURE
                $ledger_query = "INSERT INTO points_ledger 
                                (employee_id, source_type, source_id, points, points_type, description, notes, awarded_by, created_by, created_at) 
                                VALUES (?, 'FEEDBACK', ?, 50, 'EARNED', 'Positive client feedback approved', ?, ?, ?, NOW())";
                
                $ledger_stmt = mysqli_prepare($connection, $ledger_query);
                
                if (!$ledger_stmt) {
                    throw new Exception('Failed to prepare ledger insert: ' . mysqli_error($connection));
                }
                
                $description = "Feedback approved for client";
                mysqli_stmt_bind_param($ledger_stmt, "iissi", 
                    $feedback['employee_id'], 
                    $feedback_id, 
                    $notes, 
                    $user_id, 
                    $user_id
                );
                
                if (!mysqli_stmt_execute($ledger_stmt)) {
                    throw new Exception('Failed to execute ledger insert: ' . mysqli_stmt_error($ledger_stmt));
                }
                mysqli_stmt_close($ledger_stmt);

                mysqli_commit($connection);

                // Clear output buffer
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Feedback approved successfully! 50 points awarded.']);
                exit;
            } else {
                throw new Exception('Failed to update feedback');
            }
            
            mysqli_stmt_close($update_stmt);
        } catch (Exception $e) {
            mysqli_rollback($connection);
            throw $e;
        }

    } elseif ($action === 'reject') {
        if (empty($reason)) {
            throw new Exception('Rejection reason is required');
        }

        // Check if feedback exists and is pending
        $check_query = "SELECT feedback_id, is_validated, is_rejected FROM client_feedback WHERE feedback_id = ?";
        $check_stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $feedback_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $feedback = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);

        if (!$feedback) {
            throw new Exception('Feedback not found');
        }

        if ($feedback['is_validated'] == 1) {
            throw new Exception('Cannot reject validated feedback');
        }

        if ($feedback['is_rejected'] == 1) {
            throw new Exception('Feedback is already rejected');
        }

        // Update feedback
        $update_query = "UPDATE client_feedback SET 
                        is_rejected = 1,
                        validated_by = ?,
                        reviewed_at = NOW(),
                        rejection_reason = ?,
                        review_notes = ?,
                        points_awarded = 0
                        WHERE feedback_id = ?";
        
        $update_stmt = mysqli_prepare($connection, $update_query);
        mysqli_stmt_bind_param($update_stmt, "issi", $user_id, $reason, $notes, $feedback_id);
        mysqli_stmt_execute($update_stmt);
        
        if (mysqli_stmt_affected_rows($update_stmt) > 0) {
            mysqli_stmt_close($update_stmt);
            
            // Clear output buffer
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Feedback rejected successfully.']);
            exit;
        } else {
            throw new Exception('Failed to reject feedback');
        }

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Clear output buffer
    ob_clean();
    // Log error to file for debugging
    file_put_contents(__DIR__ . '/process_feedback_review_error.log', date('c') . "\n" . $e->getMessage() . "\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);
    // Return error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
ob_end_flush();
?>