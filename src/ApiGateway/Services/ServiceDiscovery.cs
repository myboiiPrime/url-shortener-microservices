using UrlShortener.ApiGateway.Models;

namespace UrlShortener.ApiGateway.Services
{
    public interface IServiceDiscovery
    {
        Task<ServiceEndpoint?> GetServiceEndpointAsync(string serviceName);
        Task<List<ServiceEndpoint>> GetAllServiceEndpointsAsync(string serviceName);
        Task RegisterServiceAsync(ServiceEndpoint endpoint);
        Task UnregisterServiceAsync(string serviceName, string host, int port);
        Task<bool> HealthCheckAsync(ServiceEndpoint endpoint);
    }

    public class ServiceDiscovery : IServiceDiscovery
    {
        private readonly IConfiguration _configuration;
        private readonly IHttpClientFactory _httpClientFactory;
        private readonly ILogger<ServiceDiscovery> _logger;
        private readonly Dictionary<string, List<ServiceEndpoint>> _services;

        public ServiceDiscovery(
            IConfiguration configuration,
            IHttpClientFactory httpClientFactory,
            ILogger<ServiceDiscovery> logger)
        {
            _configuration = configuration;
            _httpClientFactory = httpClientFactory;
            _logger = logger;
            _services = new Dictionary<string, List<ServiceEndpoint>>();

            // Initialize with configured services
            InitializeServices();
        }

        public async Task<ServiceEndpoint?> GetServiceEndpointAsync(string serviceName)
        {
            var endpoints = await GetAllServiceEndpointsAsync(serviceName);
            return endpoints.FirstOrDefault(e => e.IsHealthy);
        }

        public async Task<List<ServiceEndpoint>> GetAllServiceEndpointsAsync(string serviceName)
        {
            if (!_services.ContainsKey(serviceName))
                return new List<ServiceEndpoint>();

            var endpoints = _services[serviceName];
            
            // Perform health checks for endpoints that haven't been checked recently
            var tasks = endpoints
                .Where(e => DateTime.UtcNow - e.LastHealthCheck > TimeSpan.FromMinutes(1))
                .Select(async e =>
                {
                    e.IsHealthy = await HealthCheckAsync(e);
                    e.LastHealthCheck = DateTime.UtcNow;
                });

            await Task.WhenAll(tasks);

            return endpoints.Where(e => e.IsHealthy).ToList();
        }

        public Task RegisterServiceAsync(ServiceEndpoint endpoint)
        {
            if (!_services.ContainsKey(endpoint.Name))
                _services[endpoint.Name] = new List<ServiceEndpoint>();

            var existingEndpoint = _services[endpoint.Name]
                .FirstOrDefault(e => e.Host == endpoint.Host && e.Port == endpoint.Port);

            if (existingEndpoint != null)
            {
                existingEndpoint.IsHealthy = endpoint.IsHealthy;
                existingEndpoint.LastHealthCheck = DateTime.UtcNow;
            }
            else
            {
                _services[endpoint.Name].Add(endpoint);
            }

            _logger.LogInformation("Registered service endpoint: {ServiceName} at {BaseUrl}", 
                endpoint.Name, endpoint.BaseUrl);

            return Task.CompletedTask;
        }

        public Task UnregisterServiceAsync(string serviceName, string host, int port)
        {
            if (_services.ContainsKey(serviceName))
            {
                _services[serviceName].RemoveAll(e => e.Host == host && e.Port == port);
                _logger.LogInformation("Unregistered service endpoint: {ServiceName} at {Host}:{Port}", 
                    serviceName, host, port);
            }

            return Task.CompletedTask;
        }

        public async Task<bool> HealthCheckAsync(ServiceEndpoint endpoint)
        {
            try
            {
                using var httpClient = _httpClientFactory.CreateClient();
                httpClient.Timeout = TimeSpan.FromSeconds(5);

                var healthCheckUrl = $"{endpoint.BaseUrl}{endpoint.HealthCheckEndpoint}";
                var response = await httpClient.GetAsync(healthCheckUrl);

                return response.IsSuccessStatusCode;
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Health check failed for {ServiceName} at {BaseUrl}", 
                    endpoint.Name, endpoint.BaseUrl);
                return false;
            }
        }

        private void InitializeServices()
        {
            var servicesConfig = _configuration.GetSection("Services");
            
            foreach (var serviceConfig in servicesConfig.GetChildren())
            {
                var serviceName = serviceConfig.Key;
                var endpoints = serviceConfig.GetSection("Endpoints").GetChildren();

                foreach (var endpointConfig in endpoints)
                {
                    var endpoint = new ServiceEndpoint
                    {
                        Name = serviceName,
                        Host = endpointConfig["Host"] ?? "localhost",
                        Port = int.Parse(endpointConfig["Port"] ?? "5000"),
                        Scheme = endpointConfig["Scheme"] ?? "http",
                        HealthCheckEndpoint = endpointConfig["HealthCheckEndpoint"] ?? "/health"
                    };

                    RegisterServiceAsync(endpoint);
                }
            }
        }
    }
}