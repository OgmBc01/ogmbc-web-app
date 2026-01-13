<?php
session_start();
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Check access for main page
if(!isset($_SESSION['bank_accounts_access']) || !$_SESSION['bank_accounts_access']) {
    header('Location: dashboard.php');
    exit;
}

// Update last activity time on page load
$_SESSION['bank_accounts_last_activity'] = time();
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <h1 class="page-title">Bank Accounts Management</h1>
    
        <!-- Alert Messages -->
        <div id="alertBox"></div>
        
        <div class="content-wrapper">
            <!-- Add Bank Account Button -->
            <div class="mb-4">
                <button type="button" class="btn btn-primary" id="showBankAccountForm">
                    <i class="bi bi-plus-circle"></i> Add New Bank Account
                </button>
            </div>
            
            <!-- Table Section (Now on top) -->
            <div class="table-section mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list-ul"></i> Existing Bank Accounts
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Bank Name</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>IBAN</th>
                                    <th>SWIFT</th>
                                    <th>Country</th>
                                    <th>Currency</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php  
                                // Call the function
                                findAllBankAccounts();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Form Section (Initially hidden) -->
            <div class="form-section" id="bankAccountFormSection" style="display: none;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-bank"></i> Add New Bank Account
                        </div>
                        <button type="button" class="btn-close" id="hideBankAccountForm"></button>
                    </div>
                    
                    <form action="" method="post" id="bankAccountForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name *</label>
                                    <input type="text" id="bank_name" name="bank_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_name">Account Name *</label>
                                    <input type="text" id="account_name" name="account_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number">Account Number *</label>
                                    <input type="text" id="account_number" name="account_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="iban_number">IBAN Number</label>
                                    <input type="text" id="iban_number" name="iban_number" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="swift_code">SWIFT/BIC Code</label>
                                    <input type="text" id="swift_code" name="swift_code" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_country">Bank Country *</label>
                                    <select id="bank_country" name="bank_country" class="form-control" required>
                                        <option value="">Select Country</option>
                                        <option value="UAE">United Arab Emirates</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="USA">United States</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="QA">Qatar</option>
                                        <option value="KW">Kuwait</option>
                                        <option value="OM">Oman</option>
                                        <option value="BH">Bahrain</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bank_address">Bank Address</label>
                            <textarea id="bank_address" name="bank_address" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="currency">Currency *</label>
                            <select id="currency" name="currency" class="form-control" required>
                                <option value="USD">USD</option>
                                <option value="AED">AED</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="SAR">SAR</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="submit_bank_account" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Add Bank Account
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelBankAccountForm">
                                Cancel
                            </button>
                        </div>
                    </form>
                    <?php
                    // Call the function
                    insert_bank_account();
                    ?>
                </div>
            </div>
        </div>
    
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="confirmDeleteModalBankAccount" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the bank account "<span id="bankAccountName"></span>"?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="deleteBankAccount()">Delete</button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        // Call the function
        deleteBankAccount();
        ?>
    </div>
</div>

<script>
// JavaScript for bank accounts functionality
let deleteBankAccountId = null;

function setDeleteBankAccountId(id, name) {
    deleteBankAccountId = id;
    document.getElementById('bankAccountName').textContent = name;
}

function deleteBankAccount() {
    if(deleteBankAccountId) {
        window.location.href = 'bank_accounts.php?delete=' + deleteBankAccountId;
    }
}

// Form show/hide functionality
document.addEventListener('DOMContentLoaded', function() {
    const showFormBtn = document.getElementById('showBankAccountForm');
    const hideFormBtn = document.getElementById('hideBankAccountForm');
    const cancelFormBtn = document.getElementById('cancelBankAccountForm');
    const formSection = document.getElementById('bankAccountFormSection');
    const bankAccountForm = document.getElementById('bankAccountForm');
    
    // Show form
    showFormBtn.addEventListener('click', function() {
        formSection.style.display = 'block';
        showFormBtn.style.display = 'none';
        // Scroll to form
        formSection.scrollIntoView({ behavior: 'smooth' });
    });
    
    // Hide form
    function hideForm() {
        formSection.style.display = 'none';
        showFormBtn.style.display = 'block';
        bankAccountForm.reset();
    }
    
    hideFormBtn.addEventListener('click', hideForm);
    cancelFormBtn.addEventListener('click', hideForm);
    
    // Reset form on successful submission
    <?php if(isset($_GET['added']) && $_GET['added'] == 'true'): ?>
        hideForm();
    <?php endif; ?>
});

// Activity tracker for session timeout
let activityTimer;

function resetActivityTimer() {
    clearTimeout(activityTimer);
    
    // Set timeout for 35 seconds of inactivity
    activityTimer = setTimeout(() => {
        // Show session expiry warning
        if(confirm('Your session has expired due to inactivity. Would you like to continue?')) {
            // Reset activity and extend session
            updateLastActivity();
        } else {
            // Redirect to dashboard
            window.location.href = 'dashboard.php?session_expired=true';
        }
    }, 35000); // 35 seconds
}

function updateLastActivity() {
    // Send AJAX request to update last activity time
    fetch('update_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'update_activity=true'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            resetActivityTimer();
        }
    })
    .catch(error => {
        console.error('Activity update failed:', error);
    });
}

// Track user activity
document.addEventListener('DOMContentLoaded', function() {
    resetActivityTimer();
    
    // Reset timer on user activity
    document.addEventListener('mousemove', resetActivityTimer);
    document.addEventListener('keypress', resetActivityTimer);
    document.addEventListener('click', resetActivityTimer);
    document.addEventListener('scroll', resetActivityTimer);
    
    // Also update activity every 25 seconds to be safe
    setInterval(updateLastActivity, 25000);
});

// Update activity when form is interacted with
document.querySelectorAll('input, select, textarea, button').forEach(element => {
    element.addEventListener('focus', resetActivityTimer);
    element.addEventListener('change', resetActivityTimer);
});
</script>

<?php
include 'includes/footer.php';
?>