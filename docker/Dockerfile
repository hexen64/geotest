FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    g++ \
    git \
    libicu-dev \
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libexif-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
        exif \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        zip \
    && pecl install apcu \
    && docker-php-ext-enable apcu

WORKDIR /var/www/geotest

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
# RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
