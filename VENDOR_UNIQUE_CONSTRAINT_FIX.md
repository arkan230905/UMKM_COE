# Fix Vendor Unique Constraint Issue

## 🐛 Problem

**Issue**: Di production server, tidak bisa membuat vendor dengan nama yang sama meskipun kategorinya berbeda.

**Error**:
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '18-Tel-Mart' for key 'vendors_user_id_nama_vendor_unique'
```

**Root Cause**: 
Database production memiliki unique constraint `(user_id, nama_vendor)` yang **tidak sesuai** dengan kebutuhan bisnis.

## ✅ Expected Behavior

Vendor dengan nama yang sama **BOLEH** dibuat jika kategorinya berbeda:
- ✅ Tel-Mart | Bahan Baku
- ✅ Tel-Mart | Bahan Pendukung
- ✅ Tel-Mart | Aset

Unique constraint seharusnya: `(user_id, nama_vendor, kategori)`

## 🔍 Investigation Results

### Migration Files Found:

1. **2026_05_19_000001_fix_multi_tenant_unique_constraints.php**
   - ❌ Menambahkan: `UNIQUE (user_id, nama_vendor)`
   - Ini yang menyebabkan masalah!

2. **2026_05_24_214408_remove_unique_constraint_from_vendors_nama_vendor.php**
   - Mencoba menghapus constraint di atas
   - ⚠️ Kemungkinan gagal atau belum dijalankan di production

### Localhost vs Production:

| Environment | Unique Constraint | Behavior |
|-------------|-------------------|----------|
| **Localhost** | ❌ Tidak ada | Vendor nama sama bisa dibuat |
| **Production** | ✅ Ada: `(user_id, nama_vendor)` | Error saat vendor nama sama |

## 🛠️ Solution

### 1. Migration File

File baru sudah dibuat:
```
database/migrations/2026_06_11_000001_fix_vendors_unique_constraint_with_kategori.php
```

**What it does:**
- ❌ Drop old constraint: `vendors_user_id_nama_vendor_unique`
- ✅ Add new constraint: `vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori)`

### 2. Controller Validation Updated

File: `app/Http/Controllers/VendorController.php`

**Changes:**
- ✅ Added custom validation rule for `nama_vendor`
- ✅ Checks uniqueness based on `(user_id, nama_vendor, kategori)`
- ✅ Applied to both `store()` and `update()` methods

## 📋 Deployment Steps

### For Localhost:

```bash
# 1. Pull latest code
git pull origin main

# 2. Run migration
php artisan migrate

# 3. Verify
php artisan tinker
>>> DB::select("SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%'");
```

**Expected output:**
```
Key_name: vendors_user_id_nama_vendor_kategori_unique
Column_name: user_id, nama_vendor, kategori
```

### For Production Server:

```bash
# 1. Backup database first!
mysqldump -u [user] -p [database_name] > backup_before_vendor_fix_$(date +%Y%m%d).sql

# 2. Pull latest code
git pull origin main

# 3. Put application in maintenance mode
php artisan down

# 4. Run migration
php artisan migrate --force

# 5. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 6. Bring application back up
php artisan up

# 7. Verify constraint
php artisan tinker
>>> DB::select("SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%'");
```

## 🧪 Testing

### Test Case 1: Create vendors with same name, different kategori

```php
// Vendor 1
POST /master-data/vendor
{
    "nama_vendor": "Tel-Mart",
    "kategori": "Bahan Baku",
    "alamat": "Jl. Test 123",
    "no_telp": "081234567890",
    "email": "telmart@example.com"
}
// ✅ Should succeed

// Vendor 2
POST /master-data/vendor
{
    "nama_vendor": "Tel-Mart",
    "kategori": "Bahan Pendukung", // Different kategori
    "alamat": "Jl. Test 123",
    "no_telp": "081234567890",
    "email": "telmart2@example.com"
}
// ✅ Should succeed (THIS WAS FAILING BEFORE)
```

### Test Case 2: Create vendors with same name AND same kategori

```php
// Vendor 1
POST /master-data/vendor
{
    "nama_vendor": "Tel-Mart",
    "kategori": "Bahan Baku",
    "alamat": "Jl. Test 123",
    "no_telp": "081234567890",
    "email": "telmart@example.com"
}
// ✅ Should succeed

// Vendor 2 (duplicate)
POST /master-data/vendor
{
    "nama_vendor": "Tel-Mart",
    "kategori": "Bahan Baku", // Same kategori
    "alamat": "Jl. Test 456",
    "no_telp": "089876543210",
    "email": "telmart3@example.com"
}
// ❌ Should fail with validation error:
// "Vendor dengan nama 'Tel-Mart' dan kategori 'Bahan Baku' sudah ada."
```

## 🔧 Manual SQL (if migration fails)

If the migration fails for any reason, you can run this SQL manually:

```sql
-- Drop old constraint
ALTER TABLE vendors DROP INDEX IF EXISTS vendors_user_id_nama_vendor_unique;
ALTER TABLE vendors DROP INDEX IF EXISTS vendors_nama_vendor_unique;

-- Add new constraint
ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori);

-- Verify
SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%';
```

## 📊 Verification Queries

### Check current constraints:
```sql
SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%';
```

### Check if duplicate vendors exist:
```sql
SELECT user_id, nama_vendor, kategori, COUNT(*) as count
FROM vendors
GROUP BY user_id, nama_vendor, kategori
HAVING count > 1;
```

### Count vendors by user and kategori:
```sql
SELECT user_id, kategori, COUNT(*) as total_vendors
FROM vendors
GROUP BY user_id, kategori
ORDER BY user_id, kategori;
```

## ⚠️ Important Notes

1. **Backup First**: Always backup production database before running migration
2. **Maintenance Mode**: Put application in maintenance mode during migration
3. **Test First**: Test on staging environment if available
4. **Validation**: Both database constraint AND controller validation now enforce the rule
5. **Multi-tenant**: Constraint includes `user_id` for proper multi-tenant isolation

## 📞 Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check MySQL error log
3. Run: `php artisan migrate:status` to see migration status
4. Run: `php artisan tinker` and execute queries above to verify

## ✅ Checklist

- [x] Identified problematic migration files
- [x] Created new migration to fix constraint
- [x] Updated VendorController validation
- [x] Documented deployment steps
- [x] Provided manual SQL fallback
- [x] Created test cases
- [ ] Deploy to localhost (your task)
- [ ] Test on localhost
- [ ] Deploy to production (your task)
- [ ] Test on production
- [ ] Remove old conflicting migrations (optional cleanup)

---

**Created**: 2026-06-11  
**Status**: Ready for deployment
