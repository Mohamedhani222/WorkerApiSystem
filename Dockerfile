# Use the official PHP 8.0 FPM image as the base
FROM php:8.2.0-fpm

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    git \
    curl \
    unzip

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo_mysql zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up user and directory permissions
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
USER www
WORKDIR /var/www/html

# Expose port 8000 for the PHP development server
EXPOSE 8000

# Install application dependencies and generate key
COPY --chown=www:www . .
RUN composer install
RUN php artisan key:generate

# Start the PHP development server
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
