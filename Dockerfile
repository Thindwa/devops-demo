##
# Production-friendly multi-stage image:
# - Build frontend assets in Docker (no CI dependency)
# - Install PHP dependencies in Docker (no CI dependency)
# - Run Nginx + PHP-FPM (not `php artisan serve`)
##

FROM node:20-bookworm-slim AS frontend
WORKDIR /app

# Install frontend deps with caching.
COPY package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Build assets
COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/
RUN npm run build


FROM composer:2 AS vendor
FROM php:8.3-cli-bookworm AS vendor
WORKDIR /app

# Install PHP extensions required by Laravel/composer dependencies.
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
  && docker-php-ext-install \
    curl \
    mbstring \
    xml \
    zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --no-interaction \
  --no-progress \
  --prefer-dist \
  --optimize-autoloader


FROM php:8.3-fpm-bookworm AS app
WORKDIR /app

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    git \
    unzip \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
  && docker-php-ext-install \
    opcache \
    pdo \
    pdo_mysql \
    curl \
    mbstring \
    xml \
    zip \
  && rm -rf /var/lib/apt/lists/*

# App code
COPY . .

# PHP deps + built assets
COPY --from=vendor /app/vendor /app/vendor
COPY --from=frontend /app/public/build /app/public/build

# Runtime config
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure Laravel can write cache/logs
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord","-n","-c","/etc/supervisor/supervisord.conf"]
