FROM dunglas/frankenphp:1-php8.2

# تثبيت الإضافات اللازمة للـ Database والـ Zip لـ Laravel
RUN install-php-extensions \
    pdo_mysql \
    zip \
    bcmath \
    opcache

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت مكتبات الـ Composer للـ Production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ضبط الصلاحيات للمجلدات الخاصة بـ Laravel
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# ضبط متغيرات البيئة لمنع الـ URI Errors وتوجيه البورت
EXPOSE 80
ENV PORT=80

# إعطاء صلاحية التنفيذ لملف الـ script وتشغيله كـ Entrypoint
RUN chmod +x /app/run.sh
CMD ["/app/run.sh"]