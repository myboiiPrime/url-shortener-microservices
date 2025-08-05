<?php
require_once 'config.php';

echo "Environment: " . $ENVIRONMENT . "\n";
echo "API Server: " . $API_BASE_SERVER . "\n";
echo "API Client: " . $API_BASE_CLIENT . "\n";
echo "Frontend Base: " . $FRONTEND_BASE_URL . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "Docker env file exists: " . (file_exists('/.dockerenv') ? 'yes' : 'no') . "\n";
echo "DOCKER env var: " . ($_ENV['DOCKER'] ?? 'not set') . "\n";
?>