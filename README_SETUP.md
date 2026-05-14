# UMKM COE - Setup Instructions

## 🚀 Quick Start (Untuk Clone Baru)

### 1. Clone & Install
```bash
git clone <repository-url>
cd UMKM_COE
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env`:
```env
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create Database
```sql
CREATE DATABASE eadt_umkm;
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. (Optional) Seed Data
```bash
php artisan db:seed --class=RequiredProductionCoasSeeder
```

### 7. Start Server
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

---

## ⚠️ Common Errors & Solutions

### Error: Table 'komponen_bops' doesn't exist

**Quick Fix (Windows):**
```bash
quick_fix_komponen_bops.bat
```

**Quick Fix (Linux/Mac):**
```bash
chmod +x quick_fix_komponen_bops.sh
./quick_fix_komponen_bops.sh
```

**Manual Fix:**
```bash
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

### Error: Migration already ran

```bash
php artisan migrate:status  # Check status
php artisan migrate:fresh   # Reset (WARNING: Deletes all data!)
```

### Error: Access denied for user

Check `.env` database credentials:
```env
DB_USERNAME=root
DB_PASSWORD=your_actual_password
```

---

## 📋 Required Tables

After migration, these tables should exist:

**Core:**
- users, roles, role_user

**Master Data:**
- produks, bahan_bakus, bahan_pendukungs
- satuans, coas, vendors, kategoris

**Production:**
- komponen_bops ⚠️
- proses_produksis, bop_proses
- biaya_bahan_baku
- produksis, produksi_details

**Accounting:**
- jurnal_umum, pembelians, penjualans

**HPP:**
- harga_pokok_produksi_biaya_bahan_baku
- harga_pokok_produksi_btkl
- harga_pokok_produksi_bop

---

## 🔧 Verify Setup

```bash
# Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected!';"

# Check tables
php artisan tinker --execute="echo count(DB::select('SHOW TABLES')) . ' tables found';"

# Check komponen_bops
php artisan tinker --execute="echo Schema::hasTable('komponen_bops') ? 'OK' : 'Missing';"
```

---

## 👥 For Team Members

### After Pull
```bash
git pull
composer install  # If composer.json changed
php artisan migrate  # If new migrations
php artisan config:clear
```

### Before Push
```bash
# Don't commit:
- .env
- /vendor/
- /node_modules/
- /storage/*.key
- /public/storage (symlink)
```

---

## 📚 Documentation

- **Setup Guide:** `SETUP_GUIDE_FOR_NEW_CLONE.md`
- **Production COA Fix:** `PRODUCTION_COA_FIX_DOCUMENTATION.md`
- **Biaya Bahan Verification:** `BIAYA_BAHAN_BAKU_VERIFICATION.md`
- **Neraca Saldo Fix:** `RINGKASAN_PERBAIKAN.md`

---

## 🆘 Need Help?

1. Check `storage/logs/laravel.log`
2. Run `php artisan migrate:status`
3. Check database: `SHOW TABLES;`
4. Clear cache: `php artisan config:clear`

---

## 📝 Tech Stack

- **Framework:** Laravel 12.56.0
- **PHP:** 8.4.16
- **Database:** MySQL
- **Frontend:** Blade, Bootstrap, Livewire

---

**Last Updated:** 2026-05-06
