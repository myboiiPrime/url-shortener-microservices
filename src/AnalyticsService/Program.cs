using Microsoft.EntityFrameworkCore;
using UrlShortener.AnalyticsService.Data;
using UrlShortener.AnalyticsService.Services;
using UrlShortener.Shared.Services;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container
builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();

// Database - Switch to SQLite for easier development
builder.Services.AddDbContext<AnalyticsDbContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("DefaultConnection")));

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
builder.Services.AddScoped<IAnalyticsService, AnalyticsService>();
builder.Services.AddScoped<IReportingService, ReportingService>();
builder.Services.AddHostedService<ClickEventConsumer>();

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

// Serve static files for dashboard
app.UseStaticFiles();

// Ensure database is created
using (var scope = app.Services.CreateScope())
{
    var context = scope.ServiceProvider.GetRequiredService<AnalyticsDbContext>();
    context.Database.EnsureCreated();
}

app.Run();