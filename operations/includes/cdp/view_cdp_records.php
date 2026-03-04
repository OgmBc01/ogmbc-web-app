<?php
// Get all CDP records for this user
$query = "SELECT * FROM cdp_records 
          WHERE employee_id = $user_id 
          ORDER BY 
            CASE status
                WHEN 'PENDING' THEN 1
                WHEN 'APPROVED' THEN 2
                WHEN 'REJECTED' THEN 3
                ELSE 4
            END,
            created_at DESC";
$result = mysqli_query($connection, $query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN cdp_type = 'CERTIFICATE' THEN 1 ELSE 0 END) as certificates,
    SUM(CASE WHEN cdp_type = 'COURSE' THEN 1 ELSE 0 END) as courses,
    SUM(CASE WHEN cdp_type = 'LOYALTY' THEN 1 ELSE 0 END) as loyalty,
    SUM(CASE WHEN cdp_type = 'BEHAVIOR' THEN 1 ELSE 0 END) as behavior,
    COALESCE(SUM(uplift_percentage), 0) as total_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get uplift by year
$uplift_by_year_query = "SELECT 
    YEAR(effective_date) as year,
    SUM(uplift_percentage) as yearly_uplift
    FROM cdp_records 
    WHERE employee_id = $user_id AND status = 'APPROVED'
    GROUP BY YEAR(effective_date)
    ORDER BY year DESC";
$uplift_by_year = mysqli_query($connection, $uplift_by_year_query);
?>

<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="welcome-card d-flex flex-column flex-md-row align-items-center justify-content-between mb-3">
                <div>
                    <div class="welcome-title mb-1"><i class="bi bi-mortarboard me-2"></i>Career Development</div>
                    <div class="welcome-subtitle">Track your certifications, courses, and professional growth.</div>
                </div>
                <div class="current-date mt-3 mt-md-0">
                    <i class="bi bi-calendar3 me-2"></i> <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-primary-soft">
                        <i class="bi bi-mortarboard text-primary"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $stats['total'] ?? 0; ?></h3>
                        <p class="stat-label">Total Records</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-success-soft">
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $stats['approved'] ?? 0; ?></h3>
                        <p class="stat-label">Approved</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-warning-soft">
                        <i class="bi bi-clock-history text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $stats['pending'] ?? 0; ?></h3>
                        <p class="stat-label">Pending</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon bg-info-soft">
                        <i class="bi bi-percent text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value"><?php echo $stats['total_uplift']; ?>%</h3>
                        <p class="stat-label">Total Uplift</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row - Type Breakdown -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="type-card certificate">
                <i class="bi bi-patch-check"></i>
                <div class="type-details">
                    <span class="type-label">Certificates</span>
                    <span class="type-value"><?php echo $stats['certificates'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="type-card course">
                <i class="bi bi-book"></i>
                <div class="type-details">
                    <span class="type-label">Courses</span>
                    <span class="type-value"><?php echo $stats['courses'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="type-card loyalty">
                <i class="bi bi-star"></i>
                <div class="type-details">
                    <span class="type-label">Loyalty</span>
                    <span class="type-value"><?php echo $stats['loyalty'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="type-card behavior">
                <i class="bi bi-heart"></i>
                <div class="type-details">
                    <span class="type-label">Behavior</span>
                    <span class="type-value"><?php echo $stats['behavior'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Uplift by Year -->
    <?php if ($uplift_by_year && mysqli_num_rows($uplift_by_year) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="uplift-summary-card">
                <h6 class="mb-3"><i class="bi bi-graph-up me-2"></i>Uplift by Year</h6>
                <div class="row">
                    <?php while($year = mysqli_fetch_assoc($uplift_by_year)): ?>
                    <div class="col-md-3">
                        <div class="year-uplift">
                            <span class="year"><?php echo $year['year']; ?></span>
                            <span class="uplift">+<?php echo $year['yearly_uplift']; ?>%</span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- CDP Records List -->
    <div class="card shadow-sm">
        <div class="card-header dark-header">
            <h5 class="card-title">
                <i class="bi bi-list-ul me-2"></i>My CDP Records
            </h5>
            <div class="d-flex gap-2">
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-light" onclick="filterRecords('all')">All</button>
                    <button class="btn btn-sm btn-outline-light" onclick="filterRecords('PENDING')">Pending</button>
                    <button class="btn btn-sm btn-outline-light" onclick="filterRecords('APPROVED')">Approved</button>
                </div>
                <a href="cdp.php?source=add" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Add Record
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="cdp-records-list" id="cdpRecordsList">
                    <?php while($record = mysqli_fetch_assoc($result)): 
                        $type_class = 'secondary';
                        $type_icon = 'patch-check';
                        
                        switch($record['cdp_type']) {
                            case 'CERTIFICATE':
                                $type_class = 'success';
                                $type_icon = 'patch-check';
                                break;
                            case 'COURSE':
                                $type_class = 'info';
                                $type_icon = 'book';
                                break;
                            case 'LOYALTY':
                                $type_class = 'warning';
                                $type_icon = 'star';
                                break;
                            case 'BEHAVIOR':
                                $type_class = 'primary';
                                $type_icon = 'heart';
                                break;
                        }
                        
                        $status_class = 'warning';
                        $status_icon = 'clock-history';
                        if ($record['status'] == 'APPROVED') {
                            $status_class = 'success';
                            $status_icon = 'check-circle';
                        } elseif ($record['status'] == 'REJECTED') {
                            $status_class = 'danger';
                            $status_icon = 'x-circle';
                        }
                    ?>
                    <div class="cdp-record-item" data-status="<?php echo $record['status']; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <div class="cdp-type-icon bg-<?php echo $type_class; ?>-soft">
                                        <i class="bi bi-<?php echo $type_icon; ?> text-<?php echo $type_class; ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">
                                            <a href="cdp.php?source=view&id=<?php echo $record['cdp_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($record['title']); ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($record['effective_date'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-<?php echo $type_class; ?>">
                                    <i class="bi bi-<?php echo $type_icon; ?> me-1"></i>
                                    <?php echo $record['cdp_type']; ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <?php if ($record['uplift_percentage']): ?>
                                    <span class="badge bg-success">+<?php echo $record['uplift_percentage']; ?>% uplift</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-1">
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="bi bi-<?php echo $status_icon; ?> me-1"></i>
                                    <?php echo $record['status']; ?>
                                </span>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-info" onclick="viewCDP(<?php echo $record['cdp_id']; ?>)" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if ($record['status'] == 'PENDING'): ?>
                                    <a href="cdp.php?source=edit&id=<?php echo $record['cdp_id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $record['cdp_id']; ?>, '<?php echo htmlspecialchars($record['title'], ENT_QUOTES); ?>')" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($record['description'])): ?>
                        <div class="record-description mt-2 text-muted">
                            <small><i class="bi bi-chat me-1"></i><?php echo htmlspecialchars(substr($record['description'], 0, 100)) . (strlen($record['description']) > 100 ? '...' : ''); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-mortarboard display-1 text-muted"></i>
                    <h5 class="mt-3">No CDP Records Yet</h5>
                    <p class="text-muted">Start building your professional development portfolio.</p>
                    <a href="cdp.php?source=add" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>Add Your First Record
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pro Tip Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="pro-tip-card cdp-tip gradient-bg">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h6 class="text-white mb-2">
                            <i class="bi bi-lightbulb me-2"></i>
                            CDP Tips
                        </h6>
                        <ul class="pro-tip-list small mb-md-0">
                            <li>📜 Certificates: +18% for Ops, +15% for Sales (annual uplift)</li>
                            <li>📚 Courses: +7% for Ops, +5% for Sales (annual uplift)</li>
                            <li>⭐ Loyalty: +3% for both departments</li>
                            <li>💝 Behavior: +2% for both departments</li>
                            <li>📅 Uplifts apply to the annual performance cycle based on effective date</li>
                        </ul>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <i class="bi bi-graph-up display-4 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.18);
    padding: 28px 24px;
    margin-bottom: 24px;
}
.pro-tip-list {
    color: #fff !important;
    opacity: 0.97;
    text-shadow: 0 1px 2px rgba(60,60,60,0.10);
}

.type-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    transition: all 0.3s ease;
    height: 100%;
}

.type-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.type-card i {
    font-size: 2rem;
}

.type-card.certificate i { color: #28a745; }
.type-card.course i { color: #17a2b8; }
.type-card.loyalty i { color: #ffc107; }
.type-card.behavior i { color: #007bff; }

.type-details {
    flex: 1;
}

.type-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 3px;
}

.type-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.uplift-summary-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.year-uplift {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

.year-uplift .year {
    display: block;
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.year-uplift .uplift {
    font-size: 1.3rem;
    font-weight: 600;
    color: #28a745;
}

.cdp-record-item {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.cdp-record-item:hover {
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transform: translateX(5px);
}

.cdp-type-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.record-description {
    padding-left: 60px;
    border-left: 2px solid #dee2e6;
    margin-left: 22px;
}

.pro-tip-card.cdp-tip {
    background: linear-gradient(135deg, #2c3e50 0%, #1a2634 100%);
    border-radius: 16px;
    padding: 20px;
    color: white;
}

.pro-tip-card ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .type-card {
        padding: 15px;
    }
    
    .type-value {
        font-size: 1.2rem;
    }
    
    .cdp-record-item .row {
        gap: 10px;
    }
    
    .cdp-record-item .text-end {
        text-align: left !important;
    }
    
    .record-description {
        padding-left: 0;
        margin-left: 0;
        border-left: none;
        border-top: 1px solid #dee2e6;
        padding-top: 10px;
        margin-top: 10px;
    }
}
</style>

<script>
function filterRecords(status) {
    const items = document.querySelectorAll('.cdp-record-item');
    items.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>