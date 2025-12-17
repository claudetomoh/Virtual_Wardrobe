FROM php:8.2-apache

RUN apt-get update && apt-get install -y libzip-dev zip libicu-dev libonig-dev libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring intl zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html!' /etc/apache2/sites-available/*.conf
RUN a2enmod headers rewrite

WORKDIR /var/www/html
