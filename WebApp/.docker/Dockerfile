# Use the official PHP image with Apache
FROM php:8.4-apache

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy the application code to the container
COPY public /var/www/html/public
COPY src /var/www/html/src

# Expose the default web server port
EXPOSE 80

# Update the DocumentRoot for Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf
RUN mkdir -p /var/www/html/logs && chmod -R 777 /var/www/html/logs
