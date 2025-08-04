using Microsoft.EntityFrameworkCore;
using UrlShortener.UserService.Data;
using UrlShortener.UserService.Models;

namespace UrlShortener.UserService.Services
{
    public class UserService : IUserService
    {
        private readonly UserDbContext _context;
        private readonly IPasswordHashService _passwordHashService;
        private readonly IJwtTokenService _jwtTokenService;
        private readonly ILogger<UserService> _logger;

        public UserService(
            UserDbContext context,
            IPasswordHashService passwordHashService,
            IJwtTokenService jwtTokenService,
            ILogger<UserService> logger)
        {
            _context = context;
            _passwordHashService = passwordHashService;
            _jwtTokenService = jwtTokenService;
            _logger = logger;
        }

        public async Task<AuthResponse> RegisterAsync(RegisterRequest request)
        {
            try
            {
                // Check if username already exists
                var existingUsername = await _context.Users
                    .FirstOrDefaultAsync(u => u.Username == request.Username);
                if (existingUsername != null)
                {
                    throw new InvalidOperationException("Username already exists");
                }

                // Check if email already exists
                var existingEmail = await _context.Users
                    .FirstOrDefaultAsync(u => u.Email == request.Email);
                if (existingEmail != null)
                {
                    throw new InvalidOperationException("Email already exists");
                }

                // Create new user
                var user = new User
                {
                    Username = request.Username,
                    Email = request.Email,
                    PasswordHash = _passwordHashService.HashPassword(request.Password),
                    CreatedAt = DateTime.UtcNow
                };

                _context.Users.Add(user);
                await _context.SaveChangesAsync();

                // Generate tokens
                var token = _jwtTokenService.GenerateToken(user.Id, user.Username, user.Email);
                var refreshToken = _jwtTokenService.GenerateRefreshToken();

                _logger.LogInformation($"User registered successfully: {user.Username}");

                return new AuthResponse
                {
                    Token = token,
                    RefreshToken = refreshToken,
                    ExpiresAt = DateTime.UtcNow.AddMinutes(60),
                    User = MapToUserDto(user)
                };
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to register user: {request.Username}");
                throw;
            }
        }

        public async Task<AuthResponse> LoginAsync(LoginRequest request)
        {
            try
            {
                // Find user by username or email
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => 
                        u.Username == request.UsernameOrEmail || 
                        u.Email == request.UsernameOrEmail);

                if (user == null || !user.IsActive)
                {
                    throw new UnauthorizedAccessException("Invalid credentials");
                }

                // Verify password
                if (!_passwordHashService.VerifyPassword(request.Password, user.PasswordHash))
                {
                    throw new UnauthorizedAccessException("Invalid credentials");
                }

                // Update last login
                user.LastLoginAt = DateTime.UtcNow;
                await _context.SaveChangesAsync();

                // Generate tokens
                var token = _jwtTokenService.GenerateToken(user.Id, user.Username, user.Email);
                var refreshToken = _jwtTokenService.GenerateRefreshToken();

                _logger.LogInformation($"User logged in successfully: {user.Username}");

                return new AuthResponse
                {
                    Token = token,
                    RefreshToken = refreshToken,
                    ExpiresAt = DateTime.UtcNow.AddMinutes(60),
                    User = MapToUserDto(user)
                };
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to login user: {request.UsernameOrEmail}");
                throw;
            }
        }

        public async Task<UserDto?> GetUserByIdAsync(string userId)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Id == userId && u.IsActive);

                return user != null ? MapToUserDto(user) : null;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get user by ID: {userId}");
                throw;
            }
        }

        public async Task<UserDto?> GetUserByUsernameAsync(string username)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Username == username && u.IsActive);

                return user != null ? MapToUserDto(user) : null;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get user by username: {username}");
                throw;
            }
        }

        public async Task<UserDto?> GetUserByEmailAsync(string email)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Email == email && u.IsActive);

                return user != null ? MapToUserDto(user) : null;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to get user by email: {email}");
                throw;
            }
        }

        public async Task<bool> UpdateUserAsync(string userId, UserDto userDto)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Id == userId && u.IsActive);

                if (user == null)
                    return false;

                // Update allowed fields
                user.DailyUrlLimit = userDto.DailyUrlLimit;
                user.MonthlyUrlLimit = userDto.MonthlyUrlLimit;

                await _context.SaveChangesAsync();
                _logger.LogInformation($"User updated successfully: {userId}");
                return true;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to update user: {userId}");
                throw;
            }
        }

        public async Task<bool> DeleteUserAsync(string userId)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Id == userId);

                if (user == null)
                    return false;

                user.IsActive = false;
                await _context.SaveChangesAsync();

                _logger.LogInformation($"User deleted successfully: {userId}");
                return true;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to delete user: {userId}");
                throw;
            }
        }

        public async Task<bool> VerifyEmailAsync(string userId)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Id == userId && u.IsActive);

                if (user == null)
                    return false;

                user.IsEmailVerified = true;
                await _context.SaveChangesAsync();

                _logger.LogInformation($"Email verified for user: {userId}");
                return true;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to verify email for user: {userId}");
                throw;
            }
        }

        public async Task<bool> ChangePasswordAsync(string userId, string currentPassword, string newPassword)
        {
            try
            {
                var user = await _context.Users
                    .FirstOrDefaultAsync(u => u.Id == userId && u.IsActive);

                if (user == null)
                    return false;

                // Verify current password
                if (!_passwordHashService.VerifyPassword(currentPassword, user.PasswordHash))
                    return false;

                // Update password
                user.PasswordHash = _passwordHashService.HashPassword(newPassword);
                await _context.SaveChangesAsync();

                _logger.LogInformation($"Password changed for user: {userId}");
                return true;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, $"Failed to change password for user: {userId}");
                throw;
            }
        }

        public async Task<IEnumerable<UserDto>> GetUsersAsync(int page = 1, int pageSize = 10)
        {
            try
            {
                var users = await _context.Users
                    .Where(u => u.IsActive)
                    .OrderByDescending(u => u.CreatedAt)
                    .Skip((page - 1) * pageSize)
                    .Take(pageSize)
                    .ToListAsync();

                return users.Select(MapToUserDto);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to get users");
                throw;
            }
        }

        private static UserDto MapToUserDto(User user)
        {
            return new UserDto
            {
                Id = user.Id,
                Username = user.Username,
                Email = user.Email,
                CreatedAt = user.CreatedAt,
                LastLoginAt = user.LastLoginAt,
                IsEmailVerified = user.IsEmailVerified,
                DailyUrlLimit = user.DailyUrlLimit,
                MonthlyUrlLimit = user.MonthlyUrlLimit,
                TotalUrlsCreated = user.TotalUrlsCreated,
                TotalClicks = user.TotalClicks
            };
        }
    }
}