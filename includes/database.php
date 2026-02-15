<?php
declare(strict_types=1);

// Load environment variables (Docker Compose sets them)
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'database_name_here';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';

// Report all mysqli errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Enforce secure charset
    $connection->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {
    // Log internally, never expose DB details to users
    error_log('[DB CONNECTION ERROR] ' . $e->getMessage());

    http_response_code(500);
    exit('Database connection unavailable.');
}
