Write-Host "Restoring NuGet packages and building all services..." -ForegroundColor Green

# Restore packages for all projects
Write-Host "Restoring packages..." -ForegroundColor Yellow
dotnet restore UrlShortenerMicroservices.sln

if ($LASTEXITCODE -ne 0) {
    Write-Host "Package restore failed!" -ForegroundColor Red
    exit 1
}

# Build all projects
Write-Host "Building solution..." -ForegroundColor Yellow
dotnet build UrlShortenerMicroservices.sln

if ($LASTEXITCODE -ne 0) {
    Write-Host "Build failed!" -ForegroundColor Red
    exit 1
}

Write-Host "Build completed successfully!" -ForegroundColor Green