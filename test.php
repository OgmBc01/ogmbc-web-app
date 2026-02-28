
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


Services Configuration Module (manage service types & point rules)

Engagement Module (create, assign, track engagements)

Points Ledger Module (view ledger, monthly summaries)

Sales Target Module (set and track sales targets)

Client Feedback Module (record feedback)

CDP & Annual Performance Module (certificates, annual reviews)

Audit Log (implement last)