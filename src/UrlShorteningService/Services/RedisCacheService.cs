using StackExchange.Redis;
using System.Text.Json;

namespace UrlShortener.UrlShorteningService.Services
{
    public class RedisCacheService : ICacheService
    {
        private readonly IDatabase _database;
        private readonly ILogger<RedisCacheService> _logger;

        public RedisCacheService(IConnectionMultiplexer redis, ILogger<RedisCacheService> logger)
        {
            _database = redis.GetDatabase();
            _logger = logger;
        }

        public async Task<T?> GetAsync<T>(string key) where T : class
        {
            try
            {
                var value = await _database.StringGetAsync(key);
                if (!value.HasValue)
                    return null;

                return JsonSerializer.Deserialize<T>(value.ToString());
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get cache value for key: {key}");
                return null;
            }
        }

        public async Task SetAsync<T>(string key, T value, TimeSpan? expiration = null) where T : class
        {
            try
            {
                var json = JsonSerializer.Serialize(value);
                await _database.StringSetAsync(key, json, expiration);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to set cache value for key: {key}");
            }
        }

        public async Task RemoveAsync(string key)
        {
            try
            {
                await _database.KeyDeleteAsync(key);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to remove cache value for key: {key}");
            }
        }

        public async Task<bool> ExistsAsync(string key)
        {
            try
            {
                return await _database.KeyExistsAsync(key);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to check cache existence for key: {key}");
                return false;
            }
        }
    }
}
