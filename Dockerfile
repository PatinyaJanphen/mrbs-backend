FROM serversideup/php:8.3-fpm-nginx

# Switch to root to perform all installations with full privileges
USER root

# Set the working directory explicitly
WORKDIR /var/www/html

# Copy composer files first to leverage Docker layer caching
COPY composer.json composer.lock ./

# Install composer production dependencies (ignore platform reqs as a failsafe)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Copy the rest of the application files
COPY . .

# Ensure correct ownership and permissions for the unprivileged user
RUN chown -R www-data:www-data /var/www/html

# Switch back to the secure, unprivileged www-data user for container execution
USER www-data





