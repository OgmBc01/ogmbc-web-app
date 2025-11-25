<!DOCTYPE html>

<?php
session_start();
include '../../includes/database.php';
include '../functions.php';
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../resources/style.css">
</head>
<body>

<?php
include 'nav.php';
include 'sidebar.php';

verifyBankAccess();

// Check if edit parameter is provided
if(!isset($_GET['edit'])) {
    header("Location: ../bank_accounts.php?error=invalid_request");
    exit;
}

$account_id = intval($_GET['edit']);

// Fetch bank account data
$query = "SELECT * FROM bank_accounts WHERE account_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $account_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(!$row = mysqli_fetch_assoc($result)) {
    header("Location: ../bank_accounts.php?error=account_not_found");
    exit;
}

// Handle form submission
if(isset($_POST['submit_bank_account'])) {
    $bank_name = mysqli_real_escape_string($connection, $_POST['bank_name']);
    $account_name = mysqli_real_escape_string($connection, $_POST['account_name']);
    $account_number = mysqli_real_escape_string($connection, $_POST['account_number']);
    $iban_number = mysqli_real_escape_string($connection, $_POST['iban_number']);
    $swift_code = mysqli_real_escape_string($connection, $_POST['swift_code']);
    $bank_country = mysqli_real_escape_string($connection, $_POST['bank_country']);
    $bank_address = mysqli_real_escape_string($connection, $_POST['bank_address']);
    $currency = mysqli_real_escape_string($connection, $_POST['currency']);

    if(empty($bank_name) || empty($account_name) || empty($account_number) || empty($bank_country)) {
        $error_message = "All required fields must be filled";
    } else {
        // Update existing account
        $update_query = "UPDATE bank_accounts SET 
                        bank_name = '{$bank_name}', 
                        account_name = '{$account_name}', 
                        account_number = '{$account_number}', 
                        iban_number = '{$iban_number}', 
                        swift_code = '{$swift_code}', 
                        bank_country = '{$bank_country}', 
                        bank_address = '{$bank_address}', 
                        currency = '{$currency}' 
                        WHERE account_id = {$account_id}";
        $update_result = mysqli_query($connection, $update_query);

        if(!$update_result) {
            $error_message = "Update failed: " . mysqli_error($connection);
        } else {
            header("Location: ../bank_accounts.php?updated=true");
            exit;
        }
    }
}
?>

   <style>
        :root {
            --primary: #0b1224;
            --gold: #f1bf70;
            --muted: #94a3b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #1e293b);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gold), #e6b469);
            border: none;
            color: var(--primary);
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #e6b469, var(--gold));
            color: var(--primary);
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
        }
        
        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 0.2rem rgba(241, 191, 112, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>

<body>
 <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="container-fluid">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h4 mb-1" style="color: var(--primary);">
                            <i class="bi bi-bank me-2"></i>Edit Bank Account
                        </h2>
                        <p class="text-muted mb-0">Update bank account information</p>
                    </div>
                    <a href="../bank_accounts.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Accounts
                    </a>
                </div>

                <!-- Alert Messages -->
                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editing: <?php echo htmlspecialchars($row['bank_name']); ?> - <?php echo htmlspecialchars($row['account_name']); ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_name" class="form-label">Bank Name *</label>
                                        <input type="text" 
                                               id="bank_name" 
                                               name="bank_name" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($row['bank_name']); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_name" class="form-label">Account Name *</label>
                                        <input type="text" 
                                               id="account_name" 
                                               name="account_name" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($row['account_name']); ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="account_number" class="form-label">Account Number *</label>
                                        <input type="text" 
                                               id="account_number" 
                                               name="account_number" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($row['account_number']); ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="iban_number" class="form-label">IBAN Number</label>
                                        <input type="text" 
                                               id="iban_number" 
                                               name="iban_number" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($row['iban_number']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="swift_code" class="form-label">SWIFT/BIC Code</label>
                                        <input type="text" 
                                               id="swift_code" 
                                               name="swift_code" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($row['swift_code']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bank_country" class="form-label">Bank Country *</label>
                                        <select id="bank_country" name="bank_country" class="form-control" required>
                                            <option value="">Select Country</option>
                                            <option value="UAE" <?php echo $row['bank_country'] == 'UAE' ? 'selected' : ''; ?>>United Arab Emirates</option>
                                            <option value="UK" <?php echo $row['bank_country'] == 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                            <option value="USA" <?php echo $row['bank_country'] == 'USA' ? 'selected' : ''; ?>>United States</option>
                                            <option value="SA" <?php echo $row['bank_country'] == 'SA' ? 'selected' : ''; ?>>Saudi Arabia</option>
                                            <option value="QA" <?php echo $row['bank_country'] == 'QA' ? 'selected' : ''; ?>>Qatar</option>
                                            <option value="KW" <?php echo $row['bank_country'] == 'KW' ? 'selected' : ''; ?>>Kuwait</option>
                                            <option value="OM" <?php echo $row['bank_country'] == 'OM' ? 'selected' : ''; ?>>Oman</option>
                                            <option value="BH" <?php echo $row['bank_country'] == 'BH' ? 'selected' : ''; ?>>Bahrain</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bank_address" class="form-label">Bank Address</label>
                                <textarea id="bank_address" 
                                          name="bank_address" 
                                          class="form-control" 
                                          rows="3"><?php echo htmlspecialchars($row['bank_address']); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="currency" class="form-label">Currency *</label>
                                <select id="currency" name="currency" class="form-control" required>
                                    <option value="USD" <?php echo $row['currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
                                    <option value="AED" <?php echo $row['currency'] == 'AED' ? 'selected' : ''; ?>>AED</option>
                                    <option value="EUR" <?php echo $row['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    <option value="GBP" <?php echo $row['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP</option>
                                    <option value="SAR" <?php echo $row['currency'] == 'SAR' ? 'selected' : ''; ?>>SAR</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="../bank_accounts.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                                <button type="submit" name="submit_bank_account" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Update Bank Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Account Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Account ID:</strong> <?php echo $row['account_id']; ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge <?php echo $row['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></p>
                                <p><strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($row['updated_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
<?php
include 'footer.php';
?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Activity Tracker Script -->
    <script>
        let activityTimer;

        function resetActivityTimer() {
            clearTimeout(activityTimer);
            activityTimer = setTimeout(() => {
                if(confirm('Your session has expired due to inactivity. Would you like to continue?')) {
                    updateLastActivity();
                } else {
                    window.location.href = './dashboard.php?session_expired=true';
                }
            }, 35000);
        }

        function updateLastActivity() {
            fetch('../update_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'update_activity=true'
            }).then(() => resetActivityTimer());
        }

        // Track user activity
        document.addEventListener('DOMContentLoaded', function() {
            resetActivityTimer();
            
            const activityEvents = ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'];
            activityEvents.forEach(event => {
                document.addEventListener(event, resetActivityTimer);
            });
            
            setInterval(updateLastActivity, 25000);
        });
    </script>
