using System.ComponentModel.DataAnnotations;

namespace UrlShortener.UserService.Models
{
    public class RegisterRequest
    {
        [Required]
        [MinLength(3)]
        [MaxLength(50)]
        public required string Username { get; set; }

        [Required]
        [EmailAddress]
        public required string Email { get; set; }

        [Required]
        [MinLength(6)]
        public required string Password { get; set; }
    }

    public class LoginRequest
    {
        [Required]
        public required string UsernameOrEmail { get; set; }

        [Required]
        public required string Password { get; set; }
    }

    public class AuthResponse
    {
        public required string Token { get; set; }
        public required string RefreshToken { get; set; }
        public DateTime ExpiresAt { get; set; }
        public required UserDto User { get; set; }
    }

    public class UserDto
    {
        public required string Id { get; set; }
        public required string Username { get; set; }
        public required string Email { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime? LastLoginAt { get; set; }
        public bool IsEmailVerified { get; set; }
        public int DailyUrlLimit { get; set; }
        public int MonthlyUrlLimit { get; set; }
        public int TotalUrlsCreated { get; set; }
        public int TotalClicks { get; set; }
    }
}