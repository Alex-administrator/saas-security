FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash curl-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring curl opcache

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/99-app.ini
COPY . /var/www/html

RUN chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p /var/www/html/storage/cache/data /var/www/html/storage/cache/views /var/www/html/storage/logs /var/www/html/storage/queue /var/www/html/storage/uploads \
    && chown -R www-data:www-data /var/www/html/storage

USER www-data

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

