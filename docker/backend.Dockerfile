FROM php:8.3-cli-bookworm AS vendor
WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libcurl4-openssl-dev \
    libicu-dev \
    libonig-dev \
    libsqlite3-dev \
    libxml2-dev \
    libzip-dev \
  && docker-php-ext-install \
    bcmath \
    curl \
    dom \
    intl \
    mbstring \
    pdo_mysql \
    pdo_sqlite \
    xml \
    zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install deps with cache-friendly layers.
# We skip scripts here because Laravel's default Composer scripts call `php artisan ...`,
# which requires the app files to be present (and an APP_KEY).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts


FROM php:8.3-apache

WORKDIR /var/www/html

# Use Laravel-style public document root (works for real Laravel later too).
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Laravel needs rewrite; allow .htaccess overrides.
RUN a2enmod rewrite \
  && sed -ri 's/AllowOverride\s+None/AllowOverride All/g' /etc/apache2/apache2.conf

# Build context should be `backend/`
COPY . /var/www/html

COPY --from=vendor /var/www/html/vendor /var/www/html/vendor

EXPOSE 80

