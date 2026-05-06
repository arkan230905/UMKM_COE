#!/bin/bash

echo "=== QUICK FIX: komponen_bops Table Missing ==="
echo ""

echo "Step 1: Checking migration status..."
php artisan migrate:status

echo ""
echo "Step 2: Running pending migrations..."
php artisan migrate

echo ""
echo "Step 3: Verifying komponen_bops table exists..."
php artisan tinker --execute="echo Schema::hasTable('komponen_bops') ? '✅ Table exists' : '❌ Table missing'; echo PHP_EOL;"

echo ""
echo "Step 4: Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "=== FIX COMPLETED ==="
echo "Please refresh your browser and try again."
echo ""
echo "If still error, run:"
echo "  php artisan migrate --path=/database/migrations/2025_12_09_000002_create_komponen_bops_table.php"
