FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl libpq-dev unzip zip \
    && docker-php-ext-install pdo pdo_pgsql

#upsize upload limit
RUN echo "upload_max_filesize=10M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size=12M" >> /usr/local/etc/php/conf.d/uploads.ini \
 && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files ke container
COPY . .

# Install dependencies
RUN composer install

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
