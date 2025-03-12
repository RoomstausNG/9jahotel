FROM dunglas/frankenphp

ENV TZ="Africa/Lagos"
ENV SUPERVISOR_PHP_USER="root"
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt update && apt install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    # Add ICU dependencies
    libicu-dev \
    icu-devtools \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        mbstring \
        exif \
        bcmath \
        opcache \
        sockets \
        intl \
        pcntl \
    && pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.8.5 /usr/bin/composer /usr/bin/

# Copy composer files first
COPY composer.json composer.lock /app/


# Install dependencies
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy the rest of the application
COPY . /app

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chown -R www-data:www-data vendor
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Switch to a non-privileged user
USER www-data

EXPOSE 8002

CMD ["php","artisan","octane:frankenphp","--host=0.0.0.0", "--port=8002", "--watch"]
