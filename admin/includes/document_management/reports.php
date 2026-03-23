<?php

$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';
$start_date = '';
$end_date = date('Y-m-d');

switch ($date_range) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'month':
        $start_date = date('Y-m-01');
        break;
    case 'quarter':
        $start_date = date('Y-m-d', strtotime('-3 months'));
        break;
    case 'year':
        $start_date = date('Y-01-01');
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-01');
}

// If custom dates are provided
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $date_range = 'custom';
}

// Get overall statistics
$stats_query = "SELECT 
    COUNT(*) as total_documents,
    SUM(CASE WHEN document_type = 'general' THEN 1 ELSE 0 END) as general_documents,
    SUM(CASE WHEN document_type = 'specific' THEN 1 ELSE 0 END) as specific_documents,
    SUM(view_count) as total_views,
    SUM(download_count) as total_downloads,
    COUNT(CASE WHEN expires_at IS NOT NULL AND expires_at < CURDATE() THEN 1 END) as expired_documents,
    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_documents
    FROM client_documents";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get documents uploaded in date range
$period_query = "SELECT 
    COUNT(*) as documents_uploaded,
    SUM(view_count) as views_in_period,
    SUM(download_count) as downloads_in_period
    FROM client_documents
    WHERE created_at BETWEEN '$start_date' AND '$end_date'";
$period_result = mysqli_query($connection, $period_query);
$period_stats = mysqli_fetch_assoc($period_result);

// Get monthly trends (last 6 months)
$monthly_trends = [];
for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime("-$i months"));
    $month_label = date('M Y', strtotime($month_start));
    
    $trend_query = "SELECT 
        COUNT(*) as uploaded,
        SUM(view_count) as views,
        SUM(download_count) as downloads
        FROM client_documents
        WHERE created_at BETWEEN '$month_start' AND '$month_end'";
    $trend_result = mysqli_query($connection, $trend_query);
    $trend = mysqli_fetch_assoc($trend_result);
    
    $monthly_trends[] = [
        'month' => $month_label,
        'uploaded' => (int)$trend['uploaded'],
        'views' => (int)$trend['views'],
        'downloads' => (int)$trend['downloads']
    ];
}

// Get top documents by views
$top_views_query = "SELECT document_title, view_count, download_count, file_original_name
                    FROM client_documents
                    ORDER BY view_count DESC
                    LIMIT 10";
$top_views_result = mysqli_query($connection, $top_views_query);

// Get top documents by downloads
$top_downloads_query = "SELECT document_title, download_count, view_count, file_original_name
                        FROM client_documents
                        ORDER BY download_count DESC
                        LIMIT 10";
$top_downloads_result = mysqli_query($connection, $top_downloads_query);

// Get category distribution
$category_stats_query = "SELECT 
    c.category_name,
    COUNT(m.document_id) as document_count,
    SUM(d.view_count) as total_views,
    SUM(d.download_count) as total_downloads
    FROM document_categories c
    LEFT JOIN document_category_mapping m ON c.category_id = m.category_id
    LEFT JOIN client_documents d ON m.document_id = d.document_id
    WHERE c.is_active = 1
    GROUP BY c.category_id
    ORDER BY document_count DESC";
$category_stats_result = mysqli_query($connection, $category_stats_query);


// Get total unique clients with access and total specific docs
$client_access_query = "SELECT 
    COUNT(DISTINCT client_id) as total_clients_with_access,
    COUNT(DISTINCT document_id) as total_specific_docs
    FROM document_client_access
    WHERE is_active = 1";
$client_access_result = mysqli_query($connection, $client_access_query);
$client_access_stats = mysqli_fetch_assoc($client_access_result);

// Get average clients per document
$avg_clients_query = "SELECT AVG(client_count) as avg_clients_per_doc
    FROM (
        SELECT document_id, COUNT(client_id) as client_count
        FROM document_client_access
        WHERE is_active = 1
        GROUP BY document_id
    ) as access_counts";
$avg_clients_result = mysqli_query($connection, $avg_clients_query);
$avg_clients_row = mysqli_fetch_assoc($avg_clients_result);
$client_access_stats['avg_clients_per_doc'] = $avg_clients_row['avg_clients_per_doc'];

// Get activity over time (last 30 days)
$activity_query = "SELECT 
    DATE(accessed_at) as date,
    COUNT(CASE WHEN access_type = 'view' THEN 1 END) as views,
    COUNT(CASE WHEN access_type = 'download' THEN 1 END) as downloads
    FROM document_access_logs
    WHERE accessed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(accessed_at)
    ORDER BY date ASC";
$activity_result = mysqli_query($connection, $activity_query);

$activity_dates = [];
$activity_views = [];
$activity_downloads = [];
while ($row = mysqli_fetch_assoc($activity_result)) {
    $activity_dates[] = date('M d', strtotime($row['date']));
    $activity_views[] = (int)$row['views'];
    $activity_downloads[] = (int)$row['downloads'];
}
?>

<!-- Reports Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-2">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Document Analytics Report
                        </h5>
                        <p class="text-muted mb-0">
                            Comprehensive insights into document usage and performance
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <form method="GET" class="d-inline-block">
                            <input type="hidden" name="action" value="reports">
                            <select name="date_range" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                                <option value="week" <?php echo $date_range == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="month" <?php echo $date_range == 'month' ? 'selected' : ''; ?>>This Month</option>
                                <option value="quarter" <?php echo $date_range == 'quarter' ? 'selected' : ''; ?>>Last 3 Months</option>
                                <option value="year" <?php echo $date_range == 'year' ? 'selected' : ''; ?>>This Year</option>
                                <option value="custom" <?php echo $date_range == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                            </select>
                        </form>
                        <button class="btn btn-outline-primary ms-2" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                        <button class="btn btn-outline-success ms-2" onclick="exportReport()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Custom Date Range -->
                <?php if ($date_range == 'custom'): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="action" value="reports">
                            <input type="hidden" name="date_range" value="custom">
                            <div class="col-auto">
                                <label class="form-label">From:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">To:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-auto align-self-end">
                                <button type="submit" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon bg-primary-soft">
                    <i class="bi bi-files text-primary"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['total_documents']); ?></h3>
                    <p class="stat-label">Total Documents</p>
                    <small class="text-success">+<?php echo $period_stats['documents_uploaded']; ?> this period</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon bg-success-soft">
                    <i class="bi bi-eye text-success"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['total_views']); ?></h3>
                    <p class="stat-label">Total Views</p>
                    <small class="text-info">+<?php echo $period_stats['views_in_period']; ?> this period</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon bg-info-soft">
                    <i class="bi bi-download text-info"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($stats['total_downloads']); ?></h3>
                    <p class="stat-label">Total Downloads</p>
                    <small class="text-info">+<?php echo $period_stats['downloads_in_period']; ?> this period</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon bg-warning-soft">
                    <i class="bi bi-people text-warning"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?php echo number_format($client_access_stats['total_clients_with_access'] ?? 0); ?></h3>
                    <p class="stat-label">Clients with Access</p>
                    <small><?php echo number_format($client_access_stats['avg_clients_per_doc'] ?? 0, 1); ?> avg per document</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-graph-up me-2 text-primary"></i>
                    Document Activity Trend (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>
                    Document Type Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="typeChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trends -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                    Monthly Trends (Last 6 Months)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendsChart" style="height: 350px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Documents -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-eye me-2 text-primary"></i>
                    Most Viewed Documents
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Document</th>
                                <th>Views</th>
                                <th>Downloads</th>
                                <th>Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($doc = mysqli_fetch_assoc($top_views_result)): 
                                $conversion_rate = $doc['view_count'] > 0 ? round(($doc['download_count'] / $doc['view_count']) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doc['document_title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($doc['file_original_name']); ?></small>
                                    </td>
                                    <td><?php echo number_format($doc['view_count']); ?></td>
                                    <td><?php echo number_format($doc['download_count']); ?></td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $conversion_rate; ?>%"></div>
                                        </div>
                                        <small><?php echo $conversion_rate; ?>%</small>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-download me-2 text-primary"></i>
                    Most Downloaded Documents
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Document</th>
                                <th>Downloads</th>
                                <th>Views</th>
                                <th>Engagement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($doc = mysqli_fetch_assoc($top_downloads_result)): 
                                $engagement_rate = $doc['view_count'] > 0 ? round(($doc['download_count'] / $doc['view_count']) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($doc['document_title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($doc['file_original_name']); ?></small>
                                    </td>
                                    <td><?php echo number_format($doc['download_count']); ?></td>
                                    <td><?php echo number_format($doc['view_count']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $engagement_rate > 50 ? 'success' : ($engagement_rate > 25 ? 'warning' : 'secondary'); ?>">
                                            <?php echo $engagement_rate; ?>% downloaded
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Performance -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-tags me-2 text-primary"></i>
                    Category Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Documents</th>
                                <th>Total Views</th>
                                <th>Total Downloads</th>
                                <th>Avg Views/Doc</th>
                                <th>Avg Downloads/Doc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_docs_all = $stats['total_documents'];
                            while ($cat = mysqli_fetch_assoc($category_stats_result)): 
                                $avg_views = $cat['document_count'] > 0 ? round($cat['total_views'] / $cat['document_count'], 1) : 0;
                                $avg_downloads = $cat['document_count'] > 0 ? round($cat['total_downloads'] / $cat['document_count'], 1) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-folder me-2 text-warning"></i>
                                        <strong><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo number_format($cat['document_count']); ?>
                                        <small class="text-muted">(<?php echo round(($cat['document_count'] / max($total_docs_all, 1)) * 100, 1); ?>%)</small>
                                    </td>
                                    <td><?php echo number_format($cat['total_views'] ?? 0); ?></td>
                                    <td><?php echo number_format($cat['total_downloads'] ?? 0); ?></td>
                                    <td><?php echo number_format($avg_views, 1); ?></td>
                                    <td><?php echo number_format($avg_downloads, 1); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Insights -->
<div class="row">
    <div class="col-12">
        <div class="pro-tip-card" style="background: linear-gradient(90deg, #0a2540 0%, #193a6a 100%); color: #fff; border-radius: 1rem; box-shadow: 0 4px 24px rgba(10,37,64,0.15); padding: 2rem 2.5rem 1.5rem 2.5rem; margin-bottom: 1.5rem;">
            <div class="row align-items-center gx-4 gy-2">
                <div class="col-md-9">
                    <h6 class="mb-2" style="color: #fff;">
                        <i class="bi bi-lightbulb me-2"></i>
                        Report Insights
                    </h6>
                    <p class="mb-md-0" style="color: #e0e6ed; font-size: 1.05rem;">
                        <?php 
                        if ($stats['total_documents'] > 0) {
                            $avg_views_per_doc = round($stats['total_views'] / $stats['total_documents'], 1);
                            $avg_downloads_per_doc = round($stats['total_downloads'] / $stats['total_documents'], 1);
                            $engagement_ratio = $stats['total_views'] > 0 ? round(($stats['total_downloads'] / $stats['total_views']) * 100, 1) : 0;
                            echo "📊 Average {$avg_views_per_doc} views and {$avg_downloads_per_doc} downloads per document. ";
                            echo "📈 Overall engagement rate: {$engagement_ratio}% (downloads per view). ";
                            if ($stats['expired_documents'] > 0) {
                                echo "⚠️ {$stats['expired_documents']} documents have expired and are no longer accessible. ";
                            }
                            if ($period_stats['documents_uploaded'] > 0) {
                                echo "🚀 {$period_stats['documents_uploaded']} new documents were added in the selected period.";
                            }
                        } else {
                            echo "📊 Average 0 views and 0 downloads per document. ";
                            echo "📈 Overall engagement rate: 0% (downloads per view). ";
                            echo "🚀 0 new documents were added in the selected period.";
                        }
                        ?>
                    </p>
                </div>
                <div class="col-md-3 text-md-end">
                    <i class="bi bi-graph-up display-4" style="color: #e0e6ed;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activity Chart
    const ctx1 = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($activity_dates); ?>,
            datasets: [
                {
                    label: 'Views',
                    data: <?php echo json_encode($activity_views); ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Downloads',
                    data: <?php echo json_encode($activity_downloads); ?>,
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });
    
    // Document Type Chart
    const ctx2 = document.getElementById('typeChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['General Documents', 'Specific Documents'],
            datasets: [{
                data: [<?php echo $stats['general_documents']; ?>, <?php echo $stats['specific_documents']; ?>],
                backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(23, 162, 184, 0.8)'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = <?php echo $stats['total_documents']; ?>;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} documents (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    
    // Monthly Trends Chart
    const ctx3 = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthly_trends, 'month')); ?>,
            datasets: [
                {
                    label: 'Documents Uploaded',
                    data: <?php echo json_encode(array_column($monthly_trends, 'uploaded')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Views',
                    data: <?php echo json_encode(array_column($monthly_trends, 'views')); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Downloads',
                    data: <?php echo json_encode(array_column($monthly_trends, 'downloads')); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });
});

function exportReport() {
    window.location.href = 'includes/ajax/export_document_report.php?date_range=<?php echo $date_range; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>';
}
</script>