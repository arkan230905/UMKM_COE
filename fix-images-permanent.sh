#!/bin/bash

# Fix Laravel Storage Images - Permanent Solution
echo "=== Fixing Laravel Storage Images ==="

# 1. Create storage link (if not exists)
echo "Creating storage symbolic link..."
php artisan storage:link

# 2. Set correct permissions for storage directories
echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 public/storage
chown -R www-data:www-data storage
chown -R www-data:www-data public/storage

# 3. Create required directories if not exist
mkdir -p storage/app/public/produk
mkdir -p storage/app/public/uploads
mkdir -p storage/app/public/images
chmod -R 775 storage/app/public

# 4. Verify symbolic link
echo "Verifying symbolic link..."
if [ -L "public/storage" ]; then
    echo "✅ Symbolic link exists: public/storage -> $(readlink -f public/storage)"
else
    echo "❌ Symbolic link MISSING! Creating..."
    ln -s "$(pwd)/storage/app/public" "$(pwd)/public/storage"
fi

# 5. Test image access
echo "Testing image access..."
TEST_FILE="storage/app/public/produk/test.txt"
echo "test" > "$TEST_FILE"
if [ -f "public/storage/produk/test.txt" ]; then
    echo "✅ Storage link working!"
    rm "$TEST_FILE"
else
    echo "❌ Storage link NOT working!"
fi

echo ""
echo "=== FIX COMPLETE ==="
echo ""
echo "📌 IMPORTANT:"
echo "1. Images must be stored in: storage/app/public/produk/"
echo "2. Access via URL: /storage/produk/filename.jpg"
echo "3. Use Storage::url('produk/filename.jpg') in code"
echo ""
