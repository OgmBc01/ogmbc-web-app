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
   HANDLE DELETE COMMUNICATION
=================================*/
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $comm_id = (int)$_GET['delete'];
    
    // Verify ownership
    $check_query = "SELECT comm_id FROM client_communications WHERE comm_id = $comm_id AND user_id = $user_id";
    $check_result = mysqli_query($connection, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $delete_query = "DELETE FROM client_communications WHERE comm_id = $comm_id";
        if (mysqli_query($connection, $delete_query)) {
            $_SESSION['success_message'] = "Communication deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting communication.";
        }
    }
    
    header("Location: communications.php");
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

        <!-- Welcome Card -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">
                            <i class="bi bi-chat-dots me-2"></i>Client Communications
                        </div>
                        <div class="welcome-subtitle">Track all your interactions with clients in one place.</div>
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
                <li class="breadcrumb-item active">Communications</li>
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
                    case 'add':
                        include "includes/communications/add_communication.php";
                        break;
                    case 'view':
                        include "includes/communications/view_communication_details.php";
                        break;
                    case 'client':
                        include "includes/communications/client_communications.php";
                        break;
                    default:
                        include "includes/communications/view_communications.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Communication Details Modal -->
<div class="modal fade" id="communicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots me-2"></i>Communication Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="communicationDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading communication details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editCommunicationBtn" class="btn btn-warning">Edit</a>
                <a href="#" id="deleteCommunicationBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Add Communication Modal (Quick Add) -->
<div class="modal fade" id="quickCommModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Log Communication
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCommForm" method="POST" action="includes/ajax/add_communication_ajax.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client <span class="text-danger">*</span></label>
                        <select name="client_id" id="quick_client_id" class="form-select" required>
                            <option value="">Select Client</option>
                            <?php
                            // Get clients from user's engagements
                            $clients_query = "SELECT DISTINCT c.client_id, c.company_name 
                                            FROM clients c
                                            JOIN engagements e ON c.client_id = e.client_id
                                            WHERE e.assigned_to = $user_id
                                            ORDER BY c.company_name";
                            $clients_result = mysqli_query($connection, $clients_query);
                            while ($client = mysqli_fetch_assoc($clients_result)):
                            ?>
                            <option value="<?php echo $client['client_id']; ?>">
                                <?php echo htmlspecialchars($client['company_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Communication Type <span class="text-danger">*</span></label>
                        <select name="comm_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="call">📞 Phone Call</option>
                            <option value="email">✉️ Email</option>
                            <option value="whatsapp">💬 WhatsApp</option>
                            <option value="meeting">🤝 Meeting</option>
                            <option value="note">📝 Note</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Direction <span class="text-danger">*</span></label>
                        <select name="direction" class="form-select" required>
                            <option value="outgoing">Outgoing (To Client)</option>
                            <option value="incoming">Incoming (From Client)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Related Engagement (Optional)</label>
                        <select name="engagement_id" class="form-select" id="quick_engagement_id">
                            <option value="">Select Engagement</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Brief subject">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message / Notes</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="What was discussed..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Communication</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View communication details
function viewCommunication(id) {
    const modal = new bootstrap.Modal(document.getElementById('communicationModal'));
    const contentDiv = document.getElementById('communicationDetails');
    const editBtn = document.getElementById('editCommunicationBtn');
    const deleteBtn = document.getElementById('deleteCommunicationBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading communication details...</p>
        </div>
    `;
    
    editBtn.href = 'communications.php?source=add&edit=' + id;
    deleteBtn.href = 'communications.php?delete=' + id;
    
    modal.show();
    
    fetch('includes/ajax/get_communication_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading communication details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading communication details: ${error.message}
                </div>
            `;
        });
}

// Quick communication modal
function quickComm() {
    document.getElementById('quick_client_id').value = '';
    document.getElementById('quick_engagement_id').innerHTML = '<option value="">Select Engagement</option>';
    const modal = new bootstrap.Modal(document.getElementById('quickCommModal'));
    modal.show();
}

// Load engagements when client is selected
document.getElementById('quick_client_id')?.addEventListener('change', function() {
    const clientId = this.value;
    const engagementSelect = document.getElementById('quick_engagement_id');
    
    if (!clientId) {
        engagementSelect.innerHTML = '<option value="">Select Engagement</option>';
        return;
    }
    
    fetch('includes/ajax/get_client_engagements.php?client_id=' + clientId)
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Select Engagement</option>';
            if (data.success && data.engagements.length > 0) {
                data.engagements.forEach(eng => {
                    options += `<option value="${eng.engagement_id}">${eng.title} (${eng.status})</option>`;
                });
            }
            engagementSelect.innerHTML = options;
        })
        .catch(error => {
            console.error('Error loading engagements:', error);
        });
});

// Handle quick communication form
document.getElementById('quickCommForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/ajax/add_communication_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickCommModal'));
            modal.hide();
            showSuccess('Communication logged successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error submitting communication');
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
    background: linear-gradient(90deg, #0a2240 0%, #003366 100%);
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