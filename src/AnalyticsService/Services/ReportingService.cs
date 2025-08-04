using System.Text;
using UrlShortener.AnalyticsService.Models;

namespace UrlShortener.AnalyticsService.Services
{
    public class ReportingService : IReportingService
    {
        private readonly IAnalyticsService _analyticsService;
        private readonly ILogger<ReportingService> _logger;

        public ReportingService(IAnalyticsService analyticsService, ILogger<ReportingService> logger)
        {
            _analyticsService = analyticsService;
            _logger = logger;
        }

        public async Task<byte[]> GeneratePdfReportAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null)
        {
            try
            {
                var analytics = await _analyticsService.GetUrlAnalyticsAsync(shortCode, startDate, endDate);
                if (analytics == null)
                    throw new ArgumentException($"No analytics found for short code: {shortCode}");

                // For simplicity, generating HTML that can be converted to PDF
                var html = GenerateHtmlReport(analytics, startDate, endDate);
                return Encoding.UTF8.GetBytes(html);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating PDF report for short code: {ShortCode}", shortCode);
                throw;
            }
        }

        public async Task<byte[]> GenerateCsvReportAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null)
        {
            try
            {
                var analytics = await _analyticsService.GetUrlAnalyticsAsync(shortCode, startDate, endDate);
                if (analytics == null)
                    throw new ArgumentException($"No analytics found for short code: {shortCode}");

                var csv = GenerateCsvReport(analytics);
                return Encoding.UTF8.GetBytes(csv);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating CSV report for short code: {ShortCode}", shortCode);
                throw;
            }
        }

        public async Task<byte[]> GenerateUserReportAsync(Guid userId, DateTime? startDate = null, DateTime? endDate = null)
        {
            try
            {
                var userAnalytics = await _analyticsService.GetUserAnalyticsAsync(userId, startDate, endDate);
                var dashboardStats = await _analyticsService.GetDashboardStatsAsync(userId);

                var html = GenerateUserHtmlReport(userAnalytics, dashboardStats, startDate, endDate);
                return Encoding.UTF8.GetBytes(html);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating user report for user: {UserId}", userId);
                throw;
            }
        }

        private string GenerateHtmlReport(AnalyticsReportDto analytics, DateTime? startDate, DateTime? endDate)
        {
            var dateRange = GetDateRangeString(startDate, endDate);
            
            return $@"
<!DOCTYPE html>
<html>
<head>
    <title>Analytics Report - {analytics.ShortCode}</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background-color: #f4f4f4; padding: 20px; border-radius: 5px; }}
        .stats {{ display: flex; justify-content: space-around; margin: 20px 0; }}
        .stat-box {{ text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }}
        .chart-section {{ margin: 20px 0; }}
        table {{ width: 100%; border-collapse: collapse; margin: 10px 0; }}
        th, td {{ border: 1px solid #ddd; padding: 8px; text-align: left; }}
        th {{ background-color: #f2f2f2; }}
    </style>
</head>
<body>
    <div class='header'>
        <h1>Analytics Report</h1>
        <p><strong>Short Code:</strong> {analytics.ShortCode}</p>
        <p><strong>Original URL:</strong> {analytics.OriginalUrl}</p>
        <p><strong>Report Period:</strong> {dateRange}</p>
        <p><strong>Generated:</strong> {DateTime.UtcNow:yyyy-MM-dd HH:mm:ss} UTC</p>
    </div>

    <div class='stats'>
        <div class='stat-box'>
            <h3>{analytics.TotalClicks}</h3>
            <p>Total Clicks</p>
        </div>
        <div class='stat-box'>
            <h3>{analytics.UniqueClicks}</h3>
            <p>Unique Clicks</p>
        </div>
        <div class='stat-box'>
            <h3>{analytics.CreatedAt:yyyy-MM-dd}</h3>
            <p>Created Date</p>
        </div>
        <div class='stat-box'>
            <h3>{(analytics.LastClickAt?.ToString("yyyy-MM-dd") ?? "Never")}</h3>
            <p>Last Click</p>
        </div>
    </div>

    <div class='chart-section'>
        <h2>Daily Clicks</h2>
        <table>
            <tr><th>Date</th><th>Clicks</th></tr>
            {string.Join("", analytics.DailyClicks.Select(d => $"<tr><td>{d.Date:yyyy-MM-dd}</td><td>{d.Clicks}</td></tr>"))}
        </table>
    </div>

    <div class='chart-section'>
        <h2>Browser Statistics</h2>
        <table>
            <tr><th>Browser</th><th>Clicks</th></tr>
            {string.Join("", analytics.BrowserStats.Select(b => $"<tr><td>{b.Browser}</td><td>{b.Clicks}</td></tr>"))}
        </table>
    </div>

    <div class='chart-section'>
        <h2>Device Statistics</h2>
        <table>
            <tr><th>Device</th><th>Clicks</th></tr>
            {string.Join("", analytics.DeviceStats.Select(d => $"<tr><td>{d.Device}</td><td>{d.Clicks}</td></tr>"))}
        </table>
    </div>

    <div class='chart-section'>
        <h2>Country Statistics</h2>
        <table>
            <tr><th>Country</th><th>Clicks</th></tr>
            {string.Join("", analytics.CountryStats.Select(c => $"<tr><td>{c.Country}</td><td>{c.Clicks}</td></tr>"))}
        </table>
    </div>
</body>
</html>";
        }

        private string GenerateCsvReport(AnalyticsReportDto analytics)
        {
            var csv = new StringBuilder();
            
            // Header information
            csv.AppendLine("URL Analytics Report");
            csv.AppendLine($"Short Code,{analytics.ShortCode}");
            csv.AppendLine($"Original URL,{analytics.OriginalUrl}");
            csv.AppendLine($"Total Clicks,{analytics.TotalClicks}");
            csv.AppendLine($"Unique Clicks,{analytics.UniqueClicks}");
            csv.AppendLine($"Created Date,{analytics.CreatedAt:yyyy-MM-dd}");
            csv.AppendLine($"Last Click,{(analytics.LastClickAt?.ToString("yyyy-MM-dd") ?? "Never")}");
            csv.AppendLine();

            // Daily clicks
            csv.AppendLine("Daily Clicks");
            csv.AppendLine("Date,Clicks");
            foreach (var daily in analytics.DailyClicks)
            {
                csv.AppendLine($"{daily.Date:yyyy-MM-dd},{daily.Clicks}");
            }
            csv.AppendLine();

            // Browser stats
            csv.AppendLine("Browser Statistics");
            csv.AppendLine("Browser,Clicks");
            foreach (var browser in analytics.BrowserStats)
            {
                csv.AppendLine($"{browser.Browser},{browser.Clicks}");
            }
            csv.AppendLine();

            // Device stats
            csv.AppendLine("Device Statistics");
            csv.AppendLine("Device,Clicks");
            foreach (var device in analytics.DeviceStats)
            {
                csv.AppendLine($"{device.Device},{device.Clicks}");
            }
            csv.AppendLine();

            // Country stats
            csv.AppendLine("Country Statistics");
            csv.AppendLine("Country,Clicks");
            foreach (var country in analytics.CountryStats)
            {
                csv.AppendLine($"{country.Country},{country.Clicks}");
            }

            return csv.ToString();
        }

        private string GenerateUserHtmlReport(List<AnalyticsReportDto> userAnalytics, DashboardStatsDto dashboardStats, DateTime? startDate, DateTime? endDate)
        {
            var dateRange = GetDateRangeString(startDate, endDate);
            
            return $@"
<!DOCTYPE html>
<html>
<head>
    <title>User Analytics Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .header {{ background-color: #f4f4f4; padding: 20px; border-radius: 5px; }}
        .stats {{ display: flex; justify-content: space-around; margin: 20px 0; }}
        .stat-box {{ text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }}
        table {{ width: 100%; border-collapse: collapse; margin: 10px 0; }}
        th, td {{ border: 1px solid #ddd; padding: 8px; text-align: left; }}
        th {{ background-color: #f2f2f2; }}
        .url-section {{ margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }}
    </style>
</head>
<body>
    <div class='header'>
        <h1>User Analytics Report</h1>
        <p><strong>Report Period:</strong> {dateRange}</p>
        <p><strong>Generated:</strong> {DateTime.UtcNow:yyyy-MM-dd HH:mm:ss} UTC</p>
    </div>

    <div class='stats'>
        <div class='stat-box'>
            <h3>{dashboardStats.TotalUrls}</h3>
            <p>Total URLs</p>
        </div>
        <div class='stat-box'>
            <h3>{dashboardStats.TotalClicks}</h3>
            <p>Total Clicks</p>
        </div>
        <div class='stat-box'>
            <h3>{dashboardStats.TotalUniqueClicks}</h3>
            <p>Unique Clicks</p>
        </div>
        <div class='stat-box'>
            <h3>{dashboardStats.ClicksToday}</h3>
            <p>Clicks Today</p>
        </div>
    </div>

    <h2>Top URLs</h2>
    <table>
        <tr><th>Short Code</th><th>Original URL</th><th>Total Clicks</th></tr>
        {string.Join("", dashboardStats.TopUrls.Select(u => $"<tr><td>{u.ShortCode}</td><td>{u.OriginalUrl}</td><td>{u.TotalClicks}</td></tr>"))}
    </table>

    <h2>Detailed URL Analytics</h2>
    {string.Join("", userAnalytics.Select(url => $@"
    <div class='url-section'>
        <h3>{url.ShortCode} - {url.OriginalUrl}</h3>
        <p><strong>Total Clicks:</strong> {url.TotalClicks} | <strong>Unique Clicks:</strong> {url.UniqueClicks}</p>
        <p><strong>Created:</strong> {url.CreatedAt:yyyy-MM-dd} | <strong>Last Click:</strong> {(url.LastClickAt?.ToString("yyyy-MM-dd") ?? "Never")}</p>
    </div>"))}
</body>
</html>";
        }

        private string GetDateRangeString(DateTime? startDate, DateTime? endDate)
        {
            if (startDate.HasValue && endDate.HasValue)
                return $"{startDate.Value:yyyy-MM-dd} to {endDate.Value:yyyy-MM-dd}";
            else if (startDate.HasValue)
                return $"From {startDate.Value:yyyy-MM-dd}";
            else if (endDate.HasValue)
                return $"Until {endDate.Value:yyyy-MM-dd}";
            else
                return "All Time";
        }
    }
}