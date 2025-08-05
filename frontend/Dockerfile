FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite headers deflate expires

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]