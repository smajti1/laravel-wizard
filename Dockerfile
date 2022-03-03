FROM php:8.1-fpm-alpine

RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    shadow \
   $PHPIZE_DEPS

RUN pecl install xdebug-3.1.3 && docker-php-ext-enable xdebug

RUN usermod -u 1000 www-data
RUN chown www-data:www-data /var/www/ /var/www/html/

RUN curl --insecure https://getcomposer.org/composer-stable.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
