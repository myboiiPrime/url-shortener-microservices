using Microsoft.EntityFrameworkCore;
using UrlShortener.UrlShorteningService.Data;
using UrlShortener.UrlShorteningService.Services;
using UrlShortener.Shared.Services;
using StackExchange.Redis;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// Database - Switch to SQLite for easier development
builder.Services.AddDbContext<UrlShorteningDbContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("DefaultConnection")));

// Redis Cache - Use in-memory cache if Redis is not available
var redisConnectionString = builder.Configuration.GetConnectionString("Redis") ?? "localhost:6379";

try
{
    var redis = ConnectionMultiplexer.Connect(redisConnectionString);
    builder.Services.AddSingleton<IConnectionMultiplexer>(redis);
    builder.Services.AddScoped<ICacheService, RedisCacheService>();
    Console.WriteLine("Using Redis cache");
}
catch (Exception ex)
{
    Console.WriteLine($"Redis connection failed: {ex.Message}. Using in-memory cache instead.");
    builder.Services.AddMemoryCache();
    builder.Services.AddScoped<ICacheService, MemoryCacheService>();
}

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
builder.Services.AddScoped<IUrlShorteningService, UrlShorteningService>();
builder.Services.AddHostedService<UrlShortenRequestConsumer>();

var app = builder.Build();

// Configure the HTTP request pipeline
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseAuthorization();
app.MapControllers();

// Ensure database is created
using (var scope = app.Services.CreateScope())
{
    var context = scope.ServiceProvider.GetRequiredService<UrlShorteningDbContext>();
    context.Database.EnsureCreated();
}

app.Run();