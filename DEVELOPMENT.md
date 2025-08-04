# Development Setup Guide

## Prerequisites

Before setting up the URL Shortener microservices, ensure you have the following installed:

### Required Software
- **.NET 8.0 SDK** - [Download here](https://dotnet.microsoft.com/download/dotnet/8.0)
- **Visual Studio 2022** or **Visual Studio Code** with C# extension
- **SQL Server** (LocalDB, Express, or full version)
- **Redis** - [Download for Windows](https://github.com/microsoftarchive/redis/releases)
- **RabbitMQ** - [Download here](https://www.rabbitmq.com/download.html)

### Optional Tools
- **Docker Desktop** - For containerized deployment
- **Postman** or **Insomnia** - For API testing
- **SQL Server Management Studio (SSMS)** - For database management

## Quick Start

### Option 1: Using Startup Scripts (Recommended for Development)

1. **Clone/Download the project**
2. **Open PowerShell as Administrator** in the project root directory
3. **Run the startup script:**
   ```powershell
   .\start-services.ps1
   ```
   Or use the batch file:
   ```cmd
   start-services.bat
   ```

### Option 2: Manual Setup

1. **Restore NuGet packages:**
   ```bash
   dotnet restore
   ```

2. **Build the solution:**
   ```bash
   dotnet build
   ```

3. **Start each service in separate terminals:**
   ```bash
   # Terminal 1 - User Service
   cd src/UserService
   dotnet run

   # Terminal 2 - URL Shortening Service
   cd src/UrlShorteningService
   dotnet run

   # Terminal 3 - Analytics Service
   cd src/AnalyticsService
   dotnet run

   # Terminal 4 - API Gateway
   cd src/ApiGateway
   dotnet run
   ```

### Option 3: Docker Compose (Production-like Environment)

1. **Ensure Docker Desktop is running**
2. **Run the following command:**
   ```bash
   docker-compose up -d
   ```

## Service Endpoints

Once all services are running, they will be available at:

- **API Gateway**: https://localhost:7000
- **User Service**: https://localhost:7001
- **URL Shortening Service**: https://localhost:7002
- **Analytics Service**: https://localhost:7003

## Database Setup

### SQL Server Configuration

1. **Update connection strings** in each service's `appsettings.json`:
   ```json
   {
     "ConnectionStrings": {
       "DefaultConnection": "Server=(localdb)\\mssqllocaldb;Database=UrlShortener_[ServiceName];Trusted_Connection=true;MultipleActiveResultSets=true"
     }
   }
   ```

2. **Run Entity Framework migrations** for each service:
   ```bash
   # User Service
   cd src/UserService
   dotnet ef database update

   # URL Shortening Service
   cd src/UrlShorteningService
   dotnet ef database update

   # Analytics Service
   cd src/AnalyticsService
   dotnet ef database update
   ```

### Redis Configuration

Update Redis connection in `appsettings.json`:
```json
{
  "Redis": {
    "ConnectionString": "localhost:6379"
  }
}
```

### RabbitMQ Configuration

Update RabbitMQ settings in `appsettings.json`:
```json
{
  "RabbitMQ": {
    "HostName": "localhost",
    "Port": 5672,
    "UserName": "guest",
    "Password": "guest"
  }
}
```

## Environment Variables

You can override configuration using environment variables:

```bash
# Database
export ConnectionStrings__DefaultConnection="your-connection-string"

# JWT
export Jwt__Key="your-secret-key"
export Jwt__Issuer="UrlShortener"
export Jwt__Audience="UrlShortener"

# Redis
export Redis__ConnectionString="localhost:6379"

# RabbitMQ
export RabbitMQ__HostName="localhost"
export RabbitMQ__UserName="guest"
export RabbitMQ__Password="guest"
```

## Testing the API

### User Registration
```bash
curl -X POST https://localhost:7000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "Test123!@#"
  }'
```

### User Login
```bash
curl -X POST https://localhost:7000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "Test123!@#"
  }'
```

### Shorten URL
```bash
curl -X POST https://localhost:7000/api/urls/shorten \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "originalUrl": "https://www.example.com"
  }'
```

## Troubleshooting

### Common Issues

1. **Port conflicts**: If ports are already in use, update the `launchSettings.json` files in each service
2. **Database connection issues**: Ensure SQL Server is running and connection strings are correct
3. **Redis connection issues**: Ensure Redis server is running on the specified port
4. **RabbitMQ connection issues**: Ensure RabbitMQ service is running

### Logs

Check application logs in the console output of each service for detailed error information.

### Health Checks

Each service exposes health check endpoints:
- User Service: https://localhost:7001/health
- URL Shortening Service: https://localhost:7002/health
- Analytics Service: https://localhost:7003/health

## Development Tips

1. **Use Hot Reload**: Run services with `dotnet watch run` for automatic recompilation
2. **API Documentation**: Swagger UI is available at `/swagger` endpoint for each service
3. **Database Migrations**: Use `dotnet ef migrations add [MigrationName]` to create new migrations
4. **Testing**: Run unit tests with `dotnet test`

## Next Steps

1. Set up your development environment using this guide
2. Explore the API endpoints using Swagger UI
3. Review the codebase to understand the architecture
4. Start developing new features or fixing issues

For more detailed information, refer to the main README.md file.