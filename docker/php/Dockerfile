# Use PHP 8.4 FPM
FROM php:8.4-fpm

# Install System Dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    # libpq-dev is the program that allows PHP to communicate with PostgreSQL.
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP Extensions for Laravel and PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Redis Extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Working Directory to the project folder
WORKDIR /var/www/html

# Give the Web Server User (www-data) permission to manage files
RUN chown -R www-data:www-data /var/www/html