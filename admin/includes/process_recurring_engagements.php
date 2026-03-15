<?php
/**
 * Process Recurring Engagements
 * This script should be called by a cron job daily or can be triggered when engagements are closed
 */

function processRecurringEngagements($connection) {
    $today = date('Y-m-d');
    $new_engagements_created = [];
    
    // Find engagements that are closed and are recurring
    $query = "SELECT e.*, s.recurrence_pattern, s.recurrence_interval 
              FROM engagements e
              JOIN service_types s ON e.service_id = s.service_id
              WHERE e.status = 'CLOSED' 
                AND e.is_recurring = 1
                AND s.auto_recreate = 1
                AND (e.recurrence_sequence < e.recurrence_count OR e.recurrence_count IS NULL)
                AND NOT EXISTS (
                    SELECT 1 FROM engagements child 
                    WHERE child.parent_engagement_id = e.engagement_id 
                      AND child.recurrence_sequence = e.recurrence_sequence + 1
                )";
    
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        error_log("Error in recurring query: " . mysqli_error($connection));
        return [];
    }
    
    while ($parent = mysqli_fetch_assoc($result)) {
        // Calculate next dates based on pattern
        $next_sequence = $parent['recurrence_sequence'] + 1;
        
        // Calculate next start date
        $next_start = calculateNextDate($parent['start_date'], $parent['recurrence_pattern']);
        if (!$next_start) continue;
        
        // Calculate next deadline (same interval as original)
        $original_days = (strtotime($parent['original_deadline']) - strtotime($parent['start_date'])) / (60*60*24);
        $next_deadline = date('Y-m-d', strtotime($next_start . " + $original_days days"));
        
        // Create the next engagement
        $new_engagement_id = createRecurringEngagement($connection, $parent, $next_sequence, $next_start, $next_deadline);
        
        if ($new_engagement_id) {
            $new_engagements_created[] = [
                'parent_id' => $parent['engagement_id'],
                'new_id' => $new_engagement_id,
                'sequence' => $next_sequence,
                'start_date' => $next_start
            ];
            
            // Log the creation
            logRecurringCreation($connection, $parent['engagement_id'], $new_engagement_id, $next_sequence);
        }
    }
    
    return $new_engagements_created;
}

function calculateNextDate($start_date, $pattern) {
    $date = new DateTime($start_date);
    
    switch ($pattern) {
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'quarterly':
            $date->modify('+3 months');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        default:
            return false;
    }
    
    return $date->format('Y-m-d');
}

function createRecurringEngagement($connection, $parent, $sequence, $start_date, $deadline) {
    // Generate new title (e.g., "Monthly Bookkeeping - Mar 2024")
    $month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $month_num = date('n', strtotime($start_date));
    $year = date('Y', strtotime($start_date));
    
    $new_title = $parent['title'];
    // If title already has a date pattern, update it
    if (preg_match('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{4}\b/', $new_title)) {
        $new_title = preg_replace('/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{4}\b/', 
                                  $month_names[$month_num-1] . ' ' . $year, $new_title);
    } else {
        // Append date to title
        $new_title .= ' - ' . $month_names[$month_num-1] . ' ' . $year;
    }
    
    $new_title = mysqli_real_escape_string($connection, $new_title);
    
    // Insert new engagement
    $query = "INSERT INTO engagements (
        client_id, service_id, rule_version_id, title, description,
        assigned_to, assigned_by, reviewer_id, start_date, original_deadline,
        evidence_required, status, created_by, parent_engagement_id,
        recurrence_sequence, is_recurring
    ) VALUES (
        {$parent['client_id']}, {$parent['service_id']}, {$parent['rule_version_id']}, 
        '$new_title', '{$parent['description']}',
        {$parent['assigned_to']}, {$parent['assigned_by']}, 
        " . ($parent['reviewer_id'] ?: 'NULL') . ", 
        '$start_date', '$deadline',
        {$parent['evidence_required']}, 'ASSIGNED', 
        {$parent['created_by']}, {$parent['engagement_id']},
        $sequence, 1
    )";
    
    if (mysqli_query($connection, $query)) {
        $new_id = mysqli_insert_id($connection);
        
        // Add status history
        $history = "INSERT INTO engagement_status_history 
                   (engagement_id, old_status, new_status, changed_by, notes) 
                   VALUES ($new_id, NULL, 'ASSIGNED', {$parent['created_by']}, 
                   'Auto-created from recurring engagement #{$parent['engagement_id']}')";
        mysqli_query($connection, $history);
        
        return $new_id;
    }
    
    error_log("Failed to create recurring engagement: " . mysqli_error($connection));
    return false;
}

function logRecurringCreation($connection, $parent_id, $new_id, $sequence) {
    // Optional: Add to audit log or create notification
    $log = "INSERT INTO audit_log (user_id, username, action, table_name, record_id, description)
            VALUES (0, 'SYSTEM', 'RECURRING_CREATE', 'engagements', $new_id,
            'Auto-created recurring engagement #$new_id (sequence $sequence) from parent #$parent_id')";
    mysqli_query($connection, $log);
}
