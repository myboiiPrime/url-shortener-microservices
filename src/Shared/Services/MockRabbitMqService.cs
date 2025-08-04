using Microsoft.Extensions.Logging;
using System.Text.Json;

namespace UrlShortener.Shared.Services
{
    public class MockRabbitMqService : IRabbitMqService
    {
        private readonly ILogger<MockRabbitMqService> _logger;
        private readonly Dictionary<string, List<Func<object, Task<bool>>>> _subscribers;

        public MockRabbitMqService(ILogger<MockRabbitMqService> logger)
        {
            _logger = logger;
            _subscribers = new Dictionary<string, List<Func<object, Task<bool>>>>();
            _logger.LogInformation("MockRabbitMqService initialized - messages will be logged instead of queued");
        }

        public void PublishMessage<T>(string queueName, T message)
        {
            try
            {
                if (message == null)
                {
                    _logger.LogWarning($"[MOCK] Attempted to publish null message to queue '{queueName}'");
                    return;
                }

                var json = JsonSerializer.Serialize(message);
                _logger.LogInformation($"[MOCK] Published message to queue '{queueName}': {json}");

                // Simulate immediate processing for subscribers
                if (_subscribers.ContainsKey(queueName))
                {
                    foreach (var handler in _subscribers[queueName])
                    {
                        Task.Run(async () =>
                        {
                            try
                            {
                                await handler(message);
                                _logger.LogInformation($"[MOCK] Message processed by subscriber for queue '{queueName}'");
                            }
                            catch (Exception ex)
                            {
                                _logger.LogError(ex, $"[MOCK] Error processing message in queue '{queueName}'");
                            }
                        });
                    }
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"[MOCK] Failed to publish message to queue '{queueName}'");
            }
        }

        public void Subscribe<T>(string queueName, Func<T, Task<bool>> messageHandler)
        {
            try
            {
                if (!_subscribers.ContainsKey(queueName))
                {
                    _subscribers[queueName] = new List<Func<object, Task<bool>>>();
                }

                // Wrap the typed handler to work with object
                _subscribers[queueName].Add(async (obj) =>
                {
                    if (obj is T typedMessage)
                    {
                        return await messageHandler(typedMessage);
                    }
                    _logger.LogWarning($"[MOCK] Received message of wrong type for queue '{queueName}'");
                    return false;
                });

                _logger.LogInformation($"[MOCK] Subscribed to queue '{queueName}' - messages will be processed immediately when published");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"[MOCK] Failed to subscribe to queue '{queueName}'");
            }
        }

        public void Close()
        {
            _logger.LogInformation("[MOCK] MockRabbitMqService closed");
            _subscribers.Clear();
        }
    }
}