#!/usr/bin/env sh
set -e

# php-fpm بيشتغل كـ www-data، والملفات على الـ bind mount بتاع ويندوز
# بتتعمل بـ root. من غير الملكية دي، أول كتابة لـ storage/framework/views
# بتفشل والصفحة بترجع 500 برسالة tempnam غامضة.
# (docs/CHANGELOG-DOCS.md — المرحلة ٢)
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint "$@"
