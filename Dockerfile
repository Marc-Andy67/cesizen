# ─── Stage 1 : Build Node (assets) ───────────────────────────────────────────
FROM node:22-alpine AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts

COPY assets/ assets/
COPY tailwind.config.js ./
COPY importmap.php ./
COPY public/ public/

# ─── Stage 2 : Base PHP ───────────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    curl \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    mbstring \
    xml \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ─── Stage 3 : Dépendances PHP ────────────────────────────────────────────────
FROM base AS vendor

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --optimize-autoloader

# ─── Stage 4 : Build Tailwind via PHP console ─────────────────────────────────
FROM base AS tailwind_builder

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY --from=vendor /var/www/html/vendor vendor/
COPY . .
COPY --from=node_builder /app/node_modules node_modules/

RUN php -d memory_limit=-1 bin/console tailwind:build --minify --no-interaction

# ─── Stage 5 : Image PHP-FPM finale ──────────────────────────────────────────
FROM base AS php

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor /var/www/html/vendor vendor/
COPY --from=tailwind_builder /var/www/html/public/assets public/assets/
COPY --from=tailwind_builder /var/www/html/var/tailwind var/tailwind/

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN php bin/console importmap:install --no-interaction \
    && php bin/console assets:install --no-interaction \
    && php bin/console cache:warmup --env=prod --no-debug

RUN chown -R www-data:www-data var/ public/

EXPOSE 9000
USER www-data
CMD ["php-fpm"]

# ─── Stage 6 : Nginx ──────────────────────────────────────────────────────────
FROM nginx:1.27-alpine AS nginx

COPY --from=php /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80