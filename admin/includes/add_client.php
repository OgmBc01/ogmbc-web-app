<?php
// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$company_name = $trade_license_no = $country = $emirate_zone = $business_activity = $address = '';
$contact_title = $contact_name = $contact_designation = $contact_mobile = $contact_email = '';
$service_id = $service_description = $contract_start_date = $contract_end_date = '';
$payment_currency = 'AED';
$payment_term = 'Monthly';
$service_total_fee = '0.00';
$lead_source = 'website';
$selected_user_id = '';
$message = '';
$message_type = '';
$show_toast = false;

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_client'])) {
    
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
    // SECOND: Check for duplicate company name
    // ============================================
    if ($validation_passed) {
        $dup_query = "SELECT client_id FROM clients WHERE company_name = '$company_name_check'";
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
    // FIFTH: All validations passed - call insert function
    // ============================================
    if ($validation_passed) {
        insert_client();
    }
}

// Check for success flag
if (isset($_SESSION['client_add_success'])) {
    $show_toast = true;
    unset($_SESSION['client_add_success']);
}
// Check for error message
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = "danger";
    unset($_SESSION['error_message']);
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4">Add New Client</h2>
            <a href="./clients.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> View All Clients
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>New Client Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="clientForm">
                            <input type="hidden" name="submit_client" value="1">
                            
                            <!-- Select Existing User (Client) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2" style="color: #f1bf70;">
                                        <i class="bi bi-person-badge me-2"></i>Link to Existing User (Optional)
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
                                        <div class="form-text">Optionally link this client to an existing user account. This will associate the client with the selected user.</div>
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
                                        <input list="country_list" id="country" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>" required>
                                        <datalist id="country_list">
                                            <option value="United Arab Emirates">
                                            <option value="Saudi Arabia">
                                            <option value="Qatar">
                                            <option value="Oman">
                                            <option value="Kuwait">
                                            <option value="Bahrain">
                                            <option value="United Kingdom">
                                            <option value="United States">
                                            <option value="Germany">
                                            <option value="France">
                                            <option value="China">
                                            <option value="Japan">
                                            <option value="India">
                                            <option value="Russia">
                                        </datalist>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jurisdiction" class="form-label">Jurisdiction</label>
                                        <input list="jurisdiction_list" id="jurisdiction" name="jurisdiction" class="form-control" value="<?php echo htmlspecialchars($jurisdiction ?? ''); ?>">
                                        <datalist id="jurisdiction_list">
                                            <?php
                                            // Fetch jurisdictions from database
                                            $jurisdiction_query = "SELECT jurisdiction_name FROM jurisdictions WHERE is_active = 1 ORDER BY jurisdiction_name";
                                            $jurisdiction_result = mysqli_query($connection, $jurisdiction_query);
                                            if ($jurisdiction_result) {
                                                while($jur = mysqli_fetch_assoc($jurisdiction_result)) {
                                                    echo "<option value='" . htmlspecialchars($jur['jurisdiction_name']) . "'>" . htmlspecialchars($jur['jurisdiction_name']) . "</option>";
                                                }
                                            }
                                            ?>
                                        </datalist>
                                        <div class="form-text">You may select from the list or type a new jurisdiction.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emirate_zone" class="form-label">Emirate/Zone/State</label>
                                        <input list="emirate_zone_list" id="emirate_zone" name="emirate_zone" class="form-control" value="<?php echo htmlspecialchars($emirate_zone ?? ''); ?>">
                                        <datalist id="emirate_zone_list"></datalist>
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
                                        <label for="industry" class="form-label">Industry</label>
                                        <select id="industry" name="industry" class="form-control">
                                            <option value="">Select Industry</option>
                                            <?php
                                            // Fetch industries from database
                                            $industry_query = "SELECT industry_name, category FROM industries WHERE is_active = 1 ORDER BY category, industry_name";
                                            $industry_result = mysqli_query($connection, $industry_query);
                                            $current_category = '';
                                            if ($industry_result) {
                                                while($ind = mysqli_fetch_assoc($industry_result)) {
                                                    if ($current_category != $ind['category']) {
                                                        if ($current_category != '') echo '</optgroup>';
                                                        $current_category = $ind['category'];
                                                        echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                                                    }
                                                    echo "<option value='" . htmlspecialchars($ind['industry_name']) . "'>" . htmlspecialchars($ind['industry_name']) . "</option>";
                                                }
                                                if ($current_category != '') echo '</optgroup>';
                                            }
                                            ?>
                                        </select>
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
                                            <option value="">Select</option>
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
                                        <div class="form-text">Leave as 0.00 to use service default price</div>
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
                                            <option value="AED">AED - UAE Dirham</option>
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="CNY">CNY - Chinese Yuan</option>
                                            <option value="JPY">JPY - Japanese Yen</option>
                                            <option value="RUB">RUB - Russian Ruble</option>
                                            <option value="INR">INR - Indian Rupee</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_term" class="form-label">Payment Term</label>
                                        <select id="payment_term" name="payment_term" class="form-control">
                                            <option value="Monthly">Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Bi-yearly">Bi-yearly (2 payments)</option>
                                            <option value="One-time">One-time</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="service_description" class="form-label">Service Description</label>
                                        <textarea id="service_description" name="service_description" class="form-control" rows="3"><?php echo htmlspecialchars($service_description); ?></textarea>
                                        <div class="form-text">Detailed description of the service to be provided</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg me-2">
                                        <i class="bi bi-check-circle me-1"></i> Add Client
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container - Positioned fixed at bottom right -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Success!</strong> Client added successfully.
                <br><small class="text-white-50">Redirecting to clients list...</small>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

// Update emirate/zone options based on country selection
function updateEmirateZoneOptions(country) {
    const datalist = document.getElementById('emirate_zone_list');
    datalist.innerHTML = '';
    if (country && countryZones[country]) {
        countryZones[country].forEach(zone => {
            const option = document.createElement('option');
            option.value = zone;
            datalist.appendChild(option);
        });
    }
}

// Country change event
document.getElementById('country').addEventListener('change', function() {
    const country = this.value;
    updateEmirateZoneOptions(country);
    
    // Filter jurisdictions based on selected country
    const jurisdictionInput = document.getElementById('jurisdiction');
    fetch(`get_jurisdictions.php?country=${encodeURIComponent(country)}`)
        .then(response => response.json())
        .then(data => {
            if (jurisdictionInput.tagName.toLowerCase() === 'input') {
                const datalist = document.getElementById('jurisdiction_list');
                datalist.innerHTML = '';
                if (data.jurisdictions && data.jurisdictions.length > 0) {
                    data.jurisdictions.forEach(jur => {
                        const option = document.createElement('option');
                        option.value = jur.jurisdiction_name;
                        datalist.appendChild(option);
                    });
                }
            }
        })
        .catch(error => console.error('Error fetching jurisdictions:', error));
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
    
    const mobileRegex = /^[0-9]{8,}$/;
    if (!mobileRegex.test(mobile.replace(/[^0-9]/g, ''))) {
        e.preventDefault();
        alert('Please enter a valid mobile number (at least 8 digits)');
        return;
    }
});

// Show toast and redirect after successful client addition
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($show_toast): ?>
        const toastElement = document.getElementById('successToast');
        const toast = new bootstrap.Toast(toastElement, {
            autohide: false
        });
        toast.show();
        setTimeout(function() {
            window.location.href = 'clients.php';
        }, 3000);
    <?php endif; ?>
    
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
</script>