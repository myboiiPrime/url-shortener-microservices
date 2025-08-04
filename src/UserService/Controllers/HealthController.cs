using Microsoft.AspNetCore.Mvc;

namespace UrlShortener.UserService.Controllers
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
                service = "UserService",
                timestamp = DateTime.UtcNow,
                version = "1.0.0"
            });
        }
    }
}