<?php
// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // This is a placeholder for future notification preferences
    // You can expand this when you add a notification_preferences table
    $_SESSION['success_message'] = "Notification settings saved successfully.";
    echo "<script>window.location.href = 'notifications.php?source=settings';</script>";
    exit();
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>
                    <i class="bi bi-gear me-2"></i>Notification Settings
                </h4>
                <a href="notifications.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Notifications
                </a>
            </div>

            <!-- Settings Card -->
            <div class="card shadow-sm">
                <div class="card-header dark-header">
                    <h5 class="card-title">
                        <i class="bi bi-sliders2 me-2"></i>Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <h6 class="mb-3">Email Notifications</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_immediately" checked>
                                <label class="form-check-label" for="email_immediately">Send email immediately</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_digest">
                                <label class="form-check-label" for="email_digest">Daily digest (once per day)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_weekly">
                                <label class="form-check-label" for="email_weekly">Weekly summary</label>
                            </div>
                        </div>

                        <h6 class="mb-3 mt-4">Notification Types</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_engagement" checked>
                                    <label class="form-check-label" for="notif_engagement">
                                        <span class="badge bg-primary">Engagements</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_cdp" checked>
                                    <label class="form-check-label" for="notif_cdp">
                                        <span class="badge bg-success">CDP Records</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_feedback" checked>
                                    <label class="form-check-label" for="notif_feedback">
                                        <span class="badge bg-info">Feedback</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_deadline" checked>
                                    <label class="form-check-label" for="notif_deadline">
                                        <span class="badge bg-warning">Deadlines</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_sales" checked>
                                    <label class="form-check-label" for="notif_sales">
                                        <span class="badge bg-secondary">Sales Targets</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notif_system" checked>
                                    <label class="form-check-label" for="notif_system">
                                        <span class="badge bg-dark">System</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Notification settings will be fully implemented in the next update. Currently, all notifications are enabled.
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="save_settings" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>About Notifications</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">You receive notifications for:</p>
                    <ul class="mb-0">
                        <li>New engagement assignments</li>
                        <li>CDP record approvals/rejections</li>
                        <li>Upcoming and overdue deadlines</li>
                        <li>Client feedback received</li>
                        <li>Sales target achievements</li>
                        <li>System updates and alerts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dark-header {
    background: #1e293b;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 12px 20px;
}

.form-check {
    margin-bottom: 10px;
    padding-left: 2rem;
}

.form-check .badge {
    font-size: 0.85rem;
    padding: 5px 10px;
}
</style>