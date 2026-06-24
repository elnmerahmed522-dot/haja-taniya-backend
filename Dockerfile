FROM php:8.2-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# تفعيل الـ rewrite وتعطيل الموديول الزيادة من الـ config الرئيسي مباشرة
RUN a2enmod rewrite && \
    sed -i 's/^LoadModule mpm_event_module/#LoadModule mpm_event_module/' /etc/apache2/mods-available/mpm_event.load || true

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy files
COPY . .

# Install production dependencies only
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy Apache config (proper file instead of echo)
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
