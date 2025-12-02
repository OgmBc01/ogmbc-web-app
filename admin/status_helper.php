<?php
// includes/status_helper.php

function canReviewProposal($user_role, $current_status) {
    $review_map = [
        'manager' => ['Proposal Drafted'],
        'ceo' => ['Manager Approved Proposal'],
        'admin' => ['Proposal Drafted', 'Manager Approved Proposal', 'CEO Approved Proposal']
    ];
    
    return isset($review_map[$user_role]) && in_array($current_status, $review_map[$user_role]);
}

function canReviewProforma($user_role, $current_status) {
    $review_map = [
        'manager' => ['Proforma Drafted'],
        'ceo' => ['Manager Approved Proforma'],
        'admin' => ['Proforma Drafted', 'Manager Approved Proforma', 'CEO Approved Proforma']
    ];
    
    return isset($review_map[$user_role]) && in_array($current_status, $review_map[$user_role]);
}

function getNextStatus($user_role, $action = 'approve') {
    if($action === 'approve') {
        $status_map = [
            'manager' => 'Manager Approved Proposal',
            'ceo' => 'CEO Approved Proposal',
            'admin' => 'Admin Approved Proposal'
        ];
        return $status_map[$user_role] ?? '';
    } else {
        return 'Proposal Needs Revision';
    }
}
?>