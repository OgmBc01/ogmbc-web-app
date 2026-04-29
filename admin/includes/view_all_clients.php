<?php
// Ensure PHP session is started so AJAX requests send the session cookie
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get filter values from URL
// Helper function to check if a company name already exists (for add/update operations)
function is_company_name_duplicate($connection, $company_name, $exclude_id = null) {
    $company_name = mysqli_real_escape_string($connection, $company_name);
    $sql = "SELECT client_id FROM clients WHERE company_name = '$company_name'";
    if ($exclude_id !== null) {
        $exclude_id = (int)$exclude_id;
        $sql .= " AND client_id != $exclude_id";
    }
    $result = mysqli_query($connection, $sql);
    return mysqli_num_rows($result) > 0;
}
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($connection, $_GET['status_filter']) : '';

function is_valid_date($date) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) !== false;
}


$search_company = isset($_GET['search_company']) ? trim(mysqli_real_escape_string($connection, $_GET['search_company'])) : '';
$date_from = isset($_GET['date_from']) && is_valid_date($_GET['date_from']) ? mysqli_real_escape_string($connection, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) && is_valid_date($_GET['date_to']) ? mysqli_real_escape_string($connection, $_GET['date_to']) : '';

$where_clauses = [];
if (!empty($status_filter)) {
    if ($status_filter === 'OVERDUE_MONTH') {
        // Only show clients with at least one overdue engagement this month
        $where_clauses[] = "EXISTS (SELECT 1 FROM engagements e WHERE e.client_id = c.client_id AND e.status NOT IN ('CLOSED', 'SUBMITTED') AND COALESCE(e.approved_deadline, e.original_deadline) < CURDATE() AND MONTH(COALESCE(e.approved_deadline, e.original_deadline)) = MONTH(CURDATE()) AND YEAR(COALESCE(e.approved_deadline, e.original_deadline)) = YEAR(CURDATE()))";
    } else if ($status_filter === 'EXPIRING_MONTH') {
        // Only show clients with contract_end_date in the next 30 days
        $where_clauses[] = "contract_end_date IS NOT NULL AND contract_end_date >= CURDATE() AND contract_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } else {
        $where_clauses[] = "client_status = '$status_filter'";
    }
}

if (!empty($search_company)) {
    $where_clauses[] = "company_name LIKE '%$search_company%'";
}
if (!empty($date_from)) {
    $where_clauses[] = "DATE(created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where_clauses[] = "DATE(created_at) <= '$date_to'";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
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
                                    $lead_where = $where_clauses;
                                    // Remove EXISTS clause for stat cards to avoid alias error
                                    $lead_where = array_filter($lead_where, function($clause) {
                                        return strpos($clause, 'EXISTS (SELECT 1 FROM engagements') === false;
                                    });
                                    $lead_where[] = "client_status = 'New Lead'";
                                    $lead_sql = !empty($lead_where) ? "WHERE " . implode(" AND ", $lead_where) : "";
                                    $query = "SELECT COUNT(*) as count FROM clients $lead_sql";
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
                                    $pending_where = $where_clauses;
                                    $pending_where = array_filter($pending_where, function($clause) {
                                        return strpos($clause, 'EXISTS (SELECT 1 FROM engagements') === false;
                                    });
                                    $pending_where[] = "client_status IN ('Under Manager Review', 'Under CEO Review')";
                                    $pending_sql = !empty($pending_where) ? "WHERE " . implode(" AND ", $pending_where) : "";
                                    $query = "SELECT COUNT(*) as count FROM clients $pending_sql";
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
                                    $awaiting_where = $where_clauses;
                                    $awaiting_where = array_filter($awaiting_where, function($clause) {
                                        return strpos($clause, 'EXISTS (SELECT 1 FROM engagements') === false;
                                    });
                                    $awaiting_where[] = "client_status = 'Awaiting Client Action'";
                                    $awaiting_sql = !empty($awaiting_where) ? "WHERE " . implode(" AND ", $awaiting_where) : "";
                                    $query = "SELECT COUNT(*) as count FROM clients $awaiting_sql";
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
                                    $finance_where = $where_clauses;
                                    $finance_where = array_filter($finance_where, function($clause) {
                                        return strpos($clause, 'EXISTS (SELECT 1 FROM engagements') === false;
                                    });
                                    $finance_where[] = "client_status = 'Signed – Move to Finance'";
                                    $finance_sql = !empty($finance_where) ? "WHERE " . implode(" AND ", $finance_where) : "";
                                    $query = "SELECT COUNT(*) as count FROM clients $finance_sql";
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
            <!-- Expiring in Next 30 Days Stat Card (moved to last, red color) -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Expiring in Next 30 Days</div>
                                <div class="h5 mb-0">
                                    <?php
                                    $expiring30_where = $where_clauses;
                                    // Remove EXISTS clause for stat cards to avoid alias error
                                    $expiring30_where = array_filter($expiring30_where, function($clause) {
                                        return strpos($clause, 'EXISTS (SELECT 1 FROM engagements') === false;
                                    });
                                    $expiring30_where[] = "contract_end_date IS NOT NULL";
                                    $expiring30_sql = !empty($expiring30_where) ? "WHERE " . implode(" AND ", $expiring30_where) : "";
                                    $expiring30_query = "SELECT COUNT(*) as count FROM clients $expiring30_sql AND contract_end_date >= CURDATE() AND contract_end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                                    $expiring30_result = mysqli_query($connection, $expiring30_query);
                                    $expiring30_row = $expiring30_result ? mysqli_fetch_assoc($expiring30_result) : ['count' => 0];
                                    echo $expiring30_row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-event fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Overdue Engagements (This Month) Stat Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-dark text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Over due Engagements (This Month)</div>
                                <div class="h5 mb-0">
                                    <?php
                                    // Overdue engagements: deadline in current month, not closed/submitted, and before today
                                    $overdue_sql = "SELECT COUNT(*) as count FROM engagements WHERE status NOT IN ('CLOSED', 'SUBMITTED') AND COALESCE(approved_deadline, original_deadline) < CURDATE() AND MONTH(COALESCE(approved_deadline, original_deadline)) = MONTH(CURDATE()) AND YEAR(COALESCE(approved_deadline, original_deadline)) = YEAR(CURDATE())";
                                    $overdue_result = mysqli_query($connection, $overdue_sql);
                                    $overdue_row = $overdue_result ? mysqli_fetch_assoc($overdue_result) : ['count' => 0];
                                    echo $overdue_row['count'];
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-triangle fa-2x"></i>
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
                <form method="GET" action="" class="row g-3" id="filterForm">
                    <input type="hidden" name="source" value="view_all">
                    <div class="col-md-3">
                        <label for="search_company" class="form-label">Search Company</label>
                        <input type="text" name="search_company" id="search_company" class="form-control" placeholder="Type company name..." value="<?php echo htmlspecialchars($search_company); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status_filter" class="form-label">Status</label>
                        <select name="status_filter" id="status_filter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="New Lead">New Lead</option>
                            <option value="Contacted">Contacted</option>
                            <option value="Qualified">Qualified</option>
                            <option value="Proposal Drafted">Proposal Drafted</option>
                            <option value="Under Manager Review">Under Manager Review</option>
                            <option value="Rejected by Manager">Rejected by Manager</option>
                            <option value="Approved by Manager">Approved by Manager</option>
                            <option value="Under CEO Review">Under CEO Review</option>
                            <option value="Rejected by CEO">Rejected by CEO</option>
                            <option value="Final Proposal Ready">Final Proposal Ready</option>
                            <option value="Proposal Sent to Client">Proposal Sent to Client</option>
                            <option value="Awaiting Client Action">Awaiting Client Action</option>
                            <option value="Signed – Move to Finance">Signed – Move to Finance</option>
                            <option value="Inactive">Inactive</option>
                            <option value="OVERDUE_MONTH" <?php echo ($status_filter == 'OVERDUE_MONTH') ? 'selected' : ''; ?>>Overdue (This Month)</option>
                            <option value="EXPIRING_MONTH" <?php echo ($status_filter == 'EXPIRING_MONTH') ? 'selected' : ''; ?>>Expiring this month</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 me-2">Apply Filters</button>
                        <a href="clients.php" class="btn btn-secondary w-100">Clear</a>
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
                            // Build the filtered query
                            $query = "SELECT c.client_id, c.company_name, c.contact_name, c.contact_email, c.contact_mobile, c.country, c.jurisdiction, c.industry, c.client_status, c.created_at FROM clients c $where_sql ORDER BY c.client_id DESC";
                            $result = mysqli_query($connection, $query);
                            
                            if (!$result) {
                                echo "<tr><td colspan='11' class='text-center text-danger'>Error: " . mysqli_error($connection) . "</td></tr>";
                            } else if (mysqli_num_rows($result) == 0) {
                                echo "<tr><td colspan='11' class='text-center py-5'>";
                                echo "<div class='text-muted'>";
                                echo "<i class='bi bi-people display-4 d-block mb-3'></i>";
                                echo "<h5>No clients found</h5>";
                                if (!empty($status_filter) || !empty($date_from) || !empty($date_to)) {
                                    echo "<p>No results match your filter criteria. Try adjusting your filters.</p>";
                                    echo "<a href='clients.php' class='btn btn-outline-primary mt-2'>";
                                    echo "<i class='bi bi-arrow-counterclockwise me-2'></i>Clear All Filters";
                                    echo "</a>";
                                } else {
                                    echo "<p>Get started by adding your first client.</p>";
                                    echo "<a href='clients.php?source=add_client' class='btn btn-primary mt-2'>";
                                    echo "<i class='bi bi-plus-circle'></i> Add New Client";
                                    echo "</a>";
                                }
                                echo "</div>";
                                echo "</td></tr>";
                            } else {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Determine status badge color
                                    $status_badge = 'secondary';
                                    if ($row['client_status'] == 'New Lead') $status_badge = 'primary';
                                    elseif ($row['client_status'] == 'Contacted') $status_badge = 'info';
                                    elseif ($row['client_status'] == 'Qualified') $status_badge = 'success';
                                    elseif (strpos($row['client_status'], 'Approved') !== false) $status_badge = 'success';
                                    elseif (strpos($row['client_status'], 'Rejected') !== false) $status_badge = 'danger';
                                    elseif (strpos($row['client_status'], 'Review') !== false) $status_badge = 'warning';
                                    elseif ($row['client_status'] == 'Awaiting Client Action') $status_badge = 'warning';
                                    elseif ($row['client_status'] == 'Signed – Move to Finance') $status_badge = 'success';
                                    elseif ($row['client_status'] == 'Inactive') $status_badge = 'secondary';

                                    echo "<tr id='client-row-{$row['client_id']}'>";
                                    echo "<td>" . htmlspecialchars($row['client_id']) . "</td>";
                                    echo "<td><strong>" . htmlspecialchars($row['company_name']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['contact_name']) . "</td>";
                                    echo "<td><a href='mailto:" . htmlspecialchars($row['contact_email']) . "'>" . htmlspecialchars($row['contact_email']) . "</a></td>";
                                    echo "<td>" . htmlspecialchars($row['contact_mobile']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['country']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jurisdiction'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['industry'] ?? 'N/A') . "</td>";
                                    echo "<td><span class='badge bg-{$status_badge}'>" . htmlspecialchars($row['client_status']) . "</span></td>";
                                    echo "<td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
                                    echo "<td>";
                                    echo "<div class='btn-group btn-group-sm' role='group'>";
                                    echo "<button class='btn btn-outline-info' onclick='loadClientDetails({$row['client_id']})' title='View Details'><i class='bi bi-eye'></i></button> ";
                                    echo "<a href='clients.php?source=edit_client&id={$row['client_id']}' class='btn btn-outline-warning' title='Edit'><i class='bi bi-pencil'></i></a> ";
                                    // Hide delete button for manager role
                                    if (!isset($user_role_id)) {
                                        // Get user role if not already set (for direct access)
                                        $current_user_id = $_SESSION['user_id'] ?? 0;
                                        $user_role_id = null;
                                        $user_role_name = null;
                                        if ($current_user_id > 0) {
                                            $role_query = "SELECT r.role_id, r.role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id WHERE u.user_id = $current_user_id";
                                            $role_result = mysqli_query($connection, $role_query);
                                            if ($role_result && mysqli_num_rows($role_result) > 0) {
                                                $user_role = mysqli_fetch_assoc($role_result);
                                                $user_role_id = $user_role['role_id'];
                                                $user_role_name = $user_role['role_name'];
                                            }
                                        }
                                    }
                                    $is_manager = ($user_role_id == 2 || strtolower($user_role_name ?? '') == 'manager');
                                    if (!$is_manager) {
                                        echo "<button class='btn btn-outline-danger' onclick=\"confirmDelete({$row['client_id']},'" . addslashes(htmlspecialchars($row['company_name'])) . "')\" title='Delete'><i class='bi bi-trash'></i></button>";
                                    }
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

<!-- Client Details Modal -->
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
    const statusFilter = document.getElementById('status_filter');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    
    if (statusFilter) statusFilter.value = urlParams.get('status_filter') || '';
    if (dateFrom) dateFrom.value = urlParams.get('date_from') || '';
    if (dateTo) dateTo.value = urlParams.get('date_to') || '';
});

// Filter clients function
function filterClients() {
    const status = document.getElementById('status_filter')?.value || '';
    const dateFrom = document.getElementById('date_from')?.value || '';
    const dateTo = document.getElementById('date_to')?.value || '';
    
    let url = 'clients.php?';
    const params = [];
    
    if (status) params.push(`status_filter=${encodeURIComponent(status)}`);
    if (dateFrom) params.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo) params.push(`date_to=${encodeURIComponent(dateTo)}`);
    
    window.location.href = url + params.join('&');
}

// Load client details modal
function loadClientDetails(clientId) {
    const modalEl = document.getElementById('clientDetailsModal');
    const modalBody = document.getElementById('clientDetailsModalBody');
    const editBtn = document.getElementById('editClientBtn');
    
    if (!modalEl || !modalBody) return;
    
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading client details...</p></div>';
    
    if (editBtn) editBtn.href = 'clients.php?source=edit_client&id=' + clientId;
    
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
    
    fetch('get_client_details.php?id=' + encodeURIComponent(clientId), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }
        return response.text();
    })
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
    const deleteClientNameSpan = document.getElementById('deleteClientName');
    const deleteWarningDiv = document.getElementById('deleteWarning');
    
    if (deleteClientNameSpan) deleteClientNameSpan.textContent = companyName;
    if (deleteWarningDiv) deleteWarningDiv.style.display = 'none';
    
    // Check if client has related records
    fetch('check_client_dependencies.php?id=' + clientId)
        .then(response => response.json())
        .then(data => {
            if (data.has_dependencies && deleteWarningDiv) {
                deleteWarningDiv.style.display = 'block';
                deleteWarningDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' +
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

// Handle delete confirmation button click
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function() {
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
                if (modal) modal.hide();
                
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
}

// Show success toast
function showSuccess(message) {
    const toastEl = document.getElementById('successToast');
    const successMessageSpan = document.getElementById('successMessage');
    
    if (!toastEl) return;
    if (successMessageSpan) successMessageSpan.textContent = message;
    
    const toast = new bootstrap.Toast(toastEl, { autohide: false });
    toast.show();
    setTimeout(() => toast.hide(), 3000);
}

// Show error toast
function showError(message) {
    const toastEl = document.getElementById('errorToast');
    const errorMessageSpan = document.getElementById('errorMessage');
    
    if (!toastEl) return;
    if (errorMessageSpan) errorMessageSpan.textContent = message;
    
    const toast = new bootstrap.Toast(toastEl, { autohide: false });
    toast.show();
    setTimeout(() => toast.hide(), 3000);
}

// Make functions globally available
window.loadClientDetails = loadClientDetails;
window.confirmDelete = confirmDelete;
window.filterClients = filterClients;
</script>