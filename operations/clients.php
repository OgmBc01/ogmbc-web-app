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
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        <!-- Welcome Card (for consistency with engagements.php) -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                    <div>
                        <div class="welcome-title mb-1">My Clients</div>
                        <div class="welcome-subtitle">View, manage, and communicate with your clients here.</div>
                    </div>
                    <div class="current-date mt-3 mt-md-0">
                        <i class="bi bi-calendar-event me-2"></i> <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->

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
                        include "includes/clients/view_client_details.php";
                        break;
                    case 'communications':
                        include "includes/clients/client_communications.php";
                        break;
                    case 'files':
                        include "includes/clients/client_files.php";
                        break;
                    case 'engagements':
                        include "includes/clients/client_engagements.php";
                        break;
                    case 'add_communication':
                        include "includes/clients/add_communication.php";
                        break;
                    default:
                        include "includes/clients/view_clients.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Client Details Modal -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-building me-2"></i>Client Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="clientDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading client details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="clientEngagementsBtn" class="btn btn-info">View Engagements</a>
                <a href="#" id="clientCommunicationsBtn" class="btn btn-primary">Communications</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Communication Modal -->
<div class="modal fade" id="quickCommModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots me-2"></i>Quick Communication
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCommForm" method="POST" action="includes/ajax/add_communication.php">
                <div class="modal-body">
                    <input type="hidden" name="client_id" id="comm_client_id">
                    <div class="mb-3">
                        <label class="form-label">Communication Type</label>
                        <select name="comm_type" class="form-select" required>
                            <option value="call">📞 Phone Call</option>
                            <option value="email">✉️ Email</option>
                            <option value="whatsapp">💬 WhatsApp</option>
                            <option value="meeting">🤝 Meeting</option>
                            <option value="note">📝 Note</option>
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
// View client details
function viewClient(id) {
    const modal = new bootstrap.Modal(document.getElementById('clientDetailsModal'));
    const contentDiv = document.getElementById('clientDetailsContent');
    const engagementsBtn = document.getElementById('clientEngagementsBtn');
    const commsBtn = document.getElementById('clientCommunicationsBtn');
    
    contentDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading client details...</p>
        </div>
    `;
    
    engagementsBtn.href = 'clients.php?source=engagements&id=' + id;
    commsBtn.href = 'clients.php?source=communications&id=' + id;
    
    modal.show();
    
    fetch('includes/ajax/get_client_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentDiv.innerHTML = data.html;
            } else {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading client details'}
                    </div>
                `;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error loading client details: ${error.message}
                </div>
            `;
        });
}

// Quick communication
function quickComm(clientId, clientName) {
    document.getElementById('comm_client_id').value = clientId;
    const modal = new bootstrap.Modal(document.getElementById('quickCommModal'));
    modal.show();
}

// Handle quick communication form
document.getElementById('quickCommForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/ajax/add_communication.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickCommModal'));
            modal.hide();
            showSuccess('Communication logged successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error submitting communication');
    });
});

function showSuccess(message) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <div class="toast show align-items-center text-white bg-success border-0">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

</script>

<!-- Dashboard Theme Styles (from operations_dashboard.php, for welcome card consistency) -->
<style>
.welcome-card {
    background: linear-gradient(135deg, #0a2342 0%, #193a5e 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(10, 35, 66, 0.18);
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