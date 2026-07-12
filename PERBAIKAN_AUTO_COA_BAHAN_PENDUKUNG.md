# PERBAIKAN AUTO-CREATE COA BAHAN PENDUKUNG

## RINGKASAN PERUBAHAN

Proses auto-create COA untuk bahan pendukung telah diperbaiki agar setiap bahan memiliki kode akun yang unik dan berurutan secara numerik. Sistem sekarang mencari kode akun terakhir dengan sorting numerik (bukan string), lalu menambahkan 1 untuk membuat kode baru. Database transaction memastikan tidak ada duplicate entry saat concurrent insert.

---

## MASALAH SEBELUMNYA

### ❌ **Hardcode Kode 11500**
Semua bahan pendukung menggunakan kode yang sama, menyebabkan:
- Tidak ada diferensiasi antar bahan
- Konflik jika ada duplikasi
- Tidak bisa tracking per item

### ❌ **String Sorting**
```php
->orderBy('kode_akun', 'desc')
```

**Masalah:** Sorting string menghasilkan urutan yang salah:
```
String sorting:
'1151' > '11500' > '11501' > '1152'  ❌ SALAH!

Seharusnya (numeric sorting):
'1151' < '1152' < '11500' < '11501'  ✅ BENAR!
```

**Dampak:**
- Jika ada kode '11500', system akan mengambil '1151' sebagai "terakhir"
- Kode baru akan jadi '1152', padahal '11500' sudah ada
- Duplikasi atau konflik kode

---

## SOLUSI YANG DITERAPKAN

### 1. **Numeric Sorting dengan CAST**

**SEBELUM:**
```php
$lastCoa = Coa::where('user_id', $userId)
    ->where('kode_akun', 'LIKE', '115%')
    ->where('nama_akun', 'LIKE', 'Pers. Bahan Pendukung%')
    ->orderBy('kode_akun', 'desc')  // ❌ String sorting
    ->lockForUpdate()
    ->first();
```

**SESUDAH:**
```php
$lastCoa = Coa::where('user_id', $userId)
    ->where('kode_akun', 'LIKE', '115%')
    ->where('nama_akun', 'LIKE', 'Pers. Bahan Pendukung%')
    ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')  // ✅ Numeric sorting
    ->lockForUpdate()
    ->first();
```

**Penjelasan:**
- `CAST(kode_akun AS UNSIGNED)` mengkonversi string ke integer
- MySQL/MariaDB mengurutkan secara numerik
- `lockForUpdate()` mencegah race condition

---

### 2. **Default Starting Code**

**SEBELUM:**
```php
$nextCode = '1151';  // ❌ 4-digit, bisa konflik dengan format 5-digit
```

**SESUDAH:**
```php
$nextCode = '11500';  // ✅ 5-digit, konsisten dan jelas
```

**Alasan:**
- Konsisten dengan format 5-digit
- Menghindari konflik dengan kode 4-digit
- Lebih banyak ruang untuk ekspansi (11500-11599 = 100 slot)

---

### 3. **Database Transaction**

Transaction sudah ada di `BahanPendukungController::store()`:

```php
return \DB::transaction(function () use ($request, $validated) {
    // Auto-create COA
    $autoCoaService = new \App\Services\AutoCoaService();
    $coa = $autoCoaService->createCoaForBahanPendukung($request->nama_bahan, auth()->id());
    
    // Create bahan pendukung
    $bahanPendukung = BahanPendukung::create($validated);
    
    // ...
});
```

**Proteksi:**
- `DB::transaction()` membungkus seluruh proses
- `lockForUpdate()` di dalam query COA
- Jika terjadi error, semua rollback otomatis

---

## CARA KERJA

### Skenario 1: Belum Ada COA Bahan Pendukung

**Kondisi Awal:**
```
Tabel coas: (kosong, atau hanya ada COA lain)
```

**User Action:**
```
Menambah Bahan Pendukung: Keju
```

**Proses:**
1. Query mencari kode terakhir yang `LIKE '115%'`
2. Tidak ditemukan (`$lastCoa = null`)
3. Set `$nextCode = '11500'`
4. Create COA:
   ```
   kode_akun: 11500
   nama_akun: Pers. Bahan Pendukung Keju
   ```

**Hasil:**
```
Kode COA: 11500 - Pers. Bahan Pendukung Keju
```

---

### Skenario 2: Menambah Bahan Kedua

**Kondisi Awal:**
```
11500 - Pers. Bahan Pendukung Keju
```

**User Action:**
```
Menambah Bahan Pendukung: Keju Slice
```

**Proses:**
1. Query mencari kode terakhir:
   ```sql
   SELECT * FROM coas 
   WHERE user_id = 1 
   AND kode_akun LIKE '115%' 
   AND nama_akun LIKE 'Pers. Bahan Pendukung%'
   ORDER BY CAST(kode_akun AS UNSIGNED) DESC
   LIMIT 1
   FOR UPDATE
   ```
2. Ditemukan: `11500`
3. Increment: `intval('11500') + 1 = 11501`
4. Validate: `str_starts_with('11501', '115')` = true ✅
5. Create COA:
   ```
   kode_akun: 11501
   nama_akun: Pers. Bahan Pendukung Keju Slice
   ```

**Hasil:**
```
11500 - Pers. Bahan Pendukung Keju
11501 - Pers. Bahan Pendukung Keju Slice
```

---

### Skenario 3: Menambah Bahan Ketiga

**Kondisi Awal:**
```
11500 - Pers. Bahan Pendukung Keju
11501 - Pers. Bahan Pendukung Keju Slice
```

**User Action:**
```
Menambah Bahan Pendukung: Mentega
```

**Proses:**
1. Query numeric sorting: `ORDER BY CAST(kode_akun AS UNSIGNED) DESC`
2. Ditemukan: `11501` (bukan `11500`)
3. Increment: `11501 + 1 = 11502`
4. Create COA:
   ```
   kode_akun: 11502
   nama_akun: Pers. Bahan Pendukung Mentega
   ```

**Hasil:**
```
11500 - Pers. Bahan Pendukung Keju
11501 - Pers. Bahan Pendukung Keju Slice
11502 - Pers. Bahan Pendukung Mentega
```

---

### Skenario 4: Concurrent Insert (Race Condition)

**User A dan User B menambah bahan hampir bersamaan:**

```
Time    | User A                        | User B
--------|-------------------------------|-------------------------------
10:00:00| BEGIN TRANSACTION             | BEGIN TRANSACTION
10:00:01| Query last COA + LOCK         | (waiting for lock...)
10:00:02| Found: 11501                  | (still waiting...)
10:00:03| Generate: 11502               | (still waiting...)
10:00:04| INSERT 11502                  | (still waiting...)
10:00:05| COMMIT                        | Query last COA + LOCK
10:00:06|                               | Found: 11502 (bukan 11501!)
10:00:07|                               | Generate: 11503
10:00:08|                               | INSERT 11503
10:00:09|                               | COMMIT
```

**Hasil:**
```
11500 - Pers. Bahan Pendukung Keju
11501 - Pers. Bahan Pendukung Keju Slice
11502 - Pers. Bahan Pendukung Mentega (User A)
11503 - Pers. Bahan Pendukung Susu (User B)
```

**✅ Tidak ada duplicate!**

**Proteksi:**
- `lockForUpdate()` membuat User B menunggu sampai User A commit
- Setelah User A commit, User B query ulang dan dapat kode terbaru
- Transaction rollback otomatis jika ada error

---

## PERBANDINGAN STRING VS NUMERIC SORTING

### String Sorting (SALAH) ❌

```php
->orderBy('kode_akun', 'desc')
```

**Hasil Query:**
```
Kode di DB:    String Sort:
11500          1152  ← SALAH! (string '1152' > '11500')
11501          1151  ← SALAH!
1152           11501
1151           11500
```

**Impact:**
- System pikir '1152' adalah kode terakhir
- Kode baru: '1152' + 1 = '1153'
- Tapi '11500' sudah ada!

---

### Numeric Sorting (BENAR) ✅

```php
->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
```

**Hasil Query:**
```
Kode di DB:    Numeric Sort:
11500          11501  ← BENAR!
11501          11500  ← BENAR!
1152           1152
1151           1151
```

**Impact:**
- System benar ambil '11501' sebagai kode terakhir
- Kode baru: '11501' + 1 = '11502' ✅
- Tidak ada konflik

---

## VALIDASI & BOUNDARIES

### Validasi Prefix

```php
if (!str_starts_with($nextCode, '115')) {
    throw new \Exception('Kode akun Pers. Bahan Pendukung sudah penuh.');
}
```

**Contoh Error:**
```
Last code: 11599
Next code: 11600 (masih dimulai dengan '116', bukan '115')
→ Exception thrown ✅
```

**Batasan:**
- Prefix '115': 11500-11599 (100 slot)
- Atau bisa expand ke: 115000-115999 (1000 slot)

---

### Transition dari 4-digit ke 5-digit

Kode lama mungkin masih ada dalam format 4-digit (1151, 1152, dst).

**Handling:**
```php
if (strlen($lastCode) == 4 && $lastCode == '1159') {
    // Move to 5-digit: 11500
    $nextCode = '11500';
}
```

**Flow:**
```
1151 → 1152 → ... → 1159 → 11500 → 11501 → ...
```

---

## FILE YANG DIUBAH

### 1. **app/Services/AutoCoaService.php**

#### Method: `createCoaForBahanPendukung()`

**Perubahan:**

1. **Numeric Sorting:**
   ```php
   // BEFORE
   ->orderBy('kode_akun', 'desc')
   
   // AFTER
   ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
   ```

2. **Default Starting Code:**
   ```php
   // BEFORE
   $nextCode = '1151';
   
   // AFTER
   $nextCode = '11500';
   ```

3. **Comment Updates:**
   ```php
   // Added comments explaining numeric sorting
   // IMPORTANT: Cast to unsigned integer for proper numeric sorting
   ```

---

#### Method: `createCoaForBahanBaku()`

**Perubahan:** Sama dengan Bahan Pendukung untuk konsistensi

1. Numeric sorting dengan `CAST(kode_akun AS UNSIGNED)`
2. Default starting code: `'11400'` (bukan `'1141'`)

---

## TESTING

### Test Case 1: First Entry

```php
// Given: No COA exists
// When: Add first bahan pendukung
// Then: COA created with code 11500

$coa = $autoCoaService->createCoaForBahanPendukung('Keju', 1);
assertEquals('11500', $coa->kode_akun);
assertEquals('Pers. Bahan Pendukung Keju', $coa->nama_akun);
```

---

### Test Case 2: Sequential Entries

```php
// Given: COA 11500 exists
// When: Add second bahan pendukung
// Then: COA created with code 11501

$coa1 = $autoCoaService->createCoaForBahanPendukung('Keju', 1);
$coa2 = $autoCoaService->createCoaForBahanPendukung('Keju Slice', 1);

assertEquals('11500', $coa1->kode_akun);
assertEquals('11501', $coa2->kode_akun);
```

---

### Test Case 3: Numeric Sorting

```php
// Given: COAs 1151, 1152, 11500, 11501 exist
// When: Add new bahan pendukung
// Then: COA created with code 11502 (not 1153)

// Setup existing COAs
Coa::create(['kode_akun' => '1151', ...]);
Coa::create(['kode_akun' => '1152', ...]);
Coa::create(['kode_akun' => '11500', ...]);
Coa::create(['kode_akun' => '11501', ...]);

$coa = $autoCoaService->createCoaForBahanPendukung('Mentega', 1);
assertEquals('11502', $coa->kode_akun);  // Not 1153!
```

---

### Test Case 4: Duplicate Name

```php
// Given: COA for 'Keju' already exists
// When: Add bahan pendukung with same name
// Then: Return existing COA, don't create new

$coa1 = $autoCoaService->createCoaForBahanPendukung('Keju', 1);
$coa2 = $autoCoaService->createCoaForBahanPendukung('Keju', 1);

assertEquals($coa1->id, $coa2->id);  // Same COA
assertEquals($coa1->kode_akun, $coa2->kode_akun);
```

---

### Test Case 5: Multi-Tenant Isolation

```php
// Given: User 1 has COA 11500
// When: User 2 adds bahan pendukung
// Then: User 2 gets COA 11500 (independent from User 1)

$coa1 = $autoCoaService->createCoaForBahanPendukung('Keju', 1);  // User 1
$coa2 = $autoCoaService->createCoaForBahanPendukung('Keju', 2);  // User 2

assertEquals('11500', $coa1->kode_akun);
assertEquals('11500', $coa2->kode_akun);  // Same code, different user
assertNotEquals($coa1->id, $coa2->id);
```

---

### Test Case 6: Concurrent Insert

```php
// Simulate concurrent inserts
// Both should get different codes

DB::transaction(function() {
    $coa1 = $autoCoaService->createCoaForBahanPendukung('Mentega', 1);
});

DB::transaction(function() {
    $coa2 = $autoCoaService->createCoaForBahanPendukung('Susu', 1);
});

assertNotEquals($coa1->kode_akun, $coa2->kode_akun);
```

---

## KEUNTUNGAN SOLUSI INI

### 1. ✅ **Setiap Bahan Unik**
- Kode COA berbeda untuk setiap bahan
- Mudah tracking dan identifikasi
- Tidak ada konflik

### 2. ✅ **Numeric Sorting Akurat**
- Kode terakhir diambil dengan benar
- Tidak ada skip atau overlap
- Urutan konsisten

### 3. ✅ **Race Condition Handled**
- `lockForUpdate()` mencegah concurrent issue
- Transaction rollback jika error
- Data integrity terjaga

### 4. ✅ **Multi-Tenant Safe**
- Filter by `user_id` di semua query
- User tidak bisa lihat/ubah COA user lain
- Data isolation sempurna

### 5. ✅ **Scalable**
- Format 5-digit: 11500-11599 (100 slot)
- Bisa expand ke 6-digit: 115000-115999 (1000 slot)
- Clear error message jika penuh

### 6. ✅ **Idempotent**
- Jika nama sama, return existing COA
- Tidak create duplicate
- Safe untuk retry

---

## TROUBLESHOOTING

### Problem 1: Kode masih duplikat

**Penyebab:** Database belum diupdate atau cache.

**Solusi:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

### Problem 2: Kode loncat (11500 → 11502)

**Penyebab:** Ada 11501 yang dihapus, atau transaction rollback.

**Solusi:** Ini normal, kode tidak harus sequential. Yang penting unique.

---

### Problem 3: Error "Kode akun sudah penuh"

**Penyebab:** Sudah mencapai 11599 (atau batas prefix '115').

**Solusi:** 
1. Review kode yang tidak terpakai
2. Atau expand ke 6-digit (115000-115999)
3. Update validasi di `AutoCoaService`

---

### Problem 4: Concurrent insert masih duplikat

**Penyebab:** Database engine tidak support `lockForUpdate()` (misal: MyISAM).

**Solusi:** Pastikan menggunakan InnoDB:
```sql
ALTER TABLE coas ENGINE=InnoDB;
```

---

## KESIMPULAN

### ✅ MASALAH TERATASI

**SEBELUM:**
- ❌ Semua bahan menggunakan kode hardcode '11500'
- ❌ String sorting memberikan hasil salah
- ❌ Potensi race condition pada concurrent insert

**SESUDAH:**
- ✅ Setiap bahan memiliki kode unik dan berurutan
- ✅ Numeric sorting menggunakan `CAST(kode_akun AS UNSIGNED)`
- ✅ Database transaction + `lockForUpdate()` mencegah duplikasi
- ✅ Multi-tenant safe dengan filter `user_id`
- ✅ Idempotent: nama sama = return existing COA

### Implementasi:
- ✅ Numeric sorting dengan `CAST`
- ✅ Default starting code: `11500`
- ✅ Transaction wrapper di controller
- ✅ Lock for update di query
- ✅ Prefix validation

### Hasil:
```
User menambah:
1. Keju        → 11500 - Pers. Bahan Pendukung Keju
2. Keju Slice  → 11501 - Pers. Bahan Pendukung Keju Slice
3. Mentega     → 11502 - Pers. Bahan Pendukung Mentega
4. Susu        → 11503 - Pers. Bahan Pendukung Susu
```

Setiap bahan memiliki kode COA yang berbeda dan urutan numerik yang benar!

---

**Tanggal Perbaikan:** 10 Juli 2026  
**Versi:** 1.0  
**Status:** ✅ FIXED - Numeric Sorting + Transaction
