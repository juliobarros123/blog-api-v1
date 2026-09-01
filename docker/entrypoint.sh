#!/bin/sh
set -e

echo "🔧 Ajustando permissões do storage..."

mkdir -p /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

touch /var/www/html/storage/logs/laravel.log

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Permissões ajustadas."

echo "🔑 Verificando APP_KEY..."

php artisan config:clear

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
    echo "✅ Nova APP_KEY gerada."
else
    echo "✅ APP_KEY já existe, mantendo."
fi

echo "🗄️  Executando migrations..."
php artisan migrate --force

echo "⚙️  Recriando cache de configuração..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Aplicação pronta."

exec "$@"