
DESCRIBE clients;
[ Edit inline ] [ Edit ] [ Create PHP code ]
Field
Type
Null
Key
Default
Extra
client_id
int
NO
PRI
NULL
auto_increment
company_name
varchar(255)
NO
NULL
trade_license_no
varchar(100)
YES
NULL
country
varchar(100)
NO
NULL
emirate_zone
varchar(100)
YES
NULL
business_activity
text
YES
NULL
address
text
YES
NULL
contact_name
varchar(255)
NO
NULL
contact_designation
varchar(100)
YES
NULL
contact_mobile
varchar(20)
NO
NULL
contact_email
varchar(255)
NO
NULL
client_password
varchar(255)
YES
NULL
service_id
int
YES
MUL
NULL
service_description
text
YES
NULL
expected_start_date
date
YES
NULL
payment_currency
varchar(10)
YES
AED
payment_term
enum('Monthly','Quarterly','Bi-yearly','One-time')
YES
Monthly
service_total_fee
decimal(10,2)
YES
0.00
lead_source
enum('referral','website','digital_marketing','event')
YES
website
client_status
enum('New Lead','Contacted','Qualified','Proposal Drafted','Under Manager Review','Rejected by Manager','Approved by Manager','Under CEO Review','Rejected by CEO','Final Proposal Ready','Proposal Sent to Client','Awaiting Client Action','Signed – Move to Finance')
YES
New Lead
assigned_sales_id
int
YES
MUL
NULL
created_by
int
YES
MUL
NULL
created_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
NO
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP


Field
Type
Null
Key
Default
Extra
cat_id
int
NO
PRI
NULL
auto_increment
cat_title
varchar(255)
NO
NULL
cat_price
decimal(10,2)
YES
0.00

DESCRIBE departments;
[ Edit inline ] [ Edit ] [ Create PHP code ]
Field
Type
Null
Key
Default
Extra
id
int
NO
PRI
NULL
auto_increment
dept_name
varchar(100)
NO
NULL
dept_code
varchar(10)
NO
UNI
NULL
manager
varchar(100)
YES
NULL
budget
decimal(15,2)
NO
NULL
location
varchar(200)
YES
NULL
description
text
YES
NULL
created_at
timestamp
YES
CURRENT_TIMESTAMP
DEFAULT_GENERATED
updated_at
timestamp
YES
CURRENT_TIMESTAMP
DEFAULT_GENERATED on update CURRENT_TIMESTAMP


DESCRIBE employees;
[ Edit inline ] [ Edit ] [ Create PHP code ]
Field
Type
Null
Key
Default
Extra
employee_id
int
NO
PRI
NULL
auto_increment
user_id
int
NO
MUL
NULL
user_email
varchar(100)
NO
UNI
NULL
user_type
varchar(20)
NO
employee
password
varchar(255)
NO
NULL
first_name
varchar(50)
NO
NULL
last_name
varchar(50)
NO
NULL
user_image
varchar(50)
NO
NULL
field_of_study
varchar(100)
YES
NULL
department_id
int
YES
MUL
NULL
qualification
varchar(100)
YES
NULL
highest_graduation
varchar(100)
YES
NULL
year_of_graduation
year
YES
NULL
salary
decimal(10,2)
YES
0.00
created_at
datetime
YES
CURRENT_TIMESTAMP
DEFAULT_GENERATED


Now based on the above tables schema, I want to create a new module 'Engagement'. This is where I want to view all the services requested by the clients, then assign the task to the relevant employee (by first selecting the department from the auto populated field, then as per the department the users shud show in the next employee auto populated field).

I need to set all the necessary parameters like start date, deadline, and any other important detail.

I also want to make it possible for an employee to request for a deadline change.

Alos, there is a very important module I want to create and integrate with the Engagement module, which is the 'Point Ledger and Cash Reward Module' (you can shorten this name but make sure it understandable). It is integral part of this Engagement module since an employee assigned an engagement/task can earn redeemable points upon completion within specified deadlines.

But I want us to create this bit by bit, and first I just want you to get the idea of the whole concept, then we will create the modules one by one and integrate them slowly.

Now if you understand me up to this point, record and store everything i mentioned in your memory. Next what I would like us to do is will provide you with the logical calculations and breakdown of how I want the Point Ledger and Cash Reward system to work.

So that we can then decide on two things at the end. How we handle these logical calculations and breakdowns (through creating a database to store and update them on demand or by manually constructing them in our backend code logic)? And secondly, most importantly how we can integrate the logical calculations and breakdown of the Point Ledger and Cash Reward system into our Engagement module.

Are you ready to take in and analyze the logical calculations and breakdown of the Point Ledger and Cash Reward system?




Done!

So in our next steps (Services Configuration Module) and subsequent, I need you to apply this code structure, so that everything will align with my current system structure, styling and theme.

each unique module shud has a master page, example;
user.php;
<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   HANDLE USER DELETION
=================================*/
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot delete your own account.";
    } else {
        // First check if user exists
        $check_query = "SELECT user_id, username FROM users WHERE user_id = $user_id";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $user = mysqli_fetch_assoc($check_result);
            
            // Delete the user
            $delete_query = "DELETE FROM users WHERE user_id = $user_id";
            if (mysqli_query($connection, $delete_query)) {
                $_SESSION['success_message'] = "User '" . $user['username'] . "' deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Error deleting user: " . mysqli_error($connection);
            }
        } else {
            $_SESSION['error_message'] = "User not found.";
        }
    }
    
    // Redirect back to users page
    header("Location: users.php");
    exit();
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>

        <!-- Alert Messages Container for AJAX -->
        <div id="alertBox"></div>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all';
                }

                switch($source) {
                    case 'add_user';
                        include "includes/add_user.php";
                        break;
                    case 'edit_user';
                        include "includes/edit_user.php";
                        break;
                    default:
                        include "includes/view_all_users.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user "<span id="deleteUserName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteUserId = null;

// Show delete confirmation modal
function confirmDelete(id, name) {
    deleteUserId = id;
    document.getElementById('deleteUserName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Handle delete confirmation with AJAX
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!deleteUserId) return;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    // Show loading state
    showAlert('Deleting user...', 'info');
    
    // Send AJAX request
    fetch('includes/delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + deleteUserId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Remove the deleted user row from table
            const row = document.querySelector(`button[onclick="confirmDelete(${deleteUserId}, '")]`).closest('tr');
            if (row) {
                row.remove();
            }
            // Refresh page after 2 seconds to update counts
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        showAlert('Error: ' + error.message, 'danger');
    });
});

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
        // Create alert box if it doesn't exist
        const container = document.querySelector('.container-fluid');
        const div = document.createElement('div');
        div.id = 'alertBox';
        div.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        container.prepend(div);
    } else {
        alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
    }
}

// View user details
function viewUser(id) {
    const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    const contentDiv = document.getElementById('userDetailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading user details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/get_user_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                const roleBadge = user.role_name ? 
                    `<span class="badge bg-info">${user.role_name}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const typeBadge = user.type_name ? 
                    `<span class="badge bg-success">${user.type_name}</span>` : 
                    '<span class="badge bg-secondary">Not Assigned</span>';
                
                const statusBadge = user.user_status == 'active' ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-warning">Inactive</span>';
                
                contentDiv.innerHTML = `
                    <div class="text-center mb-3">
                        <img src="../images/${user.user_image || 'default.jpg'}" class="rounded-circle" width="100" height="100" alt="User Image">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User ID:</strong> ${user.user_id}</p>
                            <p><strong>Username:</strong> ${user.username}</p>
                            <p><strong>Full Name:</strong> ${user.first_name} ${user.last_name}</p>
                            <p><strong>Email:</strong> ${user.user_email}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Role:</strong> ${roleBadge}</p>
                            <p><strong>Type:</strong> ${typeBadge}</p>
                            <p><strong>Status:</strong> ${statusBadge}</p>
                            <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                `;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

</script>

<?php include "includes/footer.php"; ?>


includes/add_user.php
<?php
ob_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$username = $first_name = $last_name = $user_email = '';
$role_id = $type_id = '';
$user_status = 'active';
$message = '';
$message_type = '';
$showSuccessModal = false;
$new_user_id = null;

// Fetch roles for dropdown
$roles_query = "SELECT * FROM user_roles ORDER BY role_level DESC";
$roles_result = mysqli_query($connection, $roles_query);

// Fetch types for dropdown
$types_query = "SELECT * FROM user_types ORDER BY type_name";
$types_result = mysqli_query($connection, $types_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user'])) {
    
    $username = mysqli_real_escape_string($connection, trim($_POST['username']));
    $first_name = mysqli_real_escape_string($connection, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($connection, trim($_POST['last_name']));
    $user_email = mysqli_real_escape_string($connection, trim($_POST['user_email']));
    $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : 'NULL';
    $type_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : 'NULL';
    $user_status = mysqli_real_escape_string($connection, $_POST['user_status']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle file upload
    $user_image = $_FILES['user_image']['name'];
    $user_image_temp = $_FILES['user_image']['tmp_name'];
    
    // Validation
    if (empty($username) || empty($first_name) || empty($last_name) || empty($user_email) || empty($password)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } else {
        
        // Check if username exists
        $check_username = "SELECT user_id FROM users WHERE username = '$username'";
        $username_result = mysqli_query($connection, $check_username);
        
        // Check if email exists
        $check_email = "SELECT user_id FROM users WHERE user_email = '$user_email'";
        $email_result = mysqli_query($connection, $check_email);
        
        if (mysqli_num_rows($username_result) > 0) {
            $message = "Username already exists. Please choose another.";
            $message_type = "danger";
        } elseif (mysqli_num_rows($email_result) > 0) {
            $message = "Email already exists. Please use another email.";
            $message_type = "danger";
        } else {
            // Upload image
            if (!empty($user_image)) {
                $target_dir = "../images/";
                $image_name = time() . '_' . basename($user_image);
                $target_file = $target_dir . $image_name;
                
                if (move_uploaded_file($user_image_temp, $target_file)) {
                    $user_image = $image_name;
                } else {
                    $user_image = '';
                }
            } else {
                $user_image = '';
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $role_id_value = ($role_id !== 'NULL') ? $role_id : 'NULL';
            $type_id_value = ($type_id !== 'NULL') ? $type_id : 'NULL';
            
            $insert_query = "INSERT INTO users (username, first_name, last_name, user_email, password, 
                              user_image, role_id, type_id, user_status) 
                             VALUES ('$username', '$first_name', '$last_name', '$user_email', '$hashed_password', 
                                     '$user_image', $role_id_value, $type_id_value, '$user_status')";
            
            if (mysqli_query($connection, $insert_query)) {
                $new_user_id = mysqli_insert_id($connection);
                $showSuccessModal = true;
                // Clear form data
                $username = $first_name = $last_name = $user_email = '';
                $role_id = $type_id = '';
                $user_status = 'active';
                // No redirect
            } else {
                $message = "Error adding user: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}

ob_end_flush();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New User</h5>
                    <a href="users.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && !$showSuccessModal): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="userForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_email" class="form-label">Email *</label>
                                <input type="email" id="user_email" name="user_email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user_email); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       minlength="6" required>
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">User Role</label>
                                <select id="role_id" name="role_id" class="form-control">
                                    <option value="">Select Role</option>
                                    <?php 
                                    mysqli_data_seek($roles_result, 0);
                                    while($role = mysqli_fetch_assoc($roles_result)): 
                                    ?>
                                    <option value="<?php echo $role['role_id']; ?>" 
                                        <?php echo ($role_id == $role['role_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?> (Level <?php echo $role['role_level']; ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type_id" class="form-label">User Type</label>
                                <select id="type_id" name="type_id" class="form-control">
                                    <option value="">Select Type</option>
                                    <?php 
                                    mysqli_data_seek($types_result, 0);
                                    while($type = mysqli_fetch_assoc($types_result)): 
                                    ?>
                                    <option value="<?php echo $type['type_id']; ?>" 
                                        <?php echo ($type_id == $type['type_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_status" class="form-label">Status</label>
                                <select id="user_status" name="user_status" class="form-control">
                                    <option value="active" <?php echo ($user_status == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($user_status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo ($user_status == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_image" class="form-label">Profile Image</label>
                                <input type="file" id="user_image" name="user_image" class="form-control" accept="image/*">
                                <div class="form-text">Leave empty to use default image</div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_user" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add User
                                </button>
                                <a href="users.php" class="btn btn-outline-secondary btn-lg">
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

<?php if ($showSuccessModal && $new_user_id): ?>
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
          <h5 class="mt-3">User Added Successfully!</h5>
          <p class="text-muted mb-0">The user "<?php echo htmlspecialchars($username); ?>" has been created.</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <a href="users.php" class="btn btn-success px-4">
          <i class="bi bi-list-ul me-2"></i>View All Users
        </a>
        <a href="users.php?source=add_user" class="btn btn-outline-success px-4">
          <i class="bi bi-plus-circle me-2"></i>Add Another User
        </a>
        <a href="users.php?source=edit_user&id=<?php echo $new_user_id; ?>" class="btn btn-outline-primary px-4">
          <i class="bi bi-pencil me-2"></i>Edit This User
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var successModal = new bootstrap.Modal(document.getElementById('successModal'), {
      backdrop: 'static',
      keyboard: false
    });
    successModal.show();
  });
</script>
<?php endif; ?>

<script>
document.getElementById('userForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    
    // Validate username format (alphanumeric and underscore only)
    const usernameRegex = /^[a-zA-Z0-9_]+$/;
    if (!usernameRegex.test(username)) {
        e.preventDefault();
        alert('Username can only contain letters, numbers, and underscores.');
        return;
    }
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>


includes/view_all_users.php;
<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = mysqli_query($connection, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$active_users_query = "SELECT COUNT(*) as total FROM users WHERE user_status = 'active'";
$active_users_result = mysqli_query($connection, $active_users_query);
$active_users = mysqli_fetch_assoc($active_users_result)['total'];

$role_stats_query = "SELECT r.role_name, COUNT(u.user_id) as count 
                     FROM user_roles r
                     LEFT JOIN users u ON r.role_id = u.role_id
                     GROUP BY r.role_id
                     ORDER BY r.role_level DESC";
$role_stats_result = mysqli_query($connection, $role_stats_query);

$type_stats_query = "SELECT t.type_name, COUNT(u.user_id) as count 
                     FROM user_types t
                     LEFT JOIN users u ON t.type_id = u.type_id
                     GROUP BY t.type_id
                     ORDER BY t.type_name";
$type_stats_result = mysqli_query($connection, $type_stats_query);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">User Management</h1>
        <div>
            <a href="user_roles.php" class="btn btn-info me-2">
                <i class="bi bi-shield-lock"></i> Manage Roles & Types
            </a>
            <a href="users.php?source=add_user" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2><?php echo $active_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Roles</h5>
                    <h2><?php echo mysqli_num_rows($role_stats_result); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Types</h5>
                    <h2><?php echo mysqli_num_rows($type_stats_result); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Image</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                        $query = "SELECT u.*, 
                                  r.role_name, r.role_level,
                                  t.type_name
                                  FROM users u
                                  LEFT JOIN user_roles r ON u.role_id = r.role_id
                                  LEFT JOIN user_types t ON u.type_id = t.type_id
                                  ORDER BY u.user_id DESC";
                        
                        $result = mysqli_query($connection, $query);
                        
                        if (!$result) {
                            echo "<tr><td colspan='10' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                        } else if (mysqli_num_rows($result) == 0) {
                            echo "<tr><td colspan='10' class='text-center'>No users found. <a href='users.php?source=add_user'>Add your first user</a></td></tr>";
                        } else {
                            while($user = mysqli_fetch_assoc($result)) {
                                // Set badge color based on role level
                                $role_class = 'secondary';
                                if ($user['role_level'] >= 90) {
                                    $role_class = 'danger';
                                } elseif ($user['role_level'] >= 70) {
                                    $role_class = 'warning';
                                } elseif ($user['role_level'] >= 50) {
                                    $role_class = 'info';
                                }
                                
                                $status_class = $user['user_status'] == 'active' ? 'success' : 'warning';
                                ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td>
                                        <img src="../images/<?php echo $user['user_image'] ?: 'default.jpg'; ?>" 
                                             class="rounded-circle" width="40" height="40" alt="User">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                                    <td>
                                        <?php if ($user['role_name']): ?>
                                            <span class="badge bg-<?php echo $role_class; ?>">
                                                <?php echo htmlspecialchars($user['role_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['type_name']): ?>
                                            <span class="badge bg-success">
                                                <?php echo htmlspecialchars($user['type_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $user['user_status']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>

                                    <!-- In the actions column, update the delete button -->
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="users.php?source=edit_user&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

sidebar.php;
each modules menu shud be in this exixsting menu items arrangment;
            <!-- Users Menu with Dropdown -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php' && !isset($_GET['source'])) ? 'active' : ''; ?>" 
                href="#" data-menu="users">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-text">Users</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="users-menu">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['source']) && $_GET['source'] == 'add_user') ? 'active' : ''; ?>" 
                        href="users.php?source=add_user">
                            <i class="bi bi-person-plus nav-icon"></i>
                            <span class="nav-text">Add User</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['source']) || $_GET['source'] == 'view_all') ? 'active' : ''; ?>" 
                        href="./users.php">
                            <i class="bi bi-person-lines-fill nav-icon"></i>
                            <span class="nav-text">View All</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_roles.php') ? 'active' : ''; ?>" 
                        href="user_roles.php">
                            <i class="bi bi-shield-lock nav-icon"></i>
                            <span class="nav-text">Roles & Types</span>
                        </a>
                    </li>
                </ul>
            </li>

NOTE:
Avoid page redirects
On successful adds, edits, deletes, show a success modal with appropriate buttons
Add pages headers to signify page contents or intent
Follow same structure and theme
Use uniform prefix for pages, for orderly page arrangement
Create all endpoints in includes/ajax/ folder (this folder structure)
Side bar menu must be identical in structure with peculiar icons
Do not refactor PHP codes, all codes must be in the php pages for now

These are to minimize errors and bugs

If you need more clarification, let me know if there is anything i didnt cover, before we go ahead



==================================================================================
ogmbc-web-app/
├── admin/ (your existing admin panel)
├── client/
│   ├── dashboard.php
│   ├── engagements.php (master)
│   ├── files.php
│   ├── feedback.php
│   ├── invoices.php
│   ├── profile.php
│   ├── support.php
│   ├── includes/
│   │   ├── client_header.php
│   │   ├── client_nav.php
│   │   ├── client_sidebar.php
│   │   ├── client_footer.php
│   │   ├── view_engagements.php
│   │   ├── view_engagement_details.php
│   │   ├── upload_file.php
│   │   ├── submit_feedback.php
│   │   ├── view_invoices.php
│   │   ├── edit_profile.php
│   │   ├── view_support_tickets.php
│   │   ├── create_ticket.php
│   │   └── ajax/
│   │       ├── get_engagement_details.php
│   │       ├── download_file.php
│   │       ├── send_message.php
│   │       └── submit_ticket_reply.php
│   └── assets/ (copied from admin)
│
├── employee/ (for operations/sales staff)
│   ├── dashboard.php
│   ├── engagements.php
│   ├── tasks.php
│   ├── cdp.php
│   ├── sales_targets.php (sales only)
│   ├── wallet.php
│   ├── profile.php
│   ├── includes/
│   │   ├── employee_header.php
│   │   ├── employee_nav.php
│   │   ├── employee_sidebar.php
│   │   ├── employee_footer.php
│   │   ├── view_my_engagements.php
│   │   ├── update_engagement_status.php
│   │   ├── upload_evidence.php
│   │   ├── request_deadline_change.php
│   │   ├── view_cdp_records.php
│   │   ├── add_cdp_record.php
│   │   ├── view_sales_targets.php
│   │   ├── submit_achievement.php
│   │   ├── view_wallet.php
│   │   ├── edit_profile.php
│   │   └── ajax/
│   │       ├── get_task_details.php
│   │       ├── update_status.php
│   │       ├── submit_request.php
│   │       └── load_notifications.php
│   └── assets/ (copied from admin)
│
└── user/ (for internal staff)
    ├── dashboard.php
    ├── tasks.php
    ├── cdp.php
    ├── wallet.php
    ├── profile.php
    ├── includes/
    │   ├── user_header.php
    │   ├── user_nav.php
    │   ├── user_sidebar.php
    │   ├── user_footer.php
    │   └── [similar structure to employee but simplified]
    └── assets/ (copied from admin)


I need to implement ALL of the points you mentioned above, including core modules, additional features and detailed module specifications.

And yes, the example of pages structure is shown below;

main admin dashboard (admin/dashbpard.php)
<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Check if user is admin
$admin_roles = ['admin', 'super_admin', 'moderator'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $admin_roles)) {
    // Not authorized - redirect to home page
    header("Location: ../index.php?error=access_denied");
    exit();
}
?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4">Dashboard Overview</h2>
                    <p class="text-muted">Welcome back, Admin! Here's what's happening today.</p>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="row mb-4">
                <?php

                // Count total posts
                $post_sql = "SELECT COUNT(*) as total_posts FROM posts";
                $post_result = $connection->query($post_sql);
                $post_count = $post_result->fetch_assoc()['total_posts'];

                // Count total users
                $user_sql = "SELECT COUNT(*) as total_users FROM users";
                $user_result = $connection->query($user_sql);
                $user_count = $user_result->fetch_assoc()['total_users'];
                ?>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="bi bi-file-post" style="color: #f1bf70; font-size: 2rem;"></i>
                            </div>
                            <div class="stat-number"><?php echo $post_count; ?></div>
                            <div class="stat-title">Total Posts</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="bi bi-people" style="color: #f1bf70; font-size: 2rem;"></i>
                            </div>
                            <div class="stat-number"><?php echo $user_count; ?></div>
                            <div class="stat-title">Total Users</div>
                        </div>
                    </div>
                </div>

                <?php
                // Close result sets
                if ($post_result) {
                    $post_result->close();
                }
                if ($user_result) {
                    $user_result->close();
                }
                ?>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-number">956</div>
                            <div class="stat-title">Page Views</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-number">83%</div>
                            <div class="stat-title">Engagement Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            Recent Activity
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-plus-circle-fill text-success me-2"></i>
                                        New post created
                                    </div>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-person-plus-fill text-primary me-2"></i>
                                        New user registered
                                    </div>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-pencil-fill text-warning me-2"></i>
                                        Post updated
                                    </div>
                                    <small class="text-muted">Yesterday</small>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-trash-fill text-danger me-2"></i>
                                        User deleted
                                    </div>
                                    <small class="text-muted">2 days ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a class="btn btn-primary mb-2" href="posts.php?source=add_post">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Post
                                </a>
                                <a class="btn btn-success mb-2" href="employees.php?source=add_employee">
                                    <i class="bi bi-person-plus me-2"></i> Add New Employee
                                </a>
                                <button class="btn btn-info">
                                    <i class="bi bi-graph-up me-2"></i> View Reports
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
include 'includes/footer.php'
?>

master page (admin/departments.php)
<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle delete action
$delete_feedback = '';
$delete_feedback_type = '';
if (isset($_GET['delete'])) {
    $dept_id = (int)$_GET['delete'];
    // Check if department has employees
    $check_query = "SELECT COUNT(*) as emp_count FROM employees WHERE department_id = $dept_id";
    $check_result = mysqli_query($connection, $check_query);
    $row = mysqli_fetch_assoc($check_result);
    if ($row['emp_count'] > 0) {
        $delete_feedback = "Cannot delete department with existing employees. Please reassign or remove employees first.";
        $delete_feedback_type = "danger";
    } else {
        $delete_query = "DELETE FROM departments WHERE id = $dept_id";
        if (mysqli_query($connection, $delete_query)) {
            $delete_feedback = "Department deleted successfully!";
            $delete_feedback_type = "success";
        } else {
            $delete_feedback = "Error deleting department: " . mysqli_error($connection);
            $delete_feedback_type = "danger";
        }
    }
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php 
                    if (isset($_GET['source'])) {
                        switch($_GET['source']) {
                            case 'add_department':
                                echo 'Add Department';
                                break;
                            case 'edit_department':
                                echo 'Edit Department';
                                break;
                            default:
                                echo 'Departments';
                        }
                    } else {
                        echo 'Departments';
                    }
                    ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'view_all_departments';
                }

                switch($source) {
                    case 'add_department';
                        include "includes/add_department.php";
                        break;

                    case 'edit_department';
                        include "includes/edit_department.php";
                        break;

                    case 'view_all_departments';
                    default:
                        include "includes/view_all_departments.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Department Details Modal -->
<div class="modal fade" id="departmentDetailsModal" tabindex="-1" aria-labelledby="departmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header card-header text-white">
                <h5 class="modal-title" id="departmentDetailsModalLabel">Department Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="departmentDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading department details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editDepartmentBtn" class="btn btn-primary">Edit Department</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteFeedbackArea"></div>
                <p>Are you sure you want to delete the department "<span id="deleteDepartmentName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone and may affect employee records.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// View department details
function viewDepartment(id) {
    const modal = new bootstrap.Modal(document.getElementById('departmentDetailsModal'));
    const contentDiv = document.getElementById('departmentDetailsContent');
    
    // Show loading state
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading department details...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch department details
    fetch('includes/get_department_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dept = data.department;
                const employees = data.employees || [];
                
                let employeesHtml = '';
                if (employees.length > 0) {
                    employeesHtml = `
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2">Department Employees (${employees.length})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Field of Study</th>
                                            <th>Qualification</th>
                                            <th>Graduation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    employees.forEach(emp => {
                        employeesHtml += `
                            <tr>
                                <td>${emp.employee_id}</td>
                                <td>${emp.first_name} ${emp.last_name}</td>
                                <td>${emp.field_of_study || 'N/A'}</td>
                                <td>${emp.qualification || 'N/A'}</td>
                                <td>${emp.highest_graduation || 'N/A'} ${emp.year_of_graduation ? '(' + emp.year_of_graduation + ')' : ''}</td>
                            </tr>
                        `;
                    });
                    
                    employeesHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    employeesHtml = `
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            No employees assigned to this department.
                        </div>
                    `;
                }
                
                contentDiv.innerHTML = `
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="bg-light rounded-circle mx-auto mb-3" style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-building text-secondary" style="font-size:4rem;"></i>
                            </div>
                            <h4 class="mb-1">${dept.dept_name}</h4>
                            <p class="text-muted">Department Code: ${dept.dept_code}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Manager:</strong><br>${dept.manager_name || dept.manager || '<span class="text-muted">Not Assigned</span>'}</p>
                                    <p><strong>Budget:</strong><br>$${parseFloat(dept.budget).toFixed(2)}</p>
                                    <p><strong>Location:</strong><br>${dept.location || '<span class="text-muted">Not specified</span>'}</p>
                                    <p><strong>Total Employees:</strong><br>${employees.length}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Description:</strong><br>${dept.description ? dept.description.replace(/\n/g, '<br>') : '<span class="text-muted">No description provided.</span>'}</p>
                                </div>
                            </div>
                            ${employeesHtml}
                        </div>
                    </div>
                `;
                document.getElementById('editDepartmentBtn').href = 'departments.php?source=edit_department&id=' + dept.dept_id;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message || 'Error loading department details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    Error loading department details: ${error.message}
                </div>
            `;
        });
}

// Show delete confirmation modal
function confirmDelete(id, name) {
    document.getElementById('deleteDepartmentName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = 'departments.php?delete=' + id;
    document.getElementById('deleteFeedbackArea').innerHTML = '';
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Show delete feedback modal if delete was attempted
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($delete_feedback)): ?>
    document.getElementById('deleteFeedbackArea').innerHTML = '<div class="alert alert-<?php echo $delete_feedback_type; ?>">' + <?php echo json_encode($delete_feedback); ?> + '</div>';
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    <?php endif; ?>
});

// Handle menu toggle state
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a departments page and expand the menu
    const currentPath = window.location.pathname;
    const urlParams = new URLSearchParams(window.location.search);
    const source = urlParams.get('source');
    
    if (currentPath.includes('departments.php')) {
        const departmentsMenu = document.getElementById('departments-menu');
        const menuToggle = document.querySelector('[data-menu="departments"]');
        
        if (departmentsMenu && menuToggle) {
            departmentsMenu.classList.add('show');
            menuToggle.classList.add('expanded');
            
            // Mark active state based on source
            const addDeptLink = document.querySelector('a[href*="source=add_department"]');
            const viewAllLink = document.querySelector('a[href="./departments.php"]');
            
            if (addDeptLink && viewAllLink) {
                if (source === 'add_department') {
                    addDeptLink.classList.add('active');
                    viewAllLink.classList.remove('active');
                } else {
                    addDeptLink.classList.remove('active');
                    viewAllLink.classList.add('active');
                }
            }
        }
    }
});
</script>

<?php include "includes/footer.php"; ?>

includes/add_department.php;
<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$dept_name = $dept_code = $manager = $budget = $location = $description = '';
$message = '';
$message_type = '';

// Handle form submission
if (isset($_POST['submit_department'])) {
    $dept_name = mysqli_real_escape_string($connection, $_POST['dept_name']);
    $dept_code = mysqli_real_escape_string($connection, $_POST['dept_code']);
    $manager = mysqli_real_escape_string($connection, $_POST['manager']);
    $budget = mysqli_real_escape_string($connection, $_POST['budget']);
    $location = mysqli_real_escape_string($connection, $_POST['location']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    
    // Validate required fields
    if (empty($dept_name) || empty($dept_code) || empty($budget)) {
        $message = "Please fill in all required fields.";
        $message_type = "danger";
    } else {
        // Check if department code already exists
        $check_query = "SELECT id FROM departments WHERE dept_code = '$dept_code'";
        $check_result = mysqli_query($connection, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Department code already exists. Please use a different code.";
            $message_type = "danger";
        } else {
            // Insert department
            $insert_query = "INSERT INTO departments (dept_name, dept_code, manager, budget, location, description) 
                             VALUES ('$dept_name', '$dept_code', '$manager', '$budget', '$location', '$description')";
            
            if (mysqli_query($connection, $insert_query)) {
                $_SESSION['success_message'] = "Department added successfully!";
                echo "<script>window.location.href = 'departments.php';</script>";
                exit();
            } else {
                $message = "Error adding department: " . mysqli_error($connection);
                $message_type = "danger";
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Department</h5>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="departmentForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_name" class="form-label">Department Name *</label>
                                    <input type="text" id="dept_name" name="dept_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($dept_name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_code" class="form-label">Department Code *</label>
                                    <input type="text" id="dept_code" name="dept_code" class="form-control" 
                                           value="<?php echo htmlspecialchars($dept_code); ?>" 
                                           placeholder="e.g., HR, IT, FIN" maxlength="10" required>
                                    <div class="form-text">Unique identifier for the department</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager" class="form-label">Department Manager</label>
                                    <select id="manager" name="manager" class="form-control">
                                        <option value="">Select Manager</option>
                                        <?php
                                        // Get all employees to populate manager dropdown
                                        $employees_query = "SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name";
                                        $employees_result = mysqli_query($connection, $employees_query);
                                        while ($emp = mysqli_fetch_assoc($employees_result)) {
                                            $selected = ($manager == $emp['employee_id']) ? 'selected' : '';
                                            echo "<option value='{$emp['employee_id']}' {$selected}>" . 
                                                 htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . 
                                                 "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Annual Budget ($) *</label>
                                    <input type="number" step="0.01" id="budget" name="budget" class="form-control" 
                                           value="<?php echo htmlspecialchars($budget); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($location); ?>" 
                                           placeholder="Office location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="submit_department" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Add Department
                                </button>
                                <a href="departments.php" class="btn btn-outline-secondary btn-lg">
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

<script>
// Form validation
document.getElementById('departmentForm')?.addEventListener('submit', function(e) {
    const deptCode = document.getElementById('dept_code').value;
    const budget = parseFloat(document.getElementById('budget').value);
    
    // Validate department code format (alphanumeric)
    const codeRegex = /^[A-Za-z0-9]{2,10}$/;
    if (!codeRegex.test(deptCode)) {
        e.preventDefault();
        alert('Department code must be 2-10 alphanumeric characters.');
        return;
    }
    
    // Validate budget
    if (budget <= 0) {
        e.preventDefault();
        alert('Budget must be greater than 0.');
        return;
    }
});
</script>


edit_department;
<?php
ob_start();
$message = '';
$message_type = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // No redirect, just show modal
}

// Get department ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Invalid department ID.";
    $message_type = "danger";
} else {
    $dept_id = (int)$_GET['id'];
    // Fetch department data
    $query = "SELECT * FROM departments WHERE id = $dept_id";
    $result = mysqli_query($connection, $query);
    if (!$result || mysqli_num_rows($result) == 0) {
        $message = "Department not found.";
        $message_type = "danger";
    } else {
        $department = mysqli_fetch_assoc($result);
        // Handle form submission
        if (isset($_POST['update_department'])) {
            $dept_name = mysqli_real_escape_string($connection, $_POST['dept_name']);
            $dept_code = mysqli_real_escape_string($connection, $_POST['dept_code']);
            $manager = mysqli_real_escape_string($connection, $_POST['manager']);
            $budget = mysqli_real_escape_string($connection, $_POST['budget']);
            $location = mysqli_real_escape_string($connection, $_POST['location']);
            $description = mysqli_real_escape_string($connection, $_POST['description']);
            // Validate required fields
            if (empty($dept_name) || empty($dept_code) || empty($budget)) {
                $message = "Please fill in all required fields.";
                $message_type = "danger";
            } else {
                // Check if department code already exists (excluding current department)
                $check_query = "SELECT id FROM departments WHERE dept_code = '$dept_code' AND id != $dept_id";
                $check_result = mysqli_query($connection, $check_query);
                if (mysqli_num_rows($check_result) > 0) {
                    $message = "Department code already exists. Please use a different code.";
                    $message_type = "danger";
                } else {
                    // Update department
                    $update_query = "UPDATE departments SET 
                                     dept_name = '$dept_name',
                                     dept_code = '$dept_code',
                                     manager = '$manager',
                                     budget = '$budget',
                                     location = '$location',
                                     description = '$description'
                                     WHERE id = $dept_id";
                    if (mysqli_query($connection, $update_query)) {
                        $message = "Department updated successfully!";
                        $message_type = "success";
                        // Refetch updated department for form
                        $query = "SELECT * FROM departments WHERE id = $dept_id";
                        $result = mysqli_query($connection, $query);
                        $department = mysqli_fetch_assoc($result);
                    } else {
                        $message = "Error updating department: " . mysqli_error($connection);
                        $message_type = "danger";
                    }
                }
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Department</h5>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($message) && $message_type !== 'success'): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($message) && $message_type === 'success'): ?>
                    <!-- Success Modal with Full Backdrop -->
                    <div class="modal-backdrop show" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background-color:rgba(0,0,0,0.5);z-index:1050;"></div>
                    <div class="modal fade show" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-modal="true" style="display:block;z-index:1055;">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-success shadow-lg">
                                <div class="modal-header bg-success text-white border-success">
                                    <h5 class="modal-title" id="successModalLabel">
                                        <i class="bi bi-check-circle-fill me-2 fs-2"></i> Department Updated
                                    </h5>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="fs-5 mb-3">Your changes have been saved successfully.</p>
                                    <div class="d-flex justify-content-center gap-3 mt-4">
                                        <a href="departments.php" class="btn btn-success btn-lg px-4">
                                            <i class="bi bi-list-ul me-1"></i> View All Departments
                                        </a>
                                        <button type="button" class="btn btn-outline-success btn-lg px-4" onclick="closeSuccessModal()">
                                            <i class="bi bi-pencil-square me-1"></i> Continue Editing
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        function closeSuccessModal() {
                            document.getElementById('successModal').style.display = 'none';
                            var backdrop = document.querySelector('.modal-backdrop.show');
                            if (backdrop) backdrop.style.display = 'none';
                            document.body.style.overflow = '';
                        }
                        document.body.style.overflow = 'hidden';
                        document.getElementById('successModal').addEventListener('click', function(e) {
                            if (e.target === this) {
                                closeSuccessModal();
                            }
                        });
                        window.closeSuccessModal = closeSuccessModal;
                    </script>
                    <?php endif; ?>

                    <form method="POST" action="" id="editDepartmentForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_name" class="form-label">Department Name *</label>
                                    <input type="text" id="dept_name" name="dept_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['dept_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dept_code" class="form-label">Department Code *</label>
                                    <input type="text" id="dept_code" name="dept_code" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['dept_code']); ?>" 
                                           placeholder="e.g., HR, IT, FIN" maxlength="10" required>
                                    <div class="form-text">Unique identifier for the department</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manager" class="form-label">Department Manager</label>
                                    <select id="manager" name="manager" class="form-control">
                                        <option value="">Select Manager</option>
                                        <?php
                                        // Get all employees to populate manager dropdown
                                        $employees_query = "SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name";
                                        $employees_result = mysqli_query($connection, $employees_query);
                                        while ($emp = mysqli_fetch_assoc($employees_result)) {
                                            $selected = ($department['manager'] == $emp['employee_id']) ? 'selected' : '';
                                            echo "<option value='{$emp['employee_id']}' {$selected}>" . 
                                                 htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . 
                                                 "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Annual Budget ($) *</label>
                                    <input type="number" step="0.01" id="budget" name="budget" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['budget']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" id="location" name="location" class="form-control" 
                                           value="<?php echo htmlspecialchars($department['location']); ?>" 
                                           placeholder="Office location">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($department['description']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="update_department" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-check-circle me-1"></i> Update Department
                                </button>
                                <a href="departments.php" class="btn btn-outline-secondary btn-lg">
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

<script>
// Form validation
document.getElementById('editDepartmentForm')?.addEventListener('submit', function(e) {
    const deptCode = document.getElementById('dept_code').value;
    const budget = parseFloat(document.getElementById('budget').value);
    
    // Validate department code format (alphanumeric)
    const codeRegex = /^[A-Za-z0-9]{2,10}$/;
    if (!codeRegex.test(deptCode)) {
        e.preventDefault();
        alert('Department code must be 2-10 alphanumeric characters.');
        return;
    }
    
    // Validate budget
    if (budget <= 0) {
        e.preventDefault();
        alert('Budget must be greater than 0.');
        return;
    }
});
</script>


When implementing a delete operation, this is also the sample
delete_employee.php;
<?php
// Robust error handling for AJAX: always return JSON, catch fatal errors
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID']);
    exit;
}

$employee_id = (int)$_GET['id'];

// First, check if employee exists
$check_query = "SELECT employee_id, user_image FROM employees WHERE employee_id = $employee_id";
$check_result = mysqli_query($connection, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Employee not found']);
    exit;
}

$employee = mysqli_fetch_assoc($check_result);

// Delete the employee profile image if it exists
if (!empty($employee['user_image']) && $employee['user_image'] !== 'null') {
    $image_path = __DIR__ . '/../../uploads/profiles/' . $employee['user_image'];
    if (file_exists($image_path)) {
        @unlink($image_path);
    }
}

// Delete the employee from database
$query = "DELETE FROM employees WHERE employee_id = $employee_id";
$result = mysqli_query($connection, $query);

if ($result && mysqli_affected_rows($connection) > 0) {
    echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete employee. Please try again.']);
}

if ($check_result) {
    mysqli_free_result($check_result);
}
ob_end_flush();
?>

get_deparment_details.php (for modal view);
<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($errno, $errstr) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $errstr]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message']]);
        exit;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
    exit;
}

$dept_id = (int)$_GET['id'];

// Get department details with manager name from employees table
$dept_query = "SELECT d.*, 
               CONCAT(e.first_name, ' ', e.last_name) as manager_name
               FROM departments d
               LEFT JOIN employees e ON d.manager = e.employee_id
               WHERE d.id = $dept_id";
$dept_result = mysqli_query($connection, $dept_query);

if ($dept_result && mysqli_num_rows($dept_result) > 0) {
    $department = mysqli_fetch_assoc($dept_result);
    
    // Get employees in this department - using your actual table structure
    $emp_query = "SELECT employee_id, first_name, last_name, 
                  field_of_study, qualification, highest_graduation,
                  year_of_graduation, created_at
                  FROM employees 
                  WHERE department_id = $dept_id 
                  ORDER BY first_name";
    $emp_result = mysqli_query($connection, $emp_query);
    
    $employees = [];
    while ($emp = mysqli_fetch_assoc($emp_result)) {
        $employees[] = $emp;
    }
    
    // Also get employee count for this department
    $count_query = "SELECT COUNT(*) as total FROM employees WHERE department_id = $dept_id";
    $count_result = mysqli_query($connection, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    
    // Clean up null values
    $department = array_map(function($value) {
        return $value === null ? '' : $value;
    }, $department);
    
    echo json_encode([
        'success' => true,
        'department' => $department,
        'employees' => $employees,
        'employee_count' => $count_row['total']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Department not found'
    ]);
}

// Free result sets
if ($dept_result) {
    mysqli_free_result($dept_result);
}
if (isset($emp_result) && $emp_result) {
    mysqli_free_result($emp_result);
}
if (isset($count_result) && $count_result) {
    mysqli_free_result($count_result);
}

ob_end_flush();
?>


sidebar.php;
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <span class="logo-text">AdminPanel</span>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php" data-menu="services">
                    <i class="bi bi-speedometer nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Sales/CRM Menu -->
            <li class="nav-item">
                <a class="nav-link menu-toggle-btn" href="#" data-menu="sales">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span class="nav-text">Sales/CRM</span>
                    <i class="bi bi-chevron-right menu-toggle"></i>
                </a>
                <ul class="sub-menu" id="sales-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="clients.php?source=add_client">
                            <i class="bi bi-plus-circle nav-icon"></i>
                            <span class="nav-text">Add Client</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./clients.php">
                            <i class="bi bi-card-checklist nav-icon"></i>
                            <span class="nav-text">View All Clients</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

Remember all these files are samples for admin, we are going to be creating the modules we discusses above for client and other entities, following the same structure.

Smaill update:
Now creating this part:
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

Create them as this;
include 'includes/client_header.php';
include 'includes/client_nav.php';
include 'includes/client_sidebar.php';

include 'includes/client_footer.php';

Avoid creating uncessary redirects and make sure success modals are properly shown on successful operations. ALso make sure all php functions are created in relevant .php pages. Only endpoints you can create in includes/ajax folder


Now I think it is high time we get back to working on the employee module, specifically employees module for Sales Department Staff and Operations department.

Make sure you pull out all the necessary details from all of our available database tables (like points, feedbacks gained, assigned engagement, CDP request and tracking, associated clients request, associated clients details etc.) which have relations with the employee and create separate organized modules that will display these details and other interactions/operations. Note that currently I don't think we have any columns in the clients table that is holding the clients' assigned sales department or operations department employees.

We will be creating two user portals separately (for sales and operation departments employees) one at a time. 

Create more functionalities if necessary for a standard employee portal.

NOW, this is the structure we defined and discussed before, now what other things will you add to these, lets finalize before starting to roll out the code.

And in any instance we've used employee, lets change it to operations (for better understanding and since we are going to create more departments)

├── employee/ (for operations/sales staff)
│   ├── dashboard.php
│   ├── engagements.php
│   ├── tasks.php
│   ├── cdp.php
│   ├── sales_targets.php (sales only)
│   ├── wallet.php
│   ├── profile.php
│   ├── includes/
│   │   ├── employee_header.php
│   │   ├── employee_nav.php
│   │   ├── employee_sidebar.php
│   │   ├── employee_footer.php
│   │   ├── view_my_engagements.php
│   │   ├── update_engagement_status.php
│   │   ├── upload_evidence.php
│   │   ├── request_deadline_change.php
│   │   ├── view_cdp_records.php
│   │   ├── add_cdp_record.php
│   │   ├── view_sales_targets.php
│   │   ├── submit_achievement.php
│   │   ├── view_wallet.php
│   │   ├── edit_profile.php
│   │   └── ajax/
│   │       ├── get_task_details.php
│   │       ├── update_status.php
│   │       ├── submit_request.php
│   │       └── load_notifications.php
│   └── assets/ (copied from admin)

After finalizing on the modules, and feature to be created for the Operations department employee portal modules/section, I will then give you the code and pages structure so that we can begin




======================IMPROVED ADMIN DASHBOARD====================
<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Check if user is admin
$admin_roles = ['admin', 'super_admin', 'moderator'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $admin_roles)) {
    // Not authorized - redirect to home page
    header("Location: ../index.php?error=access_denied");
    exit();
}

// Get real statistics
$today = date('Y-m-d');

// Total users
$users_query = "SELECT COUNT(*) as total FROM users";
$users_result = mysqli_query($connection, $users_query);
$total_users = mysqli_fetch_assoc($users_result)['total'];

// Total clients
$clients_query = "SELECT COUNT(*) as total FROM clients";
$clients_result = mysqli_query($connection, $clients_query);
$total_clients = mysqli_fetch_assoc($clients_result)['total'];

// Active engagements
$engagements_query = "SELECT COUNT(*) as total FROM engagements WHERE status NOT IN ('CLOSED', 'SUBMITTED')";
$engagements_result = mysqli_query($connection, $engagements_query);
$active_engagements = mysqli_fetch_assoc($engagements_result)['total'];

// Pending approvals (CDP records)
$pending_query = "SELECT COUNT(*) as total FROM cdp_records WHERE status = 'PENDING'";
$pending_result = mysqli_query($connection, $pending_query);
$pending_approvals = mysqli_fetch_assoc($pending_result)['total'];

// Recent activities
$activity_query = "SELECT 
    'user' as type, 'New user registered' as action, created_at 
    FROM users ORDER BY created_at DESC LIMIT 2
    UNION ALL
    SELECT 'client', 'New client added', created_at 
    FROM clients ORDER BY created_at DESC LIMIT 2
    UNION ALL
    SELECT 'engagement', 'Engagement created', created_at 
    FROM engagements ORDER BY created_at DESC LIMIT 2
    ORDER BY created_at DESC LIMIT 5";
$activity_result = mysqli_query($connection, $activity_query);
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="welcome-title">
                                Welcome back, Admin! 👋
                            </h2>
                            <p class="welcome-subtitle">
                                Here's what's happening across your platform today.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="current-date">
                                <i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Row -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-people-fill text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($total_users); ?></h3>
                            <p class="stat-label">Total Users</p>
                            <div class="stat-progress">
                                <span class="badge bg-info-soft text-info">
                                    <i class="bi bi-person-plus me-1"></i>+12 this month
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-building text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo number_format($total_clients); ?></h3>
                            <p class="stat-label">Total Clients</p>
                            <div class="stat-progress">
                                <span class="badge bg-success-soft text-success">
                                    <i class="bi bi-graph-up me-1"></i>+8 new
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-briefcase-fill text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $active_engagements; ?></h3>
                            <p class="stat-label">Active Engagements</p>
                            <div class="stat-progress">
                                <span class="badge bg-warning-soft text-warning">
                                    <i class="bi bi-clock me-1"></i>5 due soon
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-card-body">
                        <div class="stat-icon bg-danger-soft">
                            <i class="bi bi-clock-history text-danger"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value"><?php echo $pending_approvals; ?></h3>
                            <p class="stat-label">Pending Approvals</p>
                            <div class="stat-progress">
                                <span class="badge bg-danger-soft text-danger">
                                    <i class="bi bi-hourglass me-1"></i>Need review
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row g-4">
            <!-- Left Column - Recent Activity -->
            <div class="col-xl-8">
                <div class="dashboard-card">
                    <div class="card-header dark-header">
                        <h5 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>
                            Recent Activity
                        </h5>
                        <button class="btn btn-sm btn-outline-light" onclick="refreshActivity()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($activity_result && mysqli_num_rows($activity_result) > 0): ?>
                            <div class="activity-feed">
                                <?php while($activity = mysqli_fetch_assoc($activity_result)): 
                                    $icon = $activity['type'] == 'user' ? 'person-plus' : ($activity['type'] == 'client' ? 'building' : 'briefcase');
                                    $color = $activity['type'] == 'user' ? 'primary' : ($activity['type'] == 'client' ? 'success' : 'warning');
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon bg-<?php echo $color; ?>-soft">
                                        <i class="bi bi-<?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="activity-text"><?php echo $activity['action']; ?></p>
                                                <small class="activity-details text-muted">System activity</small>
                                            </div>
                                            <small class="activity-time text-muted">
                                                <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-activity display-4"></i>
                                <h6>No recent activity</h6>
                                <p class="text-muted">Activities will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions & Tips -->
            <div class="col-xl-4">
                <!-- Quick Actions Card -->
                <div class="dashboard-card">
                    <div class="card-header dark-header">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge me-2"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions-grid">
                            <a href="users.php?source=add_user" class="quick-action-item">
                                <div class="quick-action-icon bg-primary-soft">
                                    <i class="bi bi-person-plus text-primary"></i>
                                </div>
                                <span>Add User</span>
                            </a>
                            <a href="clients.php?source=add_client" class="quick-action-item">
                                <div class="quick-action-icon bg-success-soft">
                                    <i class="bi bi-building-add text-success"></i>
                                </div>
                                <span>Add Client</span>
                            </a>
                            <a href="services.php?source=add_service" class="quick-action-item">
                                <div class="quick-action-icon bg-info-soft">
                                    <i class="bi bi-gear text-info"></i>
                                </div>
                                <span>Add Service</span>
                            </a>
                            <a href="reports.php" class="quick-action-item">
                                <div class="quick-action-icon bg-warning-soft">
                                    <i class="bi bi-graph-up text-warning"></i>
                                </div>
                                <span>View Reports</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pro Tip Card -->
                <div class="dashboard-card mt-4 pro-tip-card">
                    <div class="card-body">
                        <h6 class="text-white mb-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            Admin Pro Tip
                        </h6>
                        <p class="text-white-50 small mb-3">
                            <?php if ($pending_approvals > 0): ?>
                                You have <?php echo $pending_approvals; ?> pending approvals. Check the CDP section to review them.
                            <?php elseif ($active_engagements > 10): ?>
                                High volume of active engagements. Consider reviewing workload distribution.
                            <?php else: ?>
                                Regular system backups are recommended. Schedule them during off-peak hours.
                            <?php endif; ?>
                        </p>
                        <a href="settings.php" class="btn btn-sm btn-light">
                            <i class="bi bi-gear me-1"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


=========================================================================

<?php
// Ensure PHP session is started so AJAX requests send the session cookie
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4">Client Management</h2>
            <a href="clients.php?source=add_client" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Client
            </a>
        </div>

        <!-- Dashboard KPIs -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Clients</div>
                                <div class="h2 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Approval</div>
                                <div class="h2 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status IN ('Under Manager Review', 'Under CEO Review')";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-history fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Awaiting Client</div>
                                <div class="h2 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status = 'Awaiting Client Action'";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-envelope fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Ready for Finance</div>
                                <div class="h2 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status = 'Signed – Move to Finance'";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-filter me-2"></i>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label">Status</label>
                        <select name="status_filter" id="status_filter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="New Lead">New Lead</option>
                            <option value="Contacted">Contacted</option>
                            <option value="Qualified">Qualified</option>
                            <option value="Proposal Drafted">Proposal Drafted</option>
                            <option value="Under Manager Review">Under Manager Review</option>
                            <option value="Approved by Manager">Approved by Manager</option>
                            <option value="Under CEO Review">Under CEO Review</option>
                            <option value="Final Proposal Ready">Final Proposal Ready</option>
                            <option value="Proposal Sent to Client">Proposal Sent to Client</option>
                            <option value="Awaiting Client Action">Awaiting Client Action</option>
                            <option value="Signed – Move to Finance">Signed – Move to Finance</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="service_filter" class="form-label">Service Type</label>
                        <select name="service_filter" id="service_filter" class="form-control">
                            <option value="">All Services</option>
                            <?php
                            $services_query = "SELECT * FROM categories ORDER BY cat_title";
                            $services_result = mysqli_query($connection, $services_query);
                            while($service = mysqli_fetch_assoc($services_result)) {
                                echo "<option value='{$service['cat_id']}'>{$service['cat_title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="clients.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Clients</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="clientsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Country</th>
                                <th>Jurisdiction</th>
                                <th>Industry</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Sales Person</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php findAllClients(); ?>
                        </tbody>
                    </table>
                </div>
                
                <?php
                // Check if table is empty
                $check_query = "SELECT COUNT(*) as total FROM clients";
                $check_result = mysqli_query($connection, $check_query);
                $total_clients = mysqli_fetch_assoc($check_result)['total'];
                
                if($total_clients == 0): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No Clients Found</h4>
                    <p class="text-muted">Get started by adding your first client.</p>
                    <a href="clients.php?source=add_client" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Add First Client
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- Client Details Modal (match clients.php structure) -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDetailsModalLabel">Client Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clientDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading client details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize filters with current values
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set filter values from URL
    document.getElementById('status_filter').value = urlParams.get('status_filter') || '';
    document.getElementById('service_filter').value = urlParams.get('service_filter') || '';
    document.getElementById('date_from').value = urlParams.get('date_from') || '';
    document.getElementById('date_to').value = urlParams.get('date_to') || '';
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});


// Function to load client details (jQuery, match clients.php)
function loadClientDetails(clientId) {
    // Show modal and loading spinner
    $('#clientDetailsModal').modal('show');
    $('#clientDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading client details...</p></div>');
    $.ajax({
        url: 'get_client_details.php',
        type: 'GET',
        data: { id: clientId },
        success: function(response) {
            $('#clientDetailsContent').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading client details:', error);
            $('#clientDetailsContent').html('<div class="alert alert-danger">Error loading client details: ' + error + '</div>');
        }
    });
}

// Function to load review details
window.loadReviewDetails = function(clientId) {
    // Implement review functionality as needed
    console.log('Load review for client:', clientId);
    // You can add a review modal here
};

// Function to delete document
window.deleteDocument = function(docId) {
    if (confirm('Are you sure you want to delete this document?')) {
        fetch('delete_client_document.php?id=' + docId, {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting document: ' + data.message);
            }
        })
        .catch(err => {
            alert('Error deleting document');
        });
    }
};

// No need for event delegation, as findAllClients() already uses onclick handlers for the view button
</script>