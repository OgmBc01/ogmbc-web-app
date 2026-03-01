<?php
ob_start();

// if (!isset($_SESSION['client_id'])) {
//     ob_end_clean();
//     echo "<script>window.location.href = '../login.php';</script>";
//     exit();
// }

$client_id = $_SESSION['client_id'];

// Fetch current client data
$query = "SELECT * FROM clients WHERE client_id = " . intval($client_id);
$result = mysqli_query($connection, $query);
$client = mysqli_fetch_assoc($result);

if (!$client) {
    echo '<div class="alert alert-danger">Client not found</div>';
    return;
}

// Initialize variables
$company_name = $client['company_name'];
$trade_license_no = $client['trade_license_no'];
$country = $client['country'];
$emirate_zone = $client['emirate_zone'];
$business_activity = $client['business_activity'];
$address = $client['address'];
$contact_name = $client['contact_name'];
$contact_designation = $client['contact_designation'];
$contact_mobile = $client['contact_mobile'];
$contact_email = $client['contact_email'];

$message = '';
$message_type = '';
$showSuccessModal = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    $company_name = mysqli_real_escape_string($connection, trim($_POST['company_name']));
    $trade_license_no = mysqli_real_escape_string($connection, trim($_POST['trade_license_no']));
    $country = mysqli_real_escape_string($connection, trim($_POST['country']));
    $emirate_zone = mysqli_real_escape_string($connection, trim($_POST['emirate_zone']));
    $business_activity = mysqli_real_escape_string($connection, trim($_POST['business_activity']));
    $address = mysqli_real_escape_string($connection, trim($_POST['address']));
    $contact_name = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
    $contact_designation = mysqli_real_escape_string($connection, trim($_POST['contact_designation']));
    $contact_mobile = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
    $contact_email = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
    
    // Validate required fields
    if (empty($company_name) || empty($country) || empty($contact_name) || empty($contact_mobile) || empty($contact_email)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } else {
        // Update client
        $update_query = "UPDATE clients SET 
                        company_name = '$company_name',
                        trade_license_no = '$trade_license_no',
                        country = '$country',
                        emirate_zone = '$emirate_zone',
                        business_activity = '$business_activity',
                        address = '$address',
                        contact_name = '$contact_name',
                        contact_designation = '$contact_designation',
                        contact_mobile = '$contact_mobile',
                        contact_email = '$contact_email'
                        WHERE client_id = " . intval($client_id);
        
        if (mysqli_query($connection, $update_query)) {
            // Log activity
            $log_check = mysqli_query($connection, "SHOW TABLES LIKE 'client_activity_log'");
            if ($log_check && mysqli_num_rows($log_check) > 0) {
                $log_query = "INSERT INTO client_activity_log 
                             (client_id, activity_type, description, ip_address)
                             VALUES 
                             (" . intval($client_id) . ", 'profile_update', 'Updated company profile information', '{$_SERVER['REMOTE_ADDR']}')";
                mysqli_query($connection, $log_query);
            }
            
            // Update session name
            $_SESSION['client_name'] = $company_name;
            
            $showSuccessModal = true;
        } else {
            $message = "Error updating profile: " . mysqli_error($connection);
            $message_type = "danger";
        }
    }
}

// Country list for dropdown
$countries = [
    'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Oman', 'Kuwait', 'Bahrain',
    'United Kingdom', 'United States', 'Canada', 'Australia', 'Germany', 'France',
    'India', 'Pakistan', 'Bangladesh', 'Sri Lanka', 'Philippines', 'Egypt',
    'Jordan', 'Lebanon', 'Morocco', 'South Africa', 'Nigeria', 'Kenya'
];

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Profile</h5>
                    <a href="profile.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="profileForm">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 text-primary mb-3">
                                    <i class="bi bi-building me-2"></i>Company Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name *</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?php echo htmlspecialchars($company_name); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="trade_license_no" class="form-label">Trade License No</label>
                                <input type="text" class="form-control" id="trade_license_no" name="trade_license_no" 
                                       value="<?php echo htmlspecialchars($trade_license_no); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <select class="form-control" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <?php foreach($countries as $c): ?>
                                        <option value="<?php echo $c; ?>" <?php echo ($country == $c) ? 'selected' : ''; ?>>
                                            <?php echo $c; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="emirate_zone" class="form-label">Emirate/Zone/State</label>
                                <input type="text" class="form-control" id="emirate_zone" name="emirate_zone" 
                                       value="<?php echo htmlspecialchars($emirate_zone); ?>">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="business_activity" class="form-label">Business Activity</label>
                                <textarea class="form-control" id="business_activity" name="business_activity" rows="2"><?php echo htmlspecialchars($business_activity); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <h6 class="border-bottom pb-2 text-primary mb-3">
                                    <i class="bi bi-person me-2"></i>Contact Person Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name" 
                                       value="<?php echo htmlspecialchars($contact_name); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_designation" class="form-label">Designation</label>
                                <input type="text" class="form-control" id="contact_designation" name="contact_designation" 
                                       value="<?php echo htmlspecialchars($contact_designation); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_mobile" class="form-label">Mobile Number *</label>
                                <input type="text" class="form-control" id="contact_mobile" name="contact_mobile" 
                                       value="<?php echo htmlspecialchars($contact_mobile); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                       value="<?php echo htmlspecialchars($contact_email); ?>" required>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Profile Updated!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Your profile has been updated successfully!</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="profile.php" class="btn btn-success px-4">View Profile</a>
                <button type="button" class="btn btn-outline-success px-4" data-bs-dismiss="modal">Continue Editing</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
</script>
<?php endif; ?>