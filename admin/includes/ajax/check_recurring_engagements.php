<?php
/**
 * Cron Job Endpoint for Recurring Engagements
 * Call this URL via cron job daily: wget -q -O /dev/null http://yoursite.com/admin/includes/ajax/check_recurring_engagements.php?key=YOUR_SECRET_KEY
 */

// Security key to prevent unauthorized access
define('CRON_SECRET_KEY', 'MySuperSecretKey@OmniOGM123!');

if (!isset($_GET['key']) || $_GET['key'] !== CRON_SECRET_KEY) {
    die('Unauthorized');
}

require_once __DIR__ . '/../../../includes/database.php';
require_once __DIR__ . '/../process_recurring_engagements.php';

// Execute recurring processing
$created = processRecurringEngagements($connection);

// Return results
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'created_count' => count($created),
    'details' => $created
]);


//open in browser to run the check immediately: http://localhost:8010/admin/includes/ajax/check_recurring_engagements.php?key=MySuperSecretKey123!