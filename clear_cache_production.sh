#!/bin/bash

# Script untuk clear cache di production setelah update kode

echo "=== Clearing Laravel Cache di Production ==="
echo ""

echo "1. Clearing application cache..."
php artisan cache:clear

echo "2. Clearing config cache..."
php artisan config:clear

echo "3. Clearing route cache..."
php artisan route:clear

echo "4. Clearing view cache..."
php artisan view:clear

echo "5. Clearing compiled classes..."
php artisan clear-compiled

echo ""
echo "6. Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Cache cleared successfully!"
echo ""
echo "NEXT STEPS:"
echo "1. Refresh browser with Ctrl+F5 (hard refresh)"
echo "2. Clear browser cache if issue persists"
echo "3. Check if code is pulled: git log -1"
