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

# Install phpDocumentor
RUN curl -L https://github.com/phpDocumentor/phpDocumentor/releases/download/v3.3.0/phpDocumentor.phar -o /usr/local/bin/phpdoc && \
    chmod +x /usr/local/bin/phpdoc

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Generate PHPDoc during startup
CMD ["sh", "-c", "phpdoc -d /var/www/html -t /var/www/html/docs --template=markdown && apache2-foreground"]

