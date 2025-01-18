# Use an official PHP image with Apache
FROM php:8.1-apache

# Copy project files (in production this would be adjusted)
COPY . /var/www/html/

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

# Install additional PHP extensions (e.g., PDO for MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install and configure Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug
RUN echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.client_host=${XDEBUG_CLIENT_HOST}" >> /usr/local/etc/php/php.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/php.ini

# Set permissions
RUN chown -R www-data:www-data /var/www/html
