using UrlShortener.Shared.Models;
using UrlShortener.Shared.Services;

namespace UrlShortener.UrlShorteningService.Services
{
    public class UrlShortenRequestConsumer : BackgroundService
    {
        private readonly IRabbitMqService _rabbitMq;
        private readonly IServiceProvider _serviceProvider;
        private readonly ILogger<UrlShortenRequestConsumer> _logger;

        public UrlShortenRequestConsumer(
            IRabbitMqService rabbitMq,
            IServiceProvider serviceProvider,
            ILogger<UrlShortenRequestConsumer> logger)
        {
            _rabbitMq = rabbitMq;
            _serviceProvider = serviceProvider;
            _logger = logger;
        }

        protected override async Task ExecuteAsync(CancellationToken stoppingToken)
        {
            _logger.LogInformation("URL Shorten Request Consumer started");

            _rabbitMq.Subscribe<UrlShortenRequest>("url_shorten_requests", async (request) =>
            {
                try
                {
                    using var scope = _serviceProvider.CreateScope();
                    var urlShorteningService = scope.ServiceProvider.GetRequiredService<IUrlShorteningService>();

                    var result = await urlShorteningService.ShortenUrlAsync(request);

                    // Publish success event
                    _rabbitMq.PublishMessage("url_shortened_events", new
                    {
                        RequestId = request.RequestId,
                        ShortCode = result.ShortCode,
                        OriginalUrl = result.OriginalUrl,
                        UserId = result.UserId,
                        Success = true,
                        CreatedAt = result.CreatedAt
                    });

                    _logger.LogInformation($"Successfully processed URL shorten request: {request.RequestId}");
                    return true;
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, $"Failed to process URL shorten request: {request.RequestId}");

                    // Publish failure event
                    _rabbitMq.PublishMessage("url_shortened_events", new
                    {
                        RequestId = request.RequestId,
                        OriginalUrl = request.OriginalUrl,
                        UserId = request.UserId,
                        Success = false,
                        Error = ex.Message,
                        CreatedAt = DateTime.UtcNow
                    });

                    return false;
                }
            });

            // Keep the service running
            while (!stoppingToken.IsCancellationRequested)
            {
                await Task.Delay(1000, stoppingToken);
            }
        }

        public override void Dispose()
        {
            _rabbitMq?.Close();
            base.Dispose();
        }
    }
}