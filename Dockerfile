FROM php:8.5-apache
RUN docker-php-ext-install pdo_mysql
COPY docker/php.ini /usr/local/etc/php/conf.d/game.ini
COPY docker/apache.conf /etc/apache2/conf-enabled/game.conf
COPY . /var/www/html/
