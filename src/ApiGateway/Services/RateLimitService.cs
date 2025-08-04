using Microsoft.Extensions.Caching.Memory;
using UrlShortener.ApiGateway.Models;

namespace UrlShortener.ApiGateway.Services
{
    public interface IRateLimitService
    {
        Task<bool> IsRequestAllowedAsync(string clientId, string endpoint, int limit, TimeSpan window);
        Task<RateLimitInfo> GetRateLimitInfoAsync(string clientId, string endpoint, int limit, TimeSpan window);
    }

    public class RateLimitService : IRateLimitService
    {
        private readonly IMemoryCache _cache;
        private readonly ILogger<RateLimitService> _logger;

        public RateLimitService(IMemoryCache cache, ILogger<RateLimitService> logger)
        {
            _cache = cache;
            _logger = logger;
        }

        public async Task<bool> IsRequestAllowedAsync(string clientId, string endpoint, int limit, TimeSpan window)
        {
            var rateLimitInfo = await GetRateLimitInfoAsync(clientId, endpoint, limit, window);
            return !rateLimitInfo.IsExceeded;
        }

        public Task<RateLimitInfo> GetRateLimitInfoAsync(string clientId, string endpoint, int limit, TimeSpan window)
        {
            var key = $"rate_limit:{clientId}:{endpoint}";
            var now = DateTime.UtcNow;

            var rateLimitInfo = _cache.GetOrCreate(key, entry =>
            {
                entry.AbsoluteExpirationRelativeToNow = window;
                return new RateLimitInfo
                {
                    RequestCount = 0,
                    WindowStart = now,
                    Limit = limit,
                    Window = window
                };
            });

            // Check if we need to reset the window
            if (now - rateLimitInfo!.WindowStart >= window)
            {
                rateLimitInfo.RequestCount = 0;
                rateLimitInfo.WindowStart = now;
            }

            // Increment request count
            rateLimitInfo.RequestCount++;

            // Update cache
            _cache.Set(key, rateLimitInfo, window);

            if (rateLimitInfo.IsExceeded)
            {
                _logger.LogWarning("Rate limit exceeded for client {ClientId} on endpoint {Endpoint}. " +
                    "Count: {RequestCount}, Limit: {Limit}", clientId, endpoint, rateLimitInfo.RequestCount, limit);
            }

            return Task.FromResult(rateLimitInfo);
        }
    }
}