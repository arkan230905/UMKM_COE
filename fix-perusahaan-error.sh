#!/bin/bash

# Fix Perusahaan user_id Error Script
# This script fixes the "Unknown column 'user_id'" error

echo "=========================================="
echo "Fixing Perusahaan user_id Error"
echo "=========================================="

# Step 1: Pull latest code
echo ""
echo "Step 1: Pulling latest code from GitHub..."
git pull origin ghitha

# Step 2: Clear all Laravel caches
echo ""
echo "Step 2: Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Step 3: Clear opcache if available
echo ""
echo "Step 3: Clearing PHP opcache..."
php -r 'if (extension_loaded("Zend OPcache")) { opcache_reset(); echo "OPcache cleared\n"; } else { echo "OPcache not enabled\n"; }'

# Step 4: Verify the fix
echo ""
echo "Step 4: Verifying the fix..."
php diagnostic.php

echo ""
echo "=========================================="
echo "✅ Fix completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Visit http://jobcost.eadtmanufaktur.com/tentang-perusahaan/detail"
echo "2. If error persists, restart PHP-FPM or Apache"
echo "3. Check error logs: tail -f storage/logs/laravel.log"
