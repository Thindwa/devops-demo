FROM php:8.3-cli-bookworm AS vendor
WORKDIR /var/www/html

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
    pdo_mysql \
    xml \
    zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader


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

