using Microsoft.AspNetCore.Mvc;
using UrlShortener.ApiGateway.Services;

namespace UrlShortener.ApiGateway.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class GatewayController : ControllerBase
    {
        private readonly IServiceDiscovery _serviceDiscovery;
        private readonly ILoadBalancer _loadBalancer;
        private readonly ILogger<GatewayController> _logger;

        public GatewayController(
            IServiceDiscovery serviceDiscovery,
            ILoadBalancer loadBalancer,
            ILogger<GatewayController> logger)
        {
            _serviceDiscovery = serviceDiscovery;
            _loadBalancer = loadBalancer;
            _logger = logger;
        }

        [HttpGet("health")]
        public IActionResult Health()
        {
            return Ok(new 
            { 
                status = "healthy", 
                timestamp = DateTime.UtcNow,
                version = "1.0.0"
            });
        }

        [HttpGet("services")]
        public async Task<IActionResult> GetServices()
        {
            try
            {
                var services = new Dictionary<string, object>();
                
                // Get all configured services
                var serviceNames = new[] { "UserService", "UrlShorteningService", "AnalyticsService" };
                
                foreach (var serviceName in serviceNames)
                {
                    var endpoints = await _serviceDiscovery.GetAllServiceEndpointsAsync(serviceName);
                    services[serviceName] = endpoints.Select(e => new
                    {
                        e.BaseUrl,
                        e.IsHealthy,
                        e.LastHealthCheck
                    });
                }

                return Ok(services);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting services status");
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("routes")]
        public IActionResult GetRoutes()
        {
            try
            {
                var routes = HttpContext.RequestServices
                    .GetRequiredService<IConfiguration>()
                    .GetSection("Routes")
                    .GetChildren()
                    .Select(r => new
                    {
                        Path = r["Path"],
                        ServiceName = r["ServiceName"],
                        TargetPath = r["TargetPath"],
                        RequireAuth = r.GetValue<bool>("RequireAuth"),
                        RateLimitPerMinute = r.GetValue<int>("RateLimitPerMinute")
                    });

                return Ok(routes);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting routes configuration");
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpPost("services/{serviceName}/health-check")]
        public async Task<IActionResult> HealthCheckService(string serviceName)
        {
            try
            {
                var endpoints = await _serviceDiscovery.GetAllServiceEndpointsAsync(serviceName);
                var healthResults = new List<object>();

                foreach (var endpoint in endpoints)
                {
                    var isHealthy = await _serviceDiscovery.HealthCheckAsync(endpoint);
                    healthResults.Add(new
                    {
                        endpoint.BaseUrl,
                        IsHealthy = isHealthy,
                        CheckedAt = DateTime.UtcNow
                    });
                }

                return Ok(new
                {
                    ServiceName = serviceName,
                    Endpoints = healthResults
                });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error performing health check for service: {ServiceName}", serviceName);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("load-balancer/next/{serviceName}")]
        public async Task<IActionResult> GetNextEndpoint(string serviceName)
        {
            try
            {
                var endpoint = await _loadBalancer.SelectEndpointAsync(serviceName);
                
                if (endpoint == null)
                    return NotFound(new { message = $"No healthy endpoints found for service: {serviceName}" });

                return Ok(new
                {
                    ServiceName = serviceName,
                    SelectedEndpoint = new
                    {
                        endpoint.BaseUrl,
                        endpoint.IsHealthy,
                        endpoint.LastHealthCheck
                    }
                });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error selecting endpoint for service: {ServiceName}", serviceName);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }
    }
}