#!/bin/bash

# ========================================
# Production Deployment Script
# Created: 2026-06-16
# ========================================

echo "========================================="
echo "🚀 PRODUCTION DEPLOYMENT SCRIPT"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration - UPDATE THESE!
PROJECT_PATH="/path/to/production"  # UPDATE THIS
DB_USER="your_db_user"               # UPDATE THIS
DB_NAME="your_db_name"               # UPDATE THIS
PHP_VERSION="8.2"                    # UPDATE THIS (8.1, 8.2, etc)

echo -e "${YELLOW}⚠️  BEFORE RUNNING THIS SCRIPT:${NC}"
echo "1. Update configuration variables above"
echo "2. Make sure you have sudo access"
echo "3. Ensure all files are uploaded to production"
echo ""
read -p "Have you updated the configuration? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo -e "${RED}❌ Please update configuration first!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Starting deployment...${NC}"
echo ""

# Step 1: Backup
echo "📦 Step 1: Creating backups..."
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

cd $PROJECT_PATH || exit

# Backup database
echo "   Backing up database..."
mysqldump -u $DB_USER -p $DB_NAME > backup_db_$TIMESTAMP.sql
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} Database backup created: backup_db_$TIMESTAMP.sql"
else
    echo -e "   ${RED}✗${NC} Database backup failed!"
    exit 1
fi

# Backup views
echo "   Backing up views..."
tar -czf backup_views_$TIMESTAMP.tar.gz resources/views/
echo -e "   ${GREEN}✓${NC} Views backup created"

echo ""

# Step 2: Maintenance Mode
echo "🔧 Step 2: Enabling maintenance mode..."
php artisan down --message="Sistem sedang maintenance, akan kembali dalam 10 menit"
echo -e "   ${GREEN}✓${NC} Maintenance mode enabled"
echo ""

# Step 3: Run Migration
echo "🗄️  Step 3: Running database migration..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} Migration completed successfully"
else
    echo -e "   ${RED}✗${NC} Migration failed!"
    echo "   Rolling back..."
    php artisan up
    exit 1
fi
echo ""

# Step 4: Clear Caches
echo "🧹 Step 4: Clearing all caches..."
php artisan config:clear
echo -e "   ${GREEN}✓${NC} Config cache cleared"

php artisan cache:clear
echo -e "   ${GREEN}✓${NC} Application cache cleared"

php artisan view:clear
echo -e "   ${GREEN}✓${NC} View cache cleared"

php artisan route:clear
echo -e "   ${GREEN}✓${NC} Route cache cleared"

php artisan optimize:clear
echo -e "   ${GREEN}✓${NC} Optimization cleared"

# Delete compiled views manually
rm -rf storage/framework/views/*
echo -e "   ${GREEN}✓${NC} Compiled views deleted"

# Rebuild
php artisan optimize
echo -e "   ${GREEN}✓${NC} Application optimized"
echo ""

# Step 5: Fix Permissions
echo "🔐 Step 5: Fixing permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
chmod -R 777 storage/framework/views
chown -R www-data:www-data storage bootstrap/cache
echo -e "   ${GREEN}✓${NC} Permissions fixed"
echo ""

# Step 6: Restart Services
echo "🔄 Step 6: Restarting services..."
echo "   Restarting PHP-FPM..."
sudo systemctl restart php${PHP_VERSION}-fpm
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} PHP-FPM restarted"
else
    echo -e "   ${YELLOW}⚠${NC}  PHP-FPM restart may have failed, check manually"
fi

echo "   Restarting Apache..."
sudo systemctl restart apache2
if [ $? -eq 0 ]; then
    echo -e "   ${GREEN}✓${NC} Apache restarted"
else
    echo -e "   ${YELLOW}⚠${NC}  Apache restart may have failed, trying httpd..."
    sudo systemctl restart httpd
fi
echo ""

# Step 7: Disable Maintenance Mode
echo "✨ Step 7: Disabling maintenance mode..."
php artisan up
echo -e "   ${GREEN}✓${NC} Site is now live!"
echo ""

# Verification
echo "========================================="
echo "🎯 DEPLOYMENT COMPLETED!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Verify vendor constraint:"
echo "   php artisan tinker"
echo '   DB::select("SHOW INDEX FROM vendors WHERE Key_name LIKE '\''%unique%'\''");'
echo ""
echo "2. Test in browser:"
echo "   - Test vendor creation (same name, different kategori)"
echo "   - Check pembelian form (accordion collapsed?)"
echo "   - Test PDF export"
echo "   - Clear browser cache: Ctrl+Shift+R"
echo ""
echo "3. Check logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo -e "${GREEN}✅ All deployment steps completed successfully!${NC}"
echo ""
echo "Backups created:"
echo "   - Database: backup_db_$TIMESTAMP.sql"
echo "   - Views: backup_views_$TIMESTAMP.tar.gz"
echo ""
