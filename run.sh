#!/bin/sh

# تنفيذ الـ Migration تلقائياً عند قيام الحاوية
php artisan migrate --force

# تشغيل FrankenPHP مع Caddyfile بتاعنا اللي فيه auto_https off
exec frankenphp run --config /app/Caddyfile