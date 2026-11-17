# Vercel Container Runtime: Laravel 12 + FrankenPHP
FROM dunglas/frankenphp:1-php8.3 AS php-base

RUN install-php-extensions \
    pdo_pgsql \
    gd \
    intl \
    zip \
    exif \
    pcntl \
    posix \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app

# Install production PHP dependencies from the committed lockfile.
FROM php-base AS vendor
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Compile the Vite/Tailwind production assets.
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

# Runtime image.
FROM php-base AS runtime
WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint \
    && mkdir -p \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV SERVER_NAME=:80

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
