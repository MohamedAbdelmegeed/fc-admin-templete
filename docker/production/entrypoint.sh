#!/usr/bin/env bash
set -euo pipefail

# ⚠️ الكاشات بتتعمل هنا مش في البناء: config:cache بيثبّت قيم env
# وقت تنفيذه، والـ env في Coolify بتيجي وقت التشغيل مش وقت البناء.
# لو عملناها في الـ Dockerfile هتتخزّن قيم فاضية والتطبيق هيدوّر على
# قاعدة بيانات مش موجودة.

php artisan config:clear --quiet || true

echo "[fc] waiting for the database…"
until php -r "
    \$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST'), getenv('DB_PORT') ?: 5432, getenv('DB_DATABASE'));
    try { new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); }
    catch (Throwable \$e) { exit(1); }
" 2>/dev/null; do
    sleep 2
done
echo "[fc] database is up."

# --force لأن الأمر بيسأل تأكيد في production ومفيش حد يجاوب.
php artisan migrate --force

# مزامنة الصلاحيات من الكونفيج — لازم بعد الميجريشن وقبل ما حد يدخل.
php artisan authorization:sync --quiet || true

php artisan storage:link --quiet || true

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

chown -R www-data:www-data storage bootstrap/cache

echo "[fc] boot complete."

exec "$@"
