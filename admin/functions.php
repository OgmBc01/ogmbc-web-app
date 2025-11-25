<?php

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
    // Don't call session_start() here if it's already called in your pages
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

?>
