# Use an official PHP image with Apache
FROM php:8.1-apache

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Set the Apache document root to src/
#RUN sed -i 's|/var/www/html|/var/www/html/src|' /etc/apache2/sites-available/000-default.conf

# Restart Apache with updated configurations
RUN a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]
