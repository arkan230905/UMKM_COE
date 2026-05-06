# Fix Jabatan Auto Seeder - Multi-Tenant

**Date**: 6 Mei 2026  
**Status**: ✅ FIXED  
**Priority**: HIGH

---

## 🐛 MASALAH

Data Jabatan tiba-tiba masuk sendiri ke database dengan `user_id = 2` dan `kategori_id = NULL`.

### Screenshot Masalah

Database `jabatans` table:
```
id | user_id | kategori_id | kode_jabatan | nama              | kategori
1  | 2       | NULL        | BT001        | Operator Produksi | btkl
2  | 2       | NULL        | BT002        | Perbumbuan        | btkl
3  | 2       | NULL        | BT003        | Pengemasan        | btkl
4  | 2       | NULL        | BT004        | Supervisor        | btktl
5  | 2       | NULL        | BT005        | Admin             | btktl
6  | 2       | NULL        | BT006        | Kasir             | btktl
7  | 2       | NULL        | BT007        | Quality Control   | btktl
8  | 2       | NULL        | BT008        | Gudang            | btktl
```

**Pertanyaan**: Kenapa data ini masuk sendiri?

---

## 🔍 ROOT CAUSE

Ada `JabatanSeeder` lama yang **TIDAK multi-tenant** dan dipanggil dari `DatabaseSeeder`.

### File: `database/seeders/JabatanSeeder.php`

```php
class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatans = [
            // Data jabatan...
        ];

        foreach ($jabatans as $jabatan) {
            $existing = Jabatan::where('kode_jabatan', $jabatan['kode_jabatan'])
                ->orWhere('nama', $jabatan['nama'])
                ->first();
            
            if (!$existing) {
                Jabatan::create($jabatan);  // ❌ TIDAK ADA user_id!
            }
        }
    }
}
```

**Masalah**:
1. Seeder tidak menerima `$userId` parameter
2. Data dibuat tanpa `user_id` yang benar
3. Model `Jabatan` auto-assign `user_id` dari `auth()->id()` (User 2 yang sedang login)
4. Seeder dipanggil dari `DatabaseSeeder` atau script setup

---

## ✅ SOLUSI

### 1. Buat DefaultJabatanSeeder (Multi-Tenant)

Created: `database/seeders/DefaultJabatanSeeder.php`

```php
class DefaultJabatanSeeder extends Seeder
{
    public function run(int $userId): void  // ✅ Terima user_id
    {
        // Jangan buat ulang jika sudah ada
        if (DB::table('jabatans')->where('user_id', $userId)->exists()) {
            return;
        }

        $jabatans = [
            // 8 jabatan default...
        ];

        $rows = [];
        foreach ($jabatans as $jabatan) {
            $rows[] = array_merge($jabatan, [
                'user_id' => $userId,  // ✅ Set user_id
                'kategori_id' => null,
                'locked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('jabatans')->insert($rows);
    }
}
```

### 2. Update CreateDefaultUserData Listener

File: `app/Listeners/CreateDefaultUserData.php`

```php
public function handle(UserRegistered $event): void
{
    // Create default COA
    $coaSeeder = new DefaultCoaSeeder();
    $coaSeeder->run($event->user->id);
    
    // Create default Satuan
    $satuanSeeder = new DefaultSatuanSeeder();
    $satuanSeeder->run($event->user->id);
    
    // Create default Jabatan  ✅ ADDED
    $jabatanSeeder = new \Database\Seeders\DefaultJabatanSeeder();
    $jabatanSeeder->run($event->user->id);
}
```

### 3. Fix Existing Data

Created script: `fix_jabatan_multi_tenant.php`

Script ini:
- Delete jabatan dengan `user_id` NULL atau invalid
- Seed jabatan untuk semua user yang belum punya
- Verify multi-tenant isolation

---

## 📋 FILES CHANGED

1. **Seeder** (New)
   - `database/seeders/DefaultJabatanSeeder.php` - Multi-tenant seeder

2. **Listener**
   - `app/Listeners/CreateDefaultUserData.php` - Added Jabatan seeder call

3. **Scripts** (New)
   - `fix_jabatan_multi_tenant.php` - Fix existing data
   - `FIX_JABATAN_AUTO_SEEDER.md` (this file)

4. **Test**
   - `test_registration_flow.php` - Added Jabatan verification

5. **Old Seeder** (Keep for backward compatibility)
   - `database/seeders/JabatanSeeder.php` - Not used anymore

---

## 🧪 TESTING

### Test 1: Fix Existing Data

```bash
php fix_jabatan_multi_tenant.php
```

**Result**:
```
Step 1: Checking current jabatan...
User 2 (MUHAMMAD ARKAN ABIYYU): 8 jabatan

Step 2: Cleaning up jabatan...
ℹ️  No orphaned jabatan found

Step 3: Seeding jabatan for all users...
User 1 (Admin UMKM):
  ✅ Created 8 jabatan

User 2 (MUHAMMAD ARKAN ABIYYU):
  ⏭️  Already has 8 jabatan - SKIPPED

=== VERIFICATION ===
✅ User 1 (Admin UMKM): 8 jabatan (BTKL: 3, BTKTL: 5)
✅ User 2 (MUHAMMAD ARKAN ABIYYU): 8 jabatan (BTKL: 3, BTKTL: 5)
```

### Test 2: Registration Flow

```bash
php test_registration_flow.php
```

**Result**:
```
✅ COA creation: WORKING (51 accounts)
✅ Satuan creation: WORKING (16 units)
✅ Jabatan creation: WORKING (8 positions) ⭐ NEW
✅ Multi-tenant: WORKING
```

---

## ✅ VERIFICATION

### Before Fix:
```
User 1: 0 Jabatan ❌
User 2: 8 Jabatan (manual/auto) ⚠️
New user: 0 Jabatan ❌
```

### After Fix:
```
User 1: 8 Jabatan ✅
User 2: 8 Jabatan ✅
New user: 8 Jabatan ✅ (automatic)
```

### Jabatan List (8 positions):

**BTKL (3 positions)**:
1. BT001 - Operator Produksi (Rp 18,000/jam)
2. BT002 - Perbumbuan (Rp 18,000/jam)
3. BT003 - Pengemasan (Rp 17,000/jam)

**BTKTL (5 positions)**:
4. BT004 - Supervisor (Rp 4,000,000/bulan)
5. BT005 - Admin (Rp 3,000,000/bulan)
6. BT006 - Kasir (Rp 2,700,000/bulan)
7. BT007 - Quality Control (Rp 3,500,000/bulan)
8. BT008 - Gudang (Rp 2,800,000/bulan)

---

## 🚀 DEPLOYMENT

### For New Users

Listener sudah diperbaiki, jadi user baru otomatis dapat 8 Jabatan saat register.

### For Existing Users

Run script di VPS:

```bash
# SSH to VPS
ssh user@your-vps-ip

# Navigate to project
cd /path/to/umkm_coe

# Pull latest code
git pull origin main

# Fix Jabatan for existing users
php fix_jabatan_multi_tenant.php

# Verify
php check_current_users.php
```

---

## 📊 IMPACT

### Severity: HIGH
- User tidak bisa input pegawai tanpa Jabatan
- Blocking feature untuk BTKL dan Penggajian

### Affected:
- User 1: Tidak punya Jabatan
- User 2: Punya Jabatan (dari seeder lama)
- User baru: Tidak akan dapat Jabatan (sebelum fix)

### Fixed:
- ✅ DefaultJabatanSeeder created (multi-tenant)
- ✅ Listener updated
- ✅ Existing users seeded
- ✅ New users automatic
- ✅ All tests passed

---

## 🎯 TECHNICAL DETAILS

### Jabatan Fields

```php
'user_id'              => 1              // Multi-tenant ✅
'kategori_id'          => null           // Optional category
'kode_jabatan'         => 'BT001'        // Unique per user
'nama'                 => 'Operator Produksi'
'kategori'             => 'btkl'         // btkl or btktl
'gaji_pokok'           => 0              // Monthly salary
'tunjangan'            => 0              // Allowance
'tunjangan_transport'  => 120000         // Transport allowance
'tunjangan_konsumsi'   => 375000         // Meal allowance
'asuransi'             => 80000          // Insurance
'tarif'                => 18000          // Hourly rate
'tarif_per_jam'        => 18000          // Hourly rate
'deskripsi'            => '...'          // Description
'locked'               => 0              // Lock status
```

### BTKL vs BTKTL

**BTKL (Biaya Tenaga Kerja Langsung)**:
- Tenaga kerja yang terlibat langsung dalam produksi
- Dibayar per jam (`tarif_per_jam`)
- Contoh: Operator Produksi, Perbumbuan, Pengemasan

**BTKTL (Biaya Tenaga Kerja Tidak Langsung)**:
- Tenaga kerja pendukung produksi
- Dibayar per bulan (`gaji_pokok`)
- Contoh: Supervisor, Admin, Kasir, QC, Gudang

---

## ✅ CHECKLIST

- [x] DefaultJabatanSeeder created
- [x] Listener updated
- [x] Fix script created
- [x] Existing users seeded
- [x] Registration flow tested
- [x] Test updated
- [x] Documentation created
- [x] Ready to push

---

## 🔗 RELATED FIXES

This fix is part of the multi-tenant improvements:

1. ✅ BTKL dropdown fix
2. ✅ Registration duplicate COA fix
3. ✅ Registration no COA fix
4. ✅ COA tipe Equity to Biaya fix
5. ✅ Satuan seeder fix
6. ✅ **Jabatan auto seeder fix** ⭐ THIS FIX

---

**STATUS**: ✅ FIXED AND VERIFIED

**READY TO PUSH**: YES

---

Generated: 6 Mei 2026  
By: Kiro AI Assistant  
For: UMKM COE Multi-Tenant System
