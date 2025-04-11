FROM php:8.4-apache

# Installer nødvendige pakker og rydd opp
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        gnupg \
        git \
        zip \
        unzip \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Installer PHP-utvidelser
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Aktiver mod_rewrite
RUN a2enmod rewrite

# Sett arbeidsmappe
WORKDIR /var/www/html

# Kopier composer-filer FØRST for caching
COPY composer.json composer.lock ./

# Kjør composer update for å hente alle dependencies inkludert gelf-php
RUN composer update --no-dev --optimize-autoloader

# Installer avhengigheter
RUN composer install --no-dev --optimize-autoloader

# Endre eierskap av vendor-mappen ETTER installasjon
RUN chown -R www-data:www-data /var/www/html/vendor


# Kopier applikasjonsfiler
COPY public ./public
COPY src ./src


# Optimaliser autoloading
RUN composer dump-autoload --optimize

# Sett DocumentRoot for Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Opprett loggmappe og endre eierskap
RUN mkdir -p /var/www/html/logs && chown -R www-data:www-data /var/www/html/logs

# Eksponer port
EXPOSE 80

# Bytt til www-data bruker
USER www-data