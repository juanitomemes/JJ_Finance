#!/usr/bin/env bash
set -e

echo "-----> Instalando dependencias PHP (sin dev)..."
composer install --no-dev --optimize-autoloader

echo "-----> Instalando dependencias Node..."
npm install

echo "-----> Compilando assets con Vite..."
npm run build

echo "-----> Cacheando configuración de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "-----> Ejecutando migraciones..."
php artisan migrate --force

echo "-----> Publicando assets de Filament..."
php artisan filament:assets

echo "-----> Enlazando storage..."
php artisan storage:link

echo "✅ Build completado con éxito."
