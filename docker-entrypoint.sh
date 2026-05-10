#!/bin/bash
set -e

mkdir -p storage/fonts storage/framework/cache/data storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear
php artisan cache:clear || true
php artisan migrate --force

apache2-foreground
