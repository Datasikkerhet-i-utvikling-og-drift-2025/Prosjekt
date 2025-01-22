# Use the official MySQL base image
FROM mysql:latest

# Set environment variables for MySQL
#ENV MYSQL_ROOT_PASSWORD=root
#ENV MYSQL_DATABASE=database
#ENV MYSQL_USER=admin
#ENV MYSQL_PASSWORD=admin

# Copy SQL files to the container
COPY init.sql /docker-entrypoint-initdb.d/

# Expose default MySQL port
EXPOSE 3306
