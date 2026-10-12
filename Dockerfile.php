FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application code
COPY . /var/www/html/

# Ensure uploads directories exist and have proper permissions
RUN mkdir -p /var/www/html/uploads/audio /var/www/html/uploads/images \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads \
    && touch /var/www/html/v536/.env \
    && chown www-data:www-data /var/www/html/v536/.env

EXPOSE 80
