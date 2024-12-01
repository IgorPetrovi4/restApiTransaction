FROM php:8.4-fpm

## Install extensions
RUN apt-get update && apt-get install -y \
    wget \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install bcmath zip pdo_mysql pcntl

## Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

## Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /usr/src/app