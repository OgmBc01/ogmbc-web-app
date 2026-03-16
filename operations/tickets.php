<?php
include 'includes/operations_header.php';
include 'includes/operations_nav.php';
include 'includes/operations_sidebar.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = '../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

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
                        WHERE ticket_id = $ticket_id AND assigned_to = $user_id";
        
        if (mysqli_query($connection, $update_query)) {
            $_SESSION['success_message'] = "Ticket status updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating status: " . mysqli_error($connection);
        }
    }
    
    header("Location: tickets.php?source=view&id=$ticket_id");
    exit();
}
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Welcome Card -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">
                            <i class="bi bi-ticket me-2"></i>Support Tickets
                        </div>
                        <div class="welcome-subtitle">Manage and respond to client support requests.</div>
                    </div>
                    <div class="current-date mt-3 mt-md-0">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Support Tickets</li>
            </ol>
        </nav>

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
                        include "includes/tickets/view_ticket_details.php";
                        break;
                    case 'reply':
                        include "includes/tickets/add_ticket_reply.php";
                        break;
                    default:
                        include "includes/tickets/view_tickets.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Details Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-ticket me-2"></i>Ticket Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ticketDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading ticket details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewTicketFullBtn" class="btn btn-primary">View Full Details</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reply Modal -->
<div class="modal fade" id="quickReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-reply me-2"></i>Quick Reply
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickReplyForm" method="POST" action="includes/ajax/add_ticket_reply.php">
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="reply_ticket_id">
                    <div class="mb-3">
                        <label class="form-label">Your Reply</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Type your reply..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View ticket details
function viewTicket(id) {
    const modal = new bootstrap.Modal(document.getElementById('ticketModal'));
    const contentDiv = document.getElementById('ticketDetails');
    const fullBtn = document.getElementById('viewTicketFullBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading ticket details...</p>
        </div>
    `;
    
    fullBtn.href = 'tickets.php?source=view&id=' + id;
    modal.show();
    
    fetch('includes/ajax/get_ticket_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading ticket details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading ticket details: ${error.message}
                </div>
            `;
        });
}

// Quick reply modal
function quickReply(ticketId) {
    document.getElementById('reply_ticket_id').value = ticketId;
    const modal = new bootstrap.Modal(document.getElementById('quickReplyModal'));
    modal.show();
}

// Handle quick reply form
document.getElementById('quickReplyForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/ajax/add_ticket_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickReplyModal'));
            modal.hide();
            showSuccess('Reply sent successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error sending reply');
    });
});

// Helper function to show success message
function showSuccess(message) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) {
        const container = document.querySelector('.container-fluid');
        const div = document.createElement('div');
        div.id = 'alertBox';
        div.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        container.prepend(div);
        setTimeout(() => div.remove(), 3000);
    }
}
</script>

<!-- Dashboard Theme Styles -->
<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    width: 100%;
}
.welcome-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 10px;
}
.welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 0;
}
.current-date {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
}
@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.4rem;
    }
    .welcome-card {
        padding: 18px;
    }
}
</style>

<?php include 'includes/operations_footer.php'; ?>