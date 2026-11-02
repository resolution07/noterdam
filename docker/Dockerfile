# syntax=docker/dockerfile:1

# ---------- base ----------
FROM dunglas/frankenphp:php8.3-alpine AS base

RUN apk add --no-cache \
    icu-dev \
    mysql-client \
    zip \
    unzip \
    git \
    curl \
    su-exec \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    intl \
    zip \
    gd \
    opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/pear /var/cache/apk/*

WORKDIR /app

# ---------- vendor ----------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ---------- assets ----------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources ./resources
COPY vite.config.ts tailwind.config.js postcss.config.js tsconfig.json ./
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ---------- dev ----------
FROM base AS dev

RUN install-php-extensions xdebug

ENV XDEBUG_MODE=develop,debug

COPY .docker/php/php.ini /usr/local/etc/php/php.ini
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY .docker/php/php.dev.ini /usr/local/etc/php/conf.d/zz-dev.ini
COPY .docker/Caddyfile /etc/frankenphp/Caddyfile

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

# ---------- prod ----------
FROM base AS prod

RUN addgroup -g 1000 app \
    && adduser -u 1000 -G app -s /bin/sh -D app

COPY --chown=app:app . /app
COPY --chown=app:app --from=vendor /app/vendor /app/vendor
COPY --chown=app:app --from=assets /app/public/build /app/public/build
COPY .docker/Caddyfile /etc/frankenphp/Caddyfile
COPY .docker/php/php.ini /usr/local/etc/php/php.ini
COPY .docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && rm -f /app/bootstrap/cache/*.php \
    && mkdir -p /app/storage/framework/cache/data \
    && mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views \
    && mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/app/attachments \
    && chown -R app:app /app/storage /app/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
