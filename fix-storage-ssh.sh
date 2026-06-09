#!/bin/bash

# =========================================
# Script: Fix Storage Symlink & Permissions
# Purpose: Memperbaiki masalah foto produk tidak muncul
# =========================================

echo "========================================="
echo "🔧 Fix Storage Symlink & Permissions"
echo "========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Cek apakah di root folder Laravel
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: File 'artisan' tidak ditemukan!${NC}"
    echo "Pastikan Anda menjalankan script ini di root folder Laravel"
    echo "Gunakan: cd /path/to/your/laravel/project"
    exit 1
fi

echo -e "${GREEN}✅ Folder Laravel terdeteksi${NC}"
echo "Current directory: $(pwd)"
echo ""

# Step 1: Hapus symlink lama
echo "Step 1: Menghapus symlink lama..."
if [ -L "public/storage" ] || [ -d "public/storage" ]; then
    rm -rf public/storage
    echo -e "${GREEN}✅ Symlink lama dihapus${NC}"
else
    echo -e "${YELLOW}⚠️  Tidak ada symlink lama${NC}"
fi
echo ""

# Step 2: Buat folder storage/app/public jika belum ada
echo "Step 2: Memastikan folder storage/app/public ada..."
if [ ! -d "storage/app/public" ]; then
    mkdir -p storage/app/public
    echo -e "${GREEN}✅ Folder storage/app/public dibuat${NC}"
else
    echo -e "${GREEN}✅ Folder storage/app/public sudah ada${NC}"
fi

# Buat subfolder produk jika belum ada
if [ ! -d "storage/app/public/produk" ]; then
    mkdir -p storage/app/public/produk
    echo -e "${GREEN}✅ Folder storage/app/public/produk dibuat${NC}"
fi
echo ""

# Step 3: Buat symlink baru
echo "Step 3: Membuat symlink baru..."
php artisan storage:link
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Symlink berhasil dibuat${NC}"
else
    echo -e "${RED}❌ Gagal membuat symlink via artisan${NC}"
    echo "Mencoba cara alternatif..."
    ln -s ../storage/app/public public/storage
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Symlink berhasil dibuat (manual)${NC}"
    else
        echo -e "${RED}❌ Gagal membuat symlink${NC}"
        exit 1
    fi
fi
echo ""

# Step 4: Verifikasi symlink
echo "Step 4: Verifikasi symlink..."
if [ -L "public/storage" ]; then
    LINK_TARGET=$(readlink public/storage)
    echo -e "${GREEN}✅ Symlink valid${NC}"
    echo "   public/storage → $LINK_TARGET"
else
    echo -e "${RED}❌ Symlink tidak valid${NC}"
    exit 1
fi
echo ""

# Step 5: Set permissions
echo "Step 5: Mengatur permissions..."
echo "Mendeteksi user web server..."

# Deteksi web server user
WEB_USER=""
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    echo -e "${YELLOW}⚠️  User web server tidak terdeteksi${NC}"
    echo "Akan menggunakan user saat ini: $(whoami)"
    WEB_USER=$(whoami)
fi

echo "Web server user: $WEB_USER"
echo ""

# Cek apakah perlu sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Script tidak dijalankan sebagai root${NC}"
    echo "Mencoba dengan sudo..."
    USE_SUDO="sudo"
else
    USE_SUDO=""
fi

# Set owner
echo "Mengatur owner..."
$USE_SUDO chown -R $WEB_USER:$WEB_USER storage public/storage 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Owner diatur ke $WEB_USER${NC}"
else
    echo -e "${YELLOW}⚠️  Gagal mengatur owner (mungkin tidak punya akses sudo)${NC}"
fi

# Set permissions
echo "Mengatur permissions..."
chmod -R 775 storage
chmod -R 775 public/storage 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Permissions diatur ke 775${NC}"
else
    echo -e "${YELLOW}⚠️  Gagal mengatur permissions${NC}"
fi
echo ""

# Step 6: Clear cache
echo "Step 6: Clear cache Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo -e "${GREEN}✅ Cache cleared${NC}"
echo ""

# Step 7: Verifikasi final
echo "========================================="
echo "📊 VERIFIKASI FINAL"
echo "========================================="
echo ""

echo "1. Struktur folder:"
echo "   storage/app/public:"
ls -la storage/app/public/ 2>/dev/null | head -10
echo ""

echo "2. Symlink public/storage:"
ls -la public/storage 2>/dev/null
echo ""

echo "3. Permissions storage:"
ls -ld storage
echo ""

echo "4. Permissions public/storage:"
ls -ld public/storage 2>/dev/null
echo ""

# Step 8: Test file
echo "========================================="
echo "🧪 TEST"
echo "========================================="
echo ""

# Buat test file
TEST_FILE="storage/app/public/test-$(date +%s).txt"
echo "Test file created at $(date)" > $TEST_FILE
if [ -f "$TEST_FILE" ]; then
    echo -e "${GREEN}✅ Berhasil membuat test file${NC}"
    echo "   Location: $TEST_FILE"
    
    # Cek apakah bisa diakses via public/storage
    TEST_FILE_NAME=$(basename $TEST_FILE)
    if [ -f "public/storage/$TEST_FILE_NAME" ]; then
        echo -e "${GREEN}✅ Test file accessible via symlink${NC}"
        rm $TEST_FILE
        echo "   Test file dihapus"
    else
        echo -e "${RED}❌ Test file TIDAK accessible via symlink${NC}"
    fi
else
    echo -e "${RED}❌ Gagal membuat test file${NC}"
fi
echo ""

# Final message
echo "========================================="
echo "✨ SELESAI!"
echo "========================================="
echo ""
echo "Langkah selanjutnya:"
echo "1. Test di browser: http://your-domain.com/storage/"
echo "2. Upload foto produk baru via web interface"
echo "3. Cek apakah foto muncul di halaman Daftar Produk"
echo ""
echo -e "${YELLOW}⚠️  CATATAN:${NC}"
echo "- Jika foto LOKAL belum ada di server, upload manual via SCP/SFTP"
echo "- Folder: storage/app/public/produk/"
echo "- Atau upload via web interface (Master Data → Produk → Edit)"
echo ""
echo "Dokumentasi lengkap: .kiro/FIX_FOTO_PRODUK_SSH.md"
echo ""
