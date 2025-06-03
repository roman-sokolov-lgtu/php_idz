FROM php:8-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    intl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data var/

COPY apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

ENV APP_ENV=dev
ENV APP_DEBUG=1

RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/cache var/log \
    && chmod -R 777 var/cache var/log
