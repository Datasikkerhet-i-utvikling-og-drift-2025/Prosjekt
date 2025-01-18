# Use an official PHP image with Apache
FROM php:8.1-apache

# Copy project files (in production this would be adjusted)
COPY . /var/www/html/

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

# Install additional PHP extensions if required
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set permissions
RUN chown -R www-data:www-data /var/www/html
