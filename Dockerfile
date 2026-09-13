# Golden Eagle Casino (Laravel) — punon në Render + Railway
FROM php:8.3-apache

# ext-et e nevojshme (sqlite për DB/session/cache)
RUN apt-get update && apt-get install -y git unzip libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p database storage/app storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

EXPOSE 80

# Render/Railway e japin portin me $PORT — migrimet ekzekutohen në boot
CMD bash -c "export PORT=${PORT:-80}; sed -i \"s/Listen 80/Listen $PORT/\" /etc/apache2/ports.conf; sed -i \"s/:80/:$PORT/g\" /etc/apache2/sites-enabled/000-default.conf; php artisan migrate --force && apache2-foreground"
