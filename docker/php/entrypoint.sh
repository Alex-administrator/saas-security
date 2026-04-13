#!/bin/sh
set -e

mkdir -p /var/www/html/storage/cache/data
mkdir -p /var/www/html/storage/cache/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/queue
mkdir -p /var/www/html/storage/uploads

# Keep container env vars available to PHP workers so the app can run
# without reading a world-readable bind-mounted .env file at request time.
cat > /usr/local/etc/php-fpm.d/zz-app-env.conf <<'EOF'
[www]
clear_env = no
EOF

chmod 755 /var/www/html || true

for path in /var/www/html/app /var/www/html/bootstrap /var/www/html/config /var/www/html/database /var/www/html/public /var/www/html/routes /var/www/html/tests; do
  if [ -d "$path" ]; then
    find "$path" -type d -exec chmod 755 {} \; || true
    find "$path" -type f -exec chmod 644 {} \; || true
  fi
done

if [ -f /var/www/html/console.php ]; then
  chmod 644 /var/www/html/console.php || true
fi

if [ -f /var/www/html/.env ]; then
  chmod 600 /var/www/html/.env || true
fi

if [ -d /var/www/html/storage ]; then
  chown -R www-data:www-data /var/www/html/storage || true
  find /var/www/html/storage -type d -exec chmod 770 {} \; || true
  find /var/www/html/storage -type f -exec chmod 640 {} \; || true
fi

exec "$@"
