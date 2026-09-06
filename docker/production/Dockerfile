# ═══════════════════════════════════════════════════════════════
#  صورة الإنتاج — nginx + php-fpm + horizon + المجدول في حاوية واحدة
# ═══════════════════════════════════════════════════════════════
#
# ليه حاوية واحدة؟ عشان Coolify يديرها كمورد واحد بدومين وشهادة
# واحدة. لو الحمل كبر، اطلع الطابور لمورد منفصل بنفس الصورة وأمر
# `php artisan horizon` بدل supervisord.
#
# ⚠️ الصورة دي مالهاش علاقة بـ docker/php/Dockerfile — دي للتطوير
# (بتعتمد على bind mount ومفيهاش أصول مبنية ولا opcache مشدود).

# ─────────────── ١. الأساس: PHP + الإضافات ───────────────
#
# ⚠️ الطبقة دي مشتركة بين مرحلة الاعتماديات ومرحلة التشغيل عن قصد.
# أول محاولة كانت بتستخدم صورة composer الرسمية لتنزيل الاعتماديات،
# وهي مفيهاش intl ولا pcntl ولا exif — فـ composer install بيرفض:
#   «filament/support requires ext-intl -> it is missing from your system»
# الحل مش --ignore-platform-req (ده بيحلّ الاعتماديات على منصة مش
# اللي هتشتغل عليها فعلاً)، الحل إن composer يشتغل على نفس المنصة.
FROM php:8.4-fpm-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libwebp-dev libonig-dev \
        git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql pgsql gd intl zip bcmath exif pcntl opcache sockets

RUN pecl install redis && docker-php-ext-enable redis

# ─────────────── ٢. اعتماديات PHP ───────────────
FROM base AS vendor

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

# --no-scripts: السكربتات بتنادي artisan واللي محتاج كود التطبيق
# كامل، وهو لسه مش منسوخ. بنعيدها بعد النسخ.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ─────────────── ٣. بناء الأصول ───────────────
#
# ⚠️ لازم تيجي بعد مرحلة vendor مش قبلها: resources/css/filament/admin/
# theme.css بيعمل @import على vendor/filament/filament/resources/css/
# theme.css — ده ملف PHP package مش JS، فمفيش طريقة تانية يوصله غير
# إن vendor/ يكون موجود وقت ما Vite يشتغل. (تحليل حقيقي من فشل بناء
# سابق: «Can't resolve '../../../../vendor/filament/filament/
# resources/css/theme.css'» لما الأصول اتبنت في مرحلة منفصلة قبل ما
# vendor يتنسخ.)
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# الثيم بيمسح ملفات PHP و Blade عشان Tailwind يلاقي الفئات
# (@source في theme.css)، فلازم تكون موجودة قبل البناء.
COPY resources ./resources
COPY vite.config.js ./
COPY app ./app
COPY src ./src

# vendor/filament بس — مش vendor/ كله. الحزمة دي فيها آلاف الملفات
# لباكدجات مالهاش أي علاقة بالـ CSS، ونسخها كلها بيبطّئ البناء لغير
# داعي.
COPY --from=vendor /app/vendor/filament ./vendor/filament

RUN npm run build

# ─────────────── ٤. التشغيل ───────────────
FROM base AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/production/php.ini /usr/local/etc/php/conf.d/99-fc-admin.ini
COPY docker/production/nginx.conf /etc/nginx/sites-available/default
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/fc-admin.conf
COPY docker/production/entrypoint.sh /usr/local/bin/fc-entrypoint

RUN chmod +x /usr/local/bin/fc-entrypoint

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# nginx و php-fpm الاتنين بيشتغلوا كـ www-data.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

# Coolify بينهي الـ TLS عند البروكسي بتاعه، فالحاوية بتسمع HTTP عادي.
EXPOSE 8080

ENTRYPOINT ["fc-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
