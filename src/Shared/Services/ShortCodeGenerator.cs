using System.Security.Cryptography;

namespace UrlShortener.Shared.Services
{
    public static class ShortCodeGenerator
    {
        private static readonly char[] chars = 
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789".ToCharArray();

        public static string Generate(int length = 6)
        {
            using var rng = RandomNumberGenerator.Create();
            var shortCode = new char[length];
            var randomBytes = new byte[length];
            
            rng.GetBytes(randomBytes);
            
            for (int i = 0; i < length; i++)
            {
                shortCode[i] = chars[randomBytes[i] % chars.Length];
            }
            
            return new string(shortCode);
        }

        public static string GenerateWithRetry(Func<string, bool> isUnique, int maxRetries = 5, int initialLength = 6)
        {
            var length = initialLength;
            
            for (int attempt = 0; attempt < maxRetries; attempt++)
            {
                var shortCode = Generate(length);
                
                if (isUnique(shortCode))
                {
                    return shortCode;
                }
                
                // Increase length after failed attempts to reduce collision probability
                if (attempt >= 2)
                {
                    length++;
                }
            }
            
            throw new InvalidOperationException($"Failed to generate unique short code after {maxRetries} attempts");
        }
    }
}