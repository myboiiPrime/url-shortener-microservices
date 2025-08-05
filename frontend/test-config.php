<?php
// Test configuration script
require_once 'config.php';

echo "<h1>URL Shortener Configuration Test</h1>";
echo "<h2>Environment Detection</h2>";
echo "<p><strong>Detected Environment:</strong> $ENVIRONMENT</p>";
echo "<p><strong>Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "</p>";
echo "<p><strong>Protocol:</strong> " . (isset($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP') . "</p>";

echo "<h2>API Configuration</h2>";
echo "<p><strong>API Server (PHP):</strong> $API_BASE_SERVER</p>";
echo "<p><strong>API Client (JS):</strong> $API_BASE_CLIENT</p>";

echo "<h2>Environment Variables</h2>";
echo "<p><strong>RENDER:</strong> " . ($_ENV['RENDER'] ?? $_SERVER['RENDER'] ?? 'not set') . "</p>";
echo "<p><strong>API_GATEWAY_URL:</strong> " . ($_ENV['API_GATEWAY_URL'] ?? 'not set') . "</p>";
echo "<p><strong>RENDER_SERVICE_ID:</strong> " . ($_ENV['RENDER_SERVICE_ID'] ?? 'not set') . "</p>";

echo "<h2>Detection Logic</h2>";
echo "<ul>";
echo "<li>Has RENDER env var: " . (isset($_ENV['RENDER']) || isset($_SERVER['RENDER']) ? 'YES' : 'NO') . "</li>";
echo "<li>Has RENDER_SERVICE_ID: " . (isset($_ENV['RENDER_SERVICE_ID']) || isset($_SERVER['RENDER_SERVICE_ID']) ? 'YES' : 'NO') . "</li>";
echo "<li>Host contains .onrender.com: " . (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false ? 'YES' : 'NO') . "</li>";
echo "<li>Has Docker indicator: " . (isset($_ENV['DOCKER']) || file_exists('/.dockerenv') ? 'YES' : 'NO') . "</li>";
echo "</ul>";

echo "<h2>API Test</h2>";
echo "<script>
console.log('=== Frontend Config Test ===');
console.log('Environment:', '$ENVIRONMENT');
console.log('API Base:', '$API_BASE_CLIENT');

// Test API connectivity
fetch('$API_BASE_CLIENT/api/gateway/health')
    .then(response => {
        console.log('API Response Status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('API Health Check Success:', data);
        document.getElementById('api-status').innerHTML = '<span style=\"color: green;\">✅ API Connected</span>';
    })
    .catch(error => {
        console.error('API Health Check Failed:', error);
        document.getElementById('api-status').innerHTML = '<span style=\"color: red;\">❌ API Connection Failed: ' + error.message + '</span>';
    });
</script>";

echo "<p id='api-status'>🔄 Testing API connection...</p>";
?>