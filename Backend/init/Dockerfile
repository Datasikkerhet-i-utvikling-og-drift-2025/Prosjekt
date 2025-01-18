# Use an official PHP image with Apache
FROM php:8.1-apache

# Copy project files to the Apache document root
COPY ../ /var/www/html/

# Enable Apache mod_rewrite if needed (for frameworks or pretty URLs)
RUN a2enmod rewrite

# Install additional PHP extensions (e.g., PDO for MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug
RUN echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/php.ini
