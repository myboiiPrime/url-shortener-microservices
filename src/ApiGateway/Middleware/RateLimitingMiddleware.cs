using UrlShortener.ApiGateway.Services;

namespace UrlShortener.ApiGateway.Middleware
{
    public class RateLimitingMiddleware
    {
        private readonly RequestDelegate _next;
        private readonly IRateLimitService _rateLimitService;
        private readonly IConfiguration _configuration;
        private readonly ILogger<RateLimitingMiddleware> _logger;

        public RateLimitingMiddleware(
            RequestDelegate next,
            IRateLimitService rateLimitService,
            IConfiguration configuration,
            ILogger<RateLimitingMiddleware> logger)
        {
            _next = next;
            _rateLimitService = rateLimitService;
            _configuration = configuration;
            _logger = logger;
        }

        public async Task InvokeAsync(HttpContext context)
        {
            // Get client identifier (IP address or user ID from JWT)
            var clientId = GetClientIdentifier(context);
            var endpoint = context.Request.Path.Value ?? "";
            
            // Get rate limit configuration for this endpoint
            var (limit, window) = GetRateLimitConfig(endpoint);
            
            if (limit > 0) // Rate limiting is enabled for this endpoint
            {
                var rateLimitInfo = await _rateLimitService.GetRateLimitInfoAsync(clientId, endpoint, limit, window);
                
                // Add rate limit headers (only if response hasn't started)
                if (!context.Response.HasStarted)
                {
                    context.Response.Headers["X-RateLimit-Limit"] = limit.ToString();
                    context.Response.Headers["X-RateLimit-Remaining"] = rateLimitInfo.RemainingRequests.ToString();
                    context.Response.Headers["X-RateLimit-Reset"] = ((DateTimeOffset)rateLimitInfo.ResetTime).ToUnixTimeSeconds().ToString();
                }
                
                if (rateLimitInfo.IsExceeded)
                {
                    context.Response.StatusCode = 429; // Too Many Requests
                    
                    if (!context.Response.HasStarted)
                    {
                        context.Response.Headers["Retry-After"] = window.TotalSeconds.ToString();
                    }
                    
                    await context.Response.WriteAsync("Rate limit exceeded. Please try again later.");
                    return;
                }
            }

            await _next(context);
        }

        private string GetClientIdentifier(HttpContext context)
        {
            // Try to get user ID from JWT claims first
            var userIdClaim = context.User.FindFirst("sub") ?? context.User.FindFirst("userId");
            if (userIdClaim != null)
                return $"user:{userIdClaim.Value}";

            // Fall back to IP address
            var ipAddress = context.Connection.RemoteIpAddress?.ToString() ?? "unknown";
            return $"ip:{ipAddress}";
        }

        private (int limit, TimeSpan window) GetRateLimitConfig(string endpoint)
        {
            // Default rate limits
            var defaultLimit = _configuration.GetValue<int>("RateLimit:DefaultLimit", 100);
            var defaultWindowMinutes = _configuration.GetValue<int>("RateLimit:DefaultWindowMinutes", 1);

            // Check for endpoint-specific configuration
            var routeConfigs = _configuration.GetSection("Routes").GetChildren();
            
            foreach (var routeConfig in routeConfigs)
            {
                var path = routeConfig["Path"];
                if (!string.IsNullOrEmpty(path) && endpoint.StartsWith(path))
                {
                    var limit = routeConfig.GetValue<int>("RateLimitPerMinute", defaultLimit);
                    return (limit, TimeSpan.FromMinutes(1));
                }
            }

            return (defaultLimit, TimeSpan.FromMinutes(defaultWindowMinutes));
        }
    }
}