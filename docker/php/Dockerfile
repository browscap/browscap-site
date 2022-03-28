FROM composer:2.2 AS composer_base
FROM php:8.1-apache AS app_base

RUN apt-get update -y \
  && apt-get install -y libicu-dev zlib1g-dev libzip-dev \
  && a2enmod rewrite \
  && docker-php-ext-install pdo_mysql bcmath intl zip

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./bin /app/bin
COPY ./config /app/config
COPY ./public /app/public
COPY ./src /app/src
COPY ./views /app/views
COPY ./composer.json ./composer.lock /app/

FROM app_base AS development_deps
WORKDIR /app

COPY ./tests /app/tests
COPY ./phpcs.xml.dist ./phpunit.xml.dist ./psalm.xml.dist /app/

COPY --from=composer_base /usr/bin/composer /usr/bin/composer
RUN composer validate && composer install && chmod -R 777 cache

FROM development_deps AS development_server
WORKDIR /app
ENV docker=true
ENV APACHE_DOCUMENT_ROOT /app/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini

FROM scratch AS composer_vendor_path

COPY --from=development_deps /app/vendor .
