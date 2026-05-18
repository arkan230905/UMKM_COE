# Logika Stok Baru - REPLACE bukan ADD

## Masalah Sebelumnya
Ketika edit stok dari 24 ke 12, sistem menghitung: 24 + 12 = 36 (SALAH!)

## Solusi Baru
Ketika edit stok dari 24 ke 12, sistem langsung set: stok = 12 (BENAR!)

## Cara Kerja

### 1. **Penyimpanan Stok**
- Stok disimpan di field `saldo_awal` di tabel `bahan_bakus` dan `bahan_pendukungs`
- Tidak ada StockMovement untuk initial stock atau manual adjustment
- StockMovement hanya untuk transaksi pembelian/produksi

### 2. **Perhitungan Stok Real-Time**
```php
public function getStokRealTimeAttribute()
{
    // Hitung movements (EXCLUDE stock_adjustment)
    $stockIn = StockMovement::where('ref_type', '!=', 'stock_adjustment')
        ->where('direction', 'in')
        ->sum('qty');
    
    $stockOut = StockMovement::where('ref_type', '!=', 'stock_adjustment')
        ->where('direction', 'out')
        ->sum('qty');
    
    $netStockFromMovements = $stockIn - $stockOut;
    
    // Stok = saldo_awal + movements dari transaksi
    return $saldoAwal + $netStockFromMovements;
}
```

### 3. **Saat Create Bahan Baku/Pendukung**
- Input stok = 12
- Simpan ke `saldo_awal` = 12
- TIDAK buat StockMovement
- Tampilan stok = 12 ✅

### 4. **Saat Edit Stok**
- Edit stok dari 24 ke 12
- Update `saldo_awal` = 12 (REPLACE, bukan ADD)
- TIDAK buat/hapus StockMovement
- Tampilan stok = 12 ✅

### 5. **Saat Ada Transaksi Pembelian**
- Stok awal = 12
- Pembelian = 10
- StockMovement dibuat dengan ref_type='pembelian'
- Tampilan stok = 12 + 10 = 22 ✅

### 6. **Saat Edit Stok Setelah Ada Transaksi**
- Stok saat ini = 22 (12 awal + 10 pembelian)
- Edit stok ke 30
- Update `saldo_awal` = 30
- StockMovement pembelian tetap ada (10)
- Tampilan stok = 30 + 10 = 40 ✅

## Keuntungan Logika Baru
1. ✅ Stok yang diinput = stok yang ditampilkan
2. ✅ Tidak ada penambahan yang tidak diinginkan
3. ✅ Transaksi pembelian/produksi tetap tercatat di StockMovement
4. ✅ Lebih mudah dipahami dan di-maintain

## File yang Diubah
1. `app/Models/BahanBaku.php` - getStokRealTimeAttribute()
2. `app/Models/BahanPendukung.php` - getStokRealTimeAttribute()
3. `app/Http/Controllers/BahanBakuController.php` - store() dan update()
4. `app/Http/Controllers/BahanPendukungController.php` - store() dan update()
