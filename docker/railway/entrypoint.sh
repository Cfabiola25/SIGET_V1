#!/usr/bin/env bash
set -e

PORT="${PORT:-80}"
echo "Configuring Nginx on port ${PORT}..."
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/sites-available/default 2>/dev/null || true
sed -i "s/listen \[::\]:80;/listen \[::\]:${PORT};/g" /etc/nginx/sites-available/default 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ -n "$DB_HOST" ] || [ -n "$MYSQLHOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || true
fi

echo "Starting Nginx web server..."
service nginx start

echo "Starting PHP-FPM..."
exec php-fpm
