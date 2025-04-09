FROM php:8.4-apache

# Installer nødvendige pakker
RUN apt-get update && apt-get install -y \
    zip unzip git curl \
    && rm -rf /var/lib/apt/lists/*

# Aktiver mod_rewrite for Apache
RUN a2enmod rewrite

# Sett arbeidsmappe
WORKDIR /var/www/html

# Kopier offentlig innhold og visninger
COPY ./public ./public
COPY ./views ./views
COPY ./src ./src

# Sett Apache DocumentRoot til public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Eksponer port 80
EXPOSE 80

# Bruk Apache som default kommando
CMD ["apache2-foreground"]
