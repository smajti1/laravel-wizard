FROM php:7.2-fpm

RUN apt update -yqq && apt install -y \
    git

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
