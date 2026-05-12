#!/bin/bash

# ============================================================
# SCRIPT DEPLOYMENT OTOMATIS KE HOSTING
# Desain Modern Dashboard - Commit d419314
# ============================================================

echo "🚀 Starting deployment to hosting..."
echo ""

# Warna untuk output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# ============================================================
# STEP 1: VERIFIKASI FILE LOCAL
# ============================================================
echo "📋 Step 1: Verifying local files..."

FILES=(
    "public/css/modern-dashboard.css"
    "resources/views/layouts/app.blade.php"
    "resources/views/layouts/sidebar.blade.php"
    "resources/views/dashboard.blade.php"
    "app/Http/Controllers/DashboardController.php"
)

ALL_FILES_OK=true

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        SIZE=$(stat -c%s "$file" 2>/dev/null || stat -f%z "$file" 2>/dev/null)
        echo -e "${GREEN}✅${NC} $file (${SIZE} bytes)"
    else
        echo -e "${RED}❌${NC} $file - FILE NOT FOUND!"
        ALL_FILES_OK=false
    fi
done

if [ "$ALL_FILES_OK" = false ]; then
    echo -e "${RED}❌ Some files are missing. Please check!${NC}"
    exit 1
fi

echo ""

# ============================================================
# STEP 2: CLEAR LOCAL CACHE
# ============================================================
echo "🧹 Step 2: Clearing local cache..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo -e "${GREEN}✅ Local cache cleared${NC}"
echo ""

# ============================================================
# STEP 3: GIT COMMIT & PUSH (OPTIONAL)
# ============================================================
echo "📦 Step 3: Git operations..."
read -p "Do you want to commit and push to Git? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    git add public/css/modern-dashboard.css
    git add resources/views/layouts/app.blade.php
    git add resources/views/layouts/sidebar.blade.php
    git add resources/views/dashboard.blade.php
    git add app/Http/Controllers/DashboardController.php
    
    git commit -m "Deploy: Update desain dashboard modern dari commit d419314"
    git push origin main
    
    echo -e "${GREEN}✅ Git push completed${NC}"
else
    echo -e "${YELLOW}⏭️  Skipping Git operations${NC}"
fi

echo ""

# ============================================================
# STEP 4: INSTRUKSI UNTUK HOSTING
# ============================================================
echo "📝 Step 4: Instructions for hosting deployment"
echo ""
echo "════════════════════════════════════════════════════════"
echo "  MANUAL STEPS FOR HOSTING (via cPanel/FTP)"
echo "════════════════════════════════════════════════════════"
echo ""
echo "METHOD A: Via cPanel File Manager"
echo "─────────────────────────────────────────────────────────"
echo "1. Login to cPanel"
echo "2. Open File Manager"
echo "3. Backup old files (rename with .backup)"
echo "4. Upload these files:"
echo ""

for file in "${FILES[@]}"; do
    echo "   📁 $file"
done

echo ""
echo "5. Set permissions to 644 for all files"
echo "6. Run cache clear commands (see below)"
echo ""
echo "METHOD B: Via Git (if available on hosting)"
echo "─────────────────────────────────────────────────────────"
echo "ssh your-user@your-domain.com"
echo "cd /path/to/your/project"
echo "git pull origin main"
echo "php artisan optimize:clear"
echo ""
echo "════════════════════════════════════════════════════════"
echo "  CACHE CLEAR COMMANDS FOR HOSTING"
echo "════════════════════════════════════════════════════════"
echo ""
echo "Via SSH/Terminal:"
echo "─────────────────────────────────────────────────────────"
echo "php artisan cache:clear"
echo "php artisan config:clear"
echo "php artisan route:clear"
echo "php artisan view:clear"
echo "php artisan optimize:clear"
echo ""
echo "Via Browser (if no SSH access):"
echo "─────────────────────────────────────────────────────────"
echo "1. Upload file: public/clear-cache.php"
echo "2. Access: https://your-domain.com/clear-cache.php"
echo "3. Delete the file after use!"
echo ""
echo "════════════════════════════════════════════════════════"
echo "  VERIFICATION STEPS"
echo "════════════════════════════════════════════════════════"
echo ""
echo "1. Test CSS loading:"
echo "   https://your-domain.com/css/modern-dashboard.css"
echo "   ✅ Should show: CSS code"
echo ""
echo "2. Test dashboard:"
echo "   https://your-domain.com/dashboard"
echo "   ✅ Should show:"
echo "      - Brown sidebar (#8A6B48)"
echo "      - Gray background (#F4F6F9)"
echo "      - Colored KPI cards"
echo "      - Sales chart"
echo "      - NO raw Blade code (@extends, {{ }})"
echo ""
echo "3. Clear browser cache:"
echo "   - Press Ctrl + Shift + Delete"
echo "   - Press Ctrl + F5 (hard refresh)"
echo "   - Test in Incognito mode"
echo ""
echo "════════════════════════════════════════════════════════"
echo ""
echo -e "${GREEN}✅ Deployment preparation completed!${NC}"
echo ""
echo "📚 For detailed instructions, see:"
echo "   - DEPLOY_KE_HOSTING.md"
echo "   - FILE_UPLOAD_CHECKLIST.txt"
echo ""
echo "🎉 Good luck with your deployment!"
