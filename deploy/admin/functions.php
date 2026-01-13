<?php
// Reusable session inactivity timeout checker
function enforce_session_timeout($timeout_seconds = 1800, $redirect = '../index.php?error=session&reason=inactivity') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Only enforce for logged-in users
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $now = time();
    if (isset($_SESSION['last_activity'])) {
        $elapsed = $now - intval($_SESSION['last_activity']);
        if ($elapsed > intval($timeout_seconds)) {
            // expire session and redirect
            session_unset();
            session_destroy();
            header("Location: {$redirect}");
            exit();
        }
    }

    // update last activity timestamp
    $_SESSION['last_activity'] = $now;
}

// Function to insert/update categories
function insert_categories() {
    global $connection;

    if(isset($_POST['submit'])) {
        $cat_title = mysqli_real_escape_string($connection, $_POST['cat_title']);
        $cat_price = isset($_POST['cat_price']) ? floatval($_POST['cat_price']) : 0.00;
        $cat_id = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

        if($cat_title == "" || empty($cat_title)) {
            echo "<script>showAlert('Category title should not be empty', 'error');</script>";
        } else if($cat_price < 0) {
            echo "<script>showAlert('Price cannot be negative', 'error');</script>";
        } else {
            if ($cat_id > 0) {
                // Update existing category
                $query = "UPDATE categories SET cat_title = '{$cat_title}', cat_price = {$cat_price} WHERE cat_id = {$cat_id}";
                $success_message = "Category updated successfully!";
                $redirect_param = "updated=true";
            } else {
                // Insert new category
                $query = "INSERT INTO categories(cat_title, cat_price) VALUES('{$cat_title}', {$cat_price})";
                $success_message = "Category added successfully!";
                $redirect_param = "added=true";
            }

            $category_query = mysqli_query($connection, $query);

            if(!$category_query) {
                die('Query Failed' . mysqli_error($connection));
            } else {
                // Use JavaScript redirect instead of header redirect
                echo "<script>window.location.href = 'categories.php?{$redirect_param}';</script>";
                exit;
            }
        }
    }
}

//////////////// FIND CATEGORY BY ID //////////////////
function findCategoryById($cat_id) {
    global $connection;
    
    $query = "SELECT * FROM categories WHERE cat_id = " . intval($cat_id);
    $result = mysqli_query($connection, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

//////////////// GET CATEGORY PRICE //////////////////
function getCategoryPrice($cat_id) {
    global $connection;
    
    $query = "SELECT cat_price FROM categories WHERE cat_id = " . intval($cat_id);
    $result = mysqli_query($connection, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return floatval($row['cat_price']);
    }
    
    return 0.00;
}

//////////////// FIND ALL CATEGORIES //////////////////
function findAllCategories() {
    global $connection;

    $query = "SELECT * FROM categories ORDER BY cat_id DESC";
    $select_all_categories_query = mysqli_query($connection, $query);

    while($row = mysqli_fetch_assoc($select_all_categories_query)) {
        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];
        $cat_price = isset($row['cat_price']) ? number_format($row['cat_price'], 2) : '0.00';

        echo "<tr>";
        echo "<td>{$cat_id}</td>";
        echo "<td>{$cat_title}</td>";
        echo "<td>AED " . $cat_price . "</td>";
        echo "<td class='action-links'>";
        echo "<a href='categories.php?edit={$cat_id}'><i class='bi bi-pencil'></i> Edit</a>";
        echo "<a href='' data-bs-toggle='modal' data-bs-target='#confirmDeleteModalCategory' data-id='{$cat_id}' data-name='{$cat_title}' onclick='setDeleteId({$cat_id}, \"{$cat_title}\")'><i class='bi bi-trash'></i> Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}

// Function to delete categories
function deleteCategory() {
    global $connection;

    if (isset($_GET['delete_category'])) {
        $cat_id = intval($_GET['delete_category']); // Sanitize the input

        // Delete query
        $query = "DELETE FROM categories WHERE cat_id = {$cat_id}";
        $delete_query = mysqli_query($connection, $query);

        if ($delete_query) {
            // Use JavaScript redirect instead of header redirect
            echo "<script>window.location.href = 'categories.php?deleted=true';</script>";
            exit;
        } else {
            // Handle deletion error
            echo "<script>window.location.href = 'categories.php?error=true';</script>";
            exit;
        }
    }
}


////////////////////////

//BANK ACCOUNTS LOGIC 

//////////////////

function checkBankAccountsSession() {
    if(!isset($_SESSION['bank_accounts_access']) || !$_SESSION['bank_accounts_access']) {
        header('Location: dashboard.php?session_expired=true');
        exit;
    }
    
    // Check inactivity (30 seconds)
    $inactive_time = 30;
    if(isset($_SESSION['bank_accounts_last_activity']) && 
       (time() - $_SESSION['bank_accounts_last_activity']) > $inactive_time) {
        
        unset($_SESSION['bank_accounts_access']);
        unset($_SESSION['bank_accounts_last_activity']);
        
        header('Location: dashboard.php?session_expired=true');
        exit;
    }
    
    // Update last activity
    $_SESSION['bank_accounts_last_activity'] = time();
}

function insert_bank_account() {
    global $connection;
    
    // Check session before proceeding
    checkBankAccountsSession();

    if(isset($_POST['submit_bank_account'])) {
        $bank_name = mysqli_real_escape_string($connection, $_POST['bank_name']);
        $account_name = mysqli_real_escape_string($connection, $_POST['account_name']);
        $account_number = mysqli_real_escape_string($connection, $_POST['account_number']);
        $iban_number = mysqli_real_escape_string($connection, $_POST['iban_number']);
        $swift_code = mysqli_real_escape_string($connection, $_POST['swift_code']);
        $bank_country = mysqli_real_escape_string($connection, $_POST['bank_country']);
        $bank_address = mysqli_real_escape_string($connection, $_POST['bank_address']);
        $currency = mysqli_real_escape_string($connection, $_POST['currency']);
        $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;

        if(empty($bank_name) || empty($account_name) || empty($account_number) || empty($bank_country)) {
            echo "<script>showAlert('All required fields must be filled', 'error');</script>";
        } else {
            if ($account_id > 0) {
                // Update existing account
                $query = "UPDATE bank_accounts SET 
                         bank_name = '{$bank_name}', 
                         account_name = '{$account_name}', 
                         account_number = '{$account_number}', 
                         iban_number = '{$iban_number}', 
                         swift_code = '{$swift_code}', 
                         bank_country = '{$bank_country}', 
                         bank_address = '{$bank_address}', 
                         currency = '{$currency}' 
                         WHERE account_id = {$account_id}";
                $success_message = "Bank account updated successfully!";
                $redirect_param = "updated=true";
            } else {
                // Insert new account
                $query = "INSERT INTO bank_accounts(
                         bank_name, account_name, account_number, iban_number, 
                         swift_code, bank_country, bank_address, currency) 
                         VALUES(
                         '{$bank_name}', '{$account_name}', '{$account_number}', '{$iban_number}', 
                         '{$swift_code}', '{$bank_country}', '{$bank_address}', '{$currency}')";
                $success_message = "Bank account added successfully!";
                $redirect_param = "added=true";
            }

            $bank_account_query = mysqli_query($connection, $query);

            if(!$bank_account_query) {
                die('Query Failed: ' . mysqli_error($connection));
            } else {
                echo "<script>showAlert('{$success_message}', 'success');</script>";
                echo "<script>setTimeout(() => { window.location.href = 'bank_accounts.php?{$redirect_param}'; }, 1000);</script>";
                exit;
            }
        }
    }
}

function findAllBankAccounts() {
    global $connection;

    $query = "SELECT * FROM bank_accounts ORDER BY account_id DESC";
    $select_all_accounts_query = mysqli_query($connection, $query);

    while($row = mysqli_fetch_assoc($select_all_accounts_query)) {
        $account_id = $row['account_id'];
        $bank_name = $row['bank_name'];
        $account_name = $row['account_name'];
        $account_number = $row['account_number'];
        $iban_number = $row['iban_number'];
        $swift_code = $row['swift_code'];
        $bank_country = $row['bank_country'];
        $currency = $row['currency'];
        $is_active = $row['is_active'];

        echo "<tr>";
        echo "<td>{$account_id}</td>";
        echo "<td>{$bank_name}</td>";
        echo "<td>{$account_name}</td>";
        echo "<td>{$account_number}</td>";
        echo "<td>{$iban_number}</td>";
        echo "<td>{$swift_code}</td>";
        echo "<td>{$bank_country}</td>";
        echo "<td>{$currency}</td>";
        echo "<td>" . ($is_active ? 'Active' : 'Inactive') . "</td>";
        echo "<td class='action-links'>";
        echo "<a href='includes/edit_bank_account.php?edit={$account_id}' onclick='return verifyAccess()'><i class='bi bi-pencil'></i> Edit</a>";
        echo "<a href='' data-bs-toggle='modal' data-bs-target='#confirmDeleteModalBankAccount' data-id='{$account_id}' data-name='{$bank_name} - {$account_name}' onclick='setDeleteBankAccountId({$account_id}, \"{$bank_name} - {$account_name}\")'><i class='bi bi-trash'></i> Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}

function deleteBankAccount() {
    global $connection;
    
    // Check session before proceeding
    checkBankAccountsSession();
    
    if(isset($_GET['delete'])) {
        $account_id = $_GET['delete'];
        
        $query = "DELETE FROM bank_accounts WHERE account_id = {$account_id}";
        $delete_query = mysqli_query($connection, $query);
        
        if(!$delete_query) {
            die('Query Failed: ' . mysqli_error($connection));
        } else {
            echo "<script>showAlert('Bank account deleted successfully!', 'success');</script>";
            echo "<script>setTimeout(() => { window.location.href = 'bank_accounts.php?deleted=true'; }, 1000);</script>";
            exit;
        }
    }
}

function verifyBankAccess() {
    // Don't call session_start() here if it's already called in your pages
    
    // Check if user has access
    if(!isset($_SESSION['bank_accounts_access']) || !$_SESSION['bank_accounts_access']) {
        echo "<script>
            alert('Session expired or access denied. Please authenticate again.');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
    
    // Check inactivity (30 seconds)
    $inactive_time = 30;
    if(isset($_SESSION['bank_accounts_last_activity']) && 
       (time() - $_SESSION['bank_accounts_last_activity']) > $inactive_time) {
        
        unset($_SESSION['bank_accounts_access']);
        unset($_SESSION['bank_accounts_last_activity']);
        
        echo "<script>
            alert('Session expired due to inactivity. Please authenticate again.');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
    
    // Update last activity
    $_SESSION['bank_accounts_last_activity'] = time();
}


//////////////// CLIENT MANAGEMENT FUNCTIONS ////////////////

// Function to insert/update clients
// Function to insert/update clients
function insert_clients() {
    global $connection;

    if (isset($_POST['submit_client'])) {
        // Sanitize and validate inputs
        $client_id = intval($_POST['client_id'] ?? 0);
        $company_name = mysqli_real_escape_string($connection, trim($_POST['company_name']));
        $trade_license_no = mysqli_real_escape_string($connection, trim($_POST['trade_license_no'] ?? ''));
        $country = mysqli_real_escape_string($connection, trim($_POST['country']));
        $emirate_zone = mysqli_real_escape_string($connection, trim($_POST['emirate_zone'] ?? ''));
        $business_activity = mysqli_real_escape_string($connection, trim($_POST['business_activity'] ?? ''));
        $address = mysqli_real_escape_string($connection, trim($_POST['address'] ?? ''));
        $contact_name = mysqli_real_escape_string($connection, trim($_POST['contact_name']));
        $contact_designation = mysqli_real_escape_string($connection, trim($_POST['contact_designation'] ?? ''));
        $contact_mobile = mysqli_real_escape_string($connection, trim($_POST['contact_mobile']));
        $contact_email = mysqli_real_escape_string($connection, trim($_POST['contact_email']));
        $service_id = intval($_POST['service_id'] ?? 0);
        $service_description = mysqli_real_escape_string($connection, trim($_POST['service_description'] ?? ''));
        $expected_start_date = mysqli_real_escape_string($connection, trim($_POST['expected_start_date'] ?? ''));
        $payment_currency = mysqli_real_escape_string($connection, trim($_POST['payment_currency'] ?? 'AED'));
        $payment_term = mysqli_real_escape_string($connection, trim($_POST['payment_term'] ?? 'Monthly'));
        $service_total_fee = floatval($_POST['service_total_fee'] ?? 0.00);
        $lead_source = mysqli_real_escape_string($connection, trim($_POST['lead_source'] ?? 'website'));
        $client_status = mysqli_real_escape_string($connection, trim($_POST['client_status'] ?? 'New Lead'));

        // Validation
        if (empty($company_name) || empty($contact_name) || empty($contact_mobile) || empty($contact_email)) {
            echo "<script>showAlert('Please fill in all required fields', 'error');</script>";
            return;
        }

        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>showAlert('Please enter a valid email address', 'error');</script>";
            return;
        }

        if ($client_id > 0) {
            // Update existing client
            $query = "UPDATE clients SET 
                     company_name = '{$company_name}', 
                     trade_license_no = '{$trade_license_no}', 
                     country = '{$country}', 
                     emirate_zone = '{$emirate_zone}', 
                     business_activity = '{$business_activity}', 
                     address = '{$address}', 
                     contact_name = '{$contact_name}', 
                     contact_designation = '{$contact_designation}', 
                     contact_mobile = '{$contact_mobile}', 
                     contact_email = '{$contact_email}', 
                     service_id = {$service_id}, 
                     service_description = '{$service_description}', 
                     expected_start_date = " . ($expected_start_date ? "'{$expected_start_date}'" : "NULL") . ", 
                     payment_currency = '{$payment_currency}', 
                     payment_term = '{$payment_term}', 
                     service_total_fee = {$service_total_fee}, 
                     lead_source = '{$lead_source}',
                     client_status = '{$client_status}'
                     WHERE client_id = {$client_id}";

            $success_message = "Client updated successfully!";
            $redirect_param = "updated=true";
        } else {
            echo "<script>showAlert('Invalid client ID', 'error');</script>";
            return;
        }

        $client_query = mysqli_query($connection, $query);

        if (!$client_query) {
            die('Query Failed: ' . mysqli_error($connection));
        } else {
            echo "<script>showAlert('{$success_message}', 'success');</script>";
            echo "<script>setTimeout(() => { window.location.href = 'clients.php?{$redirect_param}'; }, 1500);</script>";
        }
    }
}

// Function to get all clients
function findAllClients() {
    global $connection;

    $query = "SELECT c.*, cat.cat_title as service_name, u.first_name, u.last_name 
              FROM clients c 
              LEFT JOIN categories cat ON c.service_id = cat.cat_id 
              LEFT JOIN users u ON c.assigned_sales_id = u.user_id 
              ORDER BY c.created_at DESC";
    
    $result = mysqli_query($connection, $query);

    if(!$result) {
        die("Query Failed: " . mysqli_error($connection));
    }

    while($row = mysqli_fetch_assoc($result)) {
        $client_id = $row['client_id'];
        $company_name = htmlspecialchars($row['company_name']);
        $contact_name = htmlspecialchars($row['contact_name']);
        $contact_email = htmlspecialchars($row['contact_email']);
        $contact_mobile = htmlspecialchars($row['contact_mobile']);
        $service_name = $row['service_name'] ? htmlspecialchars($row['service_name']) : 'N/A';
        $client_status = $row['client_status'];
        $created_at = date('M j, Y', strtotime($row['created_at']));
        $sales_person = $row['first_name'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : 'N/A';

        // Status badge color
        $status_class = getStatusBadgeClass($client_status);

        echo "<tr>";
        echo "<td>{$client_id}</td>";
        echo "<td><strong>{$company_name}</strong></td>";
        echo "<td>{$contact_name}</td>";
        echo "<td>{$contact_email}</td>";
        echo "<td>{$contact_mobile}</td>";
        echo "<td>{$service_name}</td>";
        echo "<td><span class='badge {$status_class}'>{$client_status}</span></td>";
        echo "<td>{$sales_person}</td>";
        echo "<td>{$created_at}</td>";
        echo "<td class='action-links'>";
        
       // Action buttons container - icon only version
        echo "<div class='d-flex gap-1'>";
            
            // View button
            echo "<a href='' data-bs-toggle='modal' data-bs-target='#clientDetailsModal' data-id='{$client_id}' onclick='loadClientDetails({$client_id})' class='btn btn-light btn-sm rounded-circle p-2' title='View Details' data-bs-toggle='tooltip'>
                    <i class='bi bi-eye text-primary'></i>
                </a>";
            
            // Edit button
            echo "<a href='clients.php?source=edit_client&id={$client_id}' class='btn btn-light btn-sm rounded-circle p-2' title='Edit' data-bs-toggle='tooltip'>
                    <i class='bi bi-pencil text-info'></i>
                </a>";
            
            // Review button (for Manager/CEO)
            if (shouldShowReviewButton($row)) {
                echo "<a href='' data-bs-toggle='modal' data-bs-target='#reviewModal' data-id='{$client_id}' onclick='loadReviewDetails({$client_id})' class='btn btn-light btn-sm rounded-circle p-2' title='Review' data-bs-toggle='tooltip'>
                        <i class='bi bi-clipboard-check text-warning'></i>
                    </a>";
            }

        echo "</div>";

        // Initialize tooltips if using that version
        echo "<script>
        $(document).ready(function(){
            $('[data-bs-toggle=\"tooltip\"]').tooltip();
        });
        </script>";
        
        echo "</td>";
        echo "</tr>";
    }
}

// Helper function for status badge classes
function getStatusBadgeClass($status) {
    $classes = [
        'New Lead' => 'bg-secondary',
        'Contacted' => 'bg-info',
        'Qualified' => 'bg-primary',
        'Proposal Drafted' => 'bg-warning',
        'Under Manager Review' => 'bg-warning text-dark',
        'Rejected by Manager' => 'bg-danger',
        'Approved by Manager' => 'bg-success',
        'Under CEO Review' => 'bg-warning text-dark',
        'Rejected by CEO' => 'bg-danger',
        'Final Proposal Ready' => 'bg-success',
        'Proposal Sent to Client' => 'bg-info',
        'Awaiting Client Action' => 'bg-warning',
        'Signed – Move to Finance' => 'bg-success'
    ];
    
    return $classes[$status] ?? 'bg-secondary';
}

// Helper function to check if review button should be shown
function shouldShowReviewButton($client) {
    $user_role = $_SESSION['user_role'] ?? '';
    $status = $client['client_status'];
    
    if ($user_role === 'manager' && $status === 'Under Manager Review') {
        return true;
    }
    
    if ($user_role === 'ceo' && $status === 'Under CEO Review') {
        return true;
    }
    
    return false;
}

// Function to calculate payment breakdown
function calculatePaymentBreakdown($total_amount, $payment_term) {
    $breakdown = [];
    
    switch($payment_term) {
        case 'Monthly':
            $installments = 12;
            break;
        case 'Quarterly':
            $installments = 4;
            break;
        case 'Bi-yearly':
            $installments = 2;
            break;
        case 'One-time':
            $installments = 1;
            break;
        default:
            $installments = 1;
    }
    
    $installment_amount = $total_amount / $installments;
    
    for($i = 1; $i <= $installments; $i++) {
        $breakdown[] = [
            'installment' => $i,
            'amount' => number_format($installment_amount, 2),
            'due_description' => getDueDescription($payment_term, $i)
        ];
    }
    
    return $breakdown;
}

// Helper function for due descriptions
function getDueDescription($payment_term, $installment) {
    $descriptions = [
        'Monthly' => "Month $installment",
        'Quarterly' => "Quarter $installment",
        'Bi-yearly' => "Half $installment",
        'One-time' => "One-time payment"
    ];
    
    return $descriptions[$payment_term] ?? "Payment $installment";
}
?>
