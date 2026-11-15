# Composer runs on the same PHP as the app: the composer image's newer PHP is refused by a locked package
FROM php:8.4-cli AS build
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . /app/
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

# A supported PHP on a supported Debian, so a rebuild picks up their security fixes
FROM php:8.4-apache AS production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=build /app /var/www/html
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .env.prod.example /var/www/html/.env

RUN php artisan key:generate && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite
