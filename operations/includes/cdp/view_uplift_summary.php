<?php
// Start session at the beginning
session_start();

// Ensure $user_id is set from session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Get all approved CDP records grouped by year
$query = "SELECT 
    YEAR(effective_date) as year,
    cdp_type,
    COUNT(*) as count,
    SUM(uplift_percentage) as total_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id AND status = 'APPROVED'
    GROUP BY YEAR(effective_date), cdp_type
    ORDER BY year DESC, cdp_type";

$result = mysqli_query($connection, $query);

// Get yearly totals
$yearly_query = "SELECT 
    YEAR(effective_date) as year,
    SUM(uplift_percentage) as yearly_total
    FROM cdp_records 
    WHERE employee_id = $user_id AND status = 'APPROVED'
    GROUP BY YEAR(effective_date)
    ORDER BY year DESC";
$yearly_result = mysqli_query($connection, $yearly_query);

// Get lifetime stats
$lifetime_query = "SELECT 
    COUNT(*) as total_records,
    COUNT(DISTINCT YEAR(effective_date)) as years_active,
    SUM(uplift_percentage) as lifetime_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id AND status = 'APPROVED'";
$lifetime_result = mysqli_query($connection, $lifetime_query);
$lifetime = mysqli_fetch_assoc($lifetime_result);

// Organize data by year
$uplift_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $year = $row['year'];
    if (!isset($uplift_data[$year])) {
        $uplift_data[$year] = [];
    }
    $uplift_data[$year][$row['cdp_type']] = [
        'count' => $row['count'],
        'total' => $row['total_uplift']
    ];
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="uplift-header">
                <h4><i class="bi bi-graph-up me-2"></i>Uplift Summary</h4>
                <p class="text-muted mb-0">How your CDP records contribute to annual performance</p>
            </div>
        </div>
    </div>

    <!-- Lifetime Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-primary-soft">
                        <i class="bi bi-mortarboard text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $lifetime['total_records'] ?? 0; ?></h3>
                        <p class="stat-label">Total Approved Records</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-calendar-check text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $lifetime['years_active'] ?? 0; ?></h3>
                        <p class="stat-label">Years Active</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-info-soft">
                        <i class="bi bi-percent text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $lifetime['lifetime_uplift'] ?? 0; ?>%</h3>
                        <p class="stat-label">Lifetime Uplift</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Uplift by Year -->
    <?php if (!empty($uplift_data) && $yearly_result && mysqli_num_rows($yearly_result) > 0): ?>
        <?php while($year_row = mysqli_fetch_assoc($yearly_result)): 
            $year = $year_row['year'];
            $yearly_total = $year_row['yearly_total'];
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header dark-header">
                <h5 class="card-title">
                    <i class="bi bi-calendar-year me-2"></i><?php echo $year; ?> Uplift Summary
                </h5>
                <span class="badge bg-success">Total: +<?php echo $yearly_total; ?>%</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $type_order = ['CERTIFICATE', 'COURSE', 'LOYALTY', 'BEHAVIOR'];
                    $has_data = false;
                    foreach ($type_order as $type):
                        $data = $uplift_data[$year][$type] ?? null;
                        if (!$data) continue;
                        
                        $has_data = true;
                        $type_color = 'success';
                        $type_icon = 'patch-check';
                        if ($type == 'COURSE') {
                            $type_color = 'info';
                            $type_icon = 'book';
                        } elseif ($type == 'LOYALTY') {
                            $type_color = 'warning';
                            $type_icon = 'star';
                        } elseif ($type == 'BEHAVIOR') {
                            $type_color = 'primary';
                            $type_icon = 'heart';
                        }
                    ?>
                    <div class="col-md-3">
                        <div class="type-uplift-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="type-icon bg-<?php echo $type_color; ?>-soft me-3">
                                    <i class="bi bi-<?php echo $type_icon; ?> text-<?php echo $type_color; ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $type; ?></h6>
                                    <small class="text-muted"><?php echo $data['count']; ?> record(s)</small>
                                </div>
                            </div>
                            <div class="uplift-bar">
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-<?php echo $type_color; ?>" 
                                         style="width: <?php echo min(100, $data['total'] * 5); ?>%"></div>
                                </div>
                                <span class="uplift-value">+<?php echo $data['total']; ?>%</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (!$has_data): ?>
                        <div class="col-12">
                            <p class="text-muted text-center mb-0">No CDP records for this year</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Yearly Impact -->
                <div class="yearly-impact mt-4">
                    <h6 class="mb-3">Impact on Annual Performance</h6>
                    <div class="impact-calculation">
                        <div class="calculation-item">
                            <span>Base points conversion:</span>
                            <span>70% (Ops)</span>
                        </div>
                        <div class="calculation-item">
                            <span>CDP Uplift:</span>
                            <span class="text-success">+<?php echo $yearly_total; ?>%</span>
                        </div>
                        <div class="calculation-item total">
                            <span>Potential Total:</span>
                            <span><?php echo min(100, 70 + $yearly_total); ?>% (Ops)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-graph-up display-1 text-muted"></i>
            <h5 class="mt-3">No Uplift Data Yet</h5>
            <p class="text-muted">Approved CDP records will appear here and contribute to your annual performance.</p>
            <a href="cdp.php?source=add" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Add Your First CDP Record
            </a>
        </div>
    <?php endif; ?>

    <!-- Uplift Guide -->
    <div class="uplift-guide mt-4">
        <h6 class="mb-3"><i class="bi bi-question-circle me-2"></i>How Uplift Works</h6>
        <div class="row">
            <div class="col-md-6">
                <div class="guide-card">
                    <h6>Operations Staff</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-success me-2">Certificates</span> +18% each</li>
                        <li><span class="badge bg-info me-2">Courses</span> +7% each</li>
                        <li><span class="badge bg-warning me-2">Loyalty</span> +3% each</li>
                        <li><span class="badge bg-primary me-2">Behavior</span> +2% each</li>
                    </ul>
                </div>
            </div>
        </div>
        <p class="text-muted small mt-3">
            * Uplifts are capped at 100% total annual performance. CDP records apply to the year of their effective date.
        </p>
    </div>
</div>

<style>
.uplift-header {
    background: linear-gradient(135deg, #0a2342 0%, #193a5e 100%);
    border-radius: 16px;
    padding: 25px;
    color: white;
}

.stat-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card-body {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.stat-label {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.type-uplift-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    height: 100%;
}

.type-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.uplift-bar {
    display: flex;
    align-items: center;
    gap: 10px;
}

.uplift-bar .progress {
    flex: 1;
}

.uplift-value {
    font-weight: 600;
    color: #28a745;
    min-width: 50px;
    text-align: right;
}

.yearly-impact {
    background: #e8f4fd;
    border-radius: 12px;
    padding: 15px;
}

.impact-calculation {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.calculation-item {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px dashed #dee2e6;
}

.calculation-item.total {
    border-bottom: none;
    font-weight: 600;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 2px solid #dee2e6;
}

.uplift-guide {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.guide-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    height: 100%;
}

.guide-card ul {
    margin-top: 10px;
}

.guide-card li {
    margin-bottom: 8px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
</style>