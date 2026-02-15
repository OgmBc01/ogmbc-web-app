FROM php:8.2-apache

# Install mysqli and PDO extensions
RUN apt-get update && apt-get install -y libonig-dev libzip-dev zip unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

WORKDIR /var/www/html
