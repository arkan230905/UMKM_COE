#!/bin/bash
# Permanent Fix Script for Hosting
# This script ensures vendor folder stays and permissions are correct

echo "🔧 Starting Permanent Fix..."

# Navigate to project directory
cd /var/www/html

# 1. Create vendor folder if not exists
if [ ! -d "vendor" ]; then
    echo "📦 Creating vendor folder..."
    sudo mkdir -p vendor
    sudo chown -R simcost:simcost vendor
    sudo chmod -R 755 vendor
fi

# 2. Install dependencies if vendor is empty
if [ ! -f "vendor/autoload.php" ]; then
    echo "📥 Installing dependencies..."
    sudo -u simcost composer install --no-dev --optimize-autoloader --no-interaction
fi

# 3. Create all required Laravel folders
echo "📁 Creating Laravel folders..."
sudo mkdir -p bootstrap/cache
sudo mkdir -p storage/framework/views
sudo mkdir -p storage/framework/cache
sudo mkdir -p storage/framework/sessions
sudo mkdir -p storage/logs
sudo mkdir -p storage/app/public

# 4. Set correct permissions
echo "🔒 Setting permissions..."
sudo chmod -R 755 vendor
sudo chmod -R 777 storage
sudo chmod -R 777 bootstrap/cache

# 5. Clear and rebuild cache
echo "🗑️ Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "💾 Rebuilding cache..."
php artisan config:cache
php artisan route:cache

# 6. Restart services
echo "🔄 Restarting services..."
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx

# 7. Test website
echo "🧪 Testing website..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ SUCCESS! Website is online (HTTP $HTTP_CODE)"
else
    echo "❌ ERROR! Website returned HTTP $HTTP_CODE"
fi

echo "✅ Permanent fix completed!"
