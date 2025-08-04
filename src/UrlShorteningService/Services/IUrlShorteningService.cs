using UrlShortener.Shared.Models;

namespace UrlShortener.UrlShorteningService.Services
{
    public interface IUrlShorteningService
    {
        Task<UrlMapping> ShortenUrlAsync(UrlShortenRequest request);
        Task<UrlMapping?> GetUrlMappingAsync(string shortCode);
        Task<bool> DeleteUrlMappingAsync(string shortCode, string? userId = null);
        Task<IEnumerable<UrlMapping>> GetUserUrlsAsync(string userId, int page = 1, int pageSize = 10);
    }
}