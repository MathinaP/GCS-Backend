FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd mbstring dom

# Enable Apache rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set Apache public folder and enable AllowOverride for .htaccess
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf && \
    sed -i '/<\/VirtualHost>/i\\t<Directory /var/www/html/public>\n\t\tAllowOverride All\n\t<\/Directory>' \
    /etc/apache2/sites-available/000-default.conf

# Increase PHP limits (DomPDF needs extra memory; PDF generation can take time)
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/99-custom.ini \
    && echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/99-custom.ini

# Permissions
RUN mkdir -p /var/www/html/storage/fonts /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]
