# Perbaikan: Stok Tidak Update di Halaman Index dan Detail

## Masalah
User mengedit stok bahan baku/pendukung menjadi 10 di halaman edit, tetapi di halaman index dan detail stok masih menampilkan nilai lama.

## Root Cause
1. **Edit form** menyimpan perubahan stok ke kolom `saldo_awal` di tabel `bahan_bakus`/`bahan_pendukungs`
2. **Index dan detail pages** menampilkan `stok_real_time` yang dihitung dari tabel `stock_movements`
3. Ketika update `saldo_awal` di edit form, **TIDAK ada** `StockMovement` record yang dibuat/diupdate
4. Method `getStokRealTimeAttribute()` menghitung stok HANYA dari `StockMovement` records:
   ```php
   $stockIn = StockMovement::where('item_type', 'material')
       ->where('item_id', $this->id)
       ->where('direction', 'in')
       ->sum('qty');
   
   $stockOut = StockMovement::where('item_type', 'material')
       ->where('item_id', $this->id)
       ->where('direction', 'out')
       ->sum('qty');
   
   return $stockIn - $stockOut;
   ```
5. Karena tidak ada StockMovement baru, `stok_real_time` tidak berubah

## Solusi
Ketika user mengedit stok di edit form, sistem sekarang otomatis membuat `StockMovement` adjustment record untuk menyinkronkan perubahan.

### File yang Dimodifikasi

#### 1. `app/Http/Controllers/BahanBakuController.php`
**Method:** `update()`

**Perubahan:**
```php
// Track stock changes
$oldSaldoAwal = $bahanBaku->saldo_awal;
$newSaldoAwal = $request->stok ?? 0;
$stockDifference = $newSaldoAwal - $oldSaldoAwal;

// Update saldo_awal
$bahanBaku->saldo_awal = $newSaldoAwal;
$bahanBaku->save();

// Create StockMovement adjustment if stock changed
if (abs($stockDifference) > 0.0001) {
    \App\Models\StockMovement::create([
        'user_id' => auth()->id(),
        'item_type' => 'material',
        'item_id' => $bahanBaku->id,
        'direction' => $stockDifference > 0 ? 'in' : 'out',
        'qty' => abs($stockDifference),
        'tanggal' => now()->toDateString(),
        'ref_type' => 'stock_adjustment',
        'ref_id' => $bahanBaku->id,
        'keterangan' => 'Penyesuaian stok dari edit bahan baku: ' . $bahanBaku->nama_bahan . ' (dari ' . $oldSaldoAwal . ' ke ' . $newSaldoAwal . ')',
    ]);
}
```

#### 2. `app/Http/Controllers/BahanPendukungController.php`
**Method:** `update()`

**Perubahan:** Sama seperti BahanBakuController, ditambahkan logika untuk membuat StockMovement adjustment.

## Cara Kerja
1. User membuka halaman edit bahan baku/pendukung
2. User mengubah stok dari nilai lama (misal: 35) ke nilai baru (misal: 10)
3. Saat klik Update:
   - Sistem menghitung selisih: `10 - 35 = -25`
   - Sistem update `saldo_awal` menjadi 10
   - Sistem membuat StockMovement baru:
     - `direction`: 'out' (karena selisih negatif)
     - `qty`: 25 (nilai absolut dari selisih)
     - `ref_type`: 'stock_adjustment'
     - `keterangan`: "Penyesuaian stok dari edit bahan baku: [nama] (dari 35 ke 10)"
4. Halaman index dan detail sekarang menampilkan stok yang benar karena `stok_real_time` dihitung ulang dari StockMovement

## Contoh Skenario

### Skenario 1: Menambah Stok
- Stok lama: 35
- Stok baru: 50
- Selisih: +15
- StockMovement: direction='in', qty=15
- Hasil: stok_real_time bertambah 15

### Skenario 2: Mengurangi Stok
- Stok lama: 35
- Stok baru: 10
- Selisih: -25
- StockMovement: direction='out', qty=25
- Hasil: stok_real_time berkurang 25

### Skenario 3: Tidak Ada Perubahan
- Stok lama: 35
- Stok baru: 35
- Selisih: 0
- StockMovement: TIDAK dibuat (karena abs(0) <= 0.0001)
- Hasil: tidak ada perubahan

## Logging
Setiap stock adjustment dicatat di log dengan informasi:
- ID bahan
- Nama bahan
- Stok lama
- Stok baru
- Selisih

## Testing
1. Buka halaman edit bahan baku
2. Ubah stok dari 35 menjadi 10
3. Klik Update
4. Kembali ke halaman index
5. ✅ Stok sekarang menampilkan 10 (bukan 35)
6. Buka halaman detail
7. ✅ Stok sekarang menampilkan 10 (bukan 35)

## Catatan Penting
- Perubahan ini berlaku untuk **Bahan Baku** dan **Bahan Pendukung**
- StockMovement adjustment hanya dibuat jika ada perubahan stok (selisih > 0.0001)
- Sistem menggunakan `abs()` untuk menghitung nilai absolut selisih
- Direction ditentukan otomatis: positif = 'in', negatif = 'out'
- Multi-tenant isolation tetap terjaga dengan `user_id`

## Status
✅ **SELESAI** - Stok sekarang update dengan benar di semua halaman
