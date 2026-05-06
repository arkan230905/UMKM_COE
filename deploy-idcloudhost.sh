#!/bin/bash

# ============================================================
# SCRIPT DEPLOYMENT OTOMATIS UNTUK IDCLOUDHOST
# Repository: https://github.com/arkan230905/UMKM_COE.git
# ============================================================

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                                                               ║"
echo "║  🚀 DEPLOYMENT KE IDCLOUDHOST                                ║"
echo "║                                                               ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================
# STEP 1: CEK LOKASI
# ============================================================
echo -e "${BLUE}📍 Step 1: Checking current location...${NC}"
echo "Current directory: $(pwd)"
echo ""

# ============================================================
# STEP 2: GIT PULL
# ============================================================
echo -e "${BLUE}📦 Step 2: Pulling latest changes from GitHub...${NC}"

# Stash local changes jika ada
if [[ -n $(git status -s) ]]; then
    echo -e "${YELLOW}⚠️  Local changes detected. Stashing...${NC}"
    git stash
fi

# Pull dari GitHub
git pull origin main

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Git pull successful${NC}"
else
    echo -e "${RED}❌ Git pull failed. Please check your connection.${NC}"
    exit 1
fi

echo ""

# ============================================================
# STEP 3: CLEAR CACHE
# ============================================================
echo -e "${BLUE}🧹 Step 3: Clearing Laravel cache...${NC}"

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo -e "${GREEN}✅ All caches cleared${NC}"
echo ""

# ============================================================
# STEP 4: FIX PERMISSION
# ============================================================
echo -e "${BLUE}🔐 Step 4: Fixing permissions...${NC}"

chmod -R 755 storage bootstrap/cache
chmod 644 public/css/modern-dashboard.css
chmod 644 resources/views/layouts/*.blade.php
chmod 644 resources/views/dashboard.blade.php
chmod 644 app/Http/Controllers/DashboardController.php

echo -e "${GREEN}✅ Permissions fixed${NC}"
echo ""

# ============================================================
# STEP 5: VERIFY FILES
# ============================================================
echo -e "${BLUE}🔍 Step 5: Verifying files...${NC}"

FILES=(
    "public/css/modern-dashboard.css"
    "resources/views/layouts/app.blade.php"
    "resources/views/layouts/sidebar.blade.php"
    "resources/views/dashboard.blade.php"
    "app/Http/Controllers/DashboardController.php"
)

ALL_OK=true

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        SIZE=$(stat -c%s "$file" 2>/dev/null || stat -f%z "$file" 2>/dev/null)
        echo -e "${GREEN}✅${NC} $file (${SIZE} bytes)"
    else
        echo -e "${RED}❌${NC} $file - NOT FOUND!"
        ALL_OK=false
    fi
done

echo ""

if [ "$ALL_OK" = false ]; then
    echo -e "${RED}❌ Some files are missing!${NC}"
    exit 1
fi

# ============================================================
# STEP 6: SUMMARY
# ============================================================
echo "═══════════════════════════════════════════════════════════════"
echo -e "${GREEN}✅ DEPLOYMENT COMPLETED SUCCESSFULLY!${NC}"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "📋 Next steps:"
echo "   1. Clear browser cache (Ctrl + Shift + Delete)"
echo "   2. Hard refresh (Ctrl + F5)"
echo "   3. Access: https://your-domain.com/dashboard"
echo ""
echo "🎯 Expected result:"
echo "   ✓ Brown sidebar (#8A6B48)"
echo "   ✓ Gray background (#F4F6F9)"
echo "   ✓ Colored KPI cards"
echo "   ✓ Sales chart"
echo "   ✓ Collapsible menu"
echo "   ✓ NO raw Blade code (@extends, {{ }})"
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo -e "${GREEN}🎉 DEPLOYMENT SUCCESSFUL!${NC}"
echo ""
