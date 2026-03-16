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

// Get current date for context
$current_year = date('Y');
$current_month = date('m');
?>

<div class="main-content" id="mainContent">
    <div class="container-fluid">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Points Wallet</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['source'])) {
                    $source = $_GET['source'];
                } else {
                    $source = 'summary';
                }

                switch($source) {
                    case 'history':
                        include "includes/wallet/view_points_history.php";
                        break;
                    case 'monthly':
                        include "includes/wallet/view_monthly_breakdown.php";
                        break;
                    case 'transaction':
                        include "includes/wallet/view_transaction_details.php";
                        break;
                    default:
                        include "includes/wallet/view_wallet_summary.php";
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="transactionModalLabel">
                    <i class="bi bi-receipt me-2"></i>Transaction Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Content will be loaded via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transaction details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View transaction details
function viewTransaction(id) {
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    const contentDiv = document.getElementById('transactionDetails');
    
    contentDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading transaction details...</p>
        </div>
    `;
    
    modal.show();
    
    fetch('includes/ajax/get_transaction_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const t = data.data;
                const typeClass = t.points_type === 'EARNED' ? 'success' : 
                                 (t.points_type === 'DEDUCTED' ? 'danger' : 'warning');
                const sign = t.points_type === 'EARNED' ? '+' : 
                            (t.points_type === 'DEDUCTED' ? '-' : '±');
                
                let calculationHtml = '';
                if (t.calculation_data) {
                    calculationHtml = `
                        <div class="calculation-data mt-3">
                            <h6 class="section-title">Calculation Details</h6>
                            <pre class="bg-light p-3 rounded"><code>${JSON.stringify(t.calculation_data, null, 2)}</code></pre>
                        </div>
                    `;
                }
                
                contentDiv.innerHTML = `
                    <div class="transaction-detail-view">
                        <div class="text-center mb-4">
                            <div class="display-1 mb-3">
                                <span class="badge bg-${typeClass}">${sign}${Math.abs(t.points)}</span>
                            </div>
                            <h5>${t.source.replace(/_/g, ' ')}</h5>
                            <p class="text-muted">${t.description || 'No description'}</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Transaction ID</span>
                                    <span class="detail-value">#${t.id}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">${new Date(t.created_at).toLocaleString()}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Source ID</span>
                                    <span class="detail-value">${t.source_id || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        ${t.requires_approval ? (t.approved ? 
                                            '<span class="badge bg-success">Approved</span>' : 
                                            '<span class="badge bg-warning">Pending Approval</span>') : 
                                            '<span class="badge bg-secondary">Auto-approved</span>'}
                                    </span>
                                </div>
                            </div>
                            ${t.approved ? `
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Approved By</span>
                                    <span class="detail-value">${t.approved_by || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <span class="detail-label">Approved At</span>
                                    <span class="detail-value">${t.approved_at ? new Date(t.approved_at).toLocaleString() : 'N/A'}</span>
                                </div>
                            </div>
                            ` : ''}
                            <div class="col-12">
                                <div class="detail-card">
                                    <span class="detail-label">Created By</span>
                                    <span class="detail-value">${t.created_by || 'System'}</span>
                                </div>
                            </div>
                            ${t.notes ? `
                            <div class="col-12">
                                <div class="detail-card">
                                    <span class="detail-label">Notes</span>
                                    <span class="detail-value">${t.notes}</span>
                                </div>
                            </div>
                            ` : ''}
                            ${calculationHtml}
                        </div>
                    </div>
                `;
            } else {
                contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error loading transaction details</div>`;
        });
}

// Helper function to format numbers
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<?php include 'includes/operations_footer.php'; ?>