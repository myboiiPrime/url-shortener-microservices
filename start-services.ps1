Write-Host "Starting URL Shortener Microservices..." -ForegroundColor Green
Write-Host ""

Write-Host "Starting User Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\UserService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 3

Write-Host "Starting URL Shortening Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\UrlShorteningService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 3

Write-Host "Starting Analytics Service..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\AnalyticsService'; dotnet run" -WindowStyle Normal

Start-Sleep -Seconds 3

Write-Host "Starting API Gateway..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'src\ApiGateway'; dotnet run" -WindowStyle Normal

Write-Host ""
Write-Host "All services are starting..." -ForegroundColor Green
Write-Host ""
Write-Host "Services will be available at:" -ForegroundColor Cyan
Write-Host "- API Gateway: https://localhost:7000" -ForegroundColor White
Write-Host "- User Service: https://localhost:7001" -ForegroundColor White
Write-Host "- URL Shortening Service: https://localhost:7002" -ForegroundColor White
Write-Host "- Analytics Service: https://localhost:7003" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")