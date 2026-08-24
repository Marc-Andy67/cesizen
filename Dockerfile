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
    nodejs \
    npm \
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

# ─── Stage 3 : Image finale PHP-FPM ───────────────────────────────────────────
FROM base AS php

WORKDIR /var/www/html

# Copie du code source
COPY --chown=www-data:www-data . .

# Copie des dépendances compilées
COPY --from=vendor /var/www/html/vendor vendor/

# Variables d'environnement Symfony
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Dépendances npm (plugins Tailwind CSS v4, ex. daisyui)
RUN npm ci

RUN php -d memory_limit=-1 bin/console tailwind:build --minify --no-interaction \
    && php bin/console importmap:install --no-interaction \
    && php bin/console asset-map:compile --no-interaction \
    && php bin/console assets:install --no-interaction

# Cache warmup
RUN php bin/console cache:warmup --env=prod --no-debug

# Permissions
RUN chown -R www-data:www-data var/ public/

EXPOSE 9000

USER www-data

CMD ["php-fpm"]

# ─── Stage 4 : nginx (sert le front + proxy fastcgi vers php-fpm) ─────────────
FROM nginx:alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Récupère les assets publics déjà buildés (Tailwind, importmap, assets:install)
# depuis le stage php, sans avoir besoin de rebuilder quoi que ce soit ici.
COPY --from=php /var/www/html/public /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
