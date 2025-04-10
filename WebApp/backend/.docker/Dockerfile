FROM php:8.4-apache

# Installer nødvendige pakker og PHP-utvidelser
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Aktiver mod_rewrite
RUN a2enmod rewrite

# Sett DocumentRoot eksplisitt og aktiver .htaccess-bruk
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|' /etc/apache2/sites-available/000-default.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Sett arbeidskatalog
WORKDIR /var/www/html

# Kopier composer-filer og installer alle avhengigheter
COPY composer.json composer.lock ./
RUN composer update --no-dev --optimize-autoloader

# Kopier app-kode
COPY ./src ./src
COPY ./index.php ./index.php
COPY ./api.php ./api.php

# Kopier .htaccess fra backend
COPY ./.htaccess ./.htaccess

# Sørg for at logs-mappen finnes
RUN mkdir -p /var/www/html/logs

# Eksponer HTTP-port
EXPOSE 80

# Kjør Apache i foreground
CMD ["apache2-foreground"]
