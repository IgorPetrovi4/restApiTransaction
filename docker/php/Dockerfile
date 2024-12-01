FROM php:8.4-fpm

## Install wget
RUN apt-get update && apt-get install -y wget git

## Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

## Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /usr/src/app