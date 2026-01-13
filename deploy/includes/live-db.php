<?php

declare(strict_types=1);

$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'u395261815_omniogm';
$db_user = $_ENV['DB_USER'] ?? 'u395261815_omni';
$db_pass = $_ENV['DB_PASS'] ?? 'OgmBc@6449';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Enforce secure charset
    $connection->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {

    // Log internally, never expose details
    error_log(
        '[DB CONNECTION ERROR] ' . $e->getMessage()
    );

    http_response_code(500);
    exit('Database connection unavailable.');
}
