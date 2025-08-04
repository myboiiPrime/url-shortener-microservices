using RabbitMQ.Client;
using RabbitMQ.Client.Events;
using System.Text;
using System.Text.Json;
using UrlShortener.AnalyticsService.Models;
using UrlShortener.AnalyticsService.Services;
using UrlShortener.Shared.Services;

namespace UrlShortener.AnalyticsService.Services
{
    public class ClickEventConsumer : BackgroundService
    {
        private readonly IServiceProvider _serviceProvider;
        private readonly ILogger<ClickEventConsumer> _logger;
        private readonly IRabbitMqService _rabbitMqService;
        private const string QueueName = "click_events";

        public ClickEventConsumer(
            IServiceProvider serviceProvider, 
            ILogger<ClickEventConsumer> logger,
            IRabbitMqService rabbitMqService)
        {
            _serviceProvider = serviceProvider;
            _logger = logger;
            _rabbitMqService = rabbitMqService;
        }

        protected override async Task ExecuteAsync(CancellationToken stoppingToken)
        {
            try
            {
                // Subscribe to click events using the IRabbitMqService interface
                _rabbitMqService.Subscribe<ClickEventDto>(QueueName, async (clickEvent) =>
                {
                    try
                    {
                        using var scope = _serviceProvider.CreateScope();
                        var analyticsService = scope.ServiceProvider.GetRequiredService<IAnalyticsService>();
                        
                        await analyticsService.RecordClickAsync(clickEvent);
                        
                        _logger.LogInformation("Processed click event for short code: {ShortCode}", clickEvent.ShortCode);
                        return true; // Message processed successfully
                    }
                    catch (Exception ex)
                    {
                        _logger.LogError(ex, "Error processing click event for short code: {ShortCode}", clickEvent.ShortCode);
                        return false; // Message processing failed
                    }
                });

                _logger.LogInformation("Click event consumer started and subscribed to queue: {QueueName}", QueueName);

                // Keep the service running
                while (!stoppingToken.IsCancellationRequested)
                {
                    await Task.Delay(1000, stoppingToken);
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to start click event consumer");
                throw;
            }
        }

        public override void Dispose()
        {
            try
            {
                _rabbitMqService?.Close();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error closing RabbitMQ service");
            }
            base.Dispose();
        }
    }
}