using RabbitMQ.Client;
using RabbitMQ.Client.Events;
using System.Text;
using System.Text.Json;
using Microsoft.Extensions.Logging;

namespace UrlShortener.Shared.Services
{
    public interface IRabbitMqService
    {
        void PublishMessage<T>(string queueName, T message);
        void Subscribe<T>(string queueName, Func<T, Task<bool>> messageHandler);
        void Close();
    }

    public class RabbitMqService : IRabbitMqService, IDisposable
    {
        private readonly IConnection _connection;
        private readonly IModel _channel;
        private readonly ILogger<RabbitMqService> _logger;

        public RabbitMqService(string hostName, ILogger<RabbitMqService> logger)
        {
            _logger = logger;
            
            var factory = new ConnectionFactory() 
            { 
                HostName = hostName,
                AutomaticRecoveryEnabled = true,
                NetworkRecoveryInterval = TimeSpan.FromSeconds(10)
            };
            
            _connection = factory.CreateConnection();
            _channel = _connection.CreateModel();
        }

        public void PublishMessage<T>(string queueName, T message)
        {
            try
            {
                DeclareQueue(queueName);
                
                var json = JsonSerializer.Serialize(message);
                var body = Encoding.UTF8.GetBytes(json);

                var properties = _channel.CreateBasicProperties();
                properties.Persistent = true;
                properties.MessageId = Guid.NewGuid().ToString();
                properties.Timestamp = new AmqpTimestamp(DateTimeOffset.UtcNow.ToUnixTimeSeconds());

                _channel.BasicPublish(
                    exchange: "",
                    routingKey: queueName,
                    basicProperties: properties,
                    body: body);

                _logger.LogInformation($"Published message to queue {queueName}: {json}");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to publish message to queue {queueName}");
                throw;
            }
        }

        public void Subscribe<T>(string queueName, Func<T, Task<bool>> messageHandler)
        {
            try
            {
                DeclareQueue(queueName);
                
                _channel.BasicQos(prefetchSize: 0, prefetchCount: 1, global: false);

                var consumer = new EventingBasicConsumer(_channel);
                
                consumer.Received += async (model, ea) =>
                {
                    var body = ea.Body.ToArray();
                    var json = Encoding.UTF8.GetString(body);
                    
                    try
                    {
                        var message = JsonSerializer.Deserialize<T>(json);
                        var success = await messageHandler(message);
                        
                        if (success)
                        {
                            _channel.BasicAck(deliveryTag: ea.DeliveryTag, multiple: false);
                            _logger.LogInformation($"Successfully processed message from queue {queueName}");
                        }
                        else
                        {
                            _channel.BasicNack(deliveryTag: ea.DeliveryTag, multiple: false, requeue: true);
                            _logger.LogWarning($"Failed to process message from queue {queueName}, requeuing");
                        }
                    }
                    catch (Exception ex)
                    {
                        _logger.LogError(ex, $"Error processing message from queue {queueName}: {json}");
                        _channel.BasicNack(deliveryTag: ea.DeliveryTag, multiple: false, requeue: false);
                    }
                };

                _channel.BasicConsume(queue: queueName, autoAck: false, consumer: consumer);
                _logger.LogInformation($"Started consuming messages from queue {queueName}");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to subscribe to queue {queueName}");
                throw;
            }
        }

        private void DeclareQueue(string queueName)
        {
            _channel.QueueDeclare(
                queue: queueName,
                durable: true,
                exclusive: false,
                autoDelete: false,
                arguments: null);
        }

        public void Close()
        {
            _channel?.Close();
            _connection?.Close();
        }

        public void Dispose()
        {
            Close();
        }
    }
}