# Production Deployment: Vendor Constraint Fix

## 🎯 Objective

Fix vendor unique constraint di production server agar vendor dengan nama sama tapi kategori berbeda bisa dibuat.

## 📊 Current State

### Localhost (Working ✅)
- Constraint: `vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori)`
- Behavior: Sukbir mart | Bahan Baku ✅ + Sukbir mart | Bahan Pendukung ✅ = WORKS

### Production (Broken ❌)
- Constraint: `vendors_user_id_nama_vendor_unique (user_id, nama_vendor)`
- Behavior: Error "Duplicate entry '18-Sukbir Mart'"

## 🚀 Deployment Steps

### Option 1: Laravel Migration (Recommended)

```bash
# 1. Backup database first!
mysqldump -u [user] -p [database] > backup_before_vendor_fix_$(date +%Y%m%d_%H%M%S).sql

# 2. Pull latest code
git pull origin main

# 3. Put site in maintenance mode
php artisan down

# 4. Run migration
php artisan migrate --force

# Expected output:
# Running migrations.
# Checking vendors table constraints...
# Found OLD constraint: vendors_user_id_nama_vendor_unique
# Dropping OLD constraint: vendors_user_id_nama_vendor_unique
# ✓ Successfully dropped old constraint
# Adding NEW constraint: (user_id, nama_vendor, kategori)
# ✓ Successfully added new constraint
# === VERIFICATION ===
# Current unique constraints:
#   - vendors_user_id_nama_vendor_kategori_unique: (user_id, nama_vendor, kategori)
# ✅ Migration completed!

# 5. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 6. Restart PHP-FPM / Apache
sudo systemctl restart php8.2-fpm  # or your PHP version
# OR
sudo systemctl restart apache2

# 7. Bring site back online
php artisan up

# 8. Test
# Try creating vendor with same name but different kategori
```

### Option 2: Manual SQL (If migration fails)

```bash
# 1. Backup first
mysqldump -u [user] -p [database] > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Connect to MySQL
mysql -u [user] -p [database]

# 3. Run SQL commands (from FIX_VENDOR_CONSTRAINT_PRODUCTION.sql)
```

```sql
-- Check current state
SHOW INDEX FROM vendors WHERE Non_unique = 0 AND Key_name != 'PRIMARY';

-- Drop old constraint
ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique;

-- Add new constraint
ALTER TABLE vendors 
ADD UNIQUE KEY vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori);

-- Verify
SHOW INDEX FROM vendors WHERE Non_unique = 0 AND Key_name != 'PRIMARY';
```

## ✅ Verification

### 1. Check Database Constraint

```bash
php artisan tinker
```

```php
DB::select("SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%'");
```

**Expected output:**
```
[
  {
    "Key_name": "vendors_user_id_nama_vendor_kategori_unique",
    "Column_name": "user_id",
    ...
  },
  {
    "Key_name": "vendors_user_id_nama_vendor_kategori_unique",
    "Column_name": "nama_vendor",
    ...
  },
  {
    "Key_name": "vendors_user_id_nama_vendor_kategori_unique",
    "Column_name": "kategori",
    ...
  }
]
```

### 2. Test Creating Vendors

**Test Case 1: Same name, different kategori (Should SUCCEED)**
```
Vendor 1: Sukbir Mart | Bahan Baku       ✅
Vendor 2: Sukbir Mart | Bahan Pendukung  ✅
Vendor 3: Sukbir Mart | Aset             ✅
```

**Test Case 2: Same name, same kategori (Should FAIL)**
```
Vendor 1: Sukbir Mart | Bahan Baku  ✅
Vendor 2: Sukbir Mart | Bahan Baku  ❌ Validation Error (correct!)
```

## 📁 Files Changed

### Migration Files
- `database/migrations/2026_06_15_000000_force_remove_old_vendor_constraint.php` (NEW)

### SQL Scripts
- `FIX_VENDOR_CONSTRAINT_PRODUCTION.sql` (Manual fallback)

### No Code Changes Needed
- ✅ VendorController already has correct validation
- ✅ No view changes needed
- ✅ No model changes needed

## 🔍 Troubleshooting

### Error: "Can't DROP INDEX"
```bash
# Check if index name is different
mysql> SHOW INDEX FROM vendors;

# Use the exact index name shown
mysql> ALTER TABLE vendors DROP INDEX [exact_name];
```

### Error: "Duplicate entry exists"
```bash
# Find duplicates
SELECT user_id, nama_vendor, kategori, COUNT(*) 
FROM vendors 
GROUP BY user_id, nama_vendor, kategori 
HAVING COUNT(*) > 1;

# Resolve duplicates before running migration
```

### Migration Fails Completely
```bash
# Use manual SQL method (Option 2)
# Or contact DBA for assistance
```

## 📞 Rollback Plan

If something goes wrong:

```bash
# 1. Restore database backup
mysql -u [user] -p [database] < backup_before_vendor_fix_[timestamp].sql

# 2. Or run migration rollback
php artisan migrate:rollback --step=1

# 3. Or manual SQL
ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_kategori_unique;
ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_unique (user_id, nama_vendor);
```

## ✅ Post-Deployment Checklist

- [ ] Database backup created
- [ ] Migration ran successfully
- [ ] Constraint verified in database
- [ ] Test: Create vendor with same name, different kategori (should work)
- [ ] Test: Create vendor with same name, same kategori (should fail)
- [ ] All caches cleared
- [ ] PHP/Apache restarted
- [ ] Site is back online
- [ ] No errors in logs

## 📝 Notes

- **Safe to run multiple times**: Migration checks existing constraints before making changes
- **Zero downtime**: If using manual SQL, can be done without taking site down
- **Backward compatible**: Existing vendors are not affected
- **Multi-tenant safe**: Constraint includes user_id for isolation

---

**Created**: 2026-06-15  
**Priority**: High  
**Estimated Time**: 5-10 minutes  
**Risk Level**: Low (with backup)
