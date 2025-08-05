using Microsoft.AspNetCore.Mvc;

using UrlShortener.AnalyticsService.Services;
using UrlShortener.Shared.Models;

namespace UrlShortener.AnalyticsService.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class AnalyticsController : ControllerBase
    {
        private readonly IAnalyticsService _analyticsService;
        private readonly IReportingService _reportingService;
        private readonly ILogger<AnalyticsController> _logger;

        public AnalyticsController(
            IAnalyticsService analyticsService,
            IReportingService reportingService,
            ILogger<AnalyticsController> logger)
        {
            _analyticsService = analyticsService;
            _reportingService = reportingService;
            _logger = logger;
        }

        [HttpPost("click")]
        public async Task<IActionResult> RecordClick([FromBody] ClickEvent clickEvent)
        {
            try
            {
                if (!ModelState.IsValid)
                    return BadRequest(ModelState);

                await _analyticsService.RecordClickAsync(clickEvent);
                return Ok(new { message = "Click recorded successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error recording click for short code: {ShortCode}", clickEvent.ShortCode);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("url/{shortCode}")]
        public async Task<IActionResult> GetUrlAnalytics(
            string shortCode,
            [FromQuery] DateTime? startDate = null,
            [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var analytics = await _analyticsService.GetUrlAnalyticsAsync(shortCode, startDate, endDate);
                if (analytics == null)
                    return NotFound(new { message = "Analytics not found for the specified short code" });

                return Ok(analytics);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting analytics for short code: {ShortCode}", shortCode);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("user/{userId}")]
        public async Task<IActionResult> GetUserAnalytics(
            Guid userId,
            [FromQuery] DateTime? startDate = null,
            [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var analytics = await _analyticsService.GetUserAnalyticsAsync(userId, startDate, endDate);
                return Ok(analytics);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting user analytics for user: {UserId}", userId);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("dashboard")]
        public async Task<IActionResult> GetDashboardStats([FromQuery] Guid? userId = null)
        {
            try
            {
                var stats = await _analyticsService.GetDashboardStatsAsync(userId);
                return Ok(stats);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting dashboard stats for user: {UserId}", userId);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("top-urls")]
        public async Task<IActionResult> GetTopUrls(
            [FromQuery] int count = 10,
            [FromQuery] Guid? userId = null)
        {
            try
            {
                var topUrls = await _analyticsService.GetTopUrlsAsync(count, userId);
                return Ok(topUrls);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error getting top URLs for user: {UserId}", userId);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("report/url/{shortCode}/pdf")]
        public async Task<IActionResult> GetUrlPdfReport(
            string shortCode,
            [FromQuery] DateTime? startDate = null,
            [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var report = await _reportingService.GeneratePdfReportAsync(shortCode, startDate, endDate);
                return File(report, "text/html", $"analytics-{shortCode}-{DateTime.UtcNow:yyyyMMdd}.html");
            }
            catch (ArgumentException ex)
            {
                return NotFound(new { message = ex.Message });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating PDF report for short code: {ShortCode}", shortCode);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("report/url/{shortCode}/csv")]
        public async Task<IActionResult> GetUrlCsvReport(
            string shortCode,
            [FromQuery] DateTime? startDate = null,
            [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var report = await _reportingService.GenerateCsvReportAsync(shortCode, startDate, endDate);
                return File(report, "text/csv", $"analytics-{shortCode}-{DateTime.UtcNow:yyyyMMdd}.csv");
            }
            catch (ArgumentException ex)
            {
                return NotFound(new { message = ex.Message });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating CSV report for short code: {ShortCode}", shortCode);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("report/user/{userId}")]
        public async Task<IActionResult> GetUserReport(
            Guid userId,
            [FromQuery] DateTime? startDate = null,
            [FromQuery] DateTime? endDate = null)
        {
            try
            {
                var report = await _reportingService.GenerateUserReportAsync(userId, startDate, endDate);
                return File(report, "text/html", $"user-analytics-{userId}-{DateTime.UtcNow:yyyyMMdd}.html");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error generating user report for user: {UserId}", userId);
                return StatusCode(500, new { message = "Internal server error" });
            }
        }

        [HttpGet("health")]
        public IActionResult Health()
        {
            return Ok(new { status = "healthy", timestamp = DateTime.UtcNow });
        }
    }
}