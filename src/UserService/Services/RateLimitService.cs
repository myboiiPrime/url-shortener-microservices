using Microsoft.Extensions.Caching.Memory;

namespace UrlShortener.UserService.Services
{
    public interface IRateLimitService
    {
        Task<bool> IsAllowedAsync(string key, int maxRequests, TimeSpan timeWindow);
        Task<RateLimitInfo> GetRateLimitInfoAsync(string key, int maxRequests, TimeSpan timeWindow);
    }

    public class RateLimitInfo
    {
        public bool IsAllowed { get; set; }
        public int RequestsRemaining { get; set; }
        public DateTime ResetTime { get; set; }
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

        public async Task<bool> IsAllowedAsync(string key, int maxRequests, TimeSpan timeWindow)
        {
            var info = await GetRateLimitInfoAsync(key, maxRequests, timeWindow);
            return info.IsAllowed;
        }

        public Task<RateLimitInfo> GetRateLimitInfoAsync(string key, int maxRequests, TimeSpan timeWindow)
        {
            var cacheKey = $"rate_limit:{key}";
            var now = DateTime.UtcNow;

            if (!_cache.TryGetValue(cacheKey, out RateLimitData? data) || data == null)
            {
                data = new RateLimitData
                {
                    RequestCount = 1,
                    WindowStart = now
                };

                _cache.Set(cacheKey, data, timeWindow);

                return Task.FromResult(new RateLimitInfo
                {
                    IsAllowed = true,
                    RequestsRemaining = maxRequests - 1,
                    ResetTime = now.Add(timeWindow)
                });
            }

            // Check if we're still in the same time window
            if (now - data.WindowStart < timeWindow)
            {
                if (data.RequestCount >= maxRequests)
                {
                    return Task.FromResult(new RateLimitInfo
                    {
                        IsAllowed = false,
                        RequestsRemaining = 0,
                        ResetTime = data.WindowStart.Add(timeWindow)
                    });
                }

                data.RequestCount++;
                _cache.Set(cacheKey, data, data.WindowStart.Add(timeWindow) - now);

                return Task.FromResult(new RateLimitInfo
                {
                    IsAllowed = true,
                    RequestsRemaining = maxRequests - data.RequestCount,
                    ResetTime = data.WindowStart.Add(timeWindow)
                });
            }

            // New time window
            data.RequestCount = 1;
            data.WindowStart = now;
            _cache.Set(cacheKey, data, timeWindow);

            return Task.FromResult(new RateLimitInfo
            {
                IsAllowed = true,
                RequestsRemaining = maxRequests - 1,
                ResetTime = now.Add(timeWindow)
            });
        }

        private class RateLimitData
        {
            public int RequestCount { get; set; }
            public DateTime WindowStart { get; set; }
        }
    }
}