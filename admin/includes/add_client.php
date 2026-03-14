<?php
// Call the function
insert_clients();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
              
// Initialize variables with empty values
$company_name = $trade_license_no = $country = $emirate_zone = $business_activity = $address = '';
$contact_title = $contact_name = $contact_designation = $contact_mobile = $contact_email = '';
$service_id = $service_description = $expected_start_date = '';
$payment_currency = 'AED';
$payment_term = 'Monthly';
$service_total_fee = '0.00';
$lead_source = 'website';
$message = '';
$message_type = '';
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
                            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Success Modal -->
                        <div class="modal fade" id="clientSuccessModal" tabindex="-1" aria-labelledby="clientSuccessModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="clientSuccessModalLabel"><i class="bi bi-check-circle me-2"></i>Client Added Successfully</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p class="mb-3">The new client has been added to the database.</p>
                                        <a href="./clients.php" class="btn btn-success"><i class="bi bi-people me-1"></i> View All Clients</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="" id="clientForm">
                            <input type="hidden" name="submit_client" value="1">
                            
                            <!-- Company Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 text-primary">
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
                                    <h6 class="border-bottom pb-2 text-primary">
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
                                    <h6 class="border-bottom pb-2 text-primary">
                                        <i class="bi bi-briefcase me-2"></i>Service Details
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="service_id" class="form-label">Service Type</label>
                                        <select id="service_id" name="service_id" class="form-control">
                                            <option value="">Select Service</option>
                                            <?php
                                            $services_query = "SELECT * FROM categories ORDER BY cat_title";
                                            $services_result = mysqli_query($connection, $services_query);
                                            while($service = mysqli_fetch_assoc($services_result)) {
                                                $selected = ($service_id == $service['cat_id']) ? 'selected' : '';
                                                echo "<option value='{$service['cat_id']}' {$selected}>{$service['cat_title']} - AED {$service['cat_price']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
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
                                        <label for="expected_start_date" class="form-label">Expected Start Date</label>
                                        <input type="date" id="expected_start_date" name="expected_start_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($expected_start_date); ?>">
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
    'United States': ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', /* ... add more states ... */],
    'Germany': ['Baden-Württemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', /* ... add more states ... */],
    'France': ['Île-de-France', 'Auvergne-Rhône-Alpes', 'Nouvelle-Aquitaine', 'Occitanie', /* ... add more regions ... */],
    'China': ['Beijing', 'Shanghai', 'Guangdong', 'Zhejiang', 'Jiangsu', /* ... add more provinces ... */],
    'Japan': ['Tokyo', 'Osaka', 'Kyoto', 'Hokkaido', 'Okinawa', /* ... add more prefectures ... */],
    'India': ['Delhi', 'Maharashtra', 'Karnataka', 'Tamil Nadu', 'Uttar Pradesh', /* ... add more states ... */],
    'Russia': ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', /* ... add more regions ... */]
};


// Update emirate/zone options based on country selection or manual input
function updateEmirateZoneOptions(country) {
    const emirateInput = document.getElementById('emirate_zone');
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

document.getElementById('country').addEventListener('change', function() {
    const country = this.value;
    updateEmirateZoneOptions(country);
    // Filter jurisdictions based on selected country
    const jurisdictionSelect = document.getElementById('jurisdiction');
    fetch(`get_jurisdictions.php?country=${encodeURIComponent(country)}`)
        .then(response => response.json())
        .then(data => {
            // Only update datalist if jurisdiction is an input
            if (jurisdictionSelect.tagName.toLowerCase() === 'input') {
                const datalist = document.getElementById('jurisdiction_list');
                datalist.innerHTML = '';
                if (data.jurisdictions) {
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

// Auto-fill service price when service is selected
document.getElementById('service_id').addEventListener('change', function() {
    const serviceId = this.value;
    const serviceFeeInput = document.getElementById('service_total_fee');
    
    if (serviceId) {
        // You can implement AJAX call to get service price here
        // For now, we'll use a simple approach - you can enhance this
        fetch('get_service_price.php?service_id=' + serviceId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.price > 0) {
                    serviceFeeInput.value = data.price;
                }
            })
            .catch(error => {
                console.error('Error fetching service price:', error);
            });
    }
});

// Form validation
document.getElementById('clientForm').addEventListener('submit', function(e) {
    const email = document.getElementById('contact_email').value;
    const mobile = document.getElementById('contact_mobile').value;
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        showAlert('Please enter a valid email address', 'error');
        return;
    }
    
    // Basic mobile validation (at least 8 digits)
    const mobileRegex = /^[0-9]{8,}$/;
    if (!mobileRegex.test(mobile.replace(/[^0-9]/g, ''))) {
        e.preventDefault();
        showAlert('Please enter a valid mobile number (at least 8 digits)', 'error');
        return;
    }
});

// Show success modal if redirected after add
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('added') === 'true') {
        const modal = new bootstrap.Modal(document.getElementById('clientSuccessModal'));
        modal.show();
    }
});
</script>
</script>