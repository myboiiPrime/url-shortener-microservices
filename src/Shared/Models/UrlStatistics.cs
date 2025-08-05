using System.ComponentModel.DataAnnotations;

namespace UrlShortener.Shared.Models
{
    public class UrlStatistics
    {
        [Key]
        public int Id { get; set; }
        
        [Required]
        [MaxLength(10)]
        public string ShortCode { get; set; } = string.Empty;
        
        [Required]
        [MaxLength(2000)]
        public string OriginalUrl { get; set; } = string.Empty;
        
        [MaxLength(50)]
        public string? UserId { get; set; }
        
        public int TotalClicks { get; set; }
        public int UniqueClicks { get; set; }
        public DateTime? LastClickAt { get; set; }
        public DateTime LastUpdated { get; set; }
        public DateTime CreatedAt { get; set; }
    }
}