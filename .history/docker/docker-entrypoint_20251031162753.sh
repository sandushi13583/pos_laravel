#!/bin/bash
set -e

# Default PORT fallback
: ${PORT:=80}

# Adjust nginx listen port if PORT is set
if [ -n "${PORT}" ]; then
  sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/conf.d/default.conf || true
fi

# Ensure writable directories
mkdir -p storage bootstrap/cache public/uploads || true
chown -R www-data:www-data storage bootstrap/cache public/uploads || true
chmod -R 775 storage bootstrap/cache public/uploads || true

# Optional: run artisan commands if flags are present (not automatic to avoid surprises)
if [ "${APP_KEY:-}" = "" ] && [ "${AUTO_GENERATE_KEY:-false}" = "true" ]; then
  php artisan key:generate --force || true
fi

# Start supervisord (manages php-fpm and nginx)
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
