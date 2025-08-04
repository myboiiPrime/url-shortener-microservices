# URL Shortener Microservices API Testing Script
# This script tests all API endpoints across all services

Write-Host "=== URL Shortener Microservices API Testing ===" -ForegroundColor Green
Write-Host ""

# Service URLs (HTTP ports - services are running on HTTP profile)
$ApiGateway = "http://localhost:5000"
$UserService = "http://localhost:5001"
$UrlService = "http://localhost:5002"
$AnalyticsService = "http://localhost:5003"

# Global variables for authentication
$global:AuthToken = ""
$global:UserId = ""
$global:TestShortCode = ""

# Helper function to make HTTP requests
function Invoke-ApiRequest {
    param(
        [string]$Url,
        [string]$Method = "GET",
        [object]$Body = $null,
        [hashtable]$Headers = @{},
        [string]$Description = ""
    )
    
    Write-Host "Testing: $Description" -ForegroundColor Yellow
    Write-Host "  $Method $Url" -ForegroundColor Cyan
    
    try {
        $requestParams = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ContentType = "application/json"
        }
        
        if ($Body) {
            $requestParams.Body = ($Body | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-RestMethod @requestParams
        Write-Host "  [SUCCESS]: $($response | ConvertTo-Json -Compress)" -ForegroundColor Green
        return $response
    }
    catch {
        Write-Host "  [FAILED]: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            Write-Host "  Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
        }
        return $null
    }
    Write-Host ""
}

# Test Health Endpoints
function Test-HealthEndpoints {
    Write-Host "=== TESTING HEALTH ENDPOINTS ===" -ForegroundColor Magenta
    
    Invoke-ApiRequest -Url "$ApiGateway/api/gateway/health" -Description "API Gateway Health"
    Invoke-ApiRequest -Url "$UserService/health" -Description "User Service Health"
    Invoke-ApiRequest -Url "$UrlService/health" -Description "URL Service Health"
    Invoke-ApiRequest -Url "$AnalyticsService/health" -Description "Analytics Service Health"
}

# Test User Service Endpoints
function Test-UserService {
    Write-Host "=== TESTING USER SERVICE ===" -ForegroundColor Magenta
    
    # Test Registration - Fixed request format
    $registerData = @{
        Username = "testuser123"      # Changed from FirstName/LastName
        Email = "test@example.com"
        Password = "TestPassword123!"
    }
    
    $registerResponse = Invoke-ApiRequest -Url "$UserService/api/auth/register" -Method "POST" -Body $registerData -Description "User Registration"
    
    if ($registerResponse -and $registerResponse.Token) {
        $global:AuthToken = $registerResponse.Token
        $global:UserId = $registerResponse.User.Id
        Write-Host "  [INFO] Saved Auth Token and User ID" -ForegroundColor Blue
    }
    
    # Test Login - Fixed request format
    $loginData = @{
        UsernameOrEmail = "test@example.com"  # Changed from Email
        Password = "TestPassword123!"
    }
    
    $loginResponse = Invoke-ApiRequest -Url "$UserService/api/auth/login" -Method "POST" -Body $loginData -Description "User Login"
    
    if ($loginResponse -and $loginResponse.Token) {
        $global:AuthToken = $loginResponse.Token
        Write-Host "  [INFO] Updated Auth Token from Login" -ForegroundColor Blue
    }
    
    # Test Get Current User (requires auth)
    $authHeaders = @{ "Authorization" = "Bearer $global:AuthToken" }
    Invoke-ApiRequest -Url "$UserService/api/auth/me" -Headers $authHeaders -Description "Get Current User"
    
    # Test Update User (requires auth)
    $updateData = @{
        Username = "testuser123"      # Add Username
        Email = "test@example.com"
    }
    Invoke-ApiRequest -Url "$UserService/api/auth/me" -Method "PUT" -Body $updateData -Headers $authHeaders -Description "Update User Profile"
    
    # Test Change Password (requires auth)
    $passwordData = @{
        CurrentPassword = "TestPassword123!"
        NewPassword = "NewPassword123!"
    }
    Invoke-ApiRequest -Url "$UserService/api/auth/change-password" -Method "POST" -Body $passwordData -Headers $authHeaders -Description "Change Password"
}

# Test URL Shortening Service
function Test-UrlService {
    Write-Host "=== TESTING URL SHORTENING SERVICE ===" -ForegroundColor Magenta
    
    $authHeaders = @{ "Authorization" = "Bearer $global:AuthToken" }
    
    # Test URL Shortening
    $shortenData = @{
        OriginalUrl = "https://www.example.com"
        ExpirationDate = (Get-Date).AddDays(30).ToString("yyyy-MM-ddTHH:mm:ssZ")
        UserId = $global:UserId
    }
    
    $shortenResponse = Invoke-ApiRequest -Url "$UrlService/api/url/shorten" -Method "POST" -Body $shortenData -Headers $authHeaders -Description "Shorten URL"
    
    if ($shortenResponse -and $shortenResponse.ShortCode) {
        $global:TestShortCode = $shortenResponse.ShortCode
        Write-Host "  [INFO] Saved Short Code: $global:TestShortCode" -ForegroundColor Blue
    }
    
    # Test Async URL Shortening
    $asyncShortenData = @{
        OriginalUrl = "https://www.google.com"
        RequestId = [System.Guid]::NewGuid().ToString()
        UserId = $global:UserId
    }
    Invoke-ApiRequest -Url "$UrlService/api/url/shorten-async" -Method "POST" -Body $asyncShortenData -Headers $authHeaders -Description "Async Shorten URL"
    
    # Test Get URL Details
    if ($global:TestShortCode) {
        Invoke-ApiRequest -Url "$UrlService/api/url/$global:TestShortCode" -Headers $authHeaders -Description "Get URL Details"
        
        # Test URL Redirect (this will actually redirect, so we expect a different response)
        try {
            $redirectResponse = Invoke-WebRequest -Uri "$UrlService/api/url/redirect/$global:TestShortCode" -MaximumRedirection 0
        }
        catch {
            if ($_.Exception.Response.StatusCode -eq 302) {
                Write-Host "  [SUCCESS]: URL Redirect working (302 redirect)" -ForegroundColor Green
            } else {
                Write-Host "  [FAILED]: Unexpected redirect response" -ForegroundColor Red
            }
        }
    }
    
    # Test Get User URLs
    if ($global:UserId) {
        Invoke-ApiRequest -Url "$UrlService/api/url/user/$global:UserId" -Headers $authHeaders -Description "Get User URLs"
    }
    
    # Test Delete URL
    if ($global:TestShortCode) {
        Invoke-ApiRequest -Url "$UrlService/api/url/$global:TestShortCode" -Method "DELETE" -Headers $authHeaders -Description "Delete URL"
    }
}

# Test Analytics Service
function Test-AnalyticsService {
    Write-Host "=== TESTING ANALYTICS SERVICE ===" -ForegroundColor Magenta
    
    $authHeaders = @{ "Authorization" = "Bearer $global:AuthToken" }
    
    # Test Record Click
    $clickData = @{
        ShortCode = $global:TestShortCode
        IpAddress = "192.168.1.1"
        UserAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
        Referrer = "https://www.google.com"
        Timestamp = (Get-Date).ToString("yyyy-MM-ddTHH:mm:ssZ")
    }
    Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/click" -Method "POST" -Body $clickData -Headers $authHeaders -Description "Record Click Event"
    
    # Test Get URL Analytics
    if ($global:TestShortCode) {
        Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/url/$global:TestShortCode" -Headers $authHeaders -Description "Get URL Analytics"
    }
    
    # Test Get User Analytics
    if ($global:UserId) {
        Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/user/$global:UserId" -Headers $authHeaders -Description "Get User Analytics"
    }
    
    # Test Dashboard Stats
    Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/dashboard" -Headers $authHeaders -Description "Get Dashboard Stats"
    
    # Test Top URLs
    Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/top-urls" -Headers $authHeaders -Description "Get Top URLs"
    
    # Test Generate Report
    if ($global:UserId) {
        Invoke-ApiRequest -Url "$AnalyticsService/api/analytics/report/user/$global:UserId" -Headers $authHeaders -Description "Generate User Report"
    }
}

# Test API Gateway Specific Endpoints
function Test-ApiGateway {
    Write-Host "=== TESTING API GATEWAY ===" -ForegroundColor Magenta
    
    # Test Get Services
    Invoke-ApiRequest -Url "$ApiGateway/api/gateway/services" -Description "Get Registered Services"
    
    # Test Get Routes
    Invoke-ApiRequest -Url "$ApiGateway/api/gateway/routes" -Description "Get Route Configuration"
    
    # Test Load Balancer
    Invoke-ApiRequest -Url "$ApiGateway/api/gateway/load-balancer/next/UserService" -Description "Get Next User Service Endpoint"
}

# Main execution
Write-Host "Starting API Tests..." -ForegroundColor Green
Write-Host "Make sure all services are running before proceeding." -ForegroundColor Yellow
Write-Host ""

# Run all tests
Test-HealthEndpoints
Test-UserService
Test-UrlService
Test-AnalyticsService
Test-ApiGateway

Write-Host ""
Write-Host "=== API TESTING COMPLETED ===" -ForegroundColor Green
Write-Host "Check the results above for any failed tests." -ForegroundColor Yellow