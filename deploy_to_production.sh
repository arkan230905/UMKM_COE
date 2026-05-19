#!/bin/bash

# ============================================
# Script Deploy ke Production Server
# ============================================

echo "=========================================="
echo "  DEPLOY MIGRATIONS TO PRODUCTION"
echo "=========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Backup database
echo -e "${YELLOW}Step 1: Backup database...${NC}"
php artisan db:backup 2>/dev/null || echo "Backup command not available, please backup manually via phpMyAdmin"
echo ""

# Step 2: Pull latest code
echo -e "${YELLOW}Step 2: Pulling latest code from GitHub...${NC}"
git fetch origin
git pull origin nayla
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Code pulled successfully${NC}"
else
    echo -e "${RED}✗ Failed to pull code${NC}"
    exit 1
fi
echo ""

# Step 3: Check migration files
echo -e "${YELLOW}Step 3: Checking migration files...${NC}"
MIGRATIONS=(
    "2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php"
    "2026_05_19_000002_add_bank_id_to_pembelians_table.php"
    "2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php"
    "2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php"
    "2026_05_19_000005_force_add_financial_columns_to_pembelians.php"
    "2026_05_19_000006_add_missing_columns_to_stock_movements.php"
    "2026_05_19_000007_fix_coa_pelunasan_foreign_key_in_pelunasan_utangs.php"
    "2026_05_19_000008_fix_all_accounts_foreign_keys_to_coas.php"
)

for migration in "${MIGRATIONS[@]}"; do
    if [ -f "database/migrations/$migration" ]; then
        echo -e "${GREEN}✓${NC} $migration"
    else
        echo -e "${RED}✗${NC} $migration ${RED}(MISSING!)${NC}"
    fi
done
echo ""

# Step 4: Run migrations
echo -e "${YELLOW}Step 4: Running migrations...${NC}"
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations completed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi
echo ""

# Step 5: Clear cache
echo -e "${YELLOW}Step 5: Clearing cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo -e "${GREEN}✓ Cache cleared${NC}"
echo ""

# Step 6: Verify database structure
echo -e "${YELLOW}Step 6: Verifying database structure...${NC}"
echo "Checking pembelians table..."
php artisan tinker --execute="
\$columns = Schema::getColumnListing('pembelians');
\$required = ['bank_id', 'subtotal', 'total_harga', 'status'];
\$missing = array_diff(\$required, \$columns);
if (empty(\$missing)) {
    echo '✓ pembelians table: OK\n';
} else {
    echo '✗ pembelians table missing: ' . implode(', ', \$missing) . '\n';
}
"

echo "Checking pelunasan_utangs foreign keys..."
php artisan tinker --execute="
\$fks = DB::select(\"
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'pelunasan_utangs'
        AND REFERENCED_TABLE_NAME IS NOT NULL
\");
foreach (\$fks as \$fk) {
    if (\$fk->REFERENCED_TABLE_NAME === 'coas') {
        echo '✓ ' . \$fk->COLUMN_NAME . ' -> coas (CORRECT)\n';
    } else {
        echo '✗ ' . \$fk->COLUMN_NAME . ' -> ' . \$fk->REFERENCED_TABLE_NAME . ' (WRONG!)\n';
    }
}
"
echo ""

# Step 7: Final message
echo "=========================================="
echo -e "${GREEN}  DEPLOYMENT COMPLETED!${NC}"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Test the application: http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create"
echo "2. If there are errors, check logs: storage/logs/laravel.log"
echo "3. Delete this script for security: rm deploy_to_production.sh"
echo ""
