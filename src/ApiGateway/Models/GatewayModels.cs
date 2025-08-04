namespace UrlShortener.ApiGateway.Models
{
    public class ServiceEndpoint
    {
        public string Name { get; set; } = string.Empty;
        public string Host { get; set; } = string.Empty;
        public int Port { get; set; }
        public string Scheme { get; set; } = "http";
        public bool IsHealthy { get; set; } = true;
        public DateTime LastHealthCheck { get; set; } = DateTime.UtcNow;
        public string HealthCheckEndpoint { get; set; } = "/health";

        public string BaseUrl => $"{Scheme}://{Host}:{Port}";
    }

    public class RouteConfig
    {
        public string Path { get; set; } = string.Empty;
        public string ServiceName { get; set; } = string.Empty;
        public string TargetPath { get; set; } = string.Empty;
        public bool RequireAuth { get; set; } = false;
        public List<string> AllowedMethods { get; set; } = new();
        public int RateLimitPerMinute { get; set; } = 100;
    }

    public class RateLimitInfo
    {
        public int RequestCount { get; set; }
        public DateTime WindowStart { get; set; }
        public int Limit { get; set; }
        public TimeSpan Window { get; set; }

        public bool IsExceeded => RequestCount >= Limit;
        public int RemainingRequests => Math.Max(0, Limit - RequestCount);
        public DateTime ResetTime => WindowStart.Add(Window);
    }

    public class ProxyRequest
    {
        public string Method { get; set; } = string.Empty;
        public string Path { get; set; } = string.Empty;
        public string QueryString { get; set; } = string.Empty;
        public Dictionary<string, string> Headers { get; set; } = new();
        public string? Body { get; set; }
        public string? ContentType { get; set; }
    }

    public class ProxyResponse
    {
        public int StatusCode { get; set; }
        public Dictionary<string, string> Headers { get; set; } = new();
        public string? Body { get; set; }
        public string? ContentType { get; set; }
    }
}