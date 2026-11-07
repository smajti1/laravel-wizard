FROM php:8.5-cli-alpine

WORKDIR /var/www/html

RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    shadow \
   $PHPIZE_DEPS

RUN apk add --no-cache linux-headers \
	&& pecl install xdebug-3.5.3 \
    && docker-php-ext-enable xdebug

RUN usermod -u 1000 www-data

RUN curl --insecure https://getcomposer.org/composer-stable.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
