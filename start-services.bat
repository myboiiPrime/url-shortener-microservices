@echo off
echo Starting URL Shortener Microservices...
echo.

echo Starting User Service...
start "User Service" cmd /k "cd /d src\UserService && dotnet run"
timeout /t 3 /nobreak >nul

echo Starting URL Shortening Service...
start "URL Shortening Service" cmd /k "cd /d src\UrlShorteningService && dotnet run"
timeout /t 3 /nobreak >nul

echo Starting Analytics Service...
start "Analytics Service" cmd /k "cd /d src\AnalyticsService && dotnet run"
timeout /t 3 /nobreak >nul

echo Starting API Gateway...
start "API Gateway" cmd /k "cd /d src\ApiGateway && dotnet run"

echo.
echo All services are starting...
echo.
echo Services will be available at:
echo - API Gateway: https://localhost:7000
echo - User Service: https://localhost:7001
echo - URL Shortening Service: https://localhost:7002
echo - Analytics Service: https://localhost:7003
echo.
echo Press any key to exit...
pause >nul