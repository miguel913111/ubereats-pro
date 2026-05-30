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

# Create storage symlink if missing (fixes missing images)
php artisan storage:link 2>/dev/null || true

# Cache config with correct env vars (optional but improves performance)
php artisan config:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# Start Apache
exec apache2-foreground
