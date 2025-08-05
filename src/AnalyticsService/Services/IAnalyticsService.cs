using UrlShortener.Shared.Models;

namespace UrlShortener.AnalyticsService.Services
{
    public interface IAnalyticsService
    {
        Task RecordClickAsync(ClickEvent clickEvent);
        Task<AnalyticsReportDto?> GetUrlAnalyticsAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null);
        Task<List<AnalyticsReportDto>> GetUserAnalyticsAsync(Guid userId, DateTime? startDate = null, DateTime? endDate = null);
        Task<DashboardStatsDto> GetDashboardStatsAsync(Guid? userId = null);
        Task<List<TopUrlDto>> GetTopUrlsAsync(int count = 10, Guid? userId = null);
    }
}