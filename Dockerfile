FROM php:8.3-cli AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pcntl \
        pdo_mysql \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

FROM base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM node:20-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci
RUN npm i --no-save @rollup/rollup-linux-x64-gnu

COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM base AS final

COPY . /app

COPY --from=vendor /app/vendor /app/vendor
COPY --from=assets /app/public/build /app/public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["sh", "-c", "mkdir -p storage/app/public && ln -sfn /app/storage/app/public /app/public/storage && rm -f bootstrap/cache/*.php && php -S 0.0.0.0:${PORT:-8000} -t public"]
