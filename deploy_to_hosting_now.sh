#!/bin/bash
# DEPLOY TO HOSTING SCRIPT
# This script will deploy the latest code and clear all caches

echo "==================================="
echo "DEPLOYING TO HOSTING"
echo "==================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Pull latest code
echo -e "${YELLOW}Step 1: Pulling latest code from GitHub...${NC}"
cd /var/www/html

# Fix git ownership
git config --global --add safe.directory /var/www/html

# Pull code
if git pull origin main; then
    echo -e "${GREEN}✅ Code pulled successfully!${NC}"
else
    echo -e "${RED}❌ Failed to pull code!${NC}"
    echo "Trying with sudo..."
    if sudo git pull origin main; then
        echo -e "${GREEN}✅ Code pulled successfully with sudo!${NC}"
    else
        echo -e "${RED}❌ Still failed. Please check manually.${NC}"
        exit 1
    fi
fi

echo ""

# Step 2: Clear all caches
echo -e "${YELLOW}Step 2: Clearing all Laravel caches...${NC}"

php artisan cache:clear && echo -e "${GREEN}✅ Application cache cleared${NC}"
php artisan config:clear && echo -e "${GREEN}✅ Configuration cache cleared${NC}"
php artisan route:clear && echo -e "${GREEN}✅ Route cache cleared${NC}"
php artisan view:clear && echo -e "${GREEN}✅ View cache cleared${NC}"

# Clear compiled views manually
echo -e "${YELLOW}Clearing compiled views manually...${NC}"
rm -rf storage/framework/views/*
echo -e "${GREEN}✅ Compiled views cleared${NC}"

echo ""

# Step 3: Set permissions
echo -e "${YELLOW}Step 3: Setting permissions...${NC}"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✅ Permissions set${NC}"

echo ""

# Step 4: Verify deployment
echo -e "${YELLOW}Step 4: Verifying deployment...${NC}"

# Check if critical files have user_id filter
FILES=(
    "app/Http/Controllers/CoaController.php"
    "app/Http/Controllers/VendorController.php"
    "app/Http/Controllers/PembelianController.php"
    "app/Http/Controllers/PenjualanController.php"
    "app/Http/Controllers/LaporanController.php"
    "app/Http/Controllers/ProduksiController.php"
    "app/Http/Controllers/PresensiController.php"
    "app/Http/Controllers/PenggajianController.php"
    "app/Http/Controllers/ExpensePaymentController.php"
    "app/Http/Controllers/PelunasanUtangController.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        if grep -q "where('user_id', auth()->id())" "$file"; then
            echo -e "${GREEN}✅ $(basename $file) - HAS user_id filter${NC}"
        else
            echo -e "${YELLOW}⚠️  $(basename $file) - NO user_id filter (might be OK)${NC}"
        fi
    else
        echo -e "${RED}❌ $(basename $file) - FILE NOT FOUND${NC}"
    fi
done

echo ""
echo -e "${GREEN}==================================="
echo "DEPLOYMENT COMPLETE!"
echo "===================================${NC}"
echo ""
echo "NEXT STEPS:"
echo "1. Test COA edit page"
echo "2. Test transaksi pembelian"
echo "3. Test transaksi penjualan"
echo "4. Test transaksi produksi"
echo "5. Test transaksi presensi"
echo "6. Test transaksi penggajian"
echo "7. Test all laporan pages"
echo ""
echo "Test URL: http://jobcost.eadtmanufaktur.com"
