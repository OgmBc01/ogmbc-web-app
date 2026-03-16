<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user's role for permission checks
$user_role_query = "SELECT role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = " . $_SESSION['user_id'];
$user_role_result = mysqli_query($connection, $user_role_query);
$user_role = mysqli_fetch_assoc($user_role_result)['role_name'] ?? '';

// Check if user has permission to manage tickets (admin, ceo_gm, hr_admin)
$can_manage_tickets = ($user_role == 'admin_staff' || $user_role == 'ceo_gm' || $user_role == 'hr_admin');

/* ===============================
   HANDLE TICKET ASSIGNMENT
=================================*/
if (isset($_POST['assign_ticket']) && $can_manage_tickets) {
    $ticket_id = (int)$_POST['ticket_id'];
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : 'NULL';
    
    $update_query = "UPDATE support_tickets SET 
                    assigned_to = $assigned_to,
                    updated_at = NOW()
                    WHERE ticket_id = $ticket_id";
    
    if (mysqli_query($connection, $update_query)) {
        // Add to replies as system message
        if ($assigned_to != 'NULL') {
            $assignee_query = "SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_id = $assigned_to";
            $assignee_result = mysqli_query($connection, $assignee_query);
            $assignee = mysqli_fetch_assoc($assignee_result);
            
            $system_message = "Ticket assigned to " . $assignee['name'];
            $reply_query = "INSERT INTO ticket_replies 
                           (ticket_id, user_id, message, is_staff, created_at)
                           VALUES ($ticket_id, {$_SESSION['user_id']}, '$system_message', 1, NOW())";
            mysqli_query($connection, $reply_query);
        }
        
        $_SESSION['success_message'] = "Ticket assigned successfully!";
    } else {
        $_SESSION['error_message'] = "Error assigning ticket: " . mysqli_error($connection);
    }
    
    header("Location: support_tickets.php?source=view&id=$ticket_id");
    exit();
}

/* ===============================
   HANDLE STATUS UPDATE
=================================*/
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $ticket_id = (int)$_GET['id'];
    $new_status = mysqli_real_escape_string($connection, $_GET['status']);
    
    $valid_statuses = ['open', 'in_progress', 'resolved', 'closed'];
    if (in_array($new_status, $valid_statuses)) {
        $update_query = "UPDATE support_tickets SET 
                        status = '$new_status',
                        updated_at = NOW()
                        WHERE ticket_id = $ticket_id";
        
        if (mysqli_query($connection, $update_query)) {
            // Add system message
            $system_message = "Status changed to " . str_replace('_', ' ', $new_status);
            $reply_query = "INSERT INTO ticket_replies 
                           (ticket_id, user_id, message, is_staff, created_at)
                           VALUES ($ticket_id, {$_SESSION['user_id']}, '$system_message', 1, NOW())";
            mysqli_query($connection, $reply_query);
            
            $_SESSION['success_message'] = "Ticket status updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating status: " . mysqli_error($connection);
        }
    }
    
    header("Location: support_tickets.php?source=view&id=$ticket_id");
    exit();
}

/* ===============================
   HANDLE TICKET DELETION
=================================*/
if (isset($_GET['delete']) && $can_manage_tickets) {
    $ticket_id = (int)$_GET['delete'];
    
    // First delete all replies
    $delete_replies = "DELETE FROM ticket_replies WHERE ticket_id = $ticket_id";
    mysqli_query($connection, $delete_replies);
    
    // Then delete ticket
    $delete_query = "DELETE FROM support_tickets WHERE ticket_id = $ticket_id";
    
    if (mysqli_query($connection, $delete_query)) {
        $_SESSION['success_message'] = "Ticket deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting ticket: " . mysqli_error($connection);
    }
    
    header("Location: support_tickets.php");
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
                <li class="breadcrumb-item active">Support Tickets</li>
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
                    case 'view':
                        include "includes/view_ticket_details.php";
                        break;
                    case 'edit':
                        include "includes/edit_ticket.php";
                        break;
                    case 'add_reply':
                        include "includes/add_ticket_reply.php";
                        break;
                    default:
                        include "includes/view_support_tickets.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ticketDetailsModalLabel">
                    <i class="bi bi-ticket me-2"></i>Ticket Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ticketDetailsContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading ticket details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewTicketFullBtn" class="btn btn-primary">View Full Details</a>
            </div>
        </div>
    </div>
</div>

<!-- Assign Ticket Modal -->
<div class="modal fade" id="assignTicketModal" tabindex="-1" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="assignTicketModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Assign Ticket
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="support_tickets.php">
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="assign_ticket_id">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select class="form-control" name="assigned_to" id="assigned_to" required>
                            <option value="">Select Staff Member</option>
                            <?php
                            $staff_query = "SELECT u.user_id, u.first_name, u.last_name, r.role_name
                                           FROM users u
                                           LEFT JOIN user_roles r ON u.role_id = r.role_id
                                           WHERE u.user_status = 'active'
                                           AND r.role_name IN ('admin_staff', 'operations_staff', 'sales_staff', 'hr_admin')
                                           ORDER BY u.first_name";
                            $staff_result = mysqli_query($connection, $staff_query);
                            while ($staff = mysqli_fetch_assoc($staff_result)):
                            ?>
                            <option value="<?php echo $staff['user_id']; ?>">
                                <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                                (<?php echo strtoupper($staff['role_name']); ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_ticket" class="btn btn-warning">Assign Ticket</button>
                </div>
            </form>
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
                <p>Are you sure you want to delete ticket: <strong><span id="deleteTicketSubject"></span></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. All replies will also be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// View ticket details in modal
function viewTicket(id) {
    const modal = new bootstrap.Modal(document.getElementById('ticketDetailsModal'));
    const contentDiv = document.getElementById('ticketDetailsContent');
    const fullBtn = document.getElementById('viewTicketFullBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading ticket details...</p>
        </div>
    `;
    
    fullBtn.href = 'support_tickets.php?source=view&id=' + id;
    modal.show();
    
    fetch('includes/ajax/get_ticket_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
}

// Show assign ticket modal
function assignTicket(id) {
    document.getElementById('assign_ticket_id').value = id;
    const modal = new bootstrap.Modal(document.getElementById('assignTicketModal'));
    modal.show();
}

// Show delete confirmation modal
function confirmDelete(id, subject) {
    document.getElementById('deleteTicketSubject').textContent = subject;
    document.getElementById('confirmDeleteBtn').href = 'support_tickets.php?delete=' + id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Helper function to show alerts
function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
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
</script>

<?php include "includes/footer.php"; ?>