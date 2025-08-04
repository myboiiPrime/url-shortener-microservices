using Microsoft.AspNetCore.Mvc;

namespace UrlShortener.UrlShorteningService.Controllers
{
    [ApiController]
    [Route("[controller]")]
    public class HealthController : ControllerBase
    {
        [HttpGet]
        public IActionResult Get()
        {
            return Ok(new 
            { 
                status = "healthy", 
                service = "UrlShorteningService",
                timestamp = DateTime.UtcNow,
                version = "1.0.0"
            });
        }
    }
}