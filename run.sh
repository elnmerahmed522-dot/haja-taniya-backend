#!/bin/sh

# تنفيذ الـ Migration تلقائياً عند قيام الحاوية
php artisan migrate --force

# تشغيل خادم FrankenPHP الأساسي بعد انتهاء الـ migration
exec frankenphp run --config /etc/caddy/Caddyfile