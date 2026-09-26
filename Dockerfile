FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS dependencies
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.4-apache-bookworm
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN apt-get update \
    && apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libonig-dev libpng-dev libpq-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd mbstring opcache pcntl pdo_mysql pdo_pgsql zip \
    && a2enmod headers rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=dependencies /app .
COPY --from=frontend /app/public/build ./public/build
COPY docker/start.sh /usr/local/bin/start-coreflow
RUN chmod +x /usr/local/bin/start-coreflow \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 10000
CMD ["start-coreflow"]
