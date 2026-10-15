# ==========================================
# Etapa 1: Compilación de Frontend con Node
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY src/package*.json ./
RUN npm install
COPY src/ ./
RUN npm run build

# ==========================================
# Etapa 2: Aplicación Laravel con PHP 8.5 y Nginx
# ==========================================
FROM php:8.5-fpm

# Instalar dependencias del sistema y Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones requeridas por Laravel
RUN docker-php-ext-install pdo_mysql exif pcntl bcmath gd intl zip opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código fuente
COPY src/ /var/www/html/

# Copiar assets compilados de Vite
COPY --from=frontend /app/public/build /var/www/html/public/build

# Instalar dependencias de PHP para producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos para storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración de Nginx y Entrypoint
COPY docker/railway/nginx.conf /etc/nginx/sites-available/default
COPY docker/railway/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
