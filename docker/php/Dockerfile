FROM php:8.5-fpm

# Instalar dependencias del sistema y extensiones de PHP para Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev

RUN docker-php-ext-install pdo_mysql exif pcntl bcmath gd intl zip

COPY docker/php/conf.d/performance.ini /usr/local/etc/php/conf.d/99-performance.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer