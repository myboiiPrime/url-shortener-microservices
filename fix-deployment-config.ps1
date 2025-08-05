# Fix deployment configuration for URL Shortener
Write-Host "Fixing deployment configuration..." -ForegroundColor Green

# Update dashboard.php configuration
$dashboardPath = "frontend\dashboard.php"
$dashboardContent = Get-Content $dashboardPath -Raw

# Replace the configuration section with deployment-friendly version
$newConfig = @"
// Configuration - Deployment-friendly endpoint strategy
`$API_BASE_SERVER = 'http://api-gateway';  // For server-side PHP requests

// Auto-detect client-side API base URL based on environment
`$protocol = isset(`$_SERVER['HTTPS']) && `$_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
`$host = `$_SERVER['HTTP_HOST'];

// If running in Docker (frontend on port 8080), use API Gateway on port 7000
if (strpos(`$host, ':8080') !== false) {
    `$api_host = str_replace(':8080', ':7000', `$host);
    `$API_BASE_CLIENT = `$protocol . '://' . `$api_host;
} else {
    // For local development or other deployments
    `$API_BASE_CLIENT = 'http://localhost:7000';
}

`$SITE_NAME = 'QuickLink';
"@

# Replace the old configuration
$updatedContent = $dashboardContent -replace "// Configuration - Dual endpoint strategy.*?\$SITE_NAME = 'QuickLink';", $newConfig, "Singleline"

# Write back to file
Set-Content -Path $dashboardPath -Value $updatedContent -Encoding UTF8

Write-Host "Updated dashboard.php configuration" -ForegroundColor Yellow

# Update other frontend files with the same pattern
$frontendFiles = @("index.php", "profile.php", "register.php", "login.php", "analytics.php", "redirect.php")

foreach ($file in $frontendFiles) {
    $filePath = "frontend\$file"
    if (Test-Path $filePath) {
        $content = Get-Content $filePath -Raw
        $updatedContent = $content -replace "// Configuration - Dual endpoint strategy.*?\$SITE_NAME = 'QuickLink';", $newConfig, "Singleline"
        Set-Content -Path $filePath -Value $updatedContent -Encoding UTF8
        Write-Host "Updated $file configuration" -ForegroundColor Yellow
    }
}

Write-Host "All frontend files updated successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "The application will now automatically detect:" -ForegroundColor Cyan
Write-Host "- Docker environment (port 8080 -> API Gateway on port 7000)" -ForegroundColor White
Write-Host "- Local development (localhost:7000)" -ForegroundColor White