# ─── Stage 1 : Build des assets ───────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS base

# Extensions PHP nécessaires
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

# Composer
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

# Installer Node.js + npm pour DaisyUI
RUN apk add --no-cache nodejs npm

# Copie du code source
COPY --chown=www-data:www-data . .

# Copie des dépendances compilées
COPY --from=vendor /var/www/html/vendor vendor/

# Installer les dépendances npm (DaisyUI)
RUN npm install

# Variables d'environnement Symfony
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Build des assets Tailwind + importmap
RUN php -d memory_limit=-1 bin/console tailwind:build --minify --no-interaction \
    && php bin/console importmap:install --no-interaction \
    && php bin/console assets:install --no-interaction

# Cache warmup
RUN php bin/console cache:warmup --env=prod --no-debug

# Permissions
RUN chown -R www-data:www-data var/ public/

EXPOSE 9000

USER www-data

CMD ["php-fpm"]