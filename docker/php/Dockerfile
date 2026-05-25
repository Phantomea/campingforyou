FROM dunglas/frankenphp:1-php8.4-alpine

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    opcache \
    pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

RUN printf '#!/bin/sh\n\
chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache 2>/dev/null || true\n\
[ ! -d vendor ] && composer install --no-interaction || true\n\
exec "$@"\n' > /usr/local/bin/docker-entrypoint-custom.sh \
    && chmod +x /usr/local/bin/docker-entrypoint-custom.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint-custom.sh"]
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]

EXPOSE 8000
