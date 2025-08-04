using System.ComponentModel.DataAnnotations;

namespace UrlShortener.Shared.Models
{
    public class UrlShortenRequest
    {
        public string RequestId { get; set; } = Guid.NewGuid().ToString();
        
        [Required]
        [Url]
        [MaxLength(2048)]
        public required string OriginalUrl { get; set; }
        
        public required string UserId { get; set; }
        
        public DateTime RequestedAt { get; set; } = DateTime.UtcNow;
        
        public DateTime? ExpiresAt { get; set; }
    }
}