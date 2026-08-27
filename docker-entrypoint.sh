#!/bin/sh
set -e

PORT="${PORT:-8080}"
echo "[Railway] Configuring Nginx to listen on port: $PORT"

# Replace __PORT__ with actual dynamic PORT in Nginx configuration
sed -i "s/__PORT__/$PORT/g" /etc/nginx/conf.d/default.conf

# Ensure directory structure exists
mkdir -p /var/www/html/database
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/testing
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/public/build

# Setup sqlite file if using sqlite fallback
touch /var/www/html/database/database.sqlite || true

# Set proper ownership and permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/build || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/build || true

# Laravel Startup & Migrations
echo "[Railway] Preparing Laravel optimizations and migrations..."
php artisan optimize:clear || true
php artisan storage:link || true
php artisan migrate --force || true
php artisan db:seed --class=ProductionSeeder --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[Railway] Starting PHP-FPM daemon..."
php-fpm -D

echo "[Railway] Starting Nginx web server on port $PORT..."
exec nginx -g "daemon off;"
