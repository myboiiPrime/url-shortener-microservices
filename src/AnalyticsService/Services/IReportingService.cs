namespace UrlShortener.AnalyticsService.Services
{
    public interface IReportingService
    {
        Task<byte[]> GeneratePdfReportAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null);
        Task<byte[]> GenerateCsvReportAsync(string shortCode, DateTime? startDate = null, DateTime? endDate = null);
        Task<byte[]> GenerateUserReportAsync(Guid userId, DateTime? startDate = null, DateTime? endDate = null);
    }
}