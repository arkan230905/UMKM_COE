# Dokumentasi: Menonaktifkan Logika Saldo Awal COA untuk Bahan Baku & Bahan Pendukung

## 📋 Deskripsi

Sistem ini menonaktifkan logika yang secara otomatis mengupdate saldo awal COA persediaan setiap kali ada input atau perubahan data bahan baku dan bahan pendukung. Dengan perubahan ini, input harga satuan dan stok bahan tidak akan lagi otomatis menambahkan nominal ke saldo awal COA persediaan.

## 🎯 Tujuan

- **Menghilangkan update otomatis** saldo awal COA saat input bahan baku/pendukung
- **Mencegah pencatatan nominal** bahan ke saldo awal persediaan
- **Menjaga laporan keuangan** tetap bersih dari nominal bahan di saldo awal
- **Mempertahankan tracking stok** bahan tanpa mempengaruhi COA

## 🔧 Perubahan yang Dilakukan

### 1. Modifikasi Controller

#### `BahanBakuController.php`
**Method `store()`** - Logika update saldo awal COA dinonaktifkan:
```php
// SEBELUM (AKTIF):
if ($request->coa_persediaan_id && ($request->stok ?? 0) > 0) {
    $coa = \App\Models\Coa::where('kode_akun', $request->coa_persediaan_id)
        ->where('user_id', auth()->id())
        ->first();
        
    if ($coa) {
        $nilaiSaldoAwal = ($request->stok ?? 0) * ($request->harga_satuan ?? 0);
        $coa->saldo_awal = ($coa->saldo_awal ?? 0) + $nilaiSaldoAwal;
        $coa->save();
    }
}

// SESUDAH (DINONAKTIFKAN):
if ($request->coa_persediaan_id && ($request->stok ?? 0) > 0) {
    \Log::info("Skipping COA saldo awal update for bahan baku", [
        'bahan_baku' => $bahanBaku->nama_bahan,
        'coa_code' => $request->coa_persediaan_id,
        'stok' => $request->stok,
        'harga_satuan' => $request->harga_satuan,
        'reason' => 'COA saldo awal update disabled for bahan baku'
    ]);
    // Logika update COA di-comment out
}
```

#### `BahanPendukungController.php`
**Method `store()`** - Logika serupa dinonaktifkan dengan logging yang sama.

### 2. Modifikasi Service

#### `PersediaanSaldoAwalService.php`

**Method `updateSaldoAwalItem()`** - Dinonaktifkan:
```php
// SEBELUM: Menghitung dan mengupdate saldo awal COA
// SESUDAH: Selalu return false dengan logging
public static function updateSaldoAwalItem($item, $type = 'bahan_baku')
{
    \Log::info("Skipping saldo awal update for item", [
        'item_type' => $type,
        'item_id' => $item->id ?? 'unknown',
        'item_name' => $item->nama_bahan ?? 'unknown',
        'reason' => 'Saldo awal COA update disabled for bahan baku/pendukung'
    ]);
    
    return false; // Selalu return false
}
```

**Method `postSaldoAwalPersediaan()`** - Dimodifikasi:
- Reset saldo awal COA bahan baku/pendukung ke 0
- Skip posting dari bahan baku dan bahan pendukung
- Tetap aktif untuk produk (barang jadi)

### 3. Database Migration

#### `add_coa_exclusion_flags_to_bahan_tables.php`
Menambahkan kolom flag exclusion:
```php
// Untuk tabel bahan_bakus dan bahan_pendukungs
$table->boolean('exclude_from_coa')->default(true);
$table->boolean('coa_recording_disabled')->default(true);
$table->timestamp('coa_exclusion_date')->nullable();
```

### 4. Service Baru

#### `CoaSaldoAwalDisabler.php`
Service untuk mengelola penonaktifan logika:
- `shouldSkipSaldoAwalUpdate()` - Cek apakah item harus di-skip
- `updateCoaSaldoAwal()` - Wrapper yang selalu skip update
- `resetBahanCoaSaldoAwal()` - Reset saldo awal COA bahan ke nol
- `disableExistingBahanSaldoAwal()` - Set flag exclusion untuk semua bahan

## 🚀 Implementasi

### Langkah 1: Jalankan Migrasi
```bash
php artisan migrate --force
```

### Langkah 2: Nonaktifkan Logika
```bash
php disable_bahan_saldo_awal_logic.php
```

Script ini akan:
1. Set flag exclusion untuk semua bahan yang sudah ada
2. Reset saldo awal COA bahan baku/pendukung ke nol
3. Verifikasi hasil perubahan

### Langkah 3: Verifikasi
```bash
php test_bahan_saldo_awal_disabled.php
```

## 📊 Hasil Implementasi

### Sebelum Perubahan
- Input bahan baku stok 100 @ Rp 5.000 → Saldo awal COA +Rp 500.000
- Edit stok/harga bahan → Saldo awal COA berubah
- PersediaanSaldoAwalService → Update saldo awal COA

### Setelah Perubahan
- Input bahan baku stok 100 @ Rp 5.000 → Saldo awal COA TIDAK berubah
- Edit stok/harga bahan → Saldo awal COA TIDAK berubah
- PersediaanSaldoAwalService → Skip update untuk bahan (log: "Skipping saldo awal update")

## 🔍 Verifikasi

### 1. Cek Flag Database
```sql
-- Semua bahan harus memiliki flag exclusion = true
SELECT nama_bahan, exclude_from_coa, coa_recording_disabled 
FROM bahan_bakus 
WHERE exclude_from_coa = 1;

SELECT nama_bahan, exclude_from_coa, coa_recording_disabled 
FROM bahan_pendukungs 
WHERE exclude_from_coa = 1;
```

### 2. Cek Saldo Awal COA
```sql
-- Saldo awal COA bahan harus 0
SELECT kode_akun, nama_akun, saldo_awal 
FROM coas 
WHERE kode_akun LIKE '1104%' OR kode_akun LIKE '113%';
```

### 3. Cek Log Aplikasi
```bash
tail -f storage/logs/laravel.log | grep "Skipping.*saldo.*awal"
```

## 📝 Logging

Sistem mencatat log lengkap untuk setiap aksi yang di-skip:

```
[INFO] Skipping COA saldo awal update for bahan baku: 
{
    "bahan_baku": "Tepung Terigu",
    "coa_code": "1104",
    "stok": 100,
    "harga_satuan": 5000,
    "reason": "COA saldo awal update disabled for bahan baku"
}

[INFO] Skipping saldo awal update for item: 
{
    "item_type": "bahan_baku",
    "item_id": 1,
    "item_name": "Tepung Terigu",
    "reason": "Saldo awal COA update disabled for bahan baku/pendukung"
}
```

## 🎯 Dampak Perubahan

### Yang Berubah
- ❌ Input bahan baku/pendukung tidak mengupdate saldo awal COA
- ❌ Edit stok/harga bahan tidak mengupdate saldo awal COA
- ❌ PersediaanSaldoAwalService skip untuk bahan
- ✅ Laporan keuangan bersih dari nominal bahan di saldo awal

### Yang Tetap Normal
- ✅ Tracking stok bahan tetap berjalan
- ✅ Pembelian bahan tetap tercatat (tapi tidak ke COA persediaan)
- ✅ BOM dan kalkulasi biaya tetap normal
- ✅ Logika untuk produk (barang jadi) tetap aktif

## 🔄 Rollback (Jika Diperlukan)

Untuk mengaktifkan kembali logika lama:

### 1. Update Flag Database
```sql
UPDATE bahan_bakus SET exclude_from_coa = 0, coa_recording_disabled = 0;
UPDATE bahan_pendukungs SET exclude_from_coa = 0, coa_recording_disabled = 0;
```

### 2. Uncomment Kode di Controller
Hapus comment pada logika update saldo awal COA di:
- `BahanBakuController::store()`
- `BahanPendukungController::store()`

### 3. Restore Service
Uncomment logika asli di `PersediaanSaldoAwalService::updateSaldoAwalItem()`

## ⚠️ Catatan Penting

1. **Backup Database**: Selalu backup sebelum implementasi
2. **Test Environment**: Test di development dulu
3. **Monitor Log**: Pantau log setelah implementasi
4. **Verifikasi Laporan**: Cek laporan keuangan setelah perubahan
5. **Produk Tetap Aktif**: Logika untuk barang jadi tidak berubah

## 🎉 Kesimpulan

Implementasi berhasil menonaktifkan logika update saldo awal COA untuk bahan baku dan bahan pendukung. Sistem sekarang:

- ✅ Tidak mencatat nominal bahan ke saldo awal COA
- ✅ Menjaga laporan keuangan tetap bersih
- ✅ Mempertahankan semua fungsi tracking dan operasional lainnya
- ✅ Memberikan kontrol penuh atas pencatatan ke COA
- ✅ Dilengkapi logging untuk monitoring dan debugging

Bahan baku dan bahan pendukung sekarang tidak akan lagi otomatis mengirim data nominal ke saldo awal COA persediaan, sesuai dengan kebutuhan yang diminta.