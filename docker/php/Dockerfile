FROM php:7.2-alpine

RUN apk add --update icu-dev zlib-dev \
  && docker-php-ext-install pdo_mysql bcmath intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
