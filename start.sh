#!/bin/bash

echo "=== VerseHub Startup ==="
echo "PORT: ${PORT:-not set}"
echo "DB_HOST: ${DB_HOST:-not set}"
echo "DB_DATABASE: ${DB_DATABASE:-not set}"
echo "DB_CONNECTION: ${DB_CONNECTION:-not set}"

echo ""
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed, continuing anyway..."

echo ""
echo "Seeding database..."
php artisan db:seed --force 2>&1 || echo "WARNING: Seed failed, continuing anyway..."

echo ""
echo "Starting PHP server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} server.php
