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
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">New Leads</div>
                                <div class="h5 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status = 'New Lead'";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-dark mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Approval</div>
                                <div class="h5 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status IN ('Under Manager Review', 'Under CEO Review')";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-history fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Awaiting Client</div>
                                <div class="h5 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status = 'Awaiting Client Action'";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-envelope fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Ready for Finance</div>
                                <div class="h5 mb-0">
                                    <?php
                                    $query = "SELECT COUNT(*) as count FROM clients WHERE client_status = 'Signed – Move to Finance'";
                                    $result = mysqli_query($connection, $query);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle fa-2x"></i>
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
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Call the function to display all clients
                            if (!function_exists('findAllClients')) include dirname(__DIR__) . '/functions.php';
                            if (function_exists('findAllClients')) {
                                // Custom version of findAllClients without the Service column
                                $query = "SELECT c.client_id, c.company_name, c.contact_name, c.contact_email, c.contact_mobile, c.country, c.jurisdiction, c.industry, c.client_status, c.created_at FROM clients c ORDER BY c.client_id DESC";
                                $result = mysqli_query($connection, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr id='client-row-{$row['client_id']}'>";
                                    echo "<td>" . htmlspecialchars($row['client_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['contact_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['contact_email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['contact_mobile']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['country']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jurisdiction'] ?? '') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['industry'] ?? '') . "</td>";
                                    // Service column removed
                                    echo "<td>" . htmlspecialchars($row['client_status']) . "</td>";
                                    echo "<td>" . htmlspecialchars(date('M j, Y', strtotime($row['created_at']))) . "</td>";
                                    echo "<td>";
                                    echo "<button class='btn btn-sm btn-info' onclick='loadClientDetails({$row['client_id']})'><i class='bi bi-eye'></i></button> ";
                                    echo "<a href='clients.php?source=edit_client&id={$row['client_id']}' class='btn btn-sm btn-primary'><i class='bi bi-pencil'></i></a> ";
                                    echo "<button class='btn btn-sm btn-danger' onclick=\"confirmDelete({$row['client_id']},'" . htmlspecialchars(addslashes($row['company_name'])) . "')\"><i class='bi bi-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
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

<!-- Client Details Modal -->
<style>
    #clientDetailsModal .modal-dialog {
        max-width: 90vw;
        width: 100%;
    }
    #clientDetailsModal .modal-content {
        min-height: 60vh;
        overflow-x: auto;
    }
    @media (max-width: 768px) {
        #clientDetailsModal .modal-dialog {
            max-width: 98vw;
        }
        #clientDetailsModal .modal-content {
            min-height: 40vh;
        }
    }
</style>

<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #0a2240; color: #f1bf70;">
                <h5 class="modal-title" id="clientDetailsModalLabel">
                    <i class="bi bi-building me-2"></i>Client Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="clientDetailsModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading client details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editClientBtn" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit Client
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc3545; color: white;">
                <h5 class="modal-title" id="deleteClientModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete client: <strong><span id="deleteClientName"></span></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone. This will also delete the associated user account.</small></p>
                <div id="deleteWarning" class="alert alert-warning mt-2" style="display: none;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This client has related records. Deleting will also remove all associated data.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete Client
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Toasts -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="successMessage">Client deleted successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="errorMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
// Initialize filters with current values
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('status_filter').value = urlParams.get('status_filter') || '';
    document.getElementById('date_from').value = urlParams.get('date_from') || '';
    document.getElementById('date_to').value = urlParams.get('date_to') || '';
});

function filterClients() {
    const status = document.getElementById('status_filter').value;
    const service = document.getElementById('service_filter').value;
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    let url = 'clients.php?';
    const params = [];
    if (status) params.push(`status_filter=${encodeURIComponent(status)}`);
    if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
    window.location.href = url + params.join('&');
}

function loadClientDetails(clientId) {
    const modalEl = document.getElementById('clientDetailsModal');
    const modalBody = document.getElementById('clientDetailsModalBody');
    const editBtn = document.getElementById('editClientBtn');
    if (!modalEl || !modalBody) return;
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading client details...</p></div>';
    editBtn.href = 'clients.php?source=edit_client&id=' + clientId;
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
    fetch('get_client_details.php?id=' + encodeURIComponent(clientId), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(err => {
        console.error('Error fetching client details:', err);
        modalBody.innerHTML = '<div class="alert alert-danger">Failed to load client details</div>';
    });
}

// Delete client function
let deleteClientId = null;

function confirmDelete(clientId, companyName) {
    deleteClientId = clientId;
    document.getElementById('deleteClientName').textContent = companyName;
    document.getElementById('deleteWarning').style.display = 'none';
    // Check if client has related records
    fetch('check_client_dependencies.php?id=' + clientId)
        .then(response => response.json())
        .then(data => {
            if (data.has_dependencies) {
                document.getElementById('deleteWarning').style.display = 'block';
                document.getElementById('deleteWarning').innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' +
                    'This client has related records: ' + data.dependencies.join(', ') + 
                    '. Deleting will also remove all associated data.';
            }
        })
        .catch(error => {
            console.error('Error checking dependencies:', error);
        });
    const modal = new bootstrap.Modal(document.getElementById('deleteClientModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!deleteClientId) return;
    const deleteBtn = this;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    fetch('delete_client.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: deleteClientId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteClientModal'));
            modal.hide();
            showSuccess(data.message);
            const row = document.getElementById('client-row-' + deleteClientId);
            if (row) {
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.4s';
                setTimeout(() => {
                    row.remove();
                }, 400);
            }
            deleteClientId = null;
        } else {
            showError(data.message || 'Failed to delete client');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showError('Error: ' + error.message);
    })
    .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
});

function showSuccess(message) {
    const toastEl = document.getElementById('successToast');
    document.getElementById('successMessage').textContent = message;
    const toast = new bootstrap.Toast(toastEl, { autohide: false });
    toast.show();
    setTimeout(() => toast.hide(), 3000);
}

function showError(message) {
    const toastEl = document.getElementById('errorToast');
    document.getElementById('errorMessage').textContent = message;
    const toast = new bootstrap.Toast(toastEl, { autohide: false });
    toast.show();
    setTimeout(() => toast.hide(), 3000);
}

window.loadClientDetails = loadClientDetails;
window.confirmDelete = confirmDelete;
</script>