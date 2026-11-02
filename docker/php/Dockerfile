# syntax=docker/dockerfile:1

# PHP 8.4: composer.lock (symfony 8.1) requires php >=8.4.1
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache icu-libs libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache pcntl \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

WORKDIR /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# Production dependencies + app source (incl. prebuilt public/build from host)
FROM base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

FROM base AS dev
ARG HOST_UID=1000
ARG HOST_GID=1000
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip shadow \
    && groupmod -o -g "${HOST_GID}" www-data \
    && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" www-data \
    && apk del shadow
ENV COMPOSER_HOME=/tmp/composer \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1
USER www-data

FROM base AS prod
COPY --from=vendor /var/www/html /var/www/html
RUN test -f public/build/manifest.json || { echo "public/build missing: run npm run build on host first" >&2; exit 1; } \
    && mkdir -p storage/app/public storage/app/private storage/logs \
        storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    APP_ENV=production
USER www-data
