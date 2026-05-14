# Fresh Database Setup Guide

## 🎯 Untuk Database Baru yang Kosong

Anda sudah drop database dan buat ulang. Sekarang ikuti langkah ini:

---

## ✅ Step-by-Step Setup

### 1. Pastikan Database Kosong
```sql
-- Di MySQL/phpMyAdmin
DROP DATABASE IF EXISTS eadt_umkm;
CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eadt_umkm;
SHOW TABLES;  -- Should show: Empty set
```

### 2. Check .env Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Clear All Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 4. Run ALL Migrations
```bash
php artisan migrate
```

**Expected output:**
```
INFO  Running migrations.

2014_10_12_000000_create_users_table ........................ DONE
2014_10_12_100000_create_password_reset_tokens_table ........ DONE
2019_08_19_000000_create_failed_jobs_table .................. DONE
2019_12_14_000001_create_personal_access_tokens_table ....... DONE
...
2025_12_09_000002_create_komponen_bops_table ................ DONE
...
(many more migrations)
```

### 5. Verify Database Structure
```bash
php verify_database_structure.php
```

**Expected output:**
```
=== DATABASE STRUCTURE VERIFICATION ===

Checking 30 required tables...

✅ users
   ✓ All required columns present
✅ roles
   ✓ All required columns present
✅ komponen_bops
   ✓ All required columns present
...

=== SUMMARY ===
Total tables checked: 30
✅ Tables exist: 30
❌ Tables missing: 0
⚠️  Tables with column issues: 0

=== FINAL STATUS ===
✅ DATABASE STRUCTURE IS CORRECT!
```

### 6. (Optional) Seed Initial Data
```bash
# Seed required COAs for production
php artisan db:seed --class=RequiredProductionCoasSeeder

# Or seed all
php artisan db:seed
```

### 7. Create First User
```bash
php artisan tinker
```

```php
// Create admin user
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password');
$user->save();

// Assign admin role
$adminRole = App\Models\Role::where('name', 'admin')->first();
if ($adminRole) {
    $user->roles()->attach($adminRole->id);
}

echo "User created with ID: " . $user->id;
exit
```

### 8. Start Server
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

---

## 🔍 Verification Checklist

### Database Tables
Run: `php verify_database_structure.php`

- [ ] All 30+ required tables exist
- [ ] All tables have `user_id` column (multi-tenant)
- [ ] No missing columns
- [ ] Foreign keys properly set

### Critical Tables
- [ ] `users` - Authentication
- [ ] `roles` & `role_user` - Authorization
- [ ] `produks` - Products with `coa_persediaan_id`
- [ ] `bahan_bakus` - Raw materials
- [ ] `komponen_bops` - BOP components ⚠️
- [ ] `proses_produksis` - Production processes
- [ ] `biaya_bahan_baku` - Material costs
- [ ] `jurnal_umum` - Journal entries
- [ ] `coas` - Chart of accounts
- [ ] `produksis` - Production records

### Multi-Tenant Check
All these tables should have `user_id`:
- [ ] produks
- [ ] bahan_bakus
- [ ] komponen_bops
- [ ] biaya_bahan_baku
- [ ] jurnal_umum
- [ ] produksis
- [ ] coas

### Test Access
- [ ] Login page works
- [ ] Dashboard loads
- [ ] Master Data > Produk works
- [ ] Master Data > Bahan Baku works
- [ ] Master Data > BTKL works ⚠️ (This was the error)
- [ ] Master Data > BOP works
- [ ] Produksi works
- [ ] Akuntansi > Jurnal Umum works

---

## 🚨 Common Issues & Solutions

### Issue 1: Migration Fails
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solution:**
```bash
php artisan migrate:fresh  # WARNING: Deletes all data!
```

### Issue 2: Foreign Key Constraint Fails
```
SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint
```

**Solution:**
- Check parent table exists first
- Check column types match
- Run migrations in correct order

### Issue 3: Table Missing After Migration
```
Table 'komponen_bops' doesn't exist
```

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# If migration shows "Ran" but table missing:
php fix_missing_komponen_bops_table.php
```

### Issue 4: Permission Denied
```
Access denied for user 'root'@'localhost'
```

**Solution:**
- Check `.env` DB_PASSWORD
- Check MySQL user permissions
- Try: `GRANT ALL PRIVILEGES ON eadt_umkm.* TO 'root'@'localhost';`

---

## 📊 Expected Database Structure

### Total Tables: ~40-50 tables

**Categories:**
1. **Core (5):** users, roles, role_user, password_resets, failed_jobs
2. **Master Data (10):** produks, bahan_bakus, bahan_pendukungs, satuans, coas, vendors, kategoris, etc.
3. **Production (8):** komponen_bops, proses_produksis, bop_proses, biaya_bahan_baku, produksis, produksi_details, etc.
4. **Accounting (5):** jurnal_umum, pembelians, penjualans, pembelian_details, penjualan_details
5. **HPP (3):** harga_pokok_produksi_biaya_bahan_baku, harga_pokok_produksi_btkl, harga_pokok_produksi_bop
6. **Stock (2):** stock_movements, stock_layers
7. **Others:** migrations, sessions, cache, etc.

---

## 🎯 Success Criteria

✅ **Database is ready when:**
1. All migrations run successfully
2. `verify_database_structure.php` shows no errors
3. All critical tables exist
4. All tables have `user_id` column
5. Can login and access all pages
6. No "Table doesn't exist" errors

---

## 📝 Quick Commands

```bash
# Full fresh setup
php artisan migrate:fresh
php artisan db:seed --class=RequiredProductionCoasSeeder

# Verify structure
php verify_database_structure.php

# Check specific table
php artisan tinker --execute="echo Schema::hasTable('komponen_bops') ? '✅ OK' : '❌ Missing';"

# List all tables
php artisan tinker --execute="print_r(DB::select('SHOW TABLES'));"

# Count tables
php artisan tinker --execute="echo count(DB::select('SHOW TABLES')) . ' tables';"
```

---

## 🔄 If You Need to Start Over

```bash
# 1. Drop database
mysql -u root -p -e "DROP DATABASE IF EXISTS eadt_umkm;"

# 2. Create database
mysql -u root -p -e "CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Clear cache
php artisan config:clear

# 4. Run migrations
php artisan migrate

# 5. Verify
php verify_database_structure.php
```

---

**Last Updated:** 2026-05-06
**Status:** Ready for fresh setup
