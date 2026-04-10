<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$company_name = $trade_license_no = $country = $jurisdiction = $emirate_zone = $business_activity = $industry = $address = '';
$contact_title = $contact_name = $contact_designation = $contact_mobile = $contact_email = '';
$service_description = $contract_start_date = $contract_end_date = '';
$payment_currency = 'AED';
$payment_term = 'Monthly';
$service_total_fee = '0.00';
$lead_source = 'website';
$client_status = 'New Lead';
$selected_user_id = '';
$message = '';
$message_type = '';
$show_success_modal = false;

// Fetch users with type_id = 2 (client type) for dropdown
$users_query = "SELECT u.user_id, u.first_name, u.last_name, u.user_email, u.username 
                FROM users u
                LEFT JOIN user_types ut ON u.type_id = ut.type_id
                WHERE u.type_id = 2 OR ut.type_name = 'client'
                ORDER BY u.first_name, u.last_name";
$users_result = mysqli_query($connection, $users_query);

// ============================================
// HANDLE FORM SUBMISSION WITH VALIDATION FIRST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    
    // Sanitize inputs first
    $company_name_check = mysqli_real_escape_string($connection, trim($_POST['company_name']));
    $contact_name_check = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
    $contact_mobile_check = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
    $contact_email_check = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
    $selected_user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
    
    $validation_passed = true;
    
    // ============================================
    // FIRST: Check for empty required fields
    // ============================================
    if (empty($company_name_check) || empty($contact_name_check) || empty($contact_mobile_check) || empty($contact_email_check)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
        $validation_passed = false;
    }
    
    // ============================================
    // SECOND: Check for duplicate company name (excluding current client)
    // ============================================
    if ($validation_passed) {
        $dup_query = "SELECT client_id FROM clients WHERE company_name = '$company_name_check' AND client_id != $client_id";
        $dup_result = mysqli_query($connection, $dup_query);
        if (mysqli_num_rows($dup_result) > 0) {
            $message = "A client with the company name '$company_name_check' already exists. Please use a different name.";
            $message_type = "danger";
            $validation_passed = false;
        }
    }
    
    // ============================================
    // THIRD: Validate email format
    // ============================================
    if ($validation_passed && !filter_var($contact_email_check, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
        $validation_passed = false;
    }
    
    // ============================================
    // FOURTH: Check if selected user exists and is a client
    // ============================================
    if ($validation_passed && !empty($selected_user_id)) {
        $user_check_query = "SELECT user_id FROM users WHERE user_id = $selected_user_id AND (type_id = 2 OR type_id IN (SELECT type_id FROM user_types WHERE type_name = 'client'))";
        $user_check_result = mysqli_query($connection, $user_check_query);
        if (mysqli_num_rows($user_check_result) == 0) {
            $message = "Selected user does not exist or is not a client.";
            $message_type = "danger";
            $validation_passed = false;
        }
    }
    
    // ============================================
    // FIFTH: All validations passed - call update function
    // ============================================
    if ($validation_passed) {
        update_client();
    }
}

// Fetch client data if editing existing client
if ($client_id > 0) {
    $sql = "SELECT * FROM clients WHERE client_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $client = $result->fetch_assoc();
        $company_name = $client['company_name'] ?? '';
        $trade_license_no = $client['trade_license_no'] ?? '';
        $country = $client['country'] ?? '';
        $jurisdiction = $client['jurisdiction'] ?? '';
        $emirate_zone = $client['emirate_zone'] ?? '';
        $business_activity = $client['business_activity'] ?? '';
        $industry = $client['industry'] ?? '';
        $address = $client['address'] ?? '';
        $contact_title = $client['contact_title'] ?? '';
        $contact_name = $client['contact_name'] ?? '';
        $contact_designation = $client['contact_designation'] ?? '';
        $contact_mobile = $client['contact_mobile'] ?? '';
        $contact_email = $client['contact_email'] ?? '';
        $service_description = $client['service_description'] ?? '';
        $contract_start_date = $client['contract_start_date'] ?? '';
        $contract_end_date = $client['contract_end_date'] ?? '';
        $payment_currency = $client['payment_currency'] ?? 'AED';
        $payment_term = $client['payment_term'] ?? 'Monthly';
        $service_total_fee = $client['service_total_fee'] ?? '0.00';
        $lead_source = $client['lead_source'] ?? 'website';
        $client_status = $client['client_status'] ?? 'New Lead';
        $selected_user_id = $client['user_id'] ?? '';
        
        // Check if success modal should be shown (from session)
        if (isset($_SESSION['client_update_success'])) {
            $show_success_modal = true;
            unset($_SESSION['client_update_success']);
        }
        // Check for error messages
        if (isset($_SESSION['error_message'])) {
            $message = $_SESSION['error_message'];
            $message_type = "danger";
            unset($_SESSION['error_message']);
        }
    } else {
        $message = "Client not found.";
        $message_type = "error";
    }
    $stmt->close();
} else {
    $message = "Invalid client ID.";
    $message_type = "error";
}

// Fetch jurisdictions for dropdown
$jurisdictions_query = "SELECT jurisdiction_name FROM jurisdictions WHERE is_active = 1 ORDER BY jurisdiction_name";
$jurisdictions_result = mysqli_query($connection, $jurisdictions_query);

// Fetch industries for dropdown
$industries_query = "SELECT industry_name, category FROM industries WHERE is_active = 1 ORDER BY category, industry_name";
$industries_result = mysqli_query($connection, $industries_query);
?>

<?php if ($show_success_modal): ?>
<!-- Success Modal -->
<div class="modal fade" id="clientSuccessModal" tabindex="-1" aria-labelledby="clientSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="clientSuccessModalLabel">
          <i class="bi bi-check-circle-fill me-2"></i>Success!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h5 class="mt-3">Client Updated Successfully!</h5>
        <p class="text-muted mb-0">The client "<?php echo htmlspecialchars($company_name); ?>" has been updated.</p>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="clients.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Clients
        </a>
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" id="continueEditingBtn">
          <i class="bi bi-pencil me-2"></i>Continue Editing
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      var modalElement = document.getElementById('clientSuccessModal');
      if (modalElement) {
          var modal = new bootstrap.Modal(modalElement, {
              backdrop: 'static',
              keyboard: false
          });
          modal.show();
      }
  });
</script>
<?php endif; ?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4">Edit Client</h2>
            <a href="./clients.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> View All Clients
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Client Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="clientForm">
                            <input type="hidden" name="update_client" value="1">
                            <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                            
                            <!-- Select Existing User (Client) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2" style="color: #f1bf70;">
                                        <i class="bi bi-person-badge me-2"></i>Link to Existing User
                                    </h6>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Select Client User</label>
                                        <select id="user_id" name="user_id" class="form-control select2-search" style="width: 100%;">
                                            <option value="">-- Select an existing client user --</option>
                                            <?php 
                                            if ($users_result && mysqli_num_rows($users_result) > 0) {
                                                mysqli_data_seek($users_result, 0);
                                                while($user = mysqli_fetch_assoc($users_result)) {
                                                    $user_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                                    if (empty($user_name)) {
                                                        $user_name = $user['username'];
                                                    }
                                                    $selected = ($selected_user_id == $user['user_id']) ? 'selected' : '';
                                                    echo "<option value='" . $user['user_id'] . "' {$selected}>" 
                                                         . htmlspecialchars($user_name) . " (" . htmlspecialchars($user['user_email']) . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                        <div class="form-text">Link this client to an existing user account. Leave empty to keep current or no association.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Company Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2" style="color: #f1bf70;">
                                        <i class="bi bi-building me-2"></i>Company Information
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name *</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($company_name); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="trade_license_no" class="form-label">Trade License No</label>
                                        <input type="text" id="trade_license_no" name="trade_license_no" class="form-control" 
                                               value="<?php echo htmlspecialchars($trade_license_no); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="country" class="form-label">Country *</label>
                                        <select id="country" name="country" class="form-control" required>
                                            <option value="">Select Country</option>
                                            <option value="United Arab Emirates" <?php echo ($country == 'United Arab Emirates') ? 'selected' : ''; ?>>United Arab Emirates</option>
                                            <option value="Saudi Arabia" <?php echo ($country == 'Saudi Arabia') ? 'selected' : ''; ?>>Saudi Arabia</option>
                                            <option value="Qatar" <?php echo ($country == 'Qatar') ? 'selected' : ''; ?>>Qatar</option>
                                            <option value="Oman" <?php echo ($country == 'Oman') ? 'selected' : ''; ?>>Oman</option>
                                            <option value="Kuwait" <?php echo ($country == 'Kuwait') ? 'selected' : ''; ?>>Kuwait</option>
                                            <option value="Bahrain" <?php echo ($country == 'Bahrain') ? 'selected' : ''; ?>>Bahrain</option>
                                            <option value="United Kingdom" <?php echo ($country == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                            <option value="United States" <?php echo ($country == 'United States') ? 'selected' : ''; ?>>United States</option>
                                            <option value="Germany" <?php echo ($country == 'Germany') ? 'selected' : ''; ?>>Germany</option>
                                            <option value="France" <?php echo ($country == 'France') ? 'selected' : ''; ?>>France</option>
                                            <option value="China" <?php echo ($country == 'China') ? 'selected' : ''; ?>>China</option>
                                            <option value="Japan" <?php echo ($country == 'Japan') ? 'selected' : ''; ?>>Japan</option>
                                            <option value="India" <?php echo ($country == 'India') ? 'selected' : ''; ?>>India</option>
                                            <option value="Russia" <?php echo ($country == 'Russia') ? 'selected' : ''; ?>>Russia</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jurisdiction" class="form-label">Jurisdiction</label>
                                        <select id="jurisdiction" name="jurisdiction" class="form-control">
                                            <option value="">Select Jurisdiction</option>
                                            <?php
                                            if ($jurisdictions_result && mysqli_num_rows($jurisdictions_result) > 0) {
                                                mysqli_data_seek($jurisdictions_result, 0);
                                                while($jur = mysqli_fetch_assoc($jurisdictions_result)) {
                                                    $selected = ($jurisdiction == $jur['jurisdiction_name']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($jur['jurisdiction_name']) . "' {$selected}>" . htmlspecialchars($jur['jurisdiction_name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emirate_zone" class="form-label">Emirate/Zone/State</label>
                                        <select id="emirate_zone" name="emirate_zone" class="form-control">
                                            <option value="">Select Emirate/Zone/State</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="industry" class="form-label">Industry</label>
                                        <select id="industry" name="industry" class="form-control">
                                            <option value="">Select Industry</option>
                                            <?php
                                            if ($industries_result && mysqli_num_rows($industries_result) > 0) {
                                                mysqli_data_seek($industries_result, 0);
                                                $current_category = '';
                                                while($ind = mysqli_fetch_assoc($industries_result)) {
                                                    if ($current_category != $ind['category']) {
                                                        if ($current_category != '') echo '</optgroup>';
                                                        $current_category = $ind['category'];
                                                        echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                                                    }
                                                    $selected = ($industry == $ind['industry_name']) ? 'selected' : '';
                                                    echo "<option value='" . htmlspecialchars($ind['industry_name']) . "' {$selected}>" . htmlspecialchars($ind['industry_name']) . "</option>";
                                                }
                                                if ($current_category != '') echo '</optgroup>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="business_activity" class="form-label">Business Activity</label>
                                        <input type="text" id="business_activity" name="business_activity" class="form-control" 
                                               value="<?php echo htmlspecialchars($business_activity); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lead_source" class="form-label">Lead Source</label>
                                        <select id="lead_source" name="lead_source" class="form-control">
                                            <option value="website" <?php echo ($lead_source == 'website') ? 'selected' : ''; ?>>Website</option>
                                            <option value="referral" <?php echo ($lead_source == 'referral') ? 'selected' : ''; ?>>Referral</option>
                                            <option value="digital_marketing" <?php echo ($lead_source == 'digital_marketing') ? 'selected' : ''; ?>>Digital Marketing</option>
                                            <option value="event" <?php echo ($lead_source == 'event') ? 'selected' : ''; ?>>Event</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Person Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2" style="color: #f1bf70;">
                                        <i class="bi bi-person me-2"></i>Contact Person Information
                                    </h6>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="contact_title" class="form-label">Title</label>
                                        <select id="contact_title" name="contact_title" class="form-control">
                                            <option value="" <?php echo ($contact_title == '') ? 'selected' : ''; ?>>Select</option>
                                            <option value="Mr." <?php echo ($contact_title == 'Mr.') ? 'selected' : ''; ?>>Mr.</option>
                                            <option value="Ms." <?php echo ($contact_title == 'Ms.') ? 'selected' : ''; ?>>Ms.</option>
                                            <option value="Mrs." <?php echo ($contact_title == 'Mrs.') ? 'selected' : ''; ?>>Mrs.</option>
                                            <option value="Dr." <?php echo ($contact_title == 'Dr.') ? 'selected' : ''; ?>>Dr.</option>
                                            <option value="Prof." <?php echo ($contact_title == 'Prof.') ? 'selected' : ''; ?>>Prof.</option>
                                            <option value="Eng." <?php echo ($contact_title == 'Eng.') ? 'selected' : ''; ?>>Eng.</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="contact_name" class="form-label">Full Name *</label>
                                        <input type="text" id="contact_name" name="contact_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($contact_name); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_designation" class="form-label">Designation</label>
                                        <input type="text" id="contact_designation" name="contact_designation" class="form-control" 
                                               value="<?php echo htmlspecialchars($contact_designation); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_mobile" class="form-label">Mobile Number *</label>
                                        <input type="text" id="contact_mobile" name="contact_mobile" class="form-control" 
                                               value="<?php echo htmlspecialchars($contact_mobile); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Email Address *</label>
                                        <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                               value="<?php echo htmlspecialchars($contact_email); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Details -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2" style="color: #f1bf70;">
                                        <i class="bi bi-briefcase me-2"></i>Service Details
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_total_fee" class="form-label">Service Total Fee (AED)</label>
                                        <input type="number" id="service_total_fee" name="service_total_fee" class="form-control" 
                                               value="<?php echo htmlspecialchars($service_total_fee); ?>" step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contract_start_date" class="form-label">Contract Start Date</label>
                                        <input type="date" id="contract_start_date" name="contract_start_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($contract_start_date); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contract_end_date" class="form-label">Contract End Date</label>
                                        <input type="date" id="contract_end_date" name="contract_end_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($contract_end_date); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_currency" class="form-label">Payment Currency</label>
                                        <select id="payment_currency" name="payment_currency" class="form-control">
                                            <option value="AED" <?php echo ($payment_currency == 'AED') ? 'selected' : ''; ?>>AED - UAE Dirham</option>
                                            <option value="USD" <?php echo ($payment_currency == 'USD') ? 'selected' : ''; ?>>USD - US Dollar</option>
                                            <option value="EUR" <?php echo ($payment_currency == 'EUR') ? 'selected' : ''; ?>>EUR - Euro</option>
                                            <option value="GBP" <?php echo ($payment_currency == 'GBP') ? 'selected' : ''; ?>>GBP - British Pound</option>
                                            <option value="CNY" <?php echo ($payment_currency == 'CNY') ? 'selected' : ''; ?>>CNY - Chinese Yuan</option>
                                            <option value="JPY" <?php echo ($payment_currency == 'JPY') ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                                            <option value="RUB" <?php echo ($payment_currency == 'RUB') ? 'selected' : ''; ?>>RUB - Russian Ruble</option>
                                            <option value="INR" <?php echo ($payment_currency == 'INR') ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_term" class="form-label">Payment Term</label>
                                        <select id="payment_term" name="payment_term" class="form-control">
                                            <option value="Monthly" <?php echo ($payment_term == 'Monthly') ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="Quarterly" <?php echo ($payment_term == 'Quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                            <option value="Bi-yearly" <?php echo ($payment_term == 'Bi-yearly') ? 'selected' : ''; ?>>Bi-yearly (2 payments)</option>
                                            <option value="One-time" <?php echo ($payment_term == 'One-time') ? 'selected' : ''; ?>>One-time</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="service_description" class="form-label">Service Description</label>
                                        <textarea id="service_description" name="service_description" class="form-control" rows="3"><?php echo htmlspecialchars($service_description); ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_status" class="form-label">Client Status</label>
                                        <select id="client_status" name="client_status" class="form-control">
                                            <?php
                                            $statuses = [
                                                'New Lead', 'Contacted', 'Qualified', 'Proposal Drafted',
                                                'Under Manager Review', 'Rejected by Manager', 'Approved by Manager',
                                                'Under CEO Review', 'Rejected by CEO', 'Final Proposal Ready',
                                                'Proposal Sent to Client', 'Awaiting Client Action', 'Signed – Move to Finance',
                                                'Inactive'
                                            ];
                                            foreach ($statuses as $statusOption) {
                                                $selected = ($client_status == $statusOption) ? 'selected' : '';
                                                echo "<option value='{$statusOption}' {$selected}>{$statusOption}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" name="update_client" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Update Client
                                    </button>
                                    <a href="./clients.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Country-State/Emirate mapping
const countryZones = {
    'United Arab Emirates': ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'],
    'Saudi Arabia': ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Dhahran'],
    'Qatar': ['Doha', 'Al Rayyan', 'Umm Salal', 'Al Wakrah', 'Al Khor'],
    'Oman': ['Muscat', 'Salalah', 'Sohar', 'Nizwa', 'Sur'],
    'Kuwait': ['Kuwait City', 'Hawalli', 'Farwaniya', 'Mubarak Al-Kabeer', 'Ahmadi'],
    'Bahrain': ['Manama', 'Riffa', 'Muharraq', 'Hamad Town', 'Isa Town'],
    'United Kingdom': ['England', 'Scotland', 'Wales', 'Northern Ireland'],
    'United States': ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'],
    'Germany': ['Baden-Württemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hesse', 'Lower Saxony', 'Mecklenburg-Vorpommern', 'North Rhine-Westphalia', 'Rhineland-Palatinate', 'Saarland', 'Saxony', 'Saxony-Anhalt', 'Schleswig-Holstein', 'Thuringia'],
    'France': ['Île-de-France', 'Auvergne-Rhône-Alpes', 'Nouvelle-Aquitaine', 'Occitanie', 'Hauts-de-France', 'Grand Est', 'Provence-Alpes-Côte d\'Azur', 'Pays de la Loire', 'Normandy', 'Brittany', 'Centre-Val de Loire', 'Bourgogne-Franche-Comté', 'Corsica'],
    'China': ['Beijing', 'Shanghai', 'Guangdong', 'Zhejiang', 'Jiangsu', 'Tianjin', 'Chongqing', 'Shandong', 'Sichuan', 'Hubei', 'Fujian', 'Henan', 'Hunan', 'Shaanxi', 'Liaoning', 'Jiangxi', 'Anhui', 'Hebei', 'Heilongjiang', 'Jilin'],
    'Japan': ['Tokyo', 'Osaka', 'Kyoto', 'Hokkaido', 'Okinawa', 'Aichi', 'Kanagawa', 'Hyogo', 'Fukuoka', 'Hiroshima', 'Miyagi', 'Shizuoka', 'Chiba', 'Saitama', 'Niigata', 'Gunma'],
    'India': ['Delhi', 'Maharashtra', 'Karnataka', 'Tamil Nadu', 'Uttar Pradesh', 'Gujarat', 'Rajasthan', 'West Bengal', 'Telangana', 'Andhra Pradesh', 'Madhya Pradesh', 'Kerala', 'Haryana', 'Punjab', 'Bihar', 'Odisha', 'Assam', 'Jharkhand', 'Chhattisgarh', 'Uttarakhand'],
    'Russia': ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', 'Nizhny Novgorod', 'Chelyabinsk', 'Samara', 'Omsk', 'Rostov-on-Don', 'Ufa', 'Krasnoyarsk', 'Voronezh', 'Perm', 'Volgograd']
};

// Initialize emirate/zone based on current country
document.addEventListener('DOMContentLoaded', function() {
    const country = document.getElementById('country').value;
    const emirateSelect = document.getElementById('emirate_zone');
    const currentEmirate = '<?php echo $emirate_zone; ?>';
    
    if (country && countryZones[country]) {
        countryZones[country].forEach(zone => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            if (zone === currentEmirate) {
                option.selected = true;
            }
            emirateSelect.appendChild(option);
        });
    }
    
    if (currentEmirate && !emirateSelect.querySelector(`option[value="${currentEmirate}"]`)) {
        const option = document.createElement('option');
        option.value = currentEmirate;
        option.textContent = currentEmirate;
        option.selected = true;
        emirateSelect.appendChild(option);
    }
    
    // Initialize Select2 for searchable dropdown (if jQuery and Select2 are loaded)
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('#user_id').select2({
            placeholder: '-- Select an existing client user --',
            allowClear: true,
            width: '100%'
        });
    } else {
        // Fallback: Add search capability to native select
        const select = document.getElementById('user_id');
        if (select) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search users...';
            searchInput.className = 'form-control mb-2';
            searchInput.style.marginBottom = '5px';
            select.parentNode.insertBefore(searchInput, select);
            
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                for(let i = 0; i < select.options.length; i++) {
                    const text = select.options[i].text.toLowerCase();
                    select.options[i].style.display = text.includes(filter) ? '' : 'none';
                }
            });
        }
    }
});

// Update emirate/zone options when country changes
document.getElementById('country').addEventListener('change', function() {
    const country = this.value;
    const emirateSelect = document.getElementById('emirate_zone');
    const jurisdictionSelect = document.getElementById('jurisdiction');
    
    emirateSelect.innerHTML = '<option value="">Select Emirate/Zone/State</option>';
    if (country && countryZones[country]) {
        countryZones[country].forEach(zone => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            emirateSelect.appendChild(option);
        });
    }
    
    if (country) {
        fetch(`get_jurisdictions.php?country=${encodeURIComponent(country)}`)
            .then(response => response.json())
            .then(data => {
                jurisdictionSelect.innerHTML = '<option value="">Select Jurisdiction</option>';
                if (data.success && data.jurisdictions) {
                    data.jurisdictions.forEach(jur => {
                        const option = document.createElement('option');
                        option.value = jur.jurisdiction_name;
                        option.textContent = jur.jurisdiction_name;
                        jurisdictionSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error fetching jurisdictions:', error));
    }
});

// Form validation
document.getElementById('clientForm').addEventListener('submit', function(e) {
    const email = document.getElementById('contact_email').value;
    const mobile = document.getElementById('contact_mobile').value;
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return;
    }
    
    const mobileRegex = /^[0-9+\-\s]{8,}$/;
    if (!mobileRegex.test(mobile)) {
        e.preventDefault();
        alert('Please enter a valid mobile number (at least 8 digits)');
        return;
    }
});
</script>