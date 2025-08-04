using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using System.Text;
using UrlShortener.UserService.Data;
using UrlShortener.UserService.Services;
using UrlShortener.Shared.Services;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// Database - Switch to SQLite for easier development
builder.Services.AddDbContext<UserDbContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("DefaultConnection")));

// JWT Authentication
var jwtSettings = builder.Configuration.GetSection("JwtSettings");
var secretKey = jwtSettings["SecretKey"] ?? "default-secret-key";

builder.Services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuer = true,
            ValidateAudience = true,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,
            ValidIssuer = jwtSettings["Issuer"],
            ValidAudience = jwtSettings["Audience"],
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(secretKey))
        };
    });

builder.Services.AddAuthorization();

// RabbitMQ - Use mock service if RabbitMQ is not available
builder.Services.AddSingleton<IRabbitMqService>(provider =>
{
    try
    {
        return new RabbitMqService(
            builder.Configuration.GetConnectionString("RabbitMQ") ?? "localhost",
            provider.GetRequiredService<ILogger<RabbitMqService>>());
    }
    catch
    {
        return new MockRabbitMqService(provider.GetRequiredService<ILogger<MockRabbitMqService>>());
    }
});

// Application Services
builder.Services.AddScoped<IUserService, UrlShortener.UserService.Services.UserService>();
builder.Services.AddScoped<IJwtTokenService, JwtTokenService>();
builder.Services.AddScoped<IPasswordHashService, PasswordHashService>();

// Rate Limiting
builder.Services.AddMemoryCache();
builder.Services.AddScoped<IRateLimitService, RateLimitService>();

var app = builder.Build();

// Configure the HTTP request pipeline
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();

// Ensure database is created
using (var scope = app.Services.CreateScope())
{
    var context = scope.ServiceProvider.GetRequiredService<UserDbContext>();
    context.Database.EnsureCreated();
}

app.Run();