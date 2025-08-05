using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using System.ComponentModel.DataAnnotations;
using System.Security.Claims;
using UrlShortener.UserService.Models;
using UrlShortener.UserService.Services;

namespace UrlShortener.UserService.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class AuthController : ControllerBase
    {
        private readonly IUserService _userService;
        private readonly IRateLimitService _rateLimitService;
        private readonly ILogger<AuthController> _logger;

        public AuthController(
            IUserService userService,
            IRateLimitService rateLimitService,
            ILogger<AuthController> logger)
        {
            _userService = userService;
            _rateLimitService = rateLimitService;
            _logger = logger;
        }

        [HttpPost("register")]
        public async Task<IActionResult> Register([FromBody] RegisterRequest request)
        {
            try
            {
                // Rate limiting
                var clientIp = HttpContext.Connection.RemoteIpAddress?.ToString() ?? "unknown";
                var rateLimitKey = $"register:{clientIp}";
                var isAllowed = await _rateLimitService.IsAllowedAsync(rateLimitKey, 5, TimeSpan.FromMinutes(5));

                if (!isAllowed)
                {
                    return StatusCode(429, new { Error = "Too many registration attempts. Please try again later." });
                }

                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                var result = await _userService.RegisterAsync(request);
                
                return Ok(new
                {
                    Message = "User registered successfully",
                    Token = result.Token,
                    RefreshToken = result.RefreshToken,
                    ExpiresAt = result.ExpiresAt,
                    User = result.User
                });
            }
            catch (InvalidOperationException ex)
            {
                // Handle specific conflict types for better frontend error handling
                if (ex.Message.Contains("Username already exists"))
                {
                    return Conflict(new { 
                        Error = ex.Message,
                        Field = "username",
                        Type = "username_conflict"
                    });
                }
                else if (ex.Message.Contains("Email already exists"))
                {
                    return Conflict(new { 
                        Error = ex.Message,
                        Field = "email",
                        Type = "email_conflict"
                    });
                }
                else
                {
                    return Conflict(new { Error = ex.Message });
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Registration failed");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpPost("login")]
        public async Task<IActionResult> Login([FromBody] LoginRequest request)
        {
            try
            {
                // Rate limiting
                var clientIp = HttpContext.Connection.RemoteIpAddress?.ToString() ?? "unknown";
                var rateLimitKey = $"login:{clientIp}";
                var isAllowed = await _rateLimitService.IsAllowedAsync(rateLimitKey, 10, TimeSpan.FromMinutes(15));

                if (!isAllowed)
                {
                    return StatusCode(429, new { Error = "Too many login attempts. Please try again later." });
                }

                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                var result = await _userService.LoginAsync(request);
                
                return Ok(new
                {
                    Message = "Login successful",
                    Token = result.Token,
                    RefreshToken = result.RefreshToken,
                    ExpiresAt = result.ExpiresAt,
                    User = result.User
                });
            }
            catch (UnauthorizedAccessException ex)
            {
                return Unauthorized(new { Error = ex.Message });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Login failed");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("me")]
        [Authorize]
        public async Task<IActionResult> GetCurrentUser()
        {
            try
            {
                var userId = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (string.IsNullOrEmpty(userId))
                {
                    return Unauthorized(new { Error = "Invalid token" });
                }

                var user = await _userService.GetUserByIdAsync(userId);
                if (user == null)
                {
                    return NotFound(new { Error = "User not found" });
                }

                return Ok(user);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to get current user");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpPut("me")]
        [Authorize]
        public async Task<IActionResult> UpdateCurrentUser([FromBody] UserDto userDto)
        {
            try
            {
                var userId = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (string.IsNullOrEmpty(userId))
                {
                    return Unauthorized(new { Error = "Invalid token" });
                }

                var success = await _userService.UpdateUserAsync(userId, userDto);
                if (!success)
                {
                    return NotFound(new { Error = "User not found" });
                }

                return Ok(new { Message = "User updated successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to update user");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpPost("change-password")]
        [Authorize]
        public async Task<IActionResult> ChangePassword([FromBody] ChangePasswordRequest request)
        {
            try
            {
                var userId = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (string.IsNullOrEmpty(userId))
                {
                    return Unauthorized(new { Error = "Invalid token" });
                }

                if (!ModelState.IsValid)
                {
                    return BadRequest(ModelState);
                }

                var success = await _userService.ChangePasswordAsync(userId, request.CurrentPassword, request.NewPassword);
                if (!success)
                {
                    return BadRequest(new { Error = "Current password is incorrect" });
                }

                return Ok(new { Message = "Password changed successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to change password");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpPost("verify-email/{userId}")]
        public async Task<IActionResult> VerifyEmail(string userId)
        {
            try
            {
                var success = await _userService.VerifyEmailAsync(userId);
                if (!success)
                {
                    return NotFound(new { Error = "User not found" });
                }

                return Ok(new { Message = "Email verified successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to verify email for user: {userId}");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpDelete("me")]
        [Authorize]
        public async Task<IActionResult> DeleteCurrentUser()
        {
            try
            {
                var userId = User.FindFirst(ClaimTypes.NameIdentifier)?.Value;
                if (string.IsNullOrEmpty(userId))
                {
                    return Unauthorized(new { Error = "Invalid token" });
                }

                var success = await _userService.DeleteUserAsync(userId);
                if (!success)
                {
                    return NotFound(new { Error = "User not found" });
                }

                return Ok(new { Message = "User account deleted successfully" });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to delete user");
                return StatusCode(500, new { Error = "Internal server error" });
            }
        }

        [HttpGet("health")]
        public IActionResult Health()
        {
            return Ok(new
            {
                Service = "User Service",
                Status = "Healthy",
                Timestamp = DateTime.UtcNow
            });
        }
    }

    public class ChangePasswordRequest
    {
        [Required]
        public string CurrentPassword { get; set; } = string.Empty;

        [Required]
        [MinLength(6)]
        public string NewPassword { get; set; } = string.Empty;
    }
}