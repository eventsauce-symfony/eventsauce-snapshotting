FROM php:8.1-rc-fpm-buster

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip

RUN docker-php-ext-install \
    pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/library/src
