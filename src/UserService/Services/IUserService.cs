using UrlShortener.UserService.Models;

namespace UrlShortener.UserService.Services
{
    public interface IUserService
    {
        Task<AuthResponse> RegisterAsync(RegisterRequest request);
        Task<AuthResponse> LoginAsync(LoginRequest request);
        Task<UserDto?> GetUserByIdAsync(string userId);
        Task<UserDto?> GetUserByUsernameAsync(string username);
        Task<UserDto?> GetUserByEmailAsync(string email);
        Task<bool> UpdateUserAsync(string userId, UserDto userDto);
        Task<bool> DeleteUserAsync(string userId);
        Task<bool> VerifyEmailAsync(string userId);
        Task<bool> ChangePasswordAsync(string userId, string currentPassword, string newPassword);
        Task<IEnumerable<UserDto>> GetUsersAsync(int page = 1, int pageSize = 10);
    }
}