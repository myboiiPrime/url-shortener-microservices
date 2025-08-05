namespace UrlShortener.Shared.Models
{
    // ClickEventDto removed - using unified ClickEvent model instead

    public class AnalyticsReportDto
    {
        public string ShortCode { get; set; } = string.Empty;
        public string OriginalUrl { get; set; } = string.Empty;
        public int TotalClicks { get; set; }
        public int UniqueClicks { get; set; }
        public DateTime CreatedAt { get; set; }
        public DateTime? LastClickAt { get; set; }
        public List<DailyClicksDto> DailyClicks { get; set; } = new();
        public List<CountryStatsDto> CountryStats { get; set; } = new();
        public List<BrowserStatsDto> BrowserStats { get; set; } = new();
        public List<DeviceStatsDto> DeviceStats { get; set; } = new();
        public List<ReferrerStatsDto> ReferrerStats { get; set; } = new();
    }

    public class DailyClicksDto
    {
        public DateTime Date { get; set; }
        public int Clicks { get; set; }
    }

    public class CountryStatsDto
    {
        public string Country { get; set; } = string.Empty;
        public int Clicks { get; set; }
    }

    public class BrowserStatsDto
    {
        public string Browser { get; set; } = string.Empty;
        public int Clicks { get; set; }
    }

    public class DeviceStatsDto
    {
        public string Device { get; set; } = string.Empty;
        public int Clicks { get; set; }
    }

    public class ReferrerStatsDto
    {
        public string Referrer { get; set; } = string.Empty;
        public int Clicks { get; set; }
    }

    public class DashboardStatsDto
    {
        public int TotalUrls { get; set; }
        public int TotalClicks { get; set; }
        public int TotalUniqueClicks { get; set; }
        public int ClicksToday { get; set; }
        public int ClicksThisWeek { get; set; }
        public int ClicksThisMonth { get; set; }
        public List<DailyClicksDto> Last30DaysClicks { get; set; } = new();
        public List<TopUrlDto> TopUrls { get; set; } = new();
    }

    public class TopUrlDto
    {
        public string ShortCode { get; set; } = string.Empty;
        public string OriginalUrl { get; set; } = string.Empty;
        public int TotalClicks { get; set; }
    }
}