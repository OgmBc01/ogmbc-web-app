<?php
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/sidebar.php';

// Initialize session and check permission
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'PENDING';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-2"><i class="bi bi-cash-stack me-2"></i>Points Redemption Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Redemptions</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button class="btn btn-outline-primary" onclick="exportRedemptions()">
                    <i class="bi bi-download me-1"></i>Export
                </button>
            </div>
        </div>

        <!-- Status Tabs -->
        <ul class="nav nav-tabs mb-4" id="redemptionTabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter == 'PENDING' ? 'active' : ''; ?>" href="?status=PENDING&search=<?php echo urlencode($search); ?>">
                    <i class="bi bi-clock-history me-1"></i>Pending 
                    <?php
                    $count_query = "SELECT COUNT(*) as total FROM points_redemption_requests WHERE status = 'PENDING'";
                    $count_result = mysqli_query($connection, $count_query);
                    $pending_count = mysqli_fetch_assoc($count_result)['total'];
                    if ($pending_count > 0): ?>
                        <span class="badge bg-warning text-dark"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter == 'APPROVED' ? 'active' : ''; ?>" href="?status=APPROVED&search=<?php echo urlencode($search); ?>">
                    <i class="bi bi-check-circle me-1"></i>Approved
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter == 'REJECTED' ? 'active' : ''; ?>" href="?status=REJECTED&search=<?php echo urlencode($search); ?>">
                    <i class="bi bi-x-circle me-1"></i>Rejected
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status_filter == 'ALL' ? 'active' : ''; ?>" href="?status=ALL&search=<?php echo urlencode($search); ?>">
                    <i class="bi bi-list-ul me-1"></i>All Requests
                </a>
            </li>
        </ul>

        <!-- Search and Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search by employee name or ID..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Redemption Requests Table -->
        <div class="card shadow-sm">
            <div class="card-header dark-header">
                <h5 class="card-title">
                    <i class="bi bi-list-ul me-2"></i>
                    Redemption Requests - <?php echo $status_filter == 'ALL' ? 'All' : $status_filter; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php
                // Build query
                $where = [];
                if ($status_filter != 'ALL') {
                    $where[] = "rr.status = '$status_filter'";
                }
                if (!empty($search)) {
                    $where[] = "(e.first_name LIKE '%$search%' OR e.last_name LIKE '%$search%' OR e.employee_id LIKE '%$search%')";
                }
                $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                
                $query = "SELECT rr.*, 
                                 e.first_name, e.last_name, e.employee_id,
                                 u.username
                          FROM points_redemption_requests rr
                          JOIN employees e ON rr.employee_id = e.user_id
                          JOIN users u ON rr.employee_id = u.user_id
                          $where_clause
                          ORDER BY 
                              CASE WHEN rr.status = 'PENDING' THEN 1 ELSE 2 END,
                              rr.requested_at DESC";
                
                $result = mysqli_query($connection, $query);
                
                if (mysqli_num_rows($result) > 0):
                ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Request ID</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Points Requested</th>
                                    <th>Amount</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($request = mysqli_fetch_assoc($result)):
                                    $month_name = date('F', mktime(0, 0, 0, $request['month'], 1));
                                    $status_class = 'secondary';
                                    $status_icon = 'question-circle';
                                    if ($request['status'] == 'PENDING') {
                                        $status_class = 'warning';
                                        $status_icon = 'clock-history';
                                    } elseif ($request['status'] == 'APPROVED') {
                                        $status_class = 'success';
                                        $status_icon = 'check-circle';
                                    } elseif ($request['status'] == 'REJECTED') {
                                        $status_class = 'danger';
                                        $status_icon = 'x-circle';
                                    }
                                ?>
                                    <tr>
                                        <td>#<?php echo $request['request_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo $request['employee_id']; ?></small>
                                        </td>
                                        <td><?php echo $month_name . ' ' . $request['year']; ?></td>
                                        <td class="fw-bold"><?php echo number_format($request['points_requested']); ?> pts</td>
                                        <td class="text-success fw-bold">AED <?php echo number_format($request['points_requested'], 2); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($request['requested_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                                <?php echo $request['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(substr($request['notes'] ?? '', 0, 50)); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] == 'PENDING'): ?>
                                                <button class="btn btn-sm btn-success me-1" onclick="approveRedemption(<?php echo $request['request_id']; ?>, <?php echo $request['points_requested']; ?>, '<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>')">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectRedemption(<?php echo $request['request_id']; ?>, '<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>')">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="viewDetails(<?php echo $request['request_id']; ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state text-center py-5">
                        <i class="bi bi-cash-stack display-1 text-muted"></i>
                        <h5 class="mt-3">No Redemption Requests Found</h5>
                        <p class="text-muted">No requests match your current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Approve Redemption Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST" action="includes/redemptions/approve_redemption.php">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Approving this request will deduct the points from the employee's ledger and mark the request as approved.
                    </div>
                    <p><strong>Employee:</strong> <span id="approveEmployee"></span></p>
                    <p><strong>Points:</strong> <span id="approvePoints"></span> pts (AED <span id="approveAmount"></span>)</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    <input type="hidden" name="request_id" id="approveRequestId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Redemption Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST" action="includes/redemptions/reject_redemption.php">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Rejecting this request will not deduct any points. The employee will be notified.
                    </div>
                    <p><strong>Employee:</strong> <span id="rejectEmployee"></span></p>
                    <p><strong>Points:</strong> <span id="rejectPoints"></span> pts</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                    <input type="hidden" name="request_id" id="rejectRequestId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0a2348;">
                <h5 class="modal-title" style="color: #f1bf70;"><i class="bi bi-receipt me-2"></i>Redemption Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveRedemption(id, points, employee) {
    document.getElementById('approveRequestId').value = id;
    document.getElementById('approveEmployee').innerText = employee;
    document.getElementById('approvePoints').innerText = points;
    document.getElementById('approveAmount').innerText = points;
    
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectRedemption(id, employee) {
    document.getElementById('rejectRequestId').value = id;
    document.getElementById('rejectEmployee').innerText = employee;
    
    // Get points from the row
    const row = event.target.closest('tr');
    const pointsCell = row.cells[3];
    document.getElementById('rejectPoints').innerText = pointsCell.innerText;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function viewDetails(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const contentDiv = document.getElementById('detailsContent');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/redemptions/get_redemption_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const r = data.data;
                const statusClass = r.status === 'PENDING' ? 'warning' : (r.status === 'APPROVED' ? 'success' : 'danger');
                
                contentDiv.innerHTML = `
                    <div class="redemption-details">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Request ID</span>
                                    <span class="detail-value">#${r.request_id}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="badge bg-${statusClass}">${r.status}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Employee</span>
                                    <span class="detail-value">${r.employee_name} (${r.employee_id})</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Period</span>
                                    <span class="detail-value">${r.month_name} ${r.year}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Points Requested</span>
                                    <span class="detail-value fw-bold">${r.points_requested} pts</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Amount</span>
                                    <span class="detail-value text-success fw-bold">AED ${r.points_requested}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Requested At</span>
                                    <span class="detail-value">${new Date(r.requested_at).toLocaleString()}</span>
                                </div>
                            </div>
                            ${r.reviewed_at ? `
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Reviewed At</span>
                                    <span class="detail-value">${new Date(r.reviewed_at).toLocaleString()}</span>
                                </div>
                            </div>
                            ` : ''}
                            ${r.employee_notes ? `
                            <div class="col-12">
                                <div class="detail-card">
                                    <span class="detail-label">Employee Notes</span>
                                    <span class="detail-value">${r.employee_notes}</span>
                                </div>
                            </div>
                            ` : ''}
                            ${r.admin_notes ? `
                            <div class="col-12">
                                <div class="detail-card">
                                    <span class="detail-label">Admin Notes</span>
                                    <span class="detail-value">${r.admin_notes}</span>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error loading details</div>`;
        });
}

function exportRedemptions() {
    const status = '<?php echo $status_filter; ?>';
    const search = '<?php echo $search; ?>';
    window.location.href = 'includes/redemptions/export_redemptions.php?status=' + status + '&search=' + encodeURIComponent(search);
}
</script>

<style>
.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 12px 12px 0 0;
}
.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.detail-card {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
}
.detail-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 4px;
}
.detail-value {
    font-weight: 500;
}
</style>

<?php include 'includes/footer.php'; ?>