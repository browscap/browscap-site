FROM composer:2.2 AS composer_base
FROM php:8.1-alpine AS app_base

RUN apk add --update icu-dev zlib-dev libzip-dev \
  && docker-php-ext-install pdo_mysql bcmath intl zip

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./bin /var/www/bin
COPY ./config /var/www/config
COPY ./public /var/www/public
COPY ./src /var/www/src
COPY ./views /var/www/views
COPY ./composer.json ./composer.lock /var/www/

FROM app_base AS development_deps
WORKDIR /var/www

COPY --from=composer_base /usr/bin/composer /usr/bin/composer
RUN composer validate && composer install

FROM development_deps AS development_server
WORKDIR /var/www
ENV docker=true

CMD php -S 0.0.0.0:8080 -t public public/index.php

FROM scratch AS composer_vendor_path

COPY --from=development_deps /var/www/vendor .
