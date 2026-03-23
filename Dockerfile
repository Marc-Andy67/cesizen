# ─── Stage 1 : Base PHP ───────────────────────────────────────────────────────
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

# ─── Stage 2 : Dépendances PHP ────────────────────────────────────────────────
FROM base AS vendor
COPY composer.json composer.lock symfony.lock ./
RUN composer install \
    --prefer-dist \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --optimize-autoloader

# ─── Stage 3 : Image finale ───────────────────────────────────────────────────
FROM base AS production
WORKDIR /var/www/html
RUN apk add --no-cache nodejs npm
COPY --chown=www-data:www-data . .
COPY --from=vendor /var/www/html/vendor vendor/
RUN npm install
ENV APP_ENV=prod
ENV APP_DEBUG=0
RUN php -d memory_limit=-1 bin/console tailwind:build --minify --no-interaction \
    && php bin/console importmap:install --no-interaction \
    && php bin/console assets:install --no-interaction
RUN php bin/console cache:warmup --env=prod --no-debug
RUN chown -R www-data:www-data var/ public/
EXPOSE 9000
USER www-data
CMD ["php-fpm"]

# ─── Stage 4 : Nginx ──────────────────────────────────────────────────────────
FROM nginx:1.27-alpine AS nginx
COPY --from=production /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80