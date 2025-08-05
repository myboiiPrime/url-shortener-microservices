<?php
echo "<h1>Environment Debug</h1>";

echo "<h2>Docker Detection Checks:</h2>";
echo "/.dockerenv exists: " . (file_exists('/.dockerenv') ? 'YES' : 'NO') . "<br>";
echo "\$_ENV['API_GATEWAY_URL']: " . ($_ENV['API_GATEWAY_URL'] ?? 'NOT SET') . "<br>";
echo "\$_ENV['DOCKER']: " . ($_ENV['DOCKER'] ?? 'NOT SET') . "<br>";
echo "\$_SERVER['SERVER_SOFTWARE']: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'NOT SET') . "<br>";
echo "/var/www/html exists: " . (file_exists('/var/www/html') ? 'YES' : 'NO') . "<br>";

echo "<h2>All Environment Variables (\$_ENV):</h2>";
echo "<pre>";
print_r($_ENV);
echo "</pre>";

echo "<h2>All Server Variables (\$_SERVER):</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

echo "<h2>Current Working Directory:</h2>";
echo getcwd() . "<br>";

echo "<h2>PHP Info (relevant parts):</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server API: " . php_sapi_name() . "<br>";

// Test the detection function
require_once 'config.php';
echo "<h2>Detection Result:</h2>";
echo "Environment: " . detectEnvironment() . "<br>";
?>