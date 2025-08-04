using System.Diagnostics;

namespace UrlShortener.ApiGateway.Middleware
{
    public class RequestLoggingMiddleware
    {
        private readonly RequestDelegate _next;
        private readonly ILogger<RequestLoggingMiddleware> _logger;

        public RequestLoggingMiddleware(RequestDelegate next, ILogger<RequestLoggingMiddleware> logger)
        {
            _next = next;
            _logger = logger;
        }

        public async Task InvokeAsync(HttpContext context)
        {
            var stopwatch = Stopwatch.StartNew();
            var requestId = Guid.NewGuid().ToString();
            
            // Add request ID to response headers (only if response hasn't started)
            if (!context.Response.HasStarted)
            {
                context.Response.Headers["X-Request-ID"] = requestId;
            }
            
            // Log request
            _logger.LogInformation("Request {RequestId}: {Method} {Path} from {RemoteIpAddress}",
                requestId,
                context.Request.Method,
                context.Request.Path + context.Request.QueryString,
                context.Connection.RemoteIpAddress);

            try
            {
                await _next(context);
            }
            finally
            {
                stopwatch.Stop();
                
                // Log response
                _logger.LogInformation("Response {RequestId}: {StatusCode} in {ElapsedMilliseconds}ms",
                    requestId,
                    context.Response.StatusCode,
                    stopwatch.ElapsedMilliseconds);
            }
        }
    }
}