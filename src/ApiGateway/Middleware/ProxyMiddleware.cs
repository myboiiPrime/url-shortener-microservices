using System.Text;
using UrlShortener.ApiGateway.Services;

namespace UrlShortener.ApiGateway.Middleware
{
    public class ProxyMiddleware
    {
        private readonly RequestDelegate _next;
        private readonly ILoadBalancer _loadBalancer;
        private readonly IHttpClientFactory _httpClientFactory;
        private readonly IConfiguration _configuration;
        private readonly ILogger<ProxyMiddleware> _logger;

        public ProxyMiddleware(
            RequestDelegate next,
            ILoadBalancer loadBalancer,
            IHttpClientFactory httpClientFactory,
            IConfiguration configuration,
            ILogger<ProxyMiddleware> logger)
        {
            _next = next;
            _loadBalancer = loadBalancer;
            _httpClientFactory = httpClientFactory;
            _configuration = configuration;
            _logger = logger;
        }

        public async Task InvokeAsync(HttpContext context)
        {
            var path = context.Request.Path.Value ?? "";
            
            // Find matching route configuration
            var routeConfig = FindRouteConfig(path);
            
            if (routeConfig.HasValue)
            {
                await ProxyRequestAsync(context, routeConfig.Value);
                return;
            }

            // If no route found, continue to next middleware
            await _next(context);
        }

        private async Task ProxyRequestAsync(HttpContext context, (string serviceName, string targetPath) routeConfig)
        {
            try
            {
                // Get service endpoint
                var endpoint = await _loadBalancer.SelectEndpointAsync(routeConfig.serviceName);
                if (endpoint == null)
                {
                    context.Response.StatusCode = 503; // Service Unavailable
                    await context.Response.WriteAsync($"Service '{routeConfig.serviceName}' is not available");
                    return;
                }

                // Create HTTP client with optimized timeout
                using var httpClient = _httpClientFactory.CreateClient();
                httpClient.Timeout = TimeSpan.FromSeconds(10);

                // Build target URL
                var targetUrl = $"{endpoint.BaseUrl}{routeConfig.targetPath}{context.Request.QueryString}";

                // Create request message
                var requestMessage = new HttpRequestMessage(
                    new HttpMethod(context.Request.Method),
                    targetUrl);

                // Copy headers (excluding some that shouldn't be forwarded)
                var excludedHeaders = new[] { "host", "connection", "transfer-encoding" };
                foreach (var header in context.Request.Headers)
                {
                    if (!excludedHeaders.Contains(header.Key.ToLower()))
                    {
                        requestMessage.Headers.TryAddWithoutValidation(header.Key, header.Value.ToArray());
                    }
                }

                // Copy request body for POST/PUT requests using streaming
                if (context.Request.ContentLength > 0 || context.Request.Method == "POST" || context.Request.Method == "PUT")
                {
                    requestMessage.Content = new StreamContent(context.Request.Body);
                    
                    if (!string.IsNullOrEmpty(context.Request.ContentType))
                    {
                        requestMessage.Content.Headers.TryAddWithoutValidation("Content-Type", context.Request.ContentType);
                    }
                }

                _logger.LogInformation("Proxying request to {TargetUrl}", targetUrl);

                // Send request
                var response = await httpClient.SendAsync(requestMessage);

                // Copy response status
                context.Response.StatusCode = (int)response.StatusCode;

                // Copy response headers (excluding problematic ones that can cause chunked encoding issues)
                var excludedResponseHeaders = new[] { "transfer-encoding", "content-length", "connection" };
                
                foreach (var header in response.Headers)
                {
                    if (!excludedResponseHeaders.Contains(header.Key.ToLower()))
                    {
                        context.Response.Headers[header.Key] = header.Value.ToArray();
                    }
                }

                foreach (var header in response.Content.Headers)
                {
                    if (!excludedResponseHeaders.Contains(header.Key.ToLower()))
                    {
                        context.Response.Headers[header.Key] = header.Value.ToArray();
                    }
                }

                // Copy response body using streaming
                await response.Content.CopyToAsync(context.Response.Body);

                _logger.LogInformation("Proxied request to {TargetUrl} - Status: {StatusCode}", 
                    targetUrl, response.StatusCode);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error proxying request to service {ServiceName}", routeConfig.serviceName);
                
                if (!context.Response.HasStarted)
                {
                    context.Response.StatusCode = 500;
                    await context.Response.WriteAsync("Internal server error while proxying request");
                }
            }
        }

        private (string serviceName, string targetPath)? FindRouteConfig(string path)
        {
            var routeConfigs = _configuration.GetSection("Routes").GetChildren();
            
            foreach (var routeConfig in routeConfigs)
            {
                var routePath = routeConfig["Path"];
                var serviceName = routeConfig["ServiceName"];
                var targetPath = routeConfig["TargetPath"];
                
                if (!string.IsNullOrEmpty(routePath) && 
                    !string.IsNullOrEmpty(serviceName) && 
                    path.StartsWith(routePath))
                {
                    // Replace the route path with target path
                    var modifiedPath = path.Replace(routePath, targetPath ?? routePath);
                    return (serviceName, modifiedPath);
                }
            }

            return null;
        }
    }
}