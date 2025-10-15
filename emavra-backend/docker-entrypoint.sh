#!/bin/bash
set -e

echo "🚀 Iniciando backend de EMAVRA..."

# Esperar a que PostgreSQL esté disponible
echo "⏳ Esperando PostgreSQL en ${DB_HOST}:${DB_PORT}..."
until PGPASSWORD=${DB_PASSWORD} psql -h "${DB_HOST}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -c '\q' 2>/dev/null; do
  echo "PostgreSQL no disponible, reintentando en 3 segundos..."
  sleep 3
done

echo "✅ PostgreSQL está listo!"

# Verificar extensión intl
echo "🔍 Verificando extensión intl..."
php -m | grep intl || echo "⚠️ Advertencia: extensión intl no encontrada"

# Ejecutar migraciones
echo "📊 Ejecutando migraciones..."
php artisan migrate --force

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Crear enlaces simbólicos
echo "🔗 Creando enlaces de storage..."
php artisan storage:link || echo "Enlaces ya existen"

echo "✅ Backend listo!"

# Ejecutar el comando principal (php-fpm)
exec "$@"