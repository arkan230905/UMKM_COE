# FIX: Jabatan BTKL Menampilkan "0 Pegawai" Padahal Ada Data

## 🔍 MASALAH

Di web production Anda:
- **Halaman Pegawai** (`/master-data/pegawai`): Menampilkan pegawai dengan jabatan "Pengukusan" dan "Pengemasan"
- **Halaman BTKL** (`/master-data/btkl/create`): Dropdown "Jabatan BTKL" menampilkan "Pengemasan (0 pegawai @ Rp 17,000/jam)" dan "Pengukusan (0 pegawai @ Rp 20,000/jam)"

**Masalah**: Dropdown BTKL menghitung **"0 pegawai"** padahal ada 2 pegawai!

---

## 🎯 ROOT CAUSE

### Penyebab Utama:

1. **Pegawai tidak memiliki `jabatan_id`**
   - Tabel `pegawais` memiliki kolom `jabatan` (string nama jabatan)
   - Tabel `pegawais` memiliki kolom `jabatan_id` (foreign key ke tabel `jabatans`)
   - Jika `jabatan_id` NULL, maka pegawai tidak terhitung

2. **Pegawai memiliki `jabatan_id` yang salah**
   - `jabatan_id` menunjuk ke jabatan yang tidak ada
   - `jabatan_id` menunjuk ke jabatan milik user lain (MULTI-TENANT VIOLATION!)

3. **Query di BtklController tidak cukup strict**
   - Query hanya filter `pegawais.user_id` dan `pegawais.jabatan_id`
   - Tidak memverifikasi bahwa `jabatan_id` tersebut milik user yang sama

---

## 🔧 SOLUSI

### 1. Fix Query di BtklController (SUDAH DIPERBAIKI)

**File**: `app/Http/Controllers/MasterData/BtklController.php`

**Perubahan**:

```php
// BEFORE (SALAH - Tidak verify jabatan ownership)
$pegawaiCount = DB::table('pegawais')
    ->select('jabatan_id', DB::raw('COUNT(*) as jumlah'))
    ->where('user_id', $userId)
    ->whereNotNull('jabatan_id')
    ->groupBy('jabatan_id')
    ->pluck('jumlah', 'jabatan_id');

// AFTER (BENAR - Verify jabatan ownership dengan JOIN)
$pegawaiCount = DB::table('pegawais as p')
    ->join('jabatans as j', function($join) use ($userId) {
        $join->on('p.jabatan_id', '=', 'j.id')
             ->where('j.user_id', '=', $userId); // CRITICAL: Ensure jabatan belongs to same user
    })
    ->where('p.user_id', $userId) // CRITICAL: Multi-tenant filter
    ->where('j.kategori', 'btkl') // Only BTKL jabatan
    ->whereNotNull('p.jabatan_id')
    ->select('p.jabatan_id', DB::raw('COUNT(*) as jumlah'))
    ->groupBy('p.jabatan_id')
    ->pluck('jumlah', 'p.jabatan_id');
```

**Penjelasan**:
- **JOIN dengan `jabatans`**: Memastikan `jabatan_id` benar-benar ada dan milik user yang sama
- **Filter `j.user_id = $userId`**: CRITICAL untuk multi-tenant isolation
- **Filter `j.kategori = 'btkl'`**: Hanya hitung pegawai di jabatan BTKL

---

### 2. Fix Data Inconsistency (JALANKAN DI VPS)

**Script**: `fix_jabatan_pegawai_multi_tenant.php`

Script ini akan:
1. ✅ Fix pegawai tanpa `jabatan_id` (set berdasarkan nama jabatan)
2. ✅ Fix pegawai dengan `jabatan_id` yang tidak valid
3. ✅ Sync nama jabatan (`pegawais.jabatan` != `jabatans.nama`)
4. ✅ Verify multi-tenant isolation (pegawai tidak boleh punya jabatan dari user lain)

**Cara Jalankan**:

```bash
# Di VPS production
php fix_jabatan_pegawai_multi_tenant.php
```

**Output yang Diharapkan**:

```
=== FIX JABATAN PEGAWAI MULTI-TENANT ===

Processing User ID: 1 (Admin SIMCOST)
------------------------------------------------------------
1. Checking pegawai tanpa jabatan_id...
   Found 2 pegawai without jabatan_id
      ✅ Fixed: Budi Susanto -> jabatan_id = 5 (Pengukusan)
      ✅ Fixed: Dedi Gunawan -> jabatan_id = 6 (Pengemasan)

2. Checking pegawai dengan invalid jabatan_id...
   ✅ All jabatan_id are valid

3. Syncing jabatan names...
   ✅ All jabatan names are synced

4. Verifying multi-tenant isolation...
   ✅ Multi-tenant isolation is correct

Summary for User 1:
  Fixed: 2
  Errors: 0

=== FINAL VERIFICATION ===

User 1 (Admin SIMCOST):
  Total Pegawai: 2
  With jabatan_id: 2
  Without jabatan_id: 0
  BTKL Pegawai: 2
  BTKL with jabatan_id: 2

=== COMPLETED ===
✅ All data has been checked and fixed.
   Please verify in your web application.
```

---

## 📋 CHECKLIST DEPLOYMENT

### Di Local (Sudah Selesai)

- [x] ✅ Fix `BtklController::getJabatanBtklForUser()` method
- [x] ✅ Tambahkan JOIN dengan `jabatans` untuk verify ownership
- [x] ✅ Tambahkan filter `j.user_id = $userId`
- [x] ✅ Tambahkan filter `j.kategori = 'btkl'`
- [x] ✅ Buat script `fix_jabatan_pegawai_multi_tenant.php`
- [x] ✅ Buat script `check_jabatan_pegawai_data.php`
- [x] ✅ Buat dokumentasi `FIX_JABATAN_PEGAWAI_ISSUE.md`

### Di VPS Production (Perlu Dilakukan)

- [ ] ⏳ Push code ke GitHub
- [ ] ⏳ Jenkins deploy ke VPS
- [ ] ⏳ Jalankan `php fix_jabatan_pegawai_multi_tenant.php` di VPS
- [ ] ⏳ Verify di web: Buka `/master-data/btkl/create`
- [ ] ⏳ Check dropdown "Jabatan BTKL" harus menampilkan jumlah pegawai yang benar

---

## 🧪 TESTING

### Test Case 1: Dropdown BTKL Menampilkan Jumlah Pegawai yang Benar

**Steps**:
1. Login ke web production
2. Buka `/master-data/btkl/create`
3. Lihat dropdown "Jabatan BTKL"

**Expected Result**:
```
Pengemasan (1 pegawai @ Rp 17,000/jam)
Pengukusan (1 pegawai @ Rp 20,000/jam)
```

**Bukan**:
```
Pengemasan (0 pegawai @ Rp 17,000/jam)  ❌
Pengukusan (0 pegawai @ Rp 20,000/jam)  ❌
```

---

### Test Case 2: Halaman Pegawai Menampilkan Data yang Sama

**Steps**:
1. Buka `/master-data/pegawai`
2. Check kolom "Jabatan"

**Expected Result**:
- Budi Susanto: Pengukusan
- Dedi Gunawan: Pengemasan

---

### Test Case 3: Multi-Tenant Isolation

**Steps**:
1. Login sebagai User A
2. Buka `/master-data/btkl/create`
3. Dropdown hanya menampilkan jabatan milik User A
4. Logout, login sebagai User B
5. Buka `/master-data/btkl/create`
6. Dropdown hanya menampilkan jabatan milik User B

**Expected Result**:
- User A tidak bisa melihat jabatan User B
- User B tidak bisa melihat jabatan User A
- Jumlah pegawai hanya menghitung pegawai milik user yang sama

---

## 🔒 MULTI-TENANT SECURITY

### Prinsip Multi-Tenant:

1. **SEMUA query HARUS filter by `user_id`**
   ```php
   ->where('user_id', auth()->id())
   ```

2. **JOIN dengan tabel lain HARUS verify ownership**
   ```php
   ->join('jabatans as j', function($join) use ($userId) {
       $join->on('p.jabatan_id', '=', 'j.id')
            ->where('j.user_id', '=', $userId); // CRITICAL!
   })
   ```

3. **Jangan hanya filter tabel utama, filter juga tabel relasi**
   ```php
   // SALAH ❌
   $pegawais = Pegawai::where('user_id', $userId)->get();
   // Jika pegawai punya jabatan_id dari user lain, data bocor!
   
   // BENAR ✅
   $pegawais = Pegawai::where('user_id', $userId)
       ->whereHas('jabatan', function($q) use ($userId) {
           $q->where('user_id', $userId);
       })
       ->get();
   ```

4. **Selalu verify foreign key ownership**
   - Sebelum save: Check apakah `jabatan_id` milik user yang sama
   - Sebelum update: Check apakah `jabatan_id` baru milik user yang sama
   - Saat query: JOIN dan filter by `user_id`

---

## 📝 NOTES

### Kenapa Masalah Ini Terjadi?

1. **Legacy Data**: Data lama mungkin dibuat sebelum kolom `jabatan_id` ada
2. **Manual Insert**: Data diinsert manual tanpa set `jabatan_id`
3. **Migration Issue**: Migration tidak backfill `jabatan_id` dari `jabatan` (string)
4. **Multi-Tenant Violation**: Pegawai punya `jabatan_id` dari user lain

### Pencegahan di Masa Depan:

1. **Validation di Controller**:
   ```php
   $validated = $request->validate([
       'jabatan_id' => [
           'required',
           'exists:jabatans,id',
           Rule::exists('jabatans', 'id')->where('user_id', auth()->id())
       ]
   ]);
   ```

2. **Database Constraint**:
   ```sql
   -- Add foreign key with ON DELETE CASCADE
   ALTER TABLE pegawais 
   ADD CONSTRAINT fk_pegawais_jabatan 
   FOREIGN KEY (jabatan_id) 
   REFERENCES jabatans(id) 
   ON DELETE SET NULL;
   ```

3. **Model Observer**:
   ```php
   // PegawaiObserver.php
   public function saving(Pegawai $pegawai)
   {
       if ($pegawai->jabatan_id) {
           $jabatan = Jabatan::where('id', $pegawai->jabatan_id)
               ->where('user_id', auth()->id())
               ->first();
           
           if (!$jabatan) {
               throw new \Exception('Jabatan tidak valid atau bukan milik Anda');
           }
       }
   }
   ```

---

## 🚀 DEPLOYMENT STEPS

### 1. Commit & Push

```bash
git add app/Http/Controllers/MasterData/BtklController.php
git add fix_jabatan_pegawai_multi_tenant.php
git add check_jabatan_pegawai_data.php
git add FIX_JABATAN_PEGAWAI_ISSUE.md

git commit -m "Fix: BTKL dropdown showing 0 pegawai - multi-tenant isolation

- Fixed BtklController::getJabatanBtklForUser() to JOIN with jabatans table
- Added user_id verification for jabatan ownership
- Added kategori='btkl' filter
- Created fix script for data inconsistency
- Created check script for verification
- Ensured multi-tenant isolation"

git push origin main
```

### 2. Deploy via Jenkins

- Tunggu Jenkins selesai deploy
- Check logs untuk memastikan tidak ada error

### 3. Run Fix Script di VPS

```bash
# SSH ke VPS
ssh user@your-vps-ip

# Navigate to project directory
cd /path/to/your/project

# Run fix script
php fix_jabatan_pegawai_multi_tenant.php

# Verify
php check_jabatan_pegawai_data.php
```

### 4. Test di Web

- Login ke web production
- Test semua test case di atas
- Pastikan dropdown BTKL menampilkan jumlah pegawai yang benar

---

## ✅ SUCCESS CRITERIA

- [ ] Dropdown "Jabatan BTKL" menampilkan jumlah pegawai yang benar (bukan 0)
- [ ] Halaman Pegawai dan BTKL menampilkan data jabatan yang konsisten
- [ ] Multi-tenant isolation berfungsi dengan benar
- [ ] Tidak ada pegawai dengan `jabatan_id` NULL (kecuali memang belum diset)
- [ ] Tidak ada pegawai dengan `jabatan_id` dari user lain

---

**Status**: ✅ READY TO DEPLOY

**Date**: 6 Mei 2026

**Priority**: HIGH (Multi-tenant security issue)
