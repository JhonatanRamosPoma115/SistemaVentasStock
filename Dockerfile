# Compila Vite sin instalar Node en el contenedor que atiende visitas.
FROM node:22-bookworm-slim AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

FROM php:8.4-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor gettext-base curl unzip git \
    libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    libonig-dev libzip-dev libssl-dev pkg-config \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && (docker-php-ext-install -j$(nproc) gd mbstring zip opcache bcmath > /tmp/php-build.log 2>&1 || (tail -n 80 /tmp/php-build.log; exit 1)) \
    && php -r 'if (!function_exists("imagewebp") || !gd_info()["WebP Support"]) exit(1);' \
    && rm -rf /var/lib/apt/lists/*
ARG MONGODB_EXTENSION_VERSION=1.21.5
RUN (pecl install mongodb-${MONGODB_EXTENSION_VERSION} > /tmp/mongodb-build.log 2>&1 || (tail -n 80 /tmp/mongodb-build.log; exit 1)) \
    && docker-php-ext-enable mongodb \
    && rm -f /tmp/php-build.log /tmp/mongodb-build.log
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader
COPY artisan ./artisan
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY lang ./lang
COPY resources ./resources
COPY routes ./routes
COPY public ./public
COPY database ./database
COPY --from=frontend /build/public/build ./public/build
RUN mkdir -p storage/app/public storage/app/private/pedidos storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --no-scripts \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache
COPY docker/nginx.conf /etc/nginx/templates/app.conf.template
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/start.sh /start.sh
RUN sed -i 's/\r$//' /start.sh && chmod +x /start.sh
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr LOG_LEVEL=warning \
    SESSION_DRIVER=file CACHE_STORE=file QUEUE_CONNECTION=sync PORT=10000
EXPOSE 10000
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS "http://127.0.0.1:${PORT}/up" || exit 1
CMD ["/start.sh"]
