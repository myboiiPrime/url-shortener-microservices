using System.ComponentModel.DataAnnotations;

namespace UrlShortener.Shared.Models
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

        public string? UserId { get; set; } // Keep as string for compatibility with UrlMapping

        [Required]
        public DateTime ClickedAt { get; set; } = DateTime.UtcNow;

        [MaxLength(45)]
        public string? IpAddress { get; set; }

        [MaxLength(500)]
        public string? UserAgent { get; set; }

        [MaxLength(2048)]
        public string? Referrer { get; set; }

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

        // For RabbitMQ compatibility - can be used as EventId when needed
        public string EventId => Id.ToString();
    }
}