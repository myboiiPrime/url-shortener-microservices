using Microsoft.AspNetCore.Mvc;
using UrlShortener.Shared.Models;
using UrlShortener.Shared.Services;
using UrlShortener.UrlShorteningService.Services;

namespace UrlShortener.UrlShorteningService.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class UrlController : ControllerBase
    {
        private readonly IUrlShorteningService _urlShorteningService;
        private readonly IRabbitMqService _rabbitMq;
        private readonly ILogger<UrlController> _logger;

        public UrlController(
            IUrlShorteningService urlShorteningService,
            IRabbitMqService rabbitMq,
            ILogger<UrlController> logger)
        {
            _urlShorteningService = urlShorteningService;
            _rabbitMq = rabbitMq;
            _logger = logger;
        }

        [HttpPost("shorten")]
        public async Task<IActionResult> ShortenUrl([FromBody] UrlShortenRequest request)
        {
            try
            {
                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                // For synchronous processing (immediate response)
                var result = await _urlShorteningService.ShortenUrlAsync(request);
                
                return Ok(new
                {
                    ShortCode = result.ShortCode,
                    OriginalUrl = result.OriginalUrl,
                    ShortUrl = $"{Request.Scheme}://{Request.Host}/s/{result.ShortCode}",
                    CreatedAt = result.CreatedAt,
                    ExpiresAt = result.ExpiresAt
                });
            }
            catch (InvalidOperationException ex)
            {
                return Conflict(new { Error = ex.Message });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to shorten URL");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpPost("shorten-async")]
        public IActionResult ShortenUrlAsync([FromBody] UrlShortenRequest request)
        {
            try
            {
                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                // For asynchronous processing via RabbitMQ
                _rabbitMq.PublishMessage("url_shorten_requests", request);
                
                return Accepted(new
                {
                    RequestId = request.RequestId,
                    Message = "URL shortening request has been queued for processing",
                    StatusUrl = $"{Request.Scheme}://{Request.Host}/api/url/status/{request.RequestId}"
                });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to queue URL shortening request");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("{shortCode}")]
        public async Task<IActionResult> GetUrl(string shortCode)
        {
            try
            {
                var mapping = await _urlShorteningService.GetUrlMappingAsync(shortCode);
                
                if (mapping == null)
                {
                    return NotFound(new { Error = "Short URL not found or expired" });
                }

                return Ok(new
                {
                    ShortCode = mapping.ShortCode,
                    OriginalUrl = mapping.OriginalUrl,
                    Title = mapping.Title,
                    Description = mapping.Description,
                    ClickCount = mapping.ClickCount,
                    CreatedAt = mapping.CreatedAt,
                    ExpiresAt = mapping.ExpiresAt
                });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get URL for short code: {shortCode}");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("redirect/{shortCode}")]
        public async Task<IActionResult> RedirectUrl(string shortCode)
        {
            try
            {
                var mapping = await _urlShorteningService.GetUrlMappingAsync(shortCode);
                
                if (mapping == null)
                {
                    return NotFound("Short URL not found or expired");
                }

                // Publish click event for analytics
                var clickEvent = new ClickEvent
                {
                    ShortCode = shortCode,
                    OriginalUrl = mapping.OriginalUrl,
                    IpAddress = HttpContext.Connection.RemoteIpAddress?.ToString(),
                    UserAgent = Request.Headers["User-Agent"].ToString(),
                    Referrer = Request.Headers["Referer"].ToString()
                };

                _rabbitMq.PublishMessage("click_events", clickEvent);

                return Redirect(mapping.OriginalUrl);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to redirect for short code: {shortCode}");
                return StatusCode(500, "Internal server error");
            }
        }

        [HttpDelete("{shortCode}")]
        public async Task<IActionResult> DeleteUrl(string shortCode, [FromQuery] string? userId = null)
        {
            try
            {
                var success = await _urlShorteningService.DeleteUrlMappingAsync(shortCode, userId);
                
                if (!success)
                {
                    return NotFound(new { Error = "Short URL not found or access denied" });
                }

                return Ok(new { Message = "Short URL deleted successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to delete URL: {shortCode}");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("user/{userId}")]
        public async Task<IActionResult> GetUserUrls(string userId, [FromQuery] int page = 1, [FromQuery] int pageSize = 10)
        {
            try
            {
                var urls = await _urlShorteningService.GetUserUrlsAsync(userId, page, pageSize);
                
                return Ok(new
                {
                    Page = page,
                    PageSize = pageSize,
                    Urls = urls.Select(u => new
                    {
                        ShortCode = u.ShortCode,
                        OriginalUrl = u.OriginalUrl,
                        Title = u.Title,
                        ClickCount = u.ClickCount,
                        CreatedAt = u.CreatedAt,
                        ExpiresAt = u.ExpiresAt,
                        ShortUrl = $"{Request.Scheme}://{Request.Host}/s/{u.ShortCode}"
                    })
                });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get URLs for user: {userId}");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("health")]
        public IActionResult Health()
        {
            return Ok(new
            {
                Service = "URL Shortening Service",
                Status = "Healthy",
                Timestamp = DateTime.UtcNow
            });
        }
    }
}