using System.ComponentModel.DataAnnotations;

namespace UrlShortener.AnalyticsService.Models
{
    public class ClickEvent
    {
        [Key]
        public Guid Id { get; set; } = Guid.NewGuid();

        [Required]
        [MaxLength(50)]
        public string ShortCode { get; set; } = string.Empty;

        [Required]
        [MaxLength(2048)]
        public string OriginalUrl { get; set; } = string.Empty;

        public Guid? UserId { get; set; }

        [Required]
        public DateTime ClickedAt { get; set; } = DateTime.UtcNow;

        [MaxLength(45)]
        public string? IpAddress { get; set; }

        [MaxLength(500)]
        public string? UserAgent { get; set; }

        [MaxLength(2048)]
        public string? Referer { get; set; }

        [MaxLength(100)]
        public string? Country { get; set; }

        [MaxLength(100)]
        public string? City { get; set; }

        [MaxLength(50)]
        public string? Device { get; set; }

        [MaxLength(50)]
        public string? Browser { get; set; }

        [MaxLength(50)]
        public string? OperatingSystem { get; set; }
    }

    public class UrlStatistics
    {
        [Key]
        public Guid Id { get; set; } = Guid.NewGuid();

        [Required]
        [MaxLength(50)]
        public string ShortCode { get; set; } = string.Empty;

        [Required]
        [MaxLength(2048)]
        public string OriginalUrl { get; set; } = string.Empty;

        public Guid? UserId { get; set; }

        public int TotalClicks { get; set; } = 0;

        public int UniqueClicks { get; set; } = 0;

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        public DateTime LastClickAt { get; set; }

        public DateTime LastUpdated { get; set; } = DateTime.UtcNow;
    }
}