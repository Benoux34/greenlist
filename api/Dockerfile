# syntax=docker/dockerfile:1
FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions pdo_pgsql intl opcache zip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# SERVER_NAME=:80 keeps the embedded server on plain HTTP: TLS is terminated
# by the host's nginx, not in this container.
ENV APP_ENV=prod \
    SERVER_NAME=:80 \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-progress --prefer-dist

COPY . .
# No cache warm-up here: the image carries no environment file. The container
# gets its .env from the host at run time, and the start-up commands
# below build the cache on first boot.
RUN composer dump-autoload --no-dev --classmap-authoritative

EXPOSE 80

# Both commands are idempotent: the key pair is only generated when missing,
# and only pending migrations are applied.
CMD ["sh", "-c", "php bin/console lexik:jwt:generate-keypair --skip-if-exists && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration && exec frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile"]
