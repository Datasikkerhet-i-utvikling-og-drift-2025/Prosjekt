# Bruk MySQL-basen image
FROM mysql:latest

# Sett miljøvariabler for MySQL
ENV MYSQL_ROOT_PASSWORD=root
ENV MYSQL_DATABASE=database
ENV MYSQL_USER=admin
ENV MYSQL_PASSWORD=admin

# Kopier SQL-filer til containeren
COPY init.sql /docker-entrypoint-initdb.d/

# Eksponer standard MySQL-port
EXPOSE 3306
