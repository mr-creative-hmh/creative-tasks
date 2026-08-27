# Stage 1: Build Vue 3 + Inertia + Tailwind + Leaflet Assets
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Copy dependency manifests
COPY package*.json ./

# Install frontend dependencies
RUN npm ci || npm install

# Copy application source code
COPY . .

# Build production assets into /app/public/build
RUN npm run build

# Stage 2: Production PHP 8.3 FPM + Nginx Runtime
FROM php:8.3-fpm-bookworm

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Install Nginx and required OS packages
RUN apt-get update && apt-get install -y --no-install-recommends     nginx     git     unzip     zip     curl     libzip-dev     libpng-dev     libjpeg62-turbo-dev     libfreetype6-dev     libonig-dev     libxml2-dev     libicu-dev     libsqlite3-dev     sqlite3     default-mysql-client     && rm -rf /var/lib/apt/lists/*

# Configure & Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg     && docker-php-ext-install -j$(nproc)     pdo     pdo_sqlite     pdo_mysql     bcmath     exif     gd     intl     zip     opcache

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy full application codebase first so artisan and all classes exist
COPY . .

# Install PHP dependencies with full scripts and optimization
RUN composer install     --no-dev     --prefer-dist     --optimize-autoloader     --no-interaction

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Copy Nginx server block configuration
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Clean default Debian Nginx sites
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default

# Copy entrypoint startup script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Fix permissions
RUN chown -R www-data:www-data /var/www/html     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
