#!/bin/bash
set -e

# Fix Apache MPM conflict - ensure only prefork is loaded
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork rewrite headers >/dev/null 2>&1 || true

cd /var/www/html

# Clear cached config so Laravel reads Railway env vars at runtime
php artisan config:clear 2>/dev/null || true

# Generate APP_KEY if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force 2>/dev/null || true
fi

# ── Storage volume fix ──────────────────────────────────────────────
# Railway mounts the persistent volume at /app/storage/app/public
# Laravel lives in /var/www/html. We need:
#   1. /var/www/html/storage/app/public → /app/storage/app/public (symlink)
#   2. /var/www/html/public/storage → storage/app/public (Laravel's public symlink)
#
# IMPORTANT: Never use `rm -rf` on a symlink — it follows the link
#            and deletes the *target* (the volume contents).

# 1. Ensure the volume mountpoint exists
mkdir -p /app/storage/app/public

# 2. Ensure Laravel's storage/app directory exists
mkdir -p /var/www/html/storage/app

# 3. Handle /var/www/html/storage/app/public
#    If it's a symlink already, leave it. If it's a real directory, remove it
#    (after copying contents to the volume) and create the symlink.
STORAGE_PUBLIC="/var/www/html/storage/app/public"
VOLUME_PUBLIC="/app/storage/app/public"

if [ -L "$STORAGE_PUBLIC" ]; then
    # Already a symlink — make sure it points to the right place
    CURRENT_TARGET=$(readlink "$STORAGE_PUBLIC" || true)
    if [ "$CURRENT_TARGET" != "$VOLUME_PUBLIC" ]; then
        rm -f "$STORAGE_PUBLIC"
        ln -s "$VOLUME_PUBLIC" "$STORAGE_PUBLIC"
    fi
elif [ -d "$STORAGE_PUBLIC" ]; then
    # It's a real directory — copy any contents to the volume, then replace with symlink
    cp -a "$STORAGE_PUBLIC/"* "$VOLUME_PUBLIC/" 2>/dev/null || true
    rm -rf "$STORAGE_PUBLIC"
    ln -s "$VOLUME_PUBLIC" "$STORAGE_PUBLIC"
else
    # Nothing there yet — just create the symlink
    ln -s "$VOLUME_PUBLIC" "$STORAGE_PUBLIC"
fi

# 4. Ensure Laravel's public/storage symlink exists
rm -f /var/www/html/public/storage
php artisan storage:link 2>/dev/null || true

# ── Laravel caches ──────────────────────────────────────────────────
php artisan route:clear 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# Start Apache
exec apache2-foreground
