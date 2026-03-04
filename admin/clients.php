<?php
include "includes/header.php";
include "includes/nav.php";
include "includes/sidebar.php";
?>

<div class="row">
    <div class="col-md-12">
      <?php
        if(isset($_GET['source'])) {
          $source = $_GET['source'];
        } else {
          $source = '';
        }

        switch($source) {
        case 'add_client';
        include "includes/add_client.php";
        break;

        case 'edit_client';
        include "includes/edit_client.php";
        break;

        case 'generate_proposal';
        include "includes/generate_proposal.php";
        break;

        case 'generate_proforma';
        include "includes/generate_proforma.php";
        break;

        default:
        include "includes/view_all_clients.php";
        break;
      }

      ?>
    </div>
  </div>  
</div>

<!-- Review Modal (keep this one) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="reviewModalLabel">Review Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewModalContent">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading proposal details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal for Client Addition (with credentials) -->
<div class="modal fade" id="clientPasswordModal" tabindex="-1" aria-labelledby="clientPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="clientPasswordModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Client Added Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="bi bi-info-circle me-2"></i>
                    The client has been added. Please save these credentials. The password cannot be retrieved again.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Company Name:</label>
                    <div class="p-2 bg-light rounded" id="displayCompanyName"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email:</label>
                    <div class="p-2 bg-light rounded" id="displayEmail"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Password:</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" id="displayPassword" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> The client can use this email and password to log in to the client portal.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="copyPasswordAndClose()">
                    <i class="bi bi-check-all"></i> Copy & Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Show success modal with credentials after client addition
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show_password') === 'true') {
        <?php if (isset($_SESSION['new_client_password'])): ?>
        document.getElementById('displayCompanyName').textContent = '<?php echo addslashes($_SESSION['new_client_name']); ?>';
        document.getElementById('displayEmail').textContent = '<?php echo addslashes($_SESSION['new_client_email']); ?>';
        document.getElementById('displayPassword').value = '<?php echo addslashes($_SESSION['new_client_password']); ?>';
        // Clear session data
        <?php 
        unset($_SESSION['new_client_password']);
        unset($_SESSION['new_client_email']);
        unset($_SESSION['new_client_name']);
        ?>
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('clientPasswordModal'));
        modal.show();
        <?php endif; ?>
    }
});

// Copy password to clipboard
function copyPassword() {
    const passwordInput = document.getElementById('displayPassword');
    passwordInput.select();
    passwordInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Password copied to clipboard!');
}

// Copy password and close modal
function copyPasswordAndClose() {
    copyPassword();
    bootstrap.Modal.getInstance(document.getElementById('clientPasswordModal')).hide();
}

// Load review details for modal
function loadReviewDetails(clientId) {
    const modalEl = document.getElementById('reviewModal');
    const modalBody = document.getElementById('reviewModalContent');
    
    if (!modalEl || !modalBody) return;

    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading proposal details...</p></div>';
    
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    fetch('get_review_details.php?id=' + encodeURIComponent(clientId), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(err => {
        console.error('Error loading review details:', err);
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading review details</div>';
    });
}

// Handle proposal generation
function generateProposal(clientId) {
    window.location.href = 'clients.php?source=generate_proposal&client_id=' + clientId;
}

// Handle proforma generation
function generateProforma(clientId) {
    window.location.href = 'clients.php?source=generate_proforma&client_id=' + clientId;
}

// Properly handle modal closing
document.addEventListener('DOMContentLoaded', function() {
    // Get all modals
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });
});

// Make functions globally available
window.loadReviewDetails = loadReviewDetails;
window.generateProposal = generateProposal;
window.generateProforma = generateProforma;
</script>

<?php
include "includes/footer.php";
?>