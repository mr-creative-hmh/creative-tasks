FROM php:8.3-cli

# Set environment
ENV NODE_ENV=production

# Install system packages & dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libsqlite3-dev \
    sqlite3 \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configure & Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    bcmath \
    exif \
    gd \
    intl \
    zip

# Copy Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Node.js 22 & NPM
COPY --from=node:22 /usr/local /usr/local

WORKDIR /app

# Copy dependency manifests
COPY composer.json composer.lock ./
COPY package*.json ./

# Install Node dependencies (all deps required for Vite build)
RUN npm ci || npm install

# Copy application source code
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# Build Vue 3 + Inertia SPA production bundle
RUN npm run build

# Remove node_modules to keep Docker image lightweight
RUN rm -rf node_modules ~/.npm

# Prepare Laravel storage and database directories
RUN mkdir -p \
    database \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/testing \
    storage/logs \
    bootstrap/cache \
    public/build

RUN touch database/database.sqlite
RUN chmod -R 777 storage bootstrap/cache database public/build

EXPOSE 8080

CMD sh -c "\
mkdir -p database && \
touch database/database.sqlite || true && \
chmod -R 777 database storage bootstrap/cache public/build || true && \
php artisan optimize:clear && \
php artisan storage:link || true && \
php artisan migrate --force && \
php artisan db:seed --class=ProductionSeeder --force || true && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan serve --host=0.0.0.0 --port=\${PORT:-8080}"