<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Invalid request method');
    }

    $lead_id = save_lead_to_crm();

    if ($lead_id === false) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save lead'
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Lead saved successfully',
            'lead_id' => $lead_id
        ]);
    }

} catch (Throwable $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;
