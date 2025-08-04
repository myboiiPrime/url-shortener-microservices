using System.ComponentModel.DataAnnotations;

namespace UrlShortener.UserService.Models
{
    public class User
    {
        [Key]
        public string Id { get; set; } = Guid.NewGuid().ToString();

        [Required]
        [MaxLength(100)]
        public required string Username { get; set; }

        [Required]
        [EmailAddress]
        [MaxLength(255)]
        public required string Email { get; set; }

        [Required]
        public required string PasswordHash { get; set; }

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
        
        public DateTime? LastLoginAt { get; set; }
        
        public bool IsActive { get; set; } = true;
        
        public bool IsEmailVerified { get; set; } = false;
        
        // User preferences
        public int DailyUrlLimit { get; set; } = 100;
        public int MonthlyUrlLimit { get; set; } = 1000;
        
        // Statistics
        public int TotalUrlsCreated { get; set; } = 0;
        public int TotalClicks { get; set; } = 0;
    }
}