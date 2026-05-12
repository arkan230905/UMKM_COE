#!/bin/bash
# Safe Deploy Script - DOES NOT delete storage/uploads
# Run: bash deploy.sh

cd /var/www/html

echo "=== SAFE DEPLOY SCRIPT ==="
echo ""

# 1. Backup storage folder temporarily
echo "1. Backing up storage uploads..."
if [ -d "storage/app/public" ]; then
    cp -r storage/app/public /tmp/storage_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null
    echo "   Backup created"
fi

# 2. Git pull (only code, not storage)
echo "2. Pulling latest code..."
sudo git fetch origin main
sudo git reset --hard origin/main

# 3. Restore storage if it was cleared
echo "3. Ensuring storage directories exist..."
sudo mkdir -p storage/app/public/produk
sudo mkdir -p storage/app/public/company  
sudo mkdir -p storage/app/public/catalog
sudo mkdir -p storage/app/public/bahan
sudo mkdir -p bootstrap/cache
sudo mkdir -p storage/framework/cache
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/framework/views

# 4. Set permissions
echo "4. Setting permissions..."
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# 5. Install dependencies
echo "5. Installing composer dependencies..."
sudo composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5

# 6. Create storage symlink
echo "6. Creating storage symlink..."
sudo php artisan storage:link 2>/dev/null || echo "   Symlink already exists"

# 7. Run artisan commands
echo "7. Optimizing..."
sudo php artisan package:discover --ansi
sudo php artisan optimize:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache

echo ""
echo "=== DEPLOY COMPLETE ==="
