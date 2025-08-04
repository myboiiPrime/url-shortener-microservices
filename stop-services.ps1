Write-Host "Stopping URL Shortener Microservices..." -ForegroundColor Yellow

try {
    $processes = Get-Process -Name "dotnet" -ErrorAction SilentlyContinue
    if ($processes) {
        Write-Host "Found $($processes.Count) dotnet processes. Stopping them..." -ForegroundColor Gray
        $processes | Stop-Process -Force
        Start-Sleep -Seconds 2
        Write-Host "All services stopped successfully!" -ForegroundColor Green
    } else {
        Write-Host "No running services found." -ForegroundColor Gray
    }
}
catch {
    Write-Host "Error stopping services: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")