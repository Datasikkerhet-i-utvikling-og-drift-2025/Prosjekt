FROM php:8.4-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install necessary system packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        git \  
        # Install git
        zip \  
        # Install zip
        unzip \ 
        # Install unzip
        && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy the application code to the container
COPY public /var/www/html/public
COPY src /var/www/html/src

# Expose the default web server port
EXPOSE 80

# Update the DocumentRoot for Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /var/www/html/cache && chown -R www-data:www-data /var/www/html/cache

RUN chown -R www-data:www-data /var/www/html/cache

# Switch to www-data user for running the application
USER www-data

# Optional: Find and remove extra php.ini files if you want to ensure only one is used.
# This is more advanced and might require some investigation inside your image.
# One possible approach (use with caution):
# RUN find /usr/local/etc/php/conf.d/ -name "*.ini" -not -name "docker-php-ext-*" -delete