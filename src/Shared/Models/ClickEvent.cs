namespace UrlShortener.Shared.Models
{
    public class ClickEvent
    {
        public string EventId { get; set; } = Guid.NewGuid().ToString();
        public required string ShortCode { get; set; }
        public required string OriginalUrl { get; set; }
        public DateTime ClickedAt { get; set; } = DateTime.UtcNow;
        public string? IpAddress { get; set; }
        public string? UserAgent { get; set; }
        public string? Referrer { get; set; }
        public string? Country { get; set; }
        public string? City { get; set; }
        public string? UserId { get; set; }
    }
}