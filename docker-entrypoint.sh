#!/bin/bash
set -e

# Cachear la configuración para velocidad
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones (El --force es vital en producción)
echo "Running migrations..."
php artisan migrate --force

# Iniciar Apache en primer plano
echo "Starting Apache..."
apache2-foreground
