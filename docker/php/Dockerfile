FROM php:8.1-alpine

RUN apk add --update icu-dev zlib-dev libzip-dev \
  && docker-php-ext-install pdo_mysql bcmath intl zip

ADD ./php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
