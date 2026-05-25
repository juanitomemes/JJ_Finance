#!/bin/bash
set -e

echo "-----> Registrando paquetes de Laravel (post-autoload-dump)..."
php artisan package:discover --ansi

echo "-----> Cacheando configuración de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "-----> Ejecutando migraciones en Neon.tech..."
php artisan migrate --force

echo "-----> Publicando assets de Filament..."
php artisan filament:assets

echo "-----> Enlazando storage..."
php artisan storage:link || true

echo "-----> Iniciando Apache..."
apache2-foreground
