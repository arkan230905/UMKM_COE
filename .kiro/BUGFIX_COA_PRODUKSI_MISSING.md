# Bug Fix - Missing Production COAs

## Date: June 8, 2026

---

## Issue
**Error saat menyelesaikan proses produksi:**
```
Exception - Internal Server Error
COA yang diperlukan untuk produksi tidak ditemukan:
1171 - Pers. Barang Dalam Proses - BBB (WIP BBB)
1172 - Pers. Barang Dalam Proses - BTKL (WIP BTKL)
1173 - Pers. Barang Dalam Proses - BOP (WIP BOP)
```

**Location:** `app/Http/Controllers/ProduksiController.php:1267`

---

## Root Cause

### Masalah Multi-Tenant
Sistem menggunakan multi-tenant isolation dengan `user_id`. Seeder `CoaSeeder.php` yang lama **TIDAK menyimpan `user_id`**, sehingga:

1. COA dibuat tanpa `user_id` (NULL)
2. Ketika produksi selesai, sistem mencari COA dengan filter:
   ```sql
   SELECT * FROM coas 
   WHERE kode_akun = 1171 
   AND user_id = 3  -- ❌ Tidak ditemukan!
   ```
3. COA ada di database tapi `user_id` tidak match
4. Error: "COA tidak ditemukan"

### Database Queries yang Menunjukkan Masalah
```sql
-- Query dari error log:
select exists(select * from `coas` 
where `kode_akun` = 1171 
and `user_id` = 3 
and `user_id` = 3) as `exists`
-- Result: 0 (false) ❌
```

---

## Solution

### 1. ✅ Update CoaSeeder.php
Memperbaiki seeder agar menyimpan COA untuk **setiap user** yang ada di sistem.

**Before:**
```php
foreach ($data as $item) {
    DB::table('coas')->updateOrInsert(
        ['kode_akun' => $item[1]], // ❌ Tidak ada user_id
        [
            'nama_akun' => $item[0],
            'tipe_akun' => $item[2],
            'saldo_normal' => $item[3],
            'saldo_awal' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}
```

**After:**
```php
use App\Models\User;

$users = User::all();

foreach ($users as $user) {
    foreach ($data as $item) {
        DB::table('coas')->updateOrInsert(
            [
                'kode_akun' => $item[1],
                'user_id' => $user->id  // ✅ Include user_id
            ],
            [
                'nama_akun' => $item[0],
                'tipe_akun' => $item[2],
                'saldo_normal' => $item[3],
                'saldo_awal' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
```

### 2. ✅ Update COA Data dengan Nama Lengkap
Menambahkan nama lengkap COA produksi agar lebih jelas:

```php
// Old:
['Pers. Barang Dalam Proses - BBB', '1171', 'Aset', 'debit'],
['Pers. Barang Dalam Proses - BTKL', '1172', 'Aset', 'debit'],
['Pers. Barang Dalam Proses - BOP', '1173', 'Aset', 'debit'],

// New:
['Pers. Barang Dalam Proses - BBB (WIP BBB)', '1171', 'Aset', 'debit'],
['Pers. Barang Dalam Proses - BTKL (WIP BTKL)', '1172', 'Aset', 'debit'],
['Pers. Barang Dalam Proses - BOP (WIP BOP)', '1173', 'Aset', 'debit'],
```

### 3. ✅ Run Seeder
```bash
php artisan db:seed --class=CoaSeeder --force
```

**Output:**
```
Creating COAs for user: Admin User (ID: 1)
✅ Created 51 COAs for user Admin User
Creating COAs for user: Test User (ID: 2)
✅ Created 51 COAs for user Test User
Creating COAs for user: EADT SIA (ID: 3)
✅ Created 51 COAs for user EADT SIA
...
✅ COA Seeder completed successfully!
Total users processed: 6
Total COAs per user: 51
⚠️  IMPORTANT: COA 1171, 1172, 1173 are required for production!
```

---

## COA Produksi yang Dibutuhkan

### WIP (Work In Progress) Accounts
Akun-akun ini digunakan untuk tracking biaya produksi yang sedang berjalan:

| Kode | Nama Akun | Tipe | Saldo Normal | Fungsi |
|------|-----------|------|--------------|--------|
| 1171 | Pers. Barang Dalam Proses - BBB (WIP BBB) | Aset | Debit | Biaya Bahan Baku yang sedang diproses |
| 1172 | Pers. Barang Dalam Proses - BTKL (WIP BTKL) | Aset | Debit | Biaya Tenaga Kerja Langsung yang sedang diproses |
| 1173 | Pers. Barang Dalam Proses - BOP (WIP BOP) | Aset | Debit | Biaya Overhead Pabrik yang sedang diproses |

### Additional Required Account
| Kode | Nama Akun | Tipe | Saldo Normal | Fungsi |
|------|-----------|------|--------------|--------|
| 211 | Hutang Gaji | Kewajiban | Kredit | Hutang gaji untuk BTKL |

---

## Jurnal Entries Saat Produksi Selesai

### 1. Journal Entry untuk BBB
```
Dr. 1171 - Pers. Barang Dalam Proses - BBB  Rp XXX
    Cr. 114 - Pers. Bahan Baku                    Rp XXX
(Mencatat penggunaan bahan baku dalam produksi)
```

### 2. Journal Entry untuk BTKL
```
Dr. 1172 - Pers. Barang Dalam Proses - BTKL  Rp XXX
    Cr. 211 - Hutang Gaji                         Rp XXX
(Mencatat biaya tenaga kerja langsung)
```

### 3. Journal Entry untuk BOP
```
Dr. 1173 - Pers. Barang Dalam Proses - BOP  Rp XXX
    Cr. 115 - Pers. Bahan Pendukung             Rp XXX
(Mencatat biaya overhead pabrik)
```

### 4. Journal Entry saat Produksi Selesai
```
Dr. 116 - Pers. Barang Jadi  Rp XXX
    Cr. 1171 - WIP BBB              Rp XXX
    Cr. 1172 - WIP BTKL             Rp XXX
    Cr. 1173 - WIP BOP              Rp XXX
(Transfer dari WIP ke Finished Goods)
```

---

## Verification Steps

### 1. Check COAs in Database
```sql
SELECT kode_akun, nama_akun, user_id 
FROM coas 
WHERE user_id = 3 
AND kode_akun IN ('1171', '1172', '1173', '211')
ORDER BY kode_akun;
```

**Expected Result:**
```
1171 | Pers. Barang Dalam Proses - BBB (WIP BBB)   | 3
1172 | Pers. Barang Dalam Proses - BTKL (WIP BTKL) | 3
1173 | Pers. Barang Dalam Proses - BOP (WIP BOP)   | 3
211  | Hutang Gaji                                   | 3
```

### 2. Check Total COAs per User
```sql
SELECT user_id, COUNT(*) as total_coas
FROM coas
GROUP BY user_id
ORDER BY user_id;
```

**Expected Result:**
Each user should have at least 51 COAs.

### 3. Test Production Completion
1. Start a production
2. Complete all processes
3. **Expected:** No error about missing COAs
4. **Expected:** Journal entries created successfully

---

## Files Modified

### database/seeders/CoaSeeder.php
**Changes:**
1. Added `use App\Models\User;`
2. Loop through all users
3. Insert COAs for each user with `user_id`
4. Added progress messages
5. Updated COA names for production accounts

**Total COAs:** 51 per user

**Key Production COAs:**
- 1171 (WIP BBB)
- 1172 (WIP BTKL)
- 1173 (WIP BOP)
- 211 (Hutang Gaji)

---

## Prevention

### For Future Seeders
Always include `user_id` when creating multi-tenant data:

```php
// ✅ GOOD
DB::table('coas')->updateOrInsert(
    [
        'kode_akun' => $code,
        'user_id' => $userId  // Include user_id
    ],
    [...]
);

// ❌ BAD
DB::table('coas')->updateOrInsert(
    ['kode_akun' => $code],  // Missing user_id
    [...]
);
```

### For New Users
When creating a new user, automatically seed their COAs:

```php
// In User registration/creation
$user = User::create([...]);

// Seed default COAs for new user
Artisan::call('db:seed', [
    '--class' => 'CoaSeeder',
    '--force' => true
]);
```

---

## Testing Instructions

### Test Case 1: Complete Production
1. Login as user with ID 3 (EADT SIA)
2. Navigate to `/transaksi/produksi`
3. Find a production in "Dalam Proses" status
4. Go to process management
5. Complete all processes
6. Click "Selesaikan" on the last process
7. **Expected:** Success message, no COA error
8. **Expected:** Production status = "Selesai"

### Test Case 2: Check Journal Entries
```sql
SELECT * FROM jurnal_umum 
WHERE tipe_referensi LIKE 'produksi%' 
AND referensi = [PRODUCTION_ID]
ORDER BY id;
```

**Expected:** Journal entries created for BBB, BTKL, BOP

### Test Case 3: Multi-User
1. Login as different users
2. Each user should be able to complete production
3. Each should have their own COAs
4. No cross-tenant data leakage

---

## Status
✅ **FIXED** - COA seeder updated and run successfully

### Completed:
- ✅ Updated `CoaSeeder.php` with multi-tenant support
- ✅ Added production COAs (1171, 1172, 1173)
- ✅ Seeder run for all 6 users
- ✅ 51 COAs created per user
- ✅ Documentation created

### Next Steps:
1. Test production completion
2. Verify journal entries created
3. Monitor for any other missing COAs

---

## Related Files
- `database/seeders/CoaSeeder.php` - Updated seeder
- `app/Http/Controllers/ProduksiController.php` - Uses these COAs
- `app/Services/JournalService.php` - Creates journal entries

---

**Date Fixed:** June 8, 2026
**Developer:** Kiro AI
**Status:** ✅ Ready for Testing
