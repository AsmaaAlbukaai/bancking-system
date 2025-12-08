FROM php:8.2-fpm

# Install system deps
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Give permissions
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
