Write-Host "Stopping existing services..." -ForegroundColor Yellow

# Stop any existing services
try {
    $processes = Get-Process -Name "dotnet" -ErrorAction SilentlyContinue
    if ($processes) {
        Write-Host "Found $($processes.Count) dotnet processes. Stopping them..." -ForegroundColor Gray
        $processes | Stop-Process -Force
        Start-Sleep -Seconds 3
        Write-Host "Services stopped successfully!" -ForegroundColor Green
    } else {
        Write-Host "No running services found." -ForegroundColor Gray
    }
}
catch {
    Write-Host "Error stopping services: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Starting services with updated configuration..." -ForegroundColor Green
Write-Host ""

Write-Host "Starting User Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\UserService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 5

Write-Host "Starting URL Shortening Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\UrlShorteningService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 5

Write-Host "Starting Analytics Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\AnalyticsService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 5

Write-Host "Starting API Gateway..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\ApiGateway'; dotnet run" -WindowStyle Normal

Write-Host ""
Write-Host "All services are starting..." -ForegroundColor Green
Write-Host ""
Write-Host "Services will be available at:" -ForegroundColor Cyan
Write-Host "- API Gateway: http://localhost:5000" -ForegroundColor White
Write-Host "- User Service: http://localhost:5001" -ForegroundColor White
Write-Host "- URL Shortening Service: http://localhost:5002" -ForegroundColor White
Write-Host "- Analytics Service: http://localhost:5003" -ForegroundColor White
Write-Host ""
Write-Host "Wait for all services to start (about 30 seconds), then run:" -ForegroundColor Cyan
Write-Host ".\api-test-script.ps1" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")