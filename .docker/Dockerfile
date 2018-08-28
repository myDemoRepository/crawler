FROM php:7.2.9-apache

MAINTAINER Paul Redmond

COPY . /srv/app
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN apt-get update && apt-get install mc --assume-yes

RUN docker-php-ext-install mbstring pdo pdo_mysql \
    && pecl install xdebug \
    && chown -R www-data:www-data /srv/app \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.default_enable = 0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable = 1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart = 0" >> /usr/local/etc/php/conf.d/20-xdebug.ini \
    && echo "xdebug.remote_connect_back = 0" >> /usr/local/etc/php/conf.d/20-xdebug.ini \
    && echo "xdebug.profiler_enable = 0" >> /usr/local/etc/php/conf.d/20-xdebug.ini


