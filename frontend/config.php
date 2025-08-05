<?php
// Render.com deployment configuration
// This file handles environment detection and API endpoint configuration

// Detect environment
function detectEnvironment() {
    // Check for Render.com environment (multiple ways)
    if (isset($_ENV['RENDER']) || 
        isset($_SERVER['RENDER']) || 
        isset($_ENV['RENDER_SERVICE_ID']) ||
        isset($_SERVER['RENDER_SERVICE_ID']) ||
        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false)) {
        return 'render';
    }
    
    // Check for Docker environment
    if (isset($_ENV['DOCKER']) || file_exists('/.dockerenv')) {
        return 'docker';
    }
    
    // Check for local development
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, ':8080') !== false ||
        strpos($host, ':7000') !== false) {
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
            
            // Client-side API URL depends on how Docker is deployed
            if (isset($_ENV['API_GATEWAY_URL'])) {
                // If API_GATEWAY_URL is set, use it (for production Docker)
                $clientUrl = $_ENV['API_GATEWAY_URL'];
            } else {
                // For local Docker development with port mapping
                $clientUrl = $protocol . '://' . str_replace(':8080', ':7000', $host);
            }
            
            return [
                'server' => $serverUrl,
                'client' => $clientUrl
            ];
            
        case 'local':
        default:
            return [
                'server' => 'http://localhost:7000',
                'client' => 'http://localhost:7000'
            ];
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