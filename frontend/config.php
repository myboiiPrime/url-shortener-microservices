<?php
// Render.com deployment configuration
// This file handles environment detection and API endpoint configuration

// Detect environment
function detectEnvironment() {
    // Check for Docker environment FIRST (most reliable indicators)
    if (file_exists('/.dockerenv') || 
        isset($_ENV['API_GATEWAY_URL']) || 
        isset($_ENV['DOCKER']) ||
        (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false && file_exists('/var/www/html'))) {
        return 'docker';
    }
    
    // Check for Render.com environment
    if (isset($_ENV['RENDER']) || 
        isset($_SERVER['RENDER']) || 
        isset($_ENV['RENDER_SERVICE_ID']) ||
        isset($_SERVER['RENDER_SERVICE_ID']) ||
        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false)) {
        return 'render';
    }
    
    // Check for local development (only if not in Docker)
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, ':8080') !== false ||
        strpos($host, ':7000') !== false ||
        strpos($host, ':5000') !== false) {
        return 'local';
    }
    
    // If we can't detect, assume production/render
    return 'render';
}

// Get API base URLs based on environment
function getApiConfig() {
    $environment = detectEnvironment();
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    switch ($environment) {
        case 'render':
            // For Render deployment - use HTTPS and .onrender.com domains
            $apiGatewayUrl = $_ENV['API_GATEWAY_URL'] ?? 'https://api-gateway.onrender.com';
            return [
                'server' => $apiGatewayUrl,
                'client' => $apiGatewayUrl
            ];
            
        case 'docker':
            // For Docker deployment
            $serverUrl = $_ENV['API_GATEWAY_URL'] ?? 'http://api-gateway';
            
            // Client-side API URL for Docker Compose - use localhost with port mapping
            $clientUrl = 'http://localhost:7000';
            
            return [
                'server' => $serverUrl,
                'client' => $clientUrl
            ];
            
        case 'local':
        default:
            // For local development, check if we're in a container
            $isInContainer = file_exists('/.dockerenv');
            if ($isInContainer) {
                // From container, use host.docker.internal to reach host services
                return [
                    'server' => 'http://host.docker.internal:5000',
                    'client' => 'http://localhost:5000'
                ];
            } else {
                // Direct local development
                return [
                    'server' => 'http://localhost:5000',
                    'client' => 'http://localhost:5000'
                ];
            }
    }
}

// Set global configuration
$apiConfig = getApiConfig();
$API_BASE_SERVER = $apiConfig['server'];
$API_BASE_CLIENT = $apiConfig['client'];
$SITE_NAME = 'QuickLink';
$ENVIRONMENT = detectEnvironment();

// Get the frontend base URL for short URL construction
function getFrontendBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

$FRONTEND_BASE_URL = getFrontendBaseUrl();

// Debug information for troubleshooting
error_log("=== URL Shortener Config Debug ===");
error_log("Environment: $ENVIRONMENT");
error_log("Host: " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
error_log("Protocol: " . (isset($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP'));
error_log("API Server: $API_BASE_SERVER");
error_log("API Client: $API_BASE_CLIENT");
error_log("Frontend Base: $FRONTEND_BASE_URL");
error_log("Render Env Var: " . ($_ENV['RENDER'] ?? 'not set'));
error_log("API Gateway URL Env: " . ($_ENV['API_GATEWAY_URL'] ?? 'not set'));
error_log("Docker Env File: " . (file_exists('/.dockerenv') ? 'exists' : 'not found'));
error_log("================================");
?>