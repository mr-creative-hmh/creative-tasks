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
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/build || true

# Ensure APP_KEY exists and has valid base64 format (starts with base64:)
if [ -z "$APP_KEY" ] || [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    echo "[Railway] Generating valid base64 APP_KEY for encryption..."
    export APP_KEY=$(php artisan key:generate --show)
    echo "[Railway] Using generated APP_KEY: $APP_KEY"
fi

# Set LOG_CHANNEL to stderr so any error prints directly to Railway Deploy Logs
export LOG_CHANNEL=stderr

# Configure PHP-FPM to log all worker errors to stdout/stderr
sed -i "s/;catch_workers_output = yes/catch_workers_output = yes/g" /usr/local/etc/php-fpm.d/www.conf || true
sed -i "s/;decorate_workers_output = no/decorate_workers_output = no/g" /usr/local/etc/php-fpm.d/www.conf || true

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
