using Microsoft.EntityFrameworkCore;

using UrlShortener.Shared.Models;

namespace UrlShortener.AnalyticsService.Data
{
    public class AnalyticsDbContext : DbContext
    {
        public AnalyticsDbContext(DbContextOptions<AnalyticsDbContext> options) : base(options)
        {
        }

        public DbSet<Shared.Models.ClickEvent> ClickEvents { get; set; }
        public DbSet<UrlStatistics> UrlStatistics { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);

            // ClickEvent configuration
            modelBuilder.Entity<Shared.Models.ClickEvent>(entity =>
            {
                entity.HasKey(e => e.Id);
                entity.HasIndex(e => e.ShortCode);
                entity.HasIndex(e => e.UserId);
                entity.HasIndex(e => e.ClickedAt);
                entity.HasIndex(e => new { e.ShortCode, e.ClickedAt });
                
                entity.Property(e => e.ShortCode).IsRequired().HasMaxLength(50);
                entity.Property(e => e.OriginalUrl).IsRequired().HasMaxLength(2048);
                entity.Property(e => e.IpAddress).HasMaxLength(45);
                entity.Property(e => e.UserAgent).HasMaxLength(500);
                entity.Property(e => e.Referrer).HasMaxLength(2048);
                entity.Property(e => e.Country).HasMaxLength(100);
                entity.Property(e => e.City).HasMaxLength(100);
                entity.Property(e => e.Device).HasMaxLength(50);
                entity.Property(e => e.Browser).HasMaxLength(50);
                entity.Property(e => e.OperatingSystem).HasMaxLength(50);
            });

            // UrlStatistics configuration
            modelBuilder.Entity<UrlStatistics>(entity =>
            {
                entity.HasKey(e => e.Id);
                entity.HasIndex(e => e.ShortCode).IsUnique();
                entity.HasIndex(e => e.UserId);
                entity.HasIndex(e => e.TotalClicks);
                entity.HasIndex(e => e.LastClickAt);
                entity.HasIndex(e => e.LastUpdated);
                
                entity.Property(e => e.ShortCode).IsRequired().HasMaxLength(50);
                entity.Property(e => e.OriginalUrl).IsRequired().HasMaxLength(2048);
            });
        }
    }
}