#!/bin/bash
set -e

# Esperar a que PostgreSQL esté listo
echo "⏳ Esperando a PostgreSQL..."
until php artisan db:show 2>/dev/null; do
  echo "PostgreSQL no está listo - esperando..."
  sleep 2
done

echo "✅ PostgreSQL está listo!"

# Crear enlace simbólico para storage (si no existe)
if [ ! -L public/storage ]; then
    php artisan storage:link
    echo "✅ Storage link creado"
else
    echo "ℹ️ Storage link ya existe"
fi

# Ejecutar migraciones
echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

# Limpiar cache (DESPUÉS de las migraciones)
echo "🧹 Limpiando caché..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Opcional: Seeders (descomenta si necesitas datos iniciales)
# php artisan db:seed --force

echo "🚀 Backend iniciado correctamente"

# Ejecutar el comando original (php-fpm)
exec "$@"