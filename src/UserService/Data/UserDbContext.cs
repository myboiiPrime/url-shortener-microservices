using Microsoft.EntityFrameworkCore;
using UrlShortener.UserService.Models;

namespace UrlShortener.UserService.Data
{
    public class UserDbContext : DbContext
    {
        public UserDbContext(DbContextOptions<UserDbContext> options) : base(options)
        {
        }

        public DbSet<User> Users { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<User>(entity =>
            {
                entity.HasKey(e => e.Id);
                entity.HasIndex(e => e.Username).IsUnique();
                entity.HasIndex(e => e.Email).IsUnique();
                entity.HasIndex(e => e.CreatedAt);
                
                entity.Property(e => e.Username).HasMaxLength(100);
                entity.Property(e => e.Email).HasMaxLength(255);
                entity.Property(e => e.PasswordHash).IsRequired();
            });
        }
    }
}