FROM php:7.4.0-fpm-alpine

RUN docker-php-ext-install pdo_mysql exif

WORKDIR /var/www/html/

COPY --from=composer:2.2.6 /usr/bin/composer /usr/bin/composer

COPY . .

RUN chown -R www-data:www-data /var/www/html

RUN composer install

EXPOSE 9000