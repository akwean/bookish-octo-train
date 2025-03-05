# Use the official PHP image with Apache
FROM php:8.1-apache

# Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your project files into Apache’s web root
COPY . /var/www/html/

# Ensure Apache can read the files: set ownership to www-data
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80 for Apache
EXPOSE 80
