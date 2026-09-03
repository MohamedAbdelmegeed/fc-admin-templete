# PHP 8.4 — مطلوب لـ activitylog 5.x و backup 10.x (README بند ٢)
FROM php:8.4-fpm-bookworm

# Node 22 LTS مثبّت جوه الصورة عشان الـ build يبقى نفسه على كل جهاز —
# مش بنعتمد على Node بتاع المضيف (اللي ممكن يكون إصدار تاني).
ARG NODE_MAJOR=22

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip zip curl ca-certificates gnupg \
        libpq-dev libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libwebp-dev libonig-dev \
    && curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql pgsql gd intl zip bcmath exif pcntl opcache sockets

# phpredis — الكونفيج بيستخدم REDIS_CLIENT=phpredis
RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-fc-admin.ini

WORKDIR /var/www/html

# المستخدم بتاع الحاوية لازم يقدر يكتب في storage/ و bootstrap/cache
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# الملكية بتتظبط عند كل إقلاع — الـ bind mount بتاع ويندوز بيرجّع
# الملفات لـ root وقت إعادة إنشاء الحاوية.
COPY docker/php/entrypoint.sh /usr/local/bin/fc-entrypoint
RUN chmod +x /usr/local/bin/fc-entrypoint

ENTRYPOINT ["fc-entrypoint"]
CMD ["php-fpm"]
