# FIX: Registration Error - Duplicate COA kode_akun

## 🔍 MASALAH

Saat user baru melakukan registrasi, terjadi error:

```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '11' for key 'coas_kode_akun_unique'
```

### Error Detail:
- User baru (ID: 2) mencoba register
- Sistem mencoba membuat COA default untuk user baru
- Database reject karena `kode_akun = '11'` sudah ada (milik User ID: 1)
- Constraint `coas_kode_akun_unique` mencegah duplicate

---

## 🎯 ROOT CAUSE

### Masalah Utama:
**Unique constraint pada tabel `coas` SALAH untuk sistem MULTI-TENANT!**

### Constraint yang Salah:
```sql
UNIQUE KEY `coas_kode_akun_unique` (`kode_akun`)
```

Ini berarti:
- ❌ Hanya 1 user yang bisa punya COA dengan `kode_akun = '11'`
- ❌ User kedua tidak bisa register karena COA-nya bentrok
- ❌ Sistem MULTI-TENANT tidak bisa berfungsi!

### Constraint yang Benar:
```sql
UNIQUE KEY `coas_kode_akun_user_id_unique` (`kode_akun`, `user_id`)
```

Ini berarti:
- ✅ Setiap user bisa punya COA dengan `kode_akun = '11'`
- ✅ Kombinasi (`kode_akun` + `user_id`) yang harus unique
- ✅ User 1 punya COA "11 - Aset", User 2 juga bisa punya COA "11 - Aset"
- ✅ Sistem MULTI-TENANT berfungsi dengan benar!

---

## 🔧 SOLUSI (SUDAH DIPERBAIKI)

### 1. Migration Baru ✅

**File**: `database/migrations/2026_05_06_192554_fix_coas_unique_constraint_for_multi_tenant.php`

**Yang Dilakukan**:
1. Drop constraint lama: `coas_kode_akun_unique`
2. Drop constraint salah: `coas_kode_akun_company_unique`
3. Create constraint baru: `coas_kode_akun_user_id_unique` (COMPOSITE)

### 2. Verification ✅

Script `check_coa_constraints.php` memverifikasi:
- ✅ Composite unique constraint exists
- ✅ User 1 dan User 2 bisa punya COA dengan `kode_akun` yang sama
- ✅ Multi-tenant isolation working

---

## 📋 TESTING

### Test 1: Check Constraints

```bash
php check_coa_constraints.php
```

**Expected Output**:
```
✅ CORRECT: Composite unique constraint (kode_akun + user_id) exists
   Each user can have their own COA with the same kode_akun
   Multi-tenant isolation is WORKING!
```

### Test 2: Registration

1. Buka: http://127.0.0.1:8000/register
2. Isi form registrasi dengan data baru
3. Submit

**Expected Result**:
- ✅ Registration berhasil
- ✅ User baru terbuat
- ✅ COA default terbuat untuk user baru
- ✅ Tidak ada error duplicate kode_akun

---

## 🔒 MULTI-TENANT PRINCIPLES

### Database Design untuk Multi-Tenant:

#### 1. **Unique Constraints Harus COMPOSITE**

```sql
-- SALAH ❌
UNIQUE KEY (kode_akun)

-- BENAR ✅
UNIQUE KEY (kode_akun, user_id)
```

#### 2. **Foreign Keys Harus Include user_id**

```sql
-- SALAH ❌
FOREIGN KEY (jabatan_id) REFERENCES jabatans(id)

-- BENAR ✅
-- Tidak bisa enforce di SQL, harus di application level
-- Verify di controller/model bahwa jabatan_id milik user yang sama
```

#### 3. **Indexes untuk Performance**

```sql
-- Index untuk query by user_id
INDEX idx_coas_user_id (user_id)

-- Composite index untuk query by user_id + kode_akun
INDEX idx_coas_user_kode (user_id, kode_akun)
```

---

## 📝 CHECKLIST MULTI-TENANT

Untuk setiap tabel yang multi-tenant, pastikan:

- [ ] ✅ Kolom `user_id` ada
- [ ] ✅ Unique constraints include `user_id`
- [ ] ✅ Semua query filter by `user_id`
- [ ] ✅ Foreign keys verify ownership (di application level)
- [ ] ✅ Seeders create data per user (bukan global)
- [ ] ✅ Indexes include `user_id` untuk performance

---

## 🚀 DEPLOYMENT

### Local (Sudah Selesai)

- [x] ✅ Create migration `fix_coas_unique_constraint_for_multi_tenant`
- [x] ✅ Run migration: `php artisan migrate`
- [x] ✅ Drop wrong constraint: `coas_kode_akun_company_unique`
- [x] ✅ Verify dengan `check_coa_constraints.php`
- [x] ✅ Test registration (manual)

### Production (Perlu Dilakukan)

- [ ] ⏳ Push code ke GitHub
- [ ] ⏳ Jenkins deploy ke VPS
- [ ] ⏳ Run migration di VPS: `php artisan migrate`
- [ ] ⏳ Drop wrong constraint di VPS (jika ada)
- [ ] ⏳ Test registration di production

---

## ⚠️ IMPORTANT NOTES

### 1. Existing Data

Jika ada data COA yang sudah ada sebelum fix ini:
- Data tetap aman
- Constraint baru tidak akan conflict dengan data lama
- User lama tetap bisa login dan akses data mereka

### 2. Registration Flow

Saat user baru register:
1. User record dibuat
2. Perusahaan record dibuat (jika ada)
3. **COA default dibuat** (dari seeder/listener)
4. Satuan default dibuat (jika belum ada)
5. Jabatan default dibuat (jika belum ada)

### 3. COA Seeder

File yang membuat COA default saat registration:
- `app/Listeners/CreateDefaultUserData.php`
- `database/seeders/DefaultCoaSeeder.php`

Seeder ini dipanggil otomatis saat user register.

---

## 🧪 MANUAL TESTING STEPS

### Step 1: Clear Test Data (Optional)

Jika ingin test dari awal:

```bash
# Delete test user (jika ada)
php artisan tinker --execute="App\Models\User::where('email', 'test@example.com')->delete();"

# Delete test COA (jika ada)
php artisan tinker --execute="App\Models\Coa::where('user_id', 2)->delete();"
```

### Step 2: Start Server

```bash
php artisan serve
```

### Step 3: Register New User

1. Buka: http://127.0.0.1:8000/register
2. Isi form:
   - Name: Test User
   - Email: test@example.com
   - Password: password123
   - Password Confirmation: password123
   - Nama Perusahaan: PT Test
   - Alamat: Jakarta
   - Telepon: 08123456789
3. Submit

### Step 4: Verify

```bash
# Check user created
php artisan tinker --execute="echo 'Total users: ' . App\Models\User::count();"

# Check COA created for new user
php artisan tinker --execute="echo 'COA for user 2: ' . App\Models\Coa::where('user_id', 2)->count();"
```

**Expected**:
- Total users: 2 (atau lebih)
- COA for user 2: 50+ (tergantung seeder)

---

## ✅ SUCCESS CRITERIA

- [ ] Migration berhasil dijalankan
- [ ] Composite unique constraint exists: `coas_kode_akun_user_id_unique`
- [ ] Wrong constraint removed: `coas_kode_akun_unique` dan `coas_kode_akun_company_unique`
- [ ] Registration berhasil tanpa error
- [ ] User baru punya COA default
- [ ] Multi-tenant isolation working (User 1 tidak bisa lihat COA User 2)

---

## 📊 BEFORE vs AFTER

### BEFORE (SALAH) ❌

```
Table: coas
Constraints:
  - UNIQUE (kode_akun)  ❌ SALAH!

Result:
  - User 1: COA "11 - Aset" ✅
  - User 2: COA "11 - Aset" ❌ ERROR: Duplicate entry!
```

### AFTER (BENAR) ✅

```
Table: coas
Constraints:
  - UNIQUE (kode_akun, user_id)  ✅ BENAR!

Result:
  - User 1: COA "11 - Aset" ✅
  - User 2: COA "11 - Aset" ✅ SUCCESS!
```

---

**Status**: ✅ FIXED

**Date**: 6 Mei 2026

**Priority**: CRITICAL (Blocks user registration)

**Impact**: HIGH (Affects all new user registrations)
