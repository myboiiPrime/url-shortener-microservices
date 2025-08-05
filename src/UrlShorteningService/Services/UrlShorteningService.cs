using Microsoft.EntityFrameworkCore;
using UrlShortener.Shared.Models;
using UrlShortener.Shared.Services;
using UrlShortener.UrlShorteningService.Data;

namespace UrlShortener.UrlShorteningService.Services
{
    public class UrlShorteningService : IUrlShorteningService
    {
        private readonly UrlShorteningDbContext _context;
        private readonly ICacheService _cache;
        private readonly IRabbitMqService _rabbitMq;
        private readonly ILogger<UrlShorteningService> _logger;
        private readonly IServiceProvider _serviceProvider;

        public UrlShorteningService(
            UrlShorteningDbContext context,
            ICacheService cache,
            IRabbitMqService rabbitMq,
            ILogger<UrlShorteningService> logger,
            IServiceProvider serviceProvider)
        {
            _context = context;
            _cache = cache;
            _rabbitMq = rabbitMq;
            _logger = logger;
            _serviceProvider = serviceProvider;
        }

        public async Task<UrlMapping> ShortenUrlAsync(UrlShortenRequest request)
        {
            try
            {
                // Generate unique short code
                var shortCode = ShortCodeGenerator.GenerateWithRetry(IsShortCodeUnique);

                // Create URL mapping
                var urlMapping = new UrlMapping
                {
                    ShortCode = shortCode,
                    OriginalUrl = request.OriginalUrl,
                    UserId = request.UserId,
                    ExpiresAt = request.ExpiresAt,
                    CreatedAt = DateTime.UtcNow
                };

                // Fetch URL metadata (title, description) asynchronously
                _ = Task.Run(async () => await FetchUrlMetadataAsync(urlMapping.ShortCode, urlMapping.OriginalUrl));

                _context.UrlMappings.Add(urlMapping);
                await _context.SaveChangesAsync();

                // Cache the mapping
                await _cache.SetAsync($"url:{shortCode}", urlMapping, TimeSpan.FromHours(24));

                _logger.LogInformation($"Created short URL: {shortCode} -> {request.OriginalUrl}");

                return urlMapping;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to shorten URL: {request.OriginalUrl}");
                throw;
            }
        }

        public async Task<UrlMapping?> GetUrlMappingAsync(string shortCode)
        {
            try
            {
                // Try cache first
                var cached = await _cache.GetAsync<UrlMapping>($"url:{shortCode}");
                if (cached != null)
                {
                    return cached;
                }

                // Fallback to database
                var mapping = await _context.UrlMappings
                    .FirstOrDefaultAsync(u => u.ShortCode == shortCode && u.IsActive);

                if (mapping != null)
                {
                    // Check if expired
                    if (mapping.ExpiresAt.HasValue && mapping.ExpiresAt.Value < DateTime.UtcNow)
                    {
                        mapping.IsActive = false;
                        await _context.SaveChangesAsync();
                        return null;
                    }

                    // Cache for future requests
                    await _cache.SetAsync($"url:{shortCode}", mapping, TimeSpan.FromHours(24));
                }

                return mapping;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get URL mapping for: {shortCode}");
                throw;
            }
        }

        public async Task<bool> DeleteUrlMappingAsync(string shortCode, string? userId = null)
        {
            try
            {
                var mapping = await _context.UrlMappings
                    .FirstOrDefaultAsync(u => u.ShortCode == shortCode);

                if (mapping == null)
                    return false;

                // Check ownership if userId is provided
                if (!string.IsNullOrEmpty(userId) && mapping.UserId != userId)
                    return false;

                mapping.IsActive = false;
                await _context.SaveChangesAsync();

                // Remove from cache
                await _cache.RemoveAsync($"url:{shortCode}");

                _logger.LogInformation($"Deleted short URL: {shortCode}");
                return true;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to delete URL mapping: {shortCode}");
                throw;
            }
        }

        public async Task<IEnumerable<UrlMapping>> GetUserUrlsAsync(string userId, int page = 1, int pageSize = 10)
        {
            try
            {
                return await _context.UrlMappings
                    .Where(u => u.UserId == userId && u.IsActive)
                    .OrderByDescending(u => u.CreatedAt)
                    .Skip((page - 1) * pageSize)
                    .Take(pageSize)
                    .ToListAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get URLs for user: {userId}");
                throw;
            }
        }

        public async Task IncrementClickCountAsync(string shortCode)
        {
            try
            {
                var mapping = await _context.UrlMappings
                    .FirstOrDefaultAsync(u => u.ShortCode == shortCode && u.IsActive);

                if (mapping != null)
                {
                    mapping.ClickCount++;
                    await _context.SaveChangesAsync();

                    // Update cache with new click count
                    await _cache.SetAsync($"url:{shortCode}", mapping, TimeSpan.FromHours(24));

                    _logger.LogDebug($"Incremented click count for {shortCode} to {mapping.ClickCount}");
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to increment click count for: {shortCode}");
                // Don't throw here to avoid breaking the redirect flow
            }
        }

        private bool IsShortCodeUnique(string shortCode)
        {
            return !_context.UrlMappings.Any(u => u.ShortCode == shortCode);
        }

        private async Task FetchUrlMetadataAsync(string shortCode, string originalUrl)
        {
            try
            {
                using var scope = _serviceProvider.CreateScope();
                using var context = scope.ServiceProvider.GetRequiredService<UrlShorteningDbContext>();
                
                using var httpClient = new HttpClient();
                httpClient.Timeout = TimeSpan.FromSeconds(10);
                
                var response = await httpClient.GetStringAsync(originalUrl);
                
                // Find the URL mapping in the new context
                var urlMapping = await context.UrlMappings
                    .FirstOrDefaultAsync(u => u.ShortCode == shortCode);
                
                if (urlMapping == null)
                    return;
                
                // Simple HTML parsing for title and description
                var titleMatch = System.Text.RegularExpressions.Regex.Match(response, @"<title[^>]*>([^<]+)</title>", System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                if (titleMatch.Success)
                {
                    urlMapping.Title = titleMatch.Groups[1].Value.Trim();
                }

                var descMatch = System.Text.RegularExpressions.Regex.Match(response, @"<meta[^>]*name=[""']description[""'][^>]*content=[""']([^""']+)[""'][^>]*>", System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                if (descMatch.Success)
                {
                    urlMapping.Description = descMatch.Groups[1].Value.Trim();
                }

                await context.SaveChangesAsync();
                
                _logger.LogInformation($"Successfully fetched metadata for URL: {shortCode} -> {originalUrl}");
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, $"Failed to fetch metadata for URL: {originalUrl}");
            }
        }
    }
}