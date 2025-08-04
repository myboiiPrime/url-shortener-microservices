using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using UrlShortener.ApiGateway.Middleware;
using UrlShortener.ApiGateway.Services;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// HTTP Client for service communication
builder.Services.AddHttpClient();

// JWT Authentication
var jwtSettings = builder.Configuration.GetSection("JwtSettings");
var secretKey = Encoding.ASCII.GetBytes(jwtSettings["SecretKey"] ?? "default-secret-key");

builder.Services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuerSigningKey = true,
            IssuerSigningKey = new SymmetricSecurityKey(secretKey),
            ValidateIssuer = true,
            ValidIssuer = jwtSettings["Issuer"],
            ValidateAudience = true,
            ValidAudience = jwtSettings["Audience"],
            ValidateLifetime = true,
            ClockSkew = TimeSpan.Zero
        };
    });

// Rate Limiting
builder.Services.AddMemoryCache();
builder.Services.AddSingleton<IRateLimitService, RateLimitService>();

// Service Discovery - Changed to Singleton for middleware access
builder.Services.AddSingleton<IServiceDiscovery, ServiceDiscovery>();

// Load Balancer - Changed to Singleton for middleware access
builder.Services.AddSingleton<ILoadBalancer, RoundRobinLoadBalancer>();

// CORS
builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowAll", policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

var app = builder.Build();

// Configure the HTTP request pipeline
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseCors("AllowAll");

// Custom middleware
app.UseMiddleware<RequestLoggingMiddleware>();
app.UseMiddleware<RateLimitingMiddleware>();

app.UseAuthentication();
app.UseMiddleware<AuthenticationMiddleware>();

app.UseAuthorization();

app.MapControllers();

// Proxy middleware should be last to handle unmatched routes
app.UseMiddleware<ProxyMiddleware>();

app.Run();