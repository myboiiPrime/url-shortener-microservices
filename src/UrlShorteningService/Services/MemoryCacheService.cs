using Microsoft.Extensions.Caching.Memory;

namespace UrlShortener.UrlShorteningService.Services
{
    public class MemoryCacheService : ICacheService
    {
        private readonly IMemoryCache _cache;
        private readonly ILogger<MemoryCacheService> _logger;

        public MemoryCacheService(IMemoryCache cache, ILogger<MemoryCacheService> logger)
        {
            _cache = cache;
            _logger = logger;
        }

        public Task<T?> GetAsync<T>(string key) where T : class
        {
            try
            {
                if (_cache.TryGetValue(key, out var value) && value is T result)
                {
                    return Task.FromResult<T?>(result);
                }
                return Task.FromResult<T?>(null);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get cache value for key: {key}");
                return Task.FromResult<T?>(null);
            }
        }

        public Task SetAsync<T>(string key, T value, TimeSpan? expiration = null) where T : class
        {
            try
            {
                var options = new MemoryCacheEntryOptions();
                if (expiration.HasValue)
                {
                    options.AbsoluteExpirationRelativeToNow = expiration;
                }
                else
                {
                    options.SlidingExpiration = TimeSpan.FromHours(1); // Default expiration
                }

                _cache.Set(key, value, options);
                return Task.CompletedTask;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to set cache value for key: {key}");
                return Task.CompletedTask;
            }
        }

        public Task RemoveAsync(string key)
        {
            try
            {
                _cache.Remove(key);
                return Task.CompletedTask;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to remove cache value for key: {key}");
                return Task.CompletedTask;
            }
        }

        public Task<bool> ExistsAsync(string key)
        {
            try
            {
                return Task.FromResult(_cache.TryGetValue(key, out _));
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to check cache existence for key: {key}");
                return Task.FromResult(false);
            }
        }
    }
}