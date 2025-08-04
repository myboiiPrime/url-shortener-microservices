# URL Shortener Microservices

A comprehensive URL shortening service built with .NET 8 microservices architecture, featuring user management, analytics, and an API gateway.

## Architecture Overview

This project consists of 5 main components:

### 1. **API Gateway** (Port 7000)
- **Purpose**: Single entry point for all client requests
- **Features**: 
  - Request routing and load balancing
  - JWT authentication and authorization
  - Rate limiting and request logging
  - Service discovery and health checks
- **Endpoints**: Routes requests to appropriate microservices

### 2. **User Service** (Port 7001)
- **Purpose**: User authentication and management
- **Features**:
  - User registration and login
  - JWT token generation and validation
  - Password hashing and security
  - User profile management
- **Database**: SQL Server (UserServiceDb)

### 3. **URL Shortening Service** (Port 7002)
- **Purpose**: Core URL shortening functionality
- **Features**:
  - Generate short codes for URLs
  - Custom alias support
  - URL metadata extraction
  - Redis caching for performance
  - RabbitMQ integration for async processing
- **Database**: SQL Server (UrlShorteningServiceDb)
- **Cache**: Redis

### 4. **Analytics Service** (Port 7003)
- **Purpose**: Click tracking and analytics
- **Features**:
  - Real-time click event processing
  - Comprehensive analytics dashboard
  - PDF/CSV report generation
  - Geographic and device analytics
  - RabbitMQ consumer for click events
- **Database**: SQL Server (AnalyticsServiceDb)

### 5. **Shared Library**
- **Purpose**: Common models and services
- **Contains**:
  - Shared data models (UrlMapping, ClickEvent)
  - RabbitMQ service implementation
  - Short code generation utilities

## Technology Stack

- **.NET 8**: Core framework
- **ASP.NET Core**: Web API framework
- **Entity Framework Core**: ORM for database operations
- **SQL Server**: Primary database
- **Redis**: Caching layer
- **RabbitMQ**: Message queuing
- **JWT**: Authentication tokens
- **Serilog**: Structured logging
- **Swagger**: API documentation

## Prerequisites

Before running the application, ensure you have:

1. **.NET 8 SDK** installed
2. **SQL Server** (LocalDB or full instance)
3. **Redis Server** running on localhost:6379
4. **RabbitMQ Server** running on localhost:5672

## Quick Start

### 1. Clone and Build
```bash
git clone <repository-url>
cd AMD web2
dotnet restore
dotnet build
```

### 2. Database Setup
Each service will create its database automatically on first run using Entity Framework migrations.

### 3. Start Services
Open 5 separate terminal windows and run each service:

```bash
# Terminal 1 - API Gateway
cd src/ApiGateway
dotnet run

# Terminal 2 - User Service
cd src/UserService
dotnet run

# Terminal 3 - URL Shortening Service
cd src/UrlShorteningService
dotnet run

# Terminal 4 - Analytics Service
cd src/AnalyticsService
dotnet run
```

### 4. Access the Application
- **API Gateway**: https://localhost:7000
- **Swagger Documentation**: https://localhost:7000/swagger
- **Analytics Dashboard**: https://localhost:7003

## API Endpoints

### Authentication (via API Gateway)
```
POST /api/auth/register    - Register new user
POST /api/auth/login       - User login
GET  /api/auth/me          - Get current user
PUT  /api/auth/me          - Update user profile
```

### URL Management (via API Gateway)
```
POST /api/urls/shorten     - Create short URL
GET  /api/urls/{shortCode} - Get URL details
GET  /r/{shortCode}        - Redirect to original URL
DELETE /api/urls/{shortCode} - Delete URL
GET  /api/urls/user/{userId} - Get user's URLs
```

### Analytics (via API Gateway)
```
GET  /api/analytics/dashboard - Dashboard statistics
GET  /api/analytics/url/{shortCode} - URL analytics
GET  /api/analytics/user/{userId} - User analytics
GET  /api/analytics/reports/url/{shortCode}/pdf - PDF report
GET  /api/analytics/reports/url/{shortCode}/csv - CSV report
```

## Configuration

### JWT Settings
Update `appsettings.json` in each service:
```json
{
  "JwtSettings": {
    "SecretKey": "your-super-secret-key-that-is-at-least-32-characters-long",
    "Issuer": "UrlShortener",
    "Audience": "UrlShortener",
    "ExpirationMinutes": 60
  }
}
```

### Database Connections
Each service uses SQL Server LocalDB by default. Update connection strings as needed:
```json
{
  "ConnectionStrings": {
    "DefaultConnection": "Server=(localdb)\\mssqllocaldb;Database=ServiceNameDb;Trusted_Connection=true;MultipleActiveResultSets=true;"
  }
}
```

### Message Queue
RabbitMQ configuration (default: localhost):
```json
{
  "RabbitMQ": {
    "HostName": "localhost"
  }
}
```

## Features

### 🔐 **Security**
- JWT-based authentication
- Password hashing with salt
- Rate limiting protection
- Input validation and sanitization

### 📊 **Analytics**
- Real-time click tracking
- Geographic analytics
- Browser/device detection
- Custom date range filtering
- Export capabilities (PDF/CSV)

### ⚡ **Performance**
- Redis caching for frequently accessed URLs
- Asynchronous message processing
- Database indexing optimization
- Connection pooling

### 🔄 **Scalability**
- Microservices architecture
- Load balancing support
- Horizontal scaling ready
- Service discovery

### 🛠 **Monitoring**
- Structured logging with Serilog
- Health check endpoints
- Request/response logging
- Error tracking

## Development

### Adding New Features
1. Create feature branch
2. Implement changes in appropriate service
3. Update shared models if needed
4. Add tests
5. Update documentation

### Database Migrations
```bash
# Add migration
dotnet ef migrations add MigrationName

# Update database
dotnet ef database update
```

### Testing
```bash
# Run all tests
dotnet test

# Run specific service tests
dotnet test src/UserService.Tests
```

## Deployment

### Docker Support
Docker configurations are available in the `docker/` directory for containerized deployment.

### Production Considerations
- Use external SQL Server instance
- Configure Redis cluster
- Set up RabbitMQ cluster
- Use proper SSL certificates
- Configure logging aggregation
- Set up monitoring and alerting

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure SQL Server is running
   - Check connection strings
   - Verify database permissions

2. **RabbitMQ Connection Issues**
   - Verify RabbitMQ server is running
   - Check firewall settings
   - Validate connection parameters

3. **Redis Connection Problems**
   - Ensure Redis server is running
   - Check Redis configuration
   - Verify network connectivity

4. **JWT Token Issues**
   - Ensure all services use same secret key
   - Check token expiration settings
   - Validate issuer/audience claims

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the troubleshooting guide