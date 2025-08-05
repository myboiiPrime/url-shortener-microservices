<?php
require_once 'config.php';

header('Content-Type: application/json');

// Test API connectivity
$testUrl = $API_BASE_CLIENT . '/health';

echo json_encode([
    'environment' => $ENVIRONMENT,
    'api_server' => $API_BASE_SERVER,
    'api_client' => $API_BASE_CLIENT,
    'frontend_base' => $FRONTEND_BASE_URL,
    'test_url' => $testUrl,
    'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'https' => isset($_SERVER['HTTPS']) ? 'yes' : 'no'
]);
?>