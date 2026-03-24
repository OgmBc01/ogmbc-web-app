<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Get client ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid client ID.";
    ob_end_clean();
    header("Location: clients.php");
    exit();
}

$client_id = (int)$_GET['id'];
$message = '';
$message_type = '';
$showSuccessModal = false;

// Fetch jurisdictions for dropdown
$jurisdictions_query = "SELECT jurisdiction_name FROM jurisdictions WHERE is_active = 1 ORDER BY jurisdiction_name";
$jurisdictions_result = mysqli_query($connection, $jurisdictions_query);

// Fetch industries for dropdown
$industries_query = "SELECT industry_name, category FROM industries WHERE is_active = 1 ORDER BY category, industry_name";
$industries_result = mysqli_query($connection, $industries_query);

// Fetch services for dropdown
$services_query = "SELECT * FROM categories ORDER BY cat_title";
$services_result = mysqli_query($connection, $services_query);

// Fetch client data
$stmt = $connection->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Client not found.";
    ob_end_clean();
    header("Location: clients.php");
    exit();
}

$client = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    
    $company_name = mysqli_real_escape_string($connection, trim($_POST['company_name']));
    $trade_license_no = mysqli_real_escape_string($connection, trim($_POST['trade_license_no'] ?? ''));
    $country = mysqli_real_escape_string($connection, trim($_POST['country']));
    $jurisdiction = mysqli_real_escape_string($connection, trim($_POST['jurisdiction'] ?? ''));
    $emirate_zone = mysqli_real_escape_string($connection, trim($_POST['emirate_zone'] ?? ''));
    $business_activity = mysqli_real_escape_string($connection, trim($_POST['business_activity'] ?? ''));
    $industry = mysqli_real_escape_string($connection, trim($_POST['industry'] ?? ''));
    $address = mysqli_real_escape_string($connection, trim($_POST['address'] ?? ''));
    $contact_title = mysqli_real_escape_string($connection, trim($_POST['contact_title'] ?? ''));
    $contact_name = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
    $contact_designation = mysqli_real_escape_string($connection, trim($_POST['contact_designation'] ?? ''));
    $contact_mobile = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
    $contact_email = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
    $service_id = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : 'NULL';
    $service_description = mysqli_real_escape_string($connection, trim($_POST['service_description'] ?? ''));
    $expected_start_date = !empty($_POST['expected_start_date']) ? "'" . mysqli_real_escape_string($connection, $_POST['expected_start_date']) . "'" : 'NULL';
    $payment_currency = mysqli_real_escape_string($connection, trim($_POST['payment_currency']));
    $payment_term = mysqli_real_escape_string($connection, trim($_POST['payment_term']));
    $service_total_fee = floatval($_POST['service_total_fee'] ?? 0);
    $lead_source = mysqli_real_escape_string($connection, trim($_POST['lead_source']));
    $client_status = mysqli_real_escape_string($connection, trim($_POST['client_status']));
    
    // Validation
    if (empty($company_name) || empty($contact_name) || empty($contact_mobile) || empty($contact_email)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } else {
        // Check if email exists (excluding current client)
        $check_email = "SELECT client_id FROM clients WHERE contact_email = '$contact_email' AND client_id != $client_id";
        $email_result = mysqli_query($connection, $check_email);
        
        if (mysqli_num_rows($email_result) > 0) {
            $message = "Email already exists. Please use another email.";
            $message_type = "danger";
        } else {
            $service_id_value = ($service_id !== 'NULL') ? $service_id : 'NULL';
            
            // Build update query
            $update_query = "UPDATE clients SET 
                             company_name = '$company_name',
                             trade_license_no = '$trade_license_no',
                             country = '$country',
                             jurisdiction = '$jurisdiction',
                             emirate_zone = '$emirate_zone',
                             business_activity = '$business_activity',
                             industry = '$industry',
                             address = '$address',
                             contact_title = '$contact_title',
                             contact_name = '$contact_name',
                             contact_designation = '$contact_designation',
                             contact_mobile = '$contact_mobile',
                             contact_email = '$contact_email',
                             service_id = $service_id_value,
                             service_description = '$service_description',
                             expected_start_date = $expected_start_date,
                             payment_currency = '$payment_currency',
                             payment_term = '$payment_term',
                             service_total_fee = $service_total_fee,
                             lead_source = '$lead_source',
                             client_status = '$client_status'
                             WHERE client_id = $client_id";
            
            if (mysqli_query($connection, $update_query)) {
                $showSuccessModal = true;
                // Refresh client data after update
                $refresh_stmt = $connection->prepare("SELECT * FROM clients WHERE client_id = ?");
                $refresh_stmt->bind_param("i", $client_id);
                $refresh_stmt->execute();
                $refresh_result = $refresh_stmt->get_result();
                $client = $refresh_result->fetch_assoc();
                $refresh_stmt->close();
                // Clear any previous messages
                $message = '';
                $message_type = '';
                // No redirect
            } else {
                $message = "Error updating client: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Client</h5>
                    <a href="clients.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Clients
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="editClientForm">
                        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                        
                        <!-- Company Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 text-primary">
                                    <i class="bi bi-building me-2"></i>Company Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name *</label>
                                <input type="text" id="company_name" name="company_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['company_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="trade_license_no" class="form-label">Trade License No</label>
                                <input type="text" id="trade_license_no" name="trade_license_no" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['trade_license_no'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country *</label>
                                <select id="country" name="country" class="form-control" required>
                                    <option value="">Select Country</option>
                                    <option value="United Arab Emirates" <?php echo ($client['country'] == 'United Arab Emirates') ? 'selected' : ''; ?>>United Arab Emirates</option>
                                    <option value="Saudi Arabia" <?php echo ($client['country'] == 'Saudi Arabia') ? 'selected' : ''; ?>>Saudi Arabia</option>
                                    <option value="Qatar" <?php echo ($client['country'] == 'Qatar') ? 'selected' : ''; ?>>Qatar</option>
                                    <option value="Oman" <?php echo ($client['country'] == 'Oman') ? 'selected' : ''; ?>>Oman</option>
                                    <option value="Kuwait" <?php echo ($client['country'] == 'Kuwait') ? 'selected' : ''; ?>>Kuwait</option>
                                    <option value="Bahrain" <?php echo ($client['country'] == 'Bahrain') ? 'selected' : ''; ?>>Bahrain</option>
                                    <option value="United Kingdom" <?php echo ($client['country'] == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="United States" <?php echo ($client['country'] == 'United States') ? 'selected' : ''; ?>>United States</option>
                                    <option value="Germany" <?php echo ($client['country'] == 'Germany') ? 'selected' : ''; ?>>Germany</option>
                                    <option value="France" <?php echo ($client['country'] == 'France') ? 'selected' : ''; ?>>France</option>
                                    <option value="China" <?php echo ($client['country'] == 'China') ? 'selected' : ''; ?>>China</option>
                                    <option value="Japan" <?php echo ($client['country'] == 'Japan') ? 'selected' : ''; ?>>Japan</option>
                                    <option value="India" <?php echo ($client['country'] == 'India') ? 'selected' : ''; ?>>India</option>
                                    <option value="Russia" <?php echo ($client['country'] == 'Russia') ? 'selected' : ''; ?>>Russia</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jurisdiction" class="form-label">Jurisdiction</label>
                                <select id="jurisdiction" name="jurisdiction" class="form-control">
                                    <option value="">Select Jurisdiction</option>
                                    <?php 
                                    if ($jurisdictions_result && mysqli_num_rows($jurisdictions_result) > 0) {
                                        mysqli_data_seek($jurisdictions_result, 0);
                                        while($jur = mysqli_fetch_assoc($jurisdictions_result)): 
                                        ?>
                                        <option value="<?php echo $jur['jurisdiction_name']; ?>" 
                                            <?php echo ($client['jurisdiction'] == $jur['jurisdiction_name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jur['jurisdiction_name']); ?>
                                        </option>
                                        <?php endwhile; 
                                    } ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="emirate_zone" class="form-label">Emirate/Zone/State</label>
                                <select id="emirate_zone" name="emirate_zone" class="form-control">
                                    <option value="">Select Emirate/Zone/State</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
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
                                            $selected = ($client['industry'] == $ind['industry_name']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($ind['industry_name']) . "' {$selected}>" . htmlspecialchars($ind['industry_name']) . "</option>";
                                        }
                                        if ($current_category != '') echo '</optgroup>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="business_activity" class="form-label">Business Activity</label>
                                <input type="text" id="business_activity" name="business_activity" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['business_activity'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="lead_source" class="form-label">Lead Source</label>
                                <select id="lead_source" name="lead_source" class="form-control">
                                    <option value="website" <?php echo ($client['lead_source'] == 'website') ? 'selected' : ''; ?>>Website</option>
                                    <option value="referral" <?php echo ($client['lead_source'] == 'referral') ? 'selected' : ''; ?>>Referral</option>
                                    <option value="digital_marketing" <?php echo ($client['lead_source'] == 'digital_marketing') ? 'selected' : ''; ?>>Digital Marketing</option>
                                    <option value="event" <?php echo ($client['lead_source'] == 'event') ? 'selected' : ''; ?>>Event</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($client['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Contact Person Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 text-primary">
                                    <i class="bi bi-person me-2"></i>Contact Person Information
                                </h6>
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label for="contact_title" class="form-label">Title</label>
                                <select id="contact_title" name="contact_title" class="form-control">
                                    <option value="" <?php echo ($client['contact_title'] == '') ? 'selected' : ''; ?>>Select</option>
                                    <option value="Mr." <?php echo ($client['contact_title'] == 'Mr.') ? 'selected' : ''; ?>>Mr.</option>
                                    <option value="Ms." <?php echo ($client['contact_title'] == 'Ms.') ? 'selected' : ''; ?>>Ms.</option>
                                    <option value="Mrs." <?php echo ($client['contact_title'] == 'Mrs.') ? 'selected' : ''; ?>>Mrs.</option>
                                    <option value="Dr." <?php echo ($client['contact_title'] == 'Dr.') ? 'selected' : ''; ?>>Dr.</option>
                                    <option value="Prof." <?php echo ($client['contact_title'] == 'Prof.') ? 'selected' : ''; ?>>Prof.</option>
                                    <option value="Eng." <?php echo ($client['contact_title'] == 'Eng.') ? 'selected' : ''; ?>>Eng.</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="contact_name" class="form-label">Full Name *</label>
                                <input type="text" id="contact_name" name="contact_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['contact_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_designation" class="form-label">Designation</label>
                                <input type="text" id="contact_designation" name="contact_designation" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['contact_designation'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_mobile" class="form-label">Mobile Number *</label>
                                <input type="text" id="contact_mobile" name="contact_mobile" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['contact_mobile']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">Email Address *</label>
                                <input type="email" id="contact_email" name="contact_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['contact_email']); ?>" required>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 text-primary">
                                    <i class="bi bi-briefcase me-2"></i>Service Details
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">Service Type</label>
                                <select id="service_id" name="service_id" class="form-control">
                                    <option value="">Select Service</option>
                                    <?php 
                                    mysqli_data_seek($services_result, 0);
                                    while($service = mysqli_fetch_assoc($services_result)): 
                                    ?>
                                    <option value="<?php echo $service['cat_id']; ?>" 
                                        <?php echo ($client['service_id'] == $service['cat_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['cat_title']); ?> - AED <?php echo $service['cat_price']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="service_total_fee" class="form-label">Service Total Fee (AED)</label>
                                <input type="number" id="service_total_fee" name="service_total_fee" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['service_total_fee']); ?>" step="0.01" min="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="expected_start_date" class="form-label">Expected Start Date</label>
                                <input type="date" id="expected_start_date" name="expected_start_date" class="form-control" 
                                       value="<?php echo htmlspecialchars($client['expected_start_date'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_currency" class="form-label">Payment Currency</label>
                                <select id="payment_currency" name="payment_currency" class="form-control">
                                    <option value="AED" <?php echo ($client['payment_currency'] == 'AED') ? 'selected' : ''; ?>>AED - UAE Dirham</option>
                                    <option value="USD" <?php echo ($client['payment_currency'] == 'USD') ? 'selected' : ''; ?>>USD - US Dollar</option>
                                    <option value="EUR" <?php echo ($client['payment_currency'] == 'EUR') ? 'selected' : ''; ?>>EUR - Euro</option>
                                    <option value="GBP" <?php echo ($client['payment_currency'] == 'GBP') ? 'selected' : ''; ?>>GBP - British Pound</option>
                                    <option value="CNY" <?php echo ($client['payment_currency'] == 'CNY') ? 'selected' : ''; ?>>CNY - Chinese Yuan</option>
                                    <option value="JPY" <?php echo ($client['payment_currency'] == 'JPY') ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                                    <option value="RUB" <?php echo ($client['payment_currency'] == 'RUB') ? 'selected' : ''; ?>>RUB - Russian Ruble</option>
                                    <option value="INR" <?php echo ($client['payment_currency'] == 'INR') ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="payment_term" class="form-label">Payment Term</label>
                                <select id="payment_term" name="payment_term" class="form-control">
                                    <option value="Monthly" <?php echo ($client['payment_term'] == 'Monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="Quarterly" <?php echo ($client['payment_term'] == 'Quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                    <option value="Bi-yearly" <?php echo ($client['payment_term'] == 'Bi-yearly') ? 'selected' : ''; ?>>Bi-yearly (2 payments)</option>
                                    <option value="One-time" <?php echo ($client['payment_term'] == 'One-time') ? 'selected' : ''; ?>>One-time</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="service_description" class="form-label">Service Description</label>
                                <textarea id="service_description" name="service_description" class="form-control" rows="3"><?php echo htmlspecialchars($client['service_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="client_status" class="form-label">Client Status</label>
                                <select id="client_status" name="client_status" class="form-control">
                                    <?php
                                    $statuses = [
                                        'New Lead', 'Contacted', 'Qualified', 'Proposal Drafted',
                                        'Under Manager Review', 'Rejected by Manager', 'Approved by Manager',
                                        'Under CEO Review', 'Rejected by CEO', 'Final Proposal Ready',
                                        'Proposal Sent to Client', 'Awaiting Client Action', 'Signed – Move to Finance'
                                    ];
                                    foreach ($statuses as $statusOption) {
                                        $selected = ($client['client_status'] == $statusOption) ? 'selected' : '';
                                        echo "<option value='{$statusOption}' {$selected}>{$statusOption}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_client" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Client
                                </button>
                                <a href="clients.php" class="btn btn-outline-secondary btn-lg">
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

<?php if ($showSuccessModal): ?>
<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">
          <i class="bi bi-check-circle-fill me-2"></i>Success!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
          <h5 class="mt-3">Client Updated Successfully!</h5>
          <p class="text-muted mb-0">The client "<?php echo htmlspecialchars($client['company_name']); ?>" has been updated.</p>
        </div>
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
  // Show modal with proper initialization - prevents multiple instances
  document.addEventListener('DOMContentLoaded', function() {
    // Check if modal should be shown
    var showModal = <?php echo $showSuccessModal ? 'true' : 'false'; ?>;
    
    if (showModal) {
      // Get the modal element
      var modalElement = document.getElementById('successModal');
      
      // Check if modal already has an instance
      var modalInstance = bootstrap.Modal.getInstance(modalElement);
      
      // If no instance exists, create one
      if (!modalInstance) {
        modalInstance = new bootstrap.Modal(modalElement, {
          backdrop: 'static',
          keyboard: false
        });
      }
      
      // Show the modal
      modalInstance.show();
      
      // Handle continue editing button click - properly close modal without reload
      var continueBtn = document.getElementById('continueEditingBtn');
      if (continueBtn) {
        continueBtn.addEventListener('click', function() {
          modalInstance.hide();
          // Remove any leftover backdrops
          setTimeout(function() {
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) {
              backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
          }, 150);
        });
      }
    }
  });
</script>
<?php endif; ?>

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
    const currentEmirate = '<?php echo $client['emirate_zone']; ?>';
    
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
    
    // If no zones for country, add current emirate as option
    if (currentEmirate && !emirateSelect.querySelector(`option[value="${currentEmirate}"]`)) {
        const option = document.createElement('option');
        option.value = currentEmirate;
        option.textContent = currentEmirate;
        option.selected = true;
        emirateSelect.appendChild(option);
    }
});

// Update emirate/zone options when country changes
document.getElementById('country').addEventListener('change', function() {
    const country = this.value;
    const emirateSelect = document.getElementById('emirate_zone');
    const jurisdictionSelect = document.getElementById('jurisdiction');
    
    // Update emirate/zone dropdown
    emirateSelect.innerHTML = '<option value="">Select Emirate/Zone/State</option>';
    if (country && countryZones[country]) {
        countryZones[country].forEach(zone => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            emirateSelect.appendChild(option);
        });
    }
    
    // Filter jurisdictions based on selected country via AJAX
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
document.getElementById('editClientForm')?.addEventListener('submit', function(e) {
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