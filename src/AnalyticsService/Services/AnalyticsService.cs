using Microsoft.EntityFrameworkCore;
using UrlShortener.AnalyticsService.Data;

using UrlShortener.Shared.Models;

namespace UrlShortener.AnalyticsService.Services
{
    public class AnalyticsService : IAnalyticsService
    {
        private readonly AnalyticsDbContext _context;
        private readonly ILogger<AnalyticsService> _logger;

        public AnalyticsService(AnalyticsDbContext context, ILogger<AnalyticsService> logger)
        {
            _context = context;
            _logger = logger;
        }

        public async Task RecordClickAsync(ClickEvent clickEvent)
        {
            try
            {
                // No conversion needed - using unified model
                // Just ensure ClickedAt is set if not already
                if (clickEvent.ClickedAt == default)
                    clickEvent.ClickedAt = DateTime.UtcNow;

                // Parse user agent for device/browser info
                ParseUserAgent(clickEvent, clickEvent.UserAgent);

                // Add click event
                _context.ClickEvents.Add(clickEvent);

                // Update or create URL statistics
                await UpdateUrlStatisticsAsync(clickEvent);

                await _context.SaveChangesAsync();

                _logger.LogInformation("Recorded click for URL {ShortCode} by user {UserId}", 
                    clickEvent.ShortCode, clickEvent.UserId);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error recording click event for short code: {ShortCode}", clickEvent.ShortCode);
                throw;
            }
        }

        public async Task<AnalyticsReportDto?> GetUrlAnalyticsAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null)
        {
            try
            {
                var urlStats = await _context.UrlStatistics
                    .FirstOrDefaultAsync(u => u.ShortCode == shortCode);

                if (urlStats == null)
                    return null;

                var query = _context.ClickEvents.Where(c => c.ShortCode == shortCode);

                if (startDate.HasValue)
                    query = query.Where(c => c.ClickedAt >= startDate.Value);

                if (endDate.HasValue)
                    query = query.Where(c => c.ClickedAt <= endDate.Value);

                var clickEvents = await query.ToListAsync();

                var report = new AnalyticsReportDto
                {
                    ShortCode = urlStats.ShortCode,
                    OriginalUrl = urlStats.OriginalUrl,
                    TotalClicks = urlStats.TotalClicks,
                    UniqueClicks = urlStats.UniqueClicks,
                    CreatedAt = urlStats.CreatedAt,
                    LastClickAt = urlStats.LastClickAt == DateTime.MinValue ? null : urlStats.LastClickAt,
                    DailyClicks = GetDailyClicks(clickEvents),
                    CountryStats = GetCountryStats(clickEvents),
                    BrowserStats = GetBrowserStats(clickEvents),
                    DeviceStats = GetDeviceStats(clickEvents)
                };

                return report;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting analytics for short code: {ShortCode}", shortCode);
                throw;
            }
        }

        public async Task<List<AnalyticsReportDto>> GetUserAnalyticsAsync(Guid userId, DateTime? startDate = null, DateTime? endDate = null)
        {
            try
            {
                var userIdString = userId.ToString();
                var userUrls = await _context.UrlStatistics
                    .Where(u => u.UserId == userIdString)
                    .ToListAsync();

                var reports = new List<AnalyticsReportDto>();

                foreach (var urlStats in userUrls)
                {
                    var report = await GetUrlAnalyticsAsync(urlStats.ShortCode, startDate, endDate);
                    if (report != null)
                        reports.Add(report);
                }

                return reports.OrderByDescending(r => r.TotalClicks).ToList();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting user analytics for user: {UserId}", userId);
                throw;
            }
        }

        public async Task<DashboardStatsDto> GetDashboardStatsAsync(Guid? userId = null)
        {
            try
            {
                var now = DateTime.UtcNow;
                var today = now.Date;
                var weekStart = today.AddDays(-(int)today.DayOfWeek);
                var monthStart = new DateTime(today.Year, today.Month, 1);
                var thirtyDaysAgo = today.AddDays(-30);

                var urlQuery = _context.UrlStatistics.AsQueryable();
                var clickQuery = _context.ClickEvents.AsQueryable();

                if (userId.HasValue)
                {
                    var userIdString = userId.Value.ToString();
                    urlQuery = urlQuery.Where(u => u.UserId == userIdString);
                    clickQuery = clickQuery.Where(c => c.UserId == userIdString);
                }

                var totalUrls = await urlQuery.CountAsync();
                var totalClicks = await urlQuery.SumAsync(u => u.TotalClicks);
                var totalUniqueClicks = await urlQuery.SumAsync(u => u.UniqueClicks);

                var clicksToday = await clickQuery.CountAsync(c => c.ClickedAt >= today);
                var clicksThisWeek = await clickQuery.CountAsync(c => c.ClickedAt >= weekStart);
                var clicksThisMonth = await clickQuery.CountAsync(c => c.ClickedAt >= monthStart);

                var last30DaysClicks = await clickQuery
                    .Where(c => c.ClickedAt >= thirtyDaysAgo)
                    .GroupBy(c => c.ClickedAt.Date)
                    .Select(g => new DailyClicksDto
                    {
                        Date = g.Key,
                        Clicks = g.Count()
                    })
                    .OrderBy(d => d.Date)
                    .ToListAsync();

                var topUrls = await urlQuery
                    .OrderByDescending(u => u.TotalClicks)
                    .Take(10)
                    .Select(u => new TopUrlDto
                    {
                        ShortCode = u.ShortCode,
                        OriginalUrl = u.OriginalUrl,
                        TotalClicks = u.TotalClicks
                    })
                    .ToListAsync();

                return new DashboardStatsDto
                {
                    TotalUrls = totalUrls,
                    TotalClicks = totalClicks,
                    TotalUniqueClicks = totalUniqueClicks,
                    ClicksToday = clicksToday,
                    ClicksThisWeek = clicksThisWeek,
                    ClicksThisMonth = clicksThisMonth,
                    Last30DaysClicks = last30DaysClicks,
                    TopUrls = topUrls
                };
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting dashboard stats for user: {UserId}", userId);
                throw;
            }
        }

        public async Task<List<TopUrlDto>> GetTopUrlsAsync(int count = 10, Guid? userId = null)
        {
            try
            {
                var query = _context.UrlStatistics.AsQueryable();

                if (userId.HasValue)
                {
                    var userIdString = userId.Value.ToString();
                    query = query.Where(u => u.UserId == userIdString);
                }

                return await query
                    .OrderByDescending(u => u.TotalClicks)
                    .Take(count)
                    .Select(u => new TopUrlDto
                    {
                        ShortCode = u.ShortCode,
                        OriginalUrl = u.OriginalUrl,
                        TotalClicks = u.TotalClicks
                    })
                    .ToListAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting top URLs for user: {UserId}", userId);
                throw;
            }
        }

        private async Task UpdateUrlStatisticsAsync(ClickEvent clickEvent)
        {
            var urlStats = await _context.UrlStatistics
                .FirstOrDefaultAsync(u => u.ShortCode == clickEvent.ShortCode);

            if (urlStats == null)
            {
                urlStats = new UrlStatistics
                {
                    ShortCode = clickEvent.ShortCode,
                    OriginalUrl = clickEvent.OriginalUrl,
                    UserId = clickEvent.UserId,
                    TotalClicks = 1,
                    UniqueClicks = 1,
                    LastClickAt = clickEvent.ClickedAt,
                    LastUpdated = DateTime.UtcNow,
                    CreatedAt = DateTime.UtcNow
                };
                _context.UrlStatistics.Add(urlStats);
            }
            else
            {
                urlStats.TotalClicks++;
                urlStats.LastClickAt = clickEvent.ClickedAt;
                urlStats.LastUpdated = DateTime.UtcNow;

                // Check if this is a unique click (same IP within 24 hours)
                var isUniqueClick = !await _context.ClickEvents
                    .AnyAsync(c => c.ShortCode == clickEvent.ShortCode &&
                                  c.IpAddress == clickEvent.IpAddress &&
                                  c.ClickedAt >= DateTime.UtcNow.AddHours(-24) &&
                                  c.Id != clickEvent.Id);

                if (isUniqueClick)
                    urlStats.UniqueClicks++;
            }
        }

        private void ParseUserAgent(ClickEvent clickEvent, string userAgent)
        {
            if (string.IsNullOrEmpty(userAgent))
                return;

            // Simple user agent parsing (in production, use a proper library like UAParser)
            var ua = userAgent.ToLower();

            // Browser detection
            if (ua.Contains("chrome"))
                clickEvent.Browser = "Chrome";
            else if (ua.Contains("firefox"))
                clickEvent.Browser = "Firefox";
            else if (ua.Contains("safari"))
                clickEvent.Browser = "Safari";
            else if (ua.Contains("edge"))
                clickEvent.Browser = "Edge";
            else
                clickEvent.Browser = "Other";

            // Device detection
            if (ua.Contains("mobile") || ua.Contains("android") || ua.Contains("iphone"))
                clickEvent.Device = "Mobile";
            else if (ua.Contains("tablet") || ua.Contains("ipad"))
                clickEvent.Device = "Tablet";
            else
                clickEvent.Device = "Desktop";

            // OS detection
            if (ua.Contains("windows"))
                clickEvent.OperatingSystem = "Windows";
            else if (ua.Contains("mac"))
                clickEvent.OperatingSystem = "macOS";
            else if (ua.Contains("linux"))
                clickEvent.OperatingSystem = "Linux";
            else if (ua.Contains("android"))
                clickEvent.OperatingSystem = "Android";
            else if (ua.Contains("ios"))
                clickEvent.OperatingSystem = "iOS";
            else
                clickEvent.OperatingSystem = "Other";
        }

        private List<DailyClicksDto> GetDailyClicks(List<ClickEvent> clickEvents)
        {
            return clickEvents
                .GroupBy(c => c.ClickedAt.Date)
                .Select(g => new DailyClicksDto
                {
                    Date = g.Key,
                    Clicks = g.Count()
                })
                .OrderBy(d => d.Date)
                .ToList();
        }

        private List<CountryStatsDto> GetCountryStats(List<ClickEvent> clickEvents)
        {
            return clickEvents
                .Where(c => !string.IsNullOrEmpty(c.Country))
                .GroupBy(c => c.Country!)
                .Select(g => new CountryStatsDto
                {
                    Country = g.Key,
                    Clicks = g.Count()
                })
                .OrderByDescending(c => c.Clicks)
                .ToList();
        }

        private List<BrowserStatsDto> GetBrowserStats(List<ClickEvent> clickEvents)
        {
            return clickEvents
                .Where(c => !string.IsNullOrEmpty(c.Browser))
                .GroupBy(c => c.Browser!)
                .Select(g => new BrowserStatsDto
                {
                    Browser = g.Key,
                    Clicks = g.Count()
                })
                .OrderByDescending(b => b.Clicks)
                .ToList();
        }

        private List<DeviceStatsDto> GetDeviceStats(List<ClickEvent> clickEvents)
        {
            return clickEvents
                .Where(c => !string.IsNullOrEmpty(c.Device))
                .GroupBy(c => c.Device!)
                .Select(g => new DeviceStatsDto
                {
                    Device = g.Key,
                    Clicks = g.Count()
                })
                .OrderByDescending(d => d.Clicks)
                .ToList();
        }
    }
}