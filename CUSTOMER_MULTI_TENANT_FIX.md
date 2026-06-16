# Customer Data Isolation Fix - Data Pelanggan Akun Lain Tidak Muncul di Akun Anda

## 🔴 CRITICAL BUG FIXED

**Problem:** Data akun pelanggan dari akun/perusahaan lain muncul di akun/perusahaan baru.

**Root Cause:** Pelanggan di-create dengan `user_id = NULL` dan `perusahaan_id = NULL`, sehingga:
1. Mereka tidak ter-scope ke akun/perusahaan manapun
2. Semua owner bisa melihat semua pelanggan
3. Data pelanggan tercampur antara stores yang berbeda

**Solution:** 
- Pelanggan sekarang di-create dengan `user_id = owner's user_id` dan `perusahaan_id = store's perusahaan_id`
- Semua queries di-filter berdasarkan `user_id` bukan `perusahaan_id`

---

## 📝 Changes Made

### 1. **LoginController::register()** (CRITICAL)
**File:** `app/Http/Controllers/Pelanggan/Auth/LoginController.php`

**Before:**
```php
$user = \App\Models\User::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'phone' => $validated['phone'],
    'password' => bcrypt($validated['password']),
    'plain_password' => $validated['password'],
    'role' => 'pelanggan',
    'email_verified_at' => now(),
    'user_id' => null,  // ❌ BUG: NULL user_id
]);
```

**After:**
```php
$user = \App\Models\User::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'phone' => $validated['phone'],
    'password' => bcrypt($validated['password']),
    'plain_password' => $validated['password'],
    'role' => 'pelanggan',
    'email_verified_at' => now(),
    'perusahaan_id' => $perusahaan->id,           // ✅ FIX: Set perusahaan_id
    'user_id' => $perusahaan->user_id,           // ✅ FIX: Set user_id to store owner
]);
```

**Why:** Setiap pelanggan harus ter-attach ke store/owner yang mereka registrasi di.

---

### 2. **PelangganTableController - ALL METHODS** (IMPORTANT)
**File:** `app/Http/Controllers/MasterData/PelangganTableController.php`

**Changed filter from:**
```php
->where('perusahaan_id', auth()->user()->perusahaan_id)  // ❌ OLD
```

**To:**
```php
->where('user_id', auth()->id())  // ✅ NEW - filter by store owner, not by perusahaan_id
```

**Methods updated:**
- ✅ `index()` - List pelanggan
- ✅ `edit()` - Edit pelanggan
- ✅ `update()` - Update pelanggan
- ✅ `destroy()` - Delete pelanggan
- ✅ `resetPassword()` - Reset password

**Why:** Pelanggan sekarang punya `user_id` yang di-set saat registrasi, jadi filter harus berdasarkan `user_id` owner, bukan `perusahaan_id`.

---

### 3. **Migration: fix_customer_user_id_and_perusahaan_id.php** (IMPORTANT)
**File:** `database/migrations/2026_06_16_120000_fix_customer_user_id_and_perusahaan_id.php`

**Purpose:** Fix existing pelanggan yang sudah terdaftar dengan `user_id = NULL`

**Logic:**
1. Find all customers dengan `user_id = NULL`
2. Untuk setiap customer:
   - Cari orders mereka untuk get seller's user_id
   - Atau cari cart items untuk get product owner's user_id
   - Set customer's `user_id` dan `perusahaan_id` based on seller
3. Log hasil untuk tracking

**Important:** Migration tidak bisa di-revert karena destructive

---

## 🔒 Multi-Tenant Isolation Architecture

### Before Fix:
```
Owner A (user_id=1, perusahaan_id=1)
  ├─ Pelanggan A1 (user_id=NULL, perusahaan_id=NULL)  ❌ Muncul di semua owner
  └─ Pelanggan A2 (user_id=NULL, perusahaan_id=NULL)  ❌ Muncul di semua owner

Owner B (user_id=2, perusahaan_id=2)
  ├─ Pelanggan B1 (user_id=NULL, perusahaan_id=NULL)  ❌ Muncul di semua owner
  └─ Pelanggan B2 (user_id=NULL, perusahaan_id=NULL)  ❌ Muncul di semua owner

Query: WHERE role='pelanggan' AND perusahaan_id=1
Result: EMPTY (karena semua punya perusahaan_id=NULL)
```

### After Fix:
```
Owner A (user_id=1, perusahaan_id=1)
  ├─ Pelanggan A1 (user_id=1, perusahaan_id=1)  ✅ Only visible to Owner A
  └─ Pelanggan A2 (user_id=1, perusahaan_id=1)  ✅ Only visible to Owner A

Owner B (user_id=2, perusahaan_id=2)
  ├─ Pelanggan B1 (user_id=2, perusahaan_id=2)  ✅ Only visible to Owner B
  └─ Pelanggan B2 (user_id=2, perusahaan_id=2)  ✅ Only visible to Owner B

Query: WHERE role='pelanggan' AND user_id=1
Result: Pelanggan A1, Pelanggan A2 ✅ Correct!
```

---

## 🧪 Testing Steps

### 1. Deploy & Run Migration
```bash
php artisan migrate
```

### 2. Test Case 1: Check Existing Customers
```
Login as Owner A
→ Go to Master Data > Pelanggan
✅ Should see ONLY Owner A's customers
❌ Should NOT see Owner B's customers
```

### 3. Test Case 2: Register New Customer in Store A
```
1. Logout
2. Go to http://localhost/store-a/pelanggan/login
3. Register new customer (email: customer-a@test.com)
4. Login as Owner A
5. Go to Master Data > Pelanggan
✅ Should see the new customer
✅ Customer should have user_id = Owner A's id
✅ Customer should have perusahaan_id = Store A's id
```

### 4. Test Case 3: Register New Customer in Store B
```
1. Logout
2. Go to http://localhost/store-b/pelanggan/login
3. Register new customer (email: customer-b@test.com)
4. Login as Owner B
5. Go to Master Data > Pelanggan
✅ Should see ONLY the new customer from Store B
❌ Should NOT see Store A's customers
```

### 5. Test Case 4: Cross-Store Isolation
```
1. Login as Owner A
2. Go to /master-data/pelanggan
3. Try URL manipulation to access Store B's pelanggan
❌ Should return 404 or redirect
✅ Should NOT load Store B's customers
```

---

## 📋 Deployment Checklist

- [ ] Run `php artisan migrate` to fix existing customers
- [ ] Test multi-customer scenarios in staging
- [ ] Verify no data leakage between stores
- [ ] Check logs for any migration warnings
- [ ] Confirm customer registration works correctly
- [ ] Deploy to production

---

## ⚠️ Important Notes

1. **Migration is one-way:** 
   - Old data with `user_id = NULL` will be assigned to store owners based on orders
   - Down migration is not supported (destructive)

2. **Backward compatibility:**
   - Old pelanggan links with `perusahaan_id` still work
   - New pelanggan always created with proper `user_id`

3. **Query patterns:**
   - Always filter by `user_id` for pelanggan queries
   - Use `->where('user_id', auth()->id())` pattern

4. **Performance:**
   - `user_id` is indexed in database
   - Queries should be fast even with large customer base

---

## 📚 Related Documentation

- `MULTI_TENANT_SECURITY.md` - Complete multi-tenant security guide
- `MULTI_TENANT_DATA_ISOLATION_FIX.md` - Previous isolation fixes for owner data
- `app/Traits/MultiTenantModel.php` - Auto-scoping trait for models

---

## 🎯 Summary

**Problem:** Customers from different stores were mixed together

**Root Cause:** `user_id` and `perusahaan_id` not set during registration

**Solution:** 
1. Set `user_id` and `perusahaan_id` when customer registers
2. Update queries to filter by `user_id` instead of `perusahaan_id`
3. Migrate existing customers to fix the issue

**Result:** ✅ Each store now has isolated customer data!
