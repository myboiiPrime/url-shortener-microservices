using Microsoft.EntityFrameworkCore;
using UrlShortener.Shared.Models;

namespace UrlShortener.UrlShorteningService.Data
{
    public class UrlShorteningDbContext : DbContext
    {
        public UrlShorteningDbContext(DbContextOptions<UrlShorteningDbContext> options)
            : base(options)
        {
        }

        public DbSet<UrlMapping> UrlMappings { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<UrlMapping>(entity =>
            {
                entity.HasKey(e => e.ShortCode);
                entity.HasIndex(e => e.ShortCode).IsUnique();
                entity.HasIndex(e => e.UserId);
                entity.HasIndex(e => e.CreatedAt);
                
                entity.Property(e => e.ShortCode).HasMaxLength(10);
                entity.Property(e => e.OriginalUrl).HasMaxLength(2048);
                entity.Property(e => e.UserId).HasMaxLength(50);
                entity.Property(e => e.Title).HasMaxLength(200);
                entity.Property(e => e.Description).HasMaxLength(500);
            });
        }
    }
}