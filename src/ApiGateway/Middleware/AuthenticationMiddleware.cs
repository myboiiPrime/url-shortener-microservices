namespace UrlShortener.ApiGateway.Middleware
{
    public class AuthenticationMiddleware
    {
        private readonly RequestDelegate _next;
        private readonly IConfiguration _configuration;
        private readonly ILogger<AuthenticationMiddleware> _logger;

        public AuthenticationMiddleware(
            RequestDelegate next,
            IConfiguration configuration,
            ILogger<AuthenticationMiddleware> logger)
        {
            _next = next;
            _configuration = configuration;
            _logger = logger;
        }

        public async Task InvokeAsync(HttpContext context)
        {
            var path = context.Request.Path.Value ?? "";
            
            // Check if this endpoint requires authentication
            if (RequiresAuthentication(path))
            {
                var authHeader = context.Request.Headers["Authorization"].FirstOrDefault();
                
                if (string.IsNullOrEmpty(authHeader) || !authHeader.StartsWith("Bearer "))
                {
                    context.Response.StatusCode = 401;
                    await context.Response.WriteAsync("Unauthorized: Missing or invalid authorization header");
                    return;
                }

                // The JWT validation is handled by the built-in JWT middleware
                // This middleware just checks if authentication is required
            }

            await _next(context);
        }

        private bool RequiresAuthentication(string path)
        {
            // Public endpoints that don't require authentication
            var publicEndpoints = new[]
            {
                "/api/auth/register",
                "/api/auth/login",
                "/health",
                "/api/gateway/health",
                "/api/gateway/services",     // Add this for testing
                "/api/gateway/routes",       // Add this for testing
                "/api/gateway/load-balancer", // Add this for testing
                "/swagger",
                "/api/url/redirect", // URL redirection should be public
                "/r/" // Short URL redirection
            };

            // Check if this is a public endpoint
            if (publicEndpoints.Any(endpoint => path.StartsWith(endpoint, StringComparison.OrdinalIgnoreCase)))
                return false;

            // Check route configuration
            var routeConfigs = _configuration.GetSection("Routes").GetChildren();
            
            foreach (var routeConfig in routeConfigs)
            {
                var routePath = routeConfig["Path"];
                if (!string.IsNullOrEmpty(routePath) && path.StartsWith(routePath))
                {
                    return routeConfig.GetValue<bool>("RequireAuth", true);
                }
            }

            // Default to requiring authentication
            return true;
        }
    }
}