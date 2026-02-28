<?php
// Start output buffering at the VERY beginning
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get target ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid target ID.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

$target_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch target details and verify ownership
$query = "SELECT st.*, CONCAT(u.first_name, ' ', u.last_name) as employee_name
          FROM sales_targets st
          JOIN users u ON st.employee_id = u.user_id
          WHERE st.target_id = $target_id AND st.employee_id = $user_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Target not found or you don't have permission.";
    ob_end_clean();
    header("Location: sales_targets.php");
    exit();
}

$target = mysqli_fetch_assoc($result);

// Initialize variables
$actual_value = '';
$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_achievement'])) {
    
    $actual_value = floatval($_POST['actual_value']);
    
    // Handle file upload
    $evidence_file = '';
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['evidence_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        
        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/sales_evidence/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = "sales_" . $target_id . "_" . time() . "." . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $evidence_file = $new_filename;
            }
        }
    }
    
    if ($actual_value <= 0) {
        $message = "Please enter a valid achievement amount.";
        $message_type = "danger";
    } else {
        // Update target
        $update_query = "UPDATE sales_targets SET 
                        actual_value = $actual_value,
                        evidence_file = '" . mysqli_real_escape_string($connection, $evidence_file) . "',
                        status = 'SUBMITTED'
                        WHERE target_id = $target_id";
        
        if (mysqli_query($connection, $update_query)) {
            $showSuccessModal = true;
        } else {
            $message = "Error submitting achievement: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

ob_end_flush();
?>

<!-- Rest of your HTML form remains the same -->