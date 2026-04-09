#!/bin/bash

echo "=== VerseHub Startup ==="
echo "PORT: ${PORT:-not set}"
echo "DB_HOST: ${DB_HOST:-not set}"
echo "DB_DATABASE: ${DB_DATABASE:-not set}"
echo "DB_CONNECTION: ${DB_CONNECTION:-not set}"

echo ""
echo "Fixing storage permissions..."
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo ""
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed, continuing anyway..."

echo ""
echo "Seeding database..."
php artisan db:seed --force 2>&1 || echo "WARNING: Seed failed, continuing anyway..."

echo ""
echo "Caching config and routes..."
php artisan config:cache 2>&1
php artisan route:cache 2>&1

# Fix permissions again after cache generation (files created as root)
chown -R www-data:www-data /app/storage /app/bootstrap/cache

echo ""
echo "Configuring nginx on port ${PORT:-8080}..."
sed -i "s/__PORT__/${PORT:-8080}/g" /app/nginx.conf
cp /app/nginx.conf /etc/nginx/nginx.conf

echo ""
echo "Starting Laravel scheduler (cron)..."
(while true; do php /app/artisan schedule:run --no-interaction >> /app/storage/logs/scheduler.log 2>&1; sleep 60; done) &

echo ""
echo "Starting php-fpm..."
php-fpm -D

echo "Starting nginx..."
exec nginx -g "daemon off;"
