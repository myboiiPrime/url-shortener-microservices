using System.ComponentModel.DataAnnotations;

namespace UrlShortener.Shared.Models
{
    public class UrlMapping
    {
        [Key]
        [MaxLength(10)]
        public required string ShortCode { get; set; }

        [Required]
        [MaxLength(2048)]
        public required string OriginalUrl { get; set; }

        public string? UserId { get; set; }

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
        
        public DateTime? ExpiresAt { get; set; }
        
        public long ClickCount { get; set; } = 0;
        
        public bool IsActive { get; set; } = true;
        
        // Analytics data
        public string? Title { get; set; }
        public string? Description { get; set; }
    }
}