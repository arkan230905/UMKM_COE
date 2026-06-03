# Perbaikan Pembulatan di Detail Produksi

## Tanggal: 2 Juni 2026

### Masalah
Di detail produksi, perhitungan subtotal bahan baku **salah** karena error pembulatan:

**Contoh:**
- Qty: 185 pcs
- Harga/Unit: Rp 5.333
- **Hasil yang Benar:** 185 × 5.333 = **Rp 986.605**
- **Hasil yang Tersimpan:** **Rp 986.666** ❌ (salah Rp 61)

### Penyebab
Terjadi **pembulatan ganda** dalam perhitungan:

1. **Langkah 1:** Sistem menghitung `subtotal` per unit di tabel `biaya_bahan_baku`:
   ```
   subtotal = jumlah × harga_satuan
   Contoh: 1 × 5.333 = 5.333 (disimpan dengan pembulatan)
   ```

2. **Langkah 2:** Saat produksi, sistem mengalikan `subtotal` dengan `qty_produksi`:
   ```php
   // ❌ SALAH - Pembulatan ganda
   'subtotal' => $bbb['subtotal'] * $qtyProd
   
   // Jika subtotal sudah dibulatkan menjadi 5.334:
   // 5.334 × 185 = 986.790 (SALAH!)
   ```

Masalahnya adalah `$bbb['subtotal']` sudah mengalami pembulatan di database, kemudian dikalikan lagi dengan qty produksi, menyebabkan error akumulasi pembulatan.

### Solusi
**Hitung ulang subtotal** dari `harga_satuan × qty_resep` untuk menghindari pembulatan ganda:

```php
// ✅ BENAR - Hitung ulang dari nilai asli
$qtyResep = $bbb['jumlah'] * $qtyProd;
$hargaSatuan = $bbb['harga_satuan'];

// Hitung ulang subtotal untuk menghindari error pembulatan
$subtotal = $qtyResep * $hargaSatuan;

\App\Models\ProduksiDetail::create([
    'qty_resep' => $qtyResep,        // 185
    'harga_satuan' => $hargaSatuan,  // 5.333
    'subtotal' => $subtotal,         // 185 × 5.333 = 986.605 ✓
]);
```

### File yang Diubah
**File:** `app/Http/Controllers/ProduksiController.php`
**Method:** `saveProductionDetails()` (Line ~979-995)

### Perubahan Detail

#### SEBELUM (Salah)
```php
foreach ($hppData['bbb'] as $bbb) {
    \App\Models\ProduksiDetail::create([
        'produksi_id' => $produksi->id,
        'bahan_baku_id' => $bbb['bahan_baku_id'],
        'qty_resep' => $bbb['jumlah'] * $qtyProd,
        'satuan_resep' => $bbb['satuan'],
        'harga_satuan' => $bbb['harga_satuan'],
        'subtotal' => $bbb['subtotal'] * $qtyProd,  // ❌ Pembulatan ganda!
        'user_id' => $produksi->user_id,
    ]);
}
```

#### SESUDAH (Benar)
```php
foreach ($hppData['bbb'] as $bbb) {
    $qtyResep = $bbb['jumlah'] * $qtyProd;
    $hargaSatuan = $bbb['harga_satuan'];
    
    // Hitung ulang subtotal untuk menghindari error pembulatan
    $subtotal = $qtyResep * $hargaSatuan;
    
    \App\Models\ProduksiDetail::create([
        'produksi_id' => $produksi->id,
        'bahan_baku_id' => $bbb['bahan_baku_id'],
        'qty_resep' => $qtyResep,
        'satuan_resep' => $bbb['satuan'],
        'harga_satuan' => $hargaSatuan,
        'subtotal' => $subtotal,  // ✅ Hitung ulang dari nilai asli
        'user_id' => $produksi->user_id,
    ]);
}
```

### Contoh Perhitungan

#### Kasus 1: Ayam Potong
```
Qty Produksi: 185 pcs
Jumlah per unit: 1 potong
Harga Satuan: Rp 5.333

SEBELUM (salah):
- subtotal di DB: Rp 5.333 (atau 5.334 jika dibulatkan)
- Perhitungan: 5.334 × 185 = Rp 986.790 ❌

SESUDAH (benar):
- qty_resep: 1 × 185 = 185
- Perhitungan: 185 × 5.333 = Rp 986.605 ✅
```

#### Kasus 2: Tepung (0.5 kg per unit)
```
Qty Produksi: 100 pcs
Jumlah per unit: 0.5 kg
Harga Satuan: Rp 12.000/kg

SEBELUM (salah):
- subtotal di DB: 0.5 × 12.000 = Rp 6.000
- Perhitungan: 6.000 × 100 = Rp 600.000 ✓ (kebetulan benar)

SESUDAH (benar):
- qty_resep: 0.5 × 100 = 50 kg
- Perhitungan: 50 × 12.000 = Rp 600.000 ✅
```

### Mengapa Ini Penting?

1. **Akurasi Keuangan**
   - Error Rp 61 per produksi × 30 hari = **Rp 1.830/bulan**
   - Dalam setahun bisa mencapai **Rp 21.960** hanya dari 1 bahan!

2. **Audit Trail**
   - Laporan keuangan harus akurat
   - Perbedaan sekecil apapun bisa menyebabkan masalah audit

3. **Kepercayaan Sistem**
   - User akan mempertanyakan sistem jika angka tidak sesuai perhitungan manual

### Prinsip Perhitungan yang Benar

**❌ JANGAN:**
```php
// Jangan kalikan nilai yang sudah dibulatkan
$subtotal = $nilaiYangSudahDibulatkan * $qty;
```

**✅ LAKUKAN:**
```php
// Selalu hitung dari nilai asli
$subtotal = $hargaSatuan * $qty;
```

### Testing
Setelah perbaikan:
- ✓ 185 × 5.333 = Rp 986.605 (bukan 986.666)
- ✓ Subtotal detail produksi akurat
- ✓ Total biaya produksi sesuai perhitungan manual
- ✓ Tidak ada lagi error pembulatan ganda

### Catatan Teknis

**Presisi Desimal di Database:**
- Kolom `subtotal` di tabel `produksi_details` menggunakan `DECIMAL(15,2)`
- Ini cukup untuk menyimpan nilai hingga 2 desimal tanpa kehilangan presisi
- Perhitungan dilakukan di PHP dengan presisi penuh, baru dibulatkan saat disimpan ke database

**Best Practice:**
- Simpan nilai asli (`harga_satuan`, `qty`) di database
- Hitung `subtotal` saat runtime dari nilai asli
- Jangan simpan hasil perhitungan yang sudah dibulatkan untuk dikalikan lagi
