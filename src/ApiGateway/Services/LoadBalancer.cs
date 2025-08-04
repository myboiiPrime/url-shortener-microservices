using UrlShortener.ApiGateway.Models;

namespace UrlShortener.ApiGateway.Services
{
    public interface ILoadBalancer
    {
        Task<ServiceEndpoint?> SelectEndpointAsync(string serviceName);
    }

    public class RoundRobinLoadBalancer : ILoadBalancer
    {
        private readonly IServiceDiscovery _serviceDiscovery;
        private readonly Dictionary<string, int> _roundRobinCounters;
        private readonly object _lock = new object();

        public RoundRobinLoadBalancer(IServiceDiscovery serviceDiscovery)
        {
            _serviceDiscovery = serviceDiscovery;
            _roundRobinCounters = new Dictionary<string, int>();
        }

        public async Task<ServiceEndpoint?> SelectEndpointAsync(string serviceName)
        {
            var endpoints = await _serviceDiscovery.GetAllServiceEndpointsAsync(serviceName);
            
            if (!endpoints.Any())
                return null;

            lock (_lock)
            {
                if (!_roundRobinCounters.ContainsKey(serviceName))
                    _roundRobinCounters[serviceName] = 0;

                var index = _roundRobinCounters[serviceName] % endpoints.Count;
                _roundRobinCounters[serviceName]++;

                return endpoints[index];
            }
        }
    }

    public class WeightedRoundRobinLoadBalancer : ILoadBalancer
    {
        private readonly IServiceDiscovery _serviceDiscovery;
        private readonly Dictionary<string, int> _currentWeights;
        private readonly object _lock = new object();

        public WeightedRoundRobinLoadBalancer(IServiceDiscovery serviceDiscovery)
        {
            _serviceDiscovery = serviceDiscovery;
            _currentWeights = new Dictionary<string, int>();
        }

        public async Task<ServiceEndpoint?> SelectEndpointAsync(string serviceName)
        {
            var endpoints = await _serviceDiscovery.GetAllServiceEndpointsAsync(serviceName);
            
            if (!endpoints.Any())
                return null;

            // For simplicity, using equal weights. In production, weights could be configured
            var weights = endpoints.Select(e => 1).ToArray();
            
            lock (_lock)
            {
                var key = serviceName;
                if (!_currentWeights.ContainsKey(key))
                    _currentWeights[key] = 0;

                var totalWeight = weights.Sum();
                var selectedIndex = _currentWeights[key] % totalWeight;
                _currentWeights[key]++;

                var cumulativeWeight = 0;
                for (int i = 0; i < endpoints.Count; i++)
                {
                    cumulativeWeight += weights[i];
                    if (selectedIndex < cumulativeWeight)
                        return endpoints[i];
                }

                return endpoints.First();
            }
        }
    }
}