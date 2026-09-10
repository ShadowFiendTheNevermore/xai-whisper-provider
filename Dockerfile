FROM php:8.4-cli-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer validate --no-check-publish \
    && composer install --no-interaction --prefer-dist

COPY src ./src
COPY tests ./tests
COPY phpstan.neon ./

CMD ["composer", "validate"]
