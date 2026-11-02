#!/bin/sh
set -e

# Ensure all runtime directories exist. In production these are bind-mounted
# from the host, so the owner on the host may differ from the container user.
# Fix ownership once at startup while we are still root; afterwards the
# application runs as the unprivileged `app` user.

mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs
mkdir -p /app/storage/app/attachments
mkdir -p /app/storage/app/public
mkdir -p /app/storage/app/private
mkdir -p /tmp/caddy
mkdir -p /data/caddy

chown -R app:app /app/storage /app/bootstrap/cache /tmp/caddy /data/caddy

# Run Laravel setup as the application user so generated cache files are
# owned by app instead of root.
su-exec app php artisan storage:link 2>/dev/null || true

if [ "$APP_ENV" = "production" ]; then
    su-exec app php artisan config:cache
    su-exec app php artisan route:cache
    su-exec app php artisan view:cache
    su-exec app php artisan event:cache
fi

# Drop privileges and run the real container command (FrankenPHP / scheduler).
exec su-exec app "$@"
