<?php
// Get available years
$years_query = "SELECT DISTINCT YEAR(created_at) as year 
                FROM points_ledger 
                WHERE employee_id = $user_id 
                ORDER BY year DESC";
$years_result = mysqli_query($connection, $years_query);

$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;

// Get monthly stats for selected year
$monthly_query = "SELECT 
    MONTH(created_at) as month,
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as deducted,
    COUNT(*) as transactions
    FROM points_ledger 
    WHERE employee_id = $user_id AND YEAR(created_at) = $selected_year
    GROUP BY MONTH(created_at)
    ORDER BY month ASC";
$monthly_result = mysqli_query($connection, $monthly_query);

// Get yearly totals
$yearly_query = "SELECT 
    COALESCE(SUM(CASE WHEN points_type IN ('EARNED', 'ADJUSTMENT') THEN points ELSE 0 END), 0) as total_earned,
    COALESCE(SUM(CASE WHEN points_type = 'DEDUCTED' THEN points ELSE 0 END), 0) as total_deducted,
    COUNT(*) as total_transactions
    FROM points_ledger 
    WHERE employee_id = $user_id AND YEAR(created_at) = $selected_year";
$yearly_result = mysqli_query($connection, $yearly_query);
$yearly = mysqli_fetch_assoc($yearly_result);
$yearly_net = $yearly['total_earned'] - $yearly['total_deducted'];

// Prepare data for chart
$months_data = [];
for ($m = 1; $m <= 12; $m++) {
    $months_data[$m] = ['earned' => 0, 'deducted' => 0, 'transactions' => 0];
}

while ($row = mysqli_fetch_assoc($monthly_result)) {
    $months_data[$row['month']] = [
        'earned' => $row['earned'],
        'deducted' => $row['deducted'],
        'transactions' => $row['transactions']
    ];
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-2"><i class="bi bi-calendar-month me-2"></i>Monthly Breakdown</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="wallet.php">Wallet</a></li>
                    <li class="breadcrumb-item active">Monthly</li>
                </ol>
            </nav>
        </div>
        <a href="wallet.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Summary
        </a>
    </div>

    <!-- Year Selector -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="year-selector">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="source" value="monthly">
                    <div class="col">
                        <label class="form-label">Select Year</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            <?php while($year = mysqli_fetch_assoc($years_result)): ?>
                                <option value="<?php echo $year['year']; ?>" <?php echo $selected_year == $year['year'] ? 'selected' : ''; ?>>
                                    <?php echo $year['year']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-9">
            <div class="year-summary">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <span class="stat-label">Total Earned</span>
                            <span class="stat-value text-success">+<?php echo number_format($yearly['total_earned']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <span class="stat-label">Total Deducted</span>
                            <span class="stat-value text-danger">-<?php echo number_format($yearly['total_deducted']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <span class="stat-label">Net Points</span>
                            <span class="stat-value <?php echo $yearly_net >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $yearly_net >= 0 ? '+' : ''; ?><?php echo number_format($yearly_net); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="card shadow-sm mb-4">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-bar-chart me-2"></i>Monthly Points - <?php echo $selected_year; ?>
            </h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" style="height: 300px;"></canvas>
        </div>
    </div>

    <!-- Monthly Table -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-table me-2"></i>Monthly Details
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Earned</th>
                            <th class="text-end">Deducted</th>
                            <th class="text-end">Net</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-end">Cashable</th>
                            <th class="text-end">AED Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $year_total_earned = 0;
                        $year_total_deducted = 0;
                        $year_total_cashable = 0;
                        
                        for($m = 1; $m <= 12; $m++): 
                            $month_name = date('F', mktime(0, 0, 0, $m, 1));
                            $earned = $months_data[$m]['earned'];
                            $deducted = $months_data[$m]['deducted'];
                            $net = $earned - $deducted;
                            $cashable = max(0, $net - 1000);
                            
                            $year_total_earned += $earned;
                            $year_total_deducted += $deducted;
                            $year_total_cashable += $cashable;
                            
                            if ($earned > 0 || $deducted > 0):
                        ?>
                        <tr>
                            <td><strong><?php echo $month_name; ?></strong></td>
                            <td class="text-end text-success">+<?php echo number_format($earned); ?></td>
                            <td class="text-end text-danger">-<?php echo number_format($deducted); ?></td>
                            <td class="text-end fw-bold <?php echo $net >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $net >= 0 ? '+' : ''; ?><?php echo number_format($net); ?>
                            </td>
                            <td class="text-center"><?php echo $months_data[$m]['transactions']; ?></td>
                            <td class="text-end <?php echo $cashable > 0 ? 'text-success' : 'text-muted'; ?>">
                                <?php echo $cashable > 0 ? number_format($cashable) : '-'; ?>
                            </td>
                            <td class="text-end <?php echo $cashable > 0 ? 'text-success' : 'text-muted'; ?>">
                                <?php echo $cashable > 0 ? 'AED ' . number_format($cashable) : '-'; ?>
                            </td>
                        </tr>
                        <?php endif; endfor; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Year Total</th>
                            <th class="text-end text-success">+<?php echo number_format($year_total_earned); ?></th>
                            <th class="text-end text-danger">-<?php echo number_format($year_total_deducted); ?></th>
                            <th class="text-end"><?php echo number_format($year_total_earned - $year_total_deducted); ?></th>
                            <th class="text-center"><?php echo $yearly['total_transactions']; ?></th>
                            <th class="text-end text-success"><?php echo number_format($year_total_cashable); ?></th>
                            <th class="text-end text-success">AED <?php echo number_format($year_total_cashable); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="info-card">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill text-primary fs-4 me-3"></i>
                    <div>
                        <strong>Cashable Points Calculation:</strong> For each month, cashable points = max(0, monthly total - 1,000). 
                        Cashable points are paid quarterly at AED 1 per point.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const earnedData = [
        <?php for($m = 1; $m <= 12; $m++): ?>
            <?php echo $months_data[$m]['earned']; ?>,
        <?php endfor; ?>
    ];
    const deductedData = [
        <?php for($m = 1; $m <= 12; $m++): ?>
            <?php echo $months_data[$m]['deducted']; ?>,
        <?php endfor; ?>
    ];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthNames,
            datasets: [
                {
                    label: 'Earned',
                    data: earnedData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Deducted',
                    data: deductedData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Points'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + ' points';
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
.year-selector {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.year-summary {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.summary-stat {
    text-align: center;
    padding: 10px;
}

.summary-stat .stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.summary-stat .stat-value {
    font-size: 1.3rem;
    font-weight: 600;
}

.dark-header {
    background: #1e293b;
    color: white;
    padding: 12px 20px;
}

.dark-header .card-title {
    color: white;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.info-card {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #dee2e6;
}

.table tfoot {
    font-weight: 600;
    background: #f8f9fa;
}
</style>