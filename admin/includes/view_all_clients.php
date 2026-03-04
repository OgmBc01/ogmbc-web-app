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
                            <?php
                            // Call the function to display all clients
                            if (!function_exists('findAllClients')) include dirname(__DIR__) . '/functions.php';
                            if (function_exists('findAllClients')) {
                                findAllClients();
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

<!-- Client Details Modal (SINGLE MODAL - REMOVED FROM CLIENTS.PHP) -->
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
            <div class="modal-header" style="background: var(--dark-blue); color: var(--gold);">
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

<script>
// Initialize filters with current values
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set filter values from URL
    document.getElementById('status_filter').value = urlParams.get('status_filter') || '';
    document.getElementById('service_filter').value = urlParams.get('service_filter') || '';
    document.getElementById('date_from').value = urlParams.get('date_from') || '';
    document.getElementById('date_to').value = urlParams.get('date_to') || '';
});

// Enhanced filtering function
function filterClients() {
    const status = document.getElementById('status_filter').value;
    const service = document.getElementById('service_filter').value;
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    let url = 'clients.php?';
    const params = [];
    
    if (status) params.push(`status_filter=${encodeURIComponent(status)}`);
    if (service) params.push(`service_filter=${encodeURIComponent(service)}`);
    if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
    
    window.location.href = url + params.join('&');
}

// Function to load client details
function loadClientDetails(clientId) {
    const modalEl = document.getElementById('clientDetailsModal');
    const modalBody = document.getElementById('clientDetailsModalBody');
    const editBtn = document.getElementById('editClientBtn');
    
    if (!modalEl || !modalBody) return;

    // Show loading state
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading client details...</p></div>';
    
    // Update edit button
    editBtn.href = 'clients.php?source=edit_client&id=' + clientId;

    // Show Bootstrap modal
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    // Fetch client details using Fetch API (no jQuery dependency)
    fetch('get_client_details.php?id=' + encodeURIComponent(clientId), {
        credentials: 'same-origin',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }
        return response.text();
    })
    .then(html => {
        modalBody.innerHTML = html;
        
        // Reinitialize any event listeners for document upload
        initializeDocumentUpload();
    })
    .catch(err => {
        console.error('Error fetching client details:', err);
        modalBody.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load client details. ' + (err.message || '') + '</div>';
    });
}

// Function to initialize document upload handlers
function initializeDocumentUpload() {
    // Add document field
    const addBtn = document.getElementById('addDocumentField');
    if (addBtn) {
        addBtn.onclick = function() {
            let wrapper = document.getElementById('documentFieldsWrapper');
            let firstSet = document.querySelector('.document-field-set');
            if (firstSet) {
                let clone = firstSet.cloneNode(true);
                clone.querySelectorAll('input').forEach(input => input.value = '');
                clone.querySelectorAll('select').forEach(select => select.value = 'trade_license');
                let removeBtn = clone.querySelector('.removeFieldBtn');
                if (removeBtn) removeBtn.style.display = 'inline-block';
                wrapper.appendChild(clone);
            }
        };
    }
    
    // Remove document field
    document.querySelectorAll('.removeFieldBtn').forEach(btn => {
        btn.onclick = function() {
            this.closest('.document-field-set').remove();
        };
    });
    
    // Handle form submission
    const uploadForm = document.getElementById('documentUploadForm');
    if (uploadForm) {
        uploadForm.onsubmit = function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            let submitBtn = this.querySelector('button[type="submit"]');
            let originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Uploading...';
            submitBtn.disabled = true;
            
            fetch('upload_document.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    this.reset();
                    // Reload client details to show new document
                    let clientId = this.querySelector('input[name="client_id"]').value;
                    loadClientDetails(clientId);
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                alert('Upload failed: ' + error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        };
    }
}

// Make function globally available
window.loadClientDetails = loadClientDetails;
</script>