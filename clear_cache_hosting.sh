#!/bin/bash

# Script untuk clear cache di hosting setelah deploy
# Jalankan via SSH: bash clear_cache_hosting.sh

echo "🧹 Clearing Laravel cache..."

# Clear all cache
php artisan cache:clear
echo "✅ Cache cleared"

php artisan config:clear
echo "✅ Config cache cleared"

php artisan route:clear
echo "✅ Route cache cleared"

php artisan view:clear
echo "✅ View cache cleared"

php artisan optimize:clear
echo "✅ Optimize cache cleared"

# Regenerate autoload
echo ""
echo "🔄 Regenerating autoload..."
composer dump-autoload -o
echo "✅ Autoload regenerated"

# Check if DefaultCoaSeederBaru exists
echo ""
echo "🔍 Checking DefaultCoaSeederBaru file..."
if [ -f "database/seeders/DefaultCoaSeederBaru.php" ]; then
    echo "✅ DefaultCoaSeederBaru.php found!"
    ls -lh database/seeders/DefaultCoaSeederBaru.php
else
    echo "❌ DefaultCoaSeederBaru.php NOT FOUND!"
    echo "Please check Jenkins deployment"
fi

# Check CreateDefaultUserData listener
echo ""
echo "🔍 Checking CreateDefaultUserData listener..."
if grep -q "DefaultCoaSeederBaru" app/Listeners/CreateDefaultUserData.php; then
    echo "✅ Listener using DefaultCoaSeederBaru"
else
    echo "❌ Listener NOT using DefaultCoaSeederBaru"
    echo "Please check Jenkins deployment"
fi

echo ""
echo "🎉 Done! Now test the website:"
echo "   http://jobcost.eadtmanufaktur.com/master-data/coa"
